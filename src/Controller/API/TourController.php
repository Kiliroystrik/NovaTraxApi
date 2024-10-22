<?php

namespace App\Controller\API;

use App\Entity\Delivery;
use App\Entity\Driver;
use App\Entity\LiquidProduct;
use App\Entity\Product;
use App\Entity\Status;
use App\Entity\Tour;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\Warehouse;
use App\Enum\StatusType;
use App\Enum\StatusName;
use App\Repository\StatusRepository;
use App\Repository\TourRepository;
use App\Service\VolumeCalculatorService;
use App\Service\StatusService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TourController extends AbstractController
{
    private VolumeCalculatorService $volumeCalculator;
    private StatusService $statusService;

    public function __construct(VolumeCalculatorService $volumeCalculator, StatusService $statusService)
    {
        $this->volumeCalculator = $volumeCalculator;
        $this->statusService = $statusService;
    }

    #[Route("/api/tours", methods: ["GET"])]
    public function getTours(TourRepository $tourRepository, Request $request, StatusRepository $statusRepository): JsonResponse
    {
        $qb = $tourRepository->createQueryBuilder('o')->orderBy('o.createdAt', 'DESC');

        // Récupérer le paramètre 'status' de la requête
        $statusName = $request->query->get('status');
        if ($statusName) {
            // Utilisation dynamique de l'énum StatusName pour les tournées
            $tourStatuses = [
                StatusName::PLANNED,
                StatusName::IN_PROGRESS,
                StatusName::COMPLETED_TOUR,
                StatusName::CANCELLED_TOUR,
            ];

            if (!in_array($statusName, $tourStatuses, true)) {
                return new JsonResponse(['error' => 'Invalid status for tours'], Response::HTTP_BAD_REQUEST);
            }

            // Récupérer l'objet `Status` en fonction du nom du statut et du type 'Tour'
            $status = $statusRepository->findOneBy([
                'name' => $statusName,
                'type' => StatusType::TOUR
            ]);

            if (!$status) {
                return new JsonResponse(['error' => 'Status not found'], Response::HTTP_NOT_FOUND);
            }

            // Ajouter une condition de filtrage par statut dans la requête
            $qb->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        // Adapter pour la pagination
        $paginator = new Pagerfanta(new QueryAdapter($qb));
        $paginator->setMaxPerPage($request->query->getInt('limit', 10));
        $paginator->setCurrentPage($request->query->getInt('page', 1));

        $tours = iterator_to_array($paginator->getCurrentPageResults());

        // Parcourir chaque tour pour calculer et définir les volumes des produits
        foreach ($tours as $tour) {
            $this->calculateVolumesForTour($tour);
        }

        return $this->json([
            'items' => $tours,
            'totalItems' => $paginator->getNbResults(),
            'currentPage' => $paginator->getCurrentPage(),
            'totalPages' => $paginator->getNbPages(),
        ], 200, [], ['groups' => ['tour:list']]);
    }



    #[Route("/api/tours/{id}", methods: ["GET"])]
    public function getTour(TourRepository $tourRepository, int $id): JsonResponse
    {
        $tour = $tourRepository->find($id);
        if (!$tour) {
            return new JsonResponse(['error' => 'Tour not found'], Response::HTTP_NOT_FOUND);
        }

        // Calculer les volumes pour les produits dans le tour
        $this->calculateVolumesForTour($tour);

        return $this->json($tour, 200, [], ['groups' => ['tour:read']]);
    }

    #[Route("/api/tours", methods: ["POST"])]
    public function createTour(Request $request, EntityManagerInterface $entityManager, StatusRepository $statusRepository): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
            }

            $tourCompany = $user->getCompany();
            if (!$tourCompany) {
                throw new \Exception('No company associated with the user');
            }

            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Créer un nouveau tour
            $tour = new Tour();
            $tour->setCompany($tourCompany);

            if (isset($data['tour_number'])) {
                $tour->setTourNumber($data['tour_number']);
            }

            if (isset($data['status'])) {
                $status = $entityManager->getRepository(Status::class)->find((int) $data['status']);
                if (!$status || $status->getType() !== StatusType::TOUR) {
                    throw new \Exception("Invalid status");
                }
                $tour->setStatus($status);
            }

            // Définission des livraisons si fournies et mise à jour du statut des livraisons
            $startDate = null;
            $endDate = null;
            if (isset($data['deliveries'])) {
                $scheduledStatus = $statusRepository->findOneBy(['name' => StatusName::SCHEDULED]);

                foreach ($data['deliveries'] as $deliveryData) {
                    $delivery = $entityManager->getRepository(Delivery::class)->find($deliveryData['id']);
                    if (!$delivery) {
                        throw new \Exception('Delivery not found');
                    }

                    // Ajouter la livraison à la tournée et mettre à jour les dates
                    $tour->addDelivery($delivery);

                    $deliveryDate = $delivery->getExpectedDeliveryDate();
                    if (!$startDate || $deliveryDate < $startDate) {
                        $startDate = $deliveryDate;
                    }
                    if (!$endDate || $deliveryDate > $endDate) {
                        $endDate = $deliveryDate;
                    }

                    // Si la livraison n'était pas déjà assignée à cette tournée, la marquer comme "Scheduled"
                    if ($delivery->getStatus()->getName() !== StatusName::SCHEDULED) {
                        $delivery->setStatus($scheduledStatus);
                    }
                }
            }

            // Mise à jour des dates de la tournée en fonction des livraisons
            if ($startDate && $endDate) {
                $tour->setStartDate($startDate);
                $tour->setEndDate($endDate);
            }

            $entityManager->persist($tour);
            $entityManager->flush();

            return $this->json($tour, Response::HTTP_CREATED, [], ['groups' => ['tour:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }



    #[Route("/api/tours/{id}", methods: ["PUT", "PATCH"])]
    public function updateTour(int $id, Request $request, EntityManagerInterface $entityManager, TourRepository $tourRepository, StatusRepository $statusRepository): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
            }

            $userCompany = $user->getCompany();
            if (!$userCompany) {
                throw new \Exception('No company associated with the user');
            }

            $tour = $tourRepository->find($id);
            if (!$tour || $tour->getCompany() !== $userCompany) {
                return new JsonResponse(['error' => 'Tour not found or does not belong to your company'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Mettre à jour les champs de la tournée
            if (isset($data['tour_number'])) {
                $tour->setTourNumber($data['tour_number']);
            }

            if (isset($data['status'])) {
                $status = $entityManager->getRepository(Status::class)->find((int) $data['status']);
                if (!$status || $status->getType() !== StatusType::TOUR) {
                    throw new \Exception("Invalid status");
                }
                $tour->setStatus($status);
            }

            // Définission des livraisons si fournies et mise à jour du statut des livraisons
            $startDate = null;
            $endDate = null;
            if (isset($data['deliveries'])) {
                $scheduledStatus = $statusRepository->findOneBy(['name' => StatusName::SCHEDULED]);

                foreach ($data['deliveries'] as $deliveryData) {
                    $delivery = $entityManager->getRepository(Delivery::class)->find($deliveryData['id']);
                    if (!$delivery) {
                        throw new \Exception('Delivery not found');
                    }

                    // Ajouter la livraison à la tournée et mettre à jour les dates
                    $tour->addDelivery($delivery);

                    $deliveryDate = $delivery->getExpectedDeliveryDate();
                    if (!$startDate || $deliveryDate < $startDate) {
                        $startDate = $deliveryDate;
                    }
                    if (!$endDate || $deliveryDate > $endDate) {
                        $endDate = $deliveryDate;
                    }

                    // Si la livraison n'était pas déjà assignée à cette tournée, la marquer comme "Scheduled"
                    if ($delivery->getStatus()->getName() !== StatusName::SCHEDULED) {
                        $delivery->setStatus($scheduledStatus);
                    }
                }
            }

            // Mise à jour des dates de la tournée en fonction des livraisons
            if ($startDate && $endDate) {
                $tour->setStartDate($startDate);
                $tour->setEndDate($endDate);
            }

            $tour->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->json($tour, Response::HTTP_OK, [], ['groups' => ['tour:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }


    #[Route("/api/tours/{id}", methods: ["DELETE"])]
    public function deleteTour(int $id, EntityManagerInterface $entityManager, TourRepository $tourRepository): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
            }

            $userCompany = $user->getCompany();
            if (!$userCompany) {
                throw new \Exception('No company associated with the user');
            }

            $tour = $tourRepository->find($id);
            if (!$tour || $tour->getCompany() !== $userCompany) {
                return new JsonResponse(['error' => 'Tour not found or does not belong to your company'], Response::HTTP_NOT_FOUND);
            }

            $entityManager->remove($tour);
            $entityManager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Calculer les volumes pour chaque produit dans une tournée.
     */
    private function calculateVolumesForTour(Tour $tour): void
    {
        foreach ($tour->getDeliveries() as $delivery) {
            foreach ($delivery->getProductDeliveries() as $productDelivery) {
                $product = $productDelivery->getProduct();
                if ($product) {
                    // Calculer le volume théorique via le service dédié
                    $calculatedVolume = $this->volumeCalculator->calculateVolume($product);

                    // Si le produit est sensible à la température et que la température est fournie
                    if ($product instanceof LiquidProduct && $product->isTemperatureSensitive()) {
                        $temperature = $productDelivery->getTemperature();
                        if ($temperature !== null) {
                            $calculatedVolume = $this->volumeCalculator->calculateAdjustedVolume(
                                $product,
                                $calculatedVolume,
                                $temperature
                            );
                        }
                    }

                    // Ici, utiliser une propriété existante ou ajouter une méthode pour stocker le volume calculé
                    // Par exemple, si ProductDelivery a une propriété `calculatedVolume`
                    // $productDelivery->setCalculatedVolume($calculatedVolume);
                }
            }
        }
    }
}
