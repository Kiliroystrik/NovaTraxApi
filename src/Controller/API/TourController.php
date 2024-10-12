<?php

namespace App\Controller\API;

use App\Entity\Tour;
use App\Repository\TourRepository;
use App\Repository\UserRepository;
use App\Entity\UnitOfMeasure;
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
    #[Route("/api/tours", methods: ["GET"])]
    public function getTours(TourRepository $tourRepository, Request $request): JsonResponse
    {
        $qb = $tourRepository->createQueryBuilder('o')->orderBy('o.createdAt', 'DESC');

        // Adapter pour la pagination
        $paginator = new Pagerfanta(new QueryAdapter($qb));
        $paginator->setMaxPerPage($request->query->getInt('limit', 10));
        $paginator->setCurrentPage($request->query->getInt('page', 1));

        return $this->json([
            'items' => iterator_to_array($paginator->getCurrentPageResults()),
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

        return $this->json($tour, 200, [], ['groups' => ['tour:read']]);
    }

    #[Route("/api/tours", methods: ["POST"])]
    public function createTour(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            // Récupérer l'utilisateur actuellement connecté
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
            }

            // Récupérer la compagnie associée à l'utilisateur
            $tourCompany = $user->getCompany();
            if (!$tourCompany) {
                throw new \Exception('No company associated with the user');
            }

            // Désérialiser les données envoyées dans la requête POST
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Créer un nouveau tour
            $tour = new Tour();
            $tour->setCompany($tourCompany);


            // Persister la nouvelle commande dans la base de données
            $entityManager->persist($tour);
            $entityManager->flush();

            // Renvoyer la commande créée avec les groupes appropriés
            return $this->json($tour, Response::HTTP_CREATED, [], ['groups' => ['tour:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route("/api/tours/{id}", methods: ["PUT", "PATCH"])]
    public function updateTour(int $id, Request $request, EntityManagerInterface $entityManager, TourRepository $tourRepository): JsonResponse
    {
        try {
            // Récupérer l'utilisateur actuellement connecté
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
            }

            // Récupérer la compagnie associée à l'utilisateur
            $userCompany = $user->getCompany();
            if (!$userCompany) {
                throw new \Exception('No company associated with the user');
            }

            // Récupérer la commande par son ID et vérifier qu'elle appartient à la même compagnie
            $tour = $tourRepository->find($id);
            if (!$tour || $tour->getCompany() !== $userCompany) {
                return new JsonResponse(['error' => 'Tour not found or does not belong to your company'], Response::HTTP_NOT_FOUND);
            }

            // Désérialiser les données de la requête
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Mettre à jour les champs si des données sont fournies
            if (isset($data['capacity'])) {
                $tour->setCapacity($data['capacity']);
            }
            if (isset($data['licensePlate'])) {
                $tour->setLicensePlate($data['licensePlate']);
            }
            if (isset($data['model'])) {
                $tour->setModel($data['model']);
            }
            if (isset($data['type'])) {
                $tour->setType($data['type']);
            }

            // Mettre à jour updatedAt
            $tour->setUpdatedAt(new \DateTimeImmutable());

            // Persister les changements
            $entityManager->flush();

            // Retourner la commande mise à jour
            return $this->json($tour, Response::HTTP_OK, [], ['groups' => ['tour:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }


    #[Route("/api/tours/{id}", methods: ["DELETE"])]
    public function deleteTour(int $id, EntityManagerInterface $entityManager, TourRepository $tourRepository): JsonResponse
    {
        try {
            // Récupérer l'utilisateur actuellement connecté
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
            }

            // Récupérer la compagnie associée à l'utilisateur
            $userCompany = $user->getCompany();
            if (!$userCompany) {
                throw new \Exception('No company associated with the user');
            }

            // Récupérer la commande par son ID et vérifier qu'elle appartient à la même compagnie
            $tour = $tourRepository->find($id);
            if (!$tour || $tour->getCompany() !== $userCompany) {
                return new JsonResponse(['error' => 'Tour not found or does not belong to your company'], Response::HTTP_NOT_FOUND);
            }

            // Supprimer la commande
            $entityManager->remove($tour);
            $entityManager->flush();

            // Retourner une réponse de succès
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
