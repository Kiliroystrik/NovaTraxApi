<?php

namespace App\Controller\API;

use App\Entity\Driver;
use App\Repository\DriverRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DriverController extends AbstractController
{
    #[Route("/api/drivers", methods: ["GET"])]
    public function getDrivers(DriverRepository $driverRepository, Request $request): JsonResponse
    {
        $qb = $driverRepository->createQueryBuilder('o')->orderBy('o.createdAt', 'DESC');

        // Adapter pour la pagination
        $paginator = new Pagerfanta(new QueryAdapter($qb));
        $paginator->setMaxPerPage($request->query->getInt('limit', 10));
        $paginator->setCurrentPage($request->query->getInt('page', 1));

        return $this->json([
            'items' => iterator_to_array($paginator->getCurrentPageResults()),
            'totalItems' => $paginator->getNbResults(),
            'currentPage' => $paginator->getCurrentPage(),
            'totalPages' => $paginator->getNbPages(),
        ], 200, [], ['groups' => ['driver:list']]);
    }

    #[Route("/api/drivers/{id}", methods: ["GET"])]
    public function getDriver(DriverRepository $driverRepository, int $id): JsonResponse
    {
        $driver = $driverRepository->find($id);
        if (!$driver) {
            return new JsonResponse(['error' => 'Driver not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($driver, 200, [], ['groups' => ['driver:read']]);
    }

    #[Route("/api/drivers", methods: ["POST"])]
    public function createDriver(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            // Récupérer l'utilisateur actuellement connecté
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
            }

            // Récupérer la compagnie associée à l'utilisateur
            $driverCompany = $user->getCompany();
            if (!$driverCompany) {
                throw new \Exception('No company associated with the user');
            }

            // Désérialiser les données envoyées dans la requête POST
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Créer un nouveau driver
            $driver = new Driver();
            $driver->setCompany($driverCompany);
            $driver->setFirstName($data['firstName']);
            $driver->setLastName($data['lastName']);
            $driver->setLicenseNumber($data['licenseNumber']);

            // Persister la nouvelle commande dans la base de données
            $entityManager->persist($driver);
            $entityManager->flush();

            // Renvoyer la commande créée avec les groupes appropriés
            return $this->json($driver, Response::HTTP_CREATED, [], ['groups' => ['driver:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route("/api/drivers/{id}", methods: ["PUT", "PATCH"])]
    public function updateDriver(int $id, Request $request, EntityManagerInterface $entityManager, DriverRepository $driverRepository): JsonResponse
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
            $driver = $driverRepository->find($id);
            if (!$driver || $driver->getCompany() !== $userCompany) {
                return new JsonResponse(['error' => 'Driver not found or does not belong to your company'], Response::HTTP_NOT_FOUND);
            }

            // Désérialiser les données de la requête
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Mettre à jour les champs si des données sont fournies
            if (isset($data['firstName'])) {
                $driver->setFirstName($data['firstName']);
            }
            if (isset($data['lastName'])) {
                $driver->setLastName($data['lastName']);
            }
            if (isset($data['licenseNumber'])) {
                $driver->setLicenseNumber($data['licenseNumber']);
            }

            // Mettre à jour updatedAt
            $driver->setUpdatedAt(new \DateTimeImmutable());

            // Persister les changements
            $entityManager->flush();

            // Retourner la commande mise à jour
            return $this->json($driver, Response::HTTP_OK, [], ['groups' => ['driver:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }


    #[Route("/api/drivers/{id}", methods: ["DELETE"])]
    public function deleteDriver(int $id, EntityManagerInterface $entityManager, DriverRepository $driverRepository): JsonResponse
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
            $driver = $driverRepository->find($id);
            if (!$driver || $driver->getCompany() !== $userCompany) {
                return new JsonResponse(['error' => 'Driver not found or does not belong to your company'], Response::HTTP_NOT_FOUND);
            }

            // Supprimer la commande
            $entityManager->remove($driver);
            $entityManager->flush();

            // Retourner une réponse de succès
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
