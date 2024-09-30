<?php

namespace App\Controller\API;

use App\Entity\Vehicle;
use App\Repository\VehicleRepository;
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

class VehicleController extends AbstractController
{
    #[Route("/api/vehicles", methods: ["GET"])]
    public function getVehicles(VehicleRepository $vehicleRepository, Request $request): JsonResponse
    {
        $qb = $vehicleRepository->createQueryBuilder('o')->orderBy('o.createdAt', 'DESC');

        // Adapter pour la pagination
        $paginator = new Pagerfanta(new QueryAdapter($qb));
        $paginator->setMaxPerPage($request->query->getInt('limit', 10));
        $paginator->setCurrentPage($request->query->getInt('page', 1));

        return $this->json([
            'items' => iterator_to_array($paginator->getCurrentPageResults()),
            'totalItems' => $paginator->getNbResults(),
            'currentPage' => $paginator->getCurrentPage(),
            'totalPages' => $paginator->getNbPages(),
        ], 200, [], ['groups' => ['vehicle:list']]);
    }

    #[Route("/api/vehicles/{id}", methods: ["GET"])]
    public function getVehicle(VehicleRepository $vehicleRepository, int $id): JsonResponse
    {
        $vehicle = $vehicleRepository->find($id);
        if (!$vehicle) {
            return new JsonResponse(['error' => 'Vehicle not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($vehicle, 200, [], ['groups' => ['vehicle:read']]);
    }

    #[Route("/api/vehicles", methods: ["POST"])]
    public function createVehicle(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            // Récupérer l'utilisateur actuellement connecté
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
            }

            // Récupérer la compagnie associée à l'utilisateur
            $vehicleCompany = $user->getCompany();
            if (!$vehicleCompany) {
                throw new \Exception('No company associated with the user');
            }

            // Désérialiser les données envoyées dans la requête POST
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Créer un nouveau vehicle
            $vehicle = new Vehicle();
            $vehicle->setCompany($vehicleCompany);
            $vehicle->setCapacity($data['capacity']);
            $vehicle->setLicensePlate($data['licensePlate']);
            $vehicle->setModel($data['model']);
            $vehicle->setType($data['type']);

            // Persister la nouvelle commande dans la base de données
            $entityManager->persist($vehicle);
            $entityManager->flush();

            // Renvoyer la commande créée avec les groupes appropriés
            return $this->json($vehicle, Response::HTTP_CREATED, [], ['groups' => ['vehicle:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route("/api/vehicles/{id}", methods: ["PUT", "PATCH"])]
    public function updateVehicle(int $id, Request $request, EntityManagerInterface $entityManager, VehicleRepository $vehicleRepository): JsonResponse
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
            $vehicle = $vehicleRepository->find($id);
            if (!$vehicle || $vehicle->getCompany() !== $userCompany) {
                return new JsonResponse(['error' => 'Vehicle not found or does not belong to your company'], Response::HTTP_NOT_FOUND);
            }

            // Désérialiser les données de la requête
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Mettre à jour les champs si des données sont fournies
            if (isset($data['capacity'])) {
                $vehicle->setCapacity($data['capacity']);
            }
            if (isset($data['licensePlate'])) {
                $vehicle->setLicensePlate($data['licensePlate']);
            }
            if (isset($data['model'])) {
                $vehicle->setModel($data['model']);
            }
            if (isset($data['type'])) {
                $vehicle->setType($data['type']);
            }

            // Mettre à jour updatedAt
            $vehicle->setUpdatedAt(new \DateTimeImmutable());

            // Persister les changements
            $entityManager->flush();

            // Retourner la commande mise à jour
            return $this->json($vehicle, Response::HTTP_OK, [], ['groups' => ['vehicle:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }


    #[Route("/api/vehicles/{id}", methods: ["DELETE"])]
    public function deleteVehicle(int $id, EntityManagerInterface $entityManager, VehicleRepository $vehicleRepository): JsonResponse
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
            $vehicle = $vehicleRepository->find($id);
            if (!$vehicle || $vehicle->getCompany() !== $userCompany) {
                return new JsonResponse(['error' => 'Vehicle not found or does not belong to your company'], Response::HTTP_NOT_FOUND);
            }

            // Supprimer la commande
            $entityManager->remove($vehicle);
            $entityManager->flush();

            // Retourner une réponse de succès
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
