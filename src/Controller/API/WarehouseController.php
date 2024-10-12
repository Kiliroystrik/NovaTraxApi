<?php

namespace App\Controller\API;

use App\Entity\GeocodedAddress;
use App\Entity\Warehouse;
use App\Repository\WarehouseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class WarehouseController extends AbstractController
{
    #[Route("/api/warehouses", methods: ["GET"])]
    public function getWarehouses(WarehouseRepository $warehouseRepository, Request $request): JsonResponse
    {
        $qb = $warehouseRepository->createQueryBuilder('o');

        // Récupérer la limite passée dans la requête, avec une valeur par défaut de 10
        $limit = $request->query->getInt('limit', 10);
        $page = $request->query->getInt('page', 1);

        // Si la limite est définie à 0, désactiver la pagination
        if ($limit === 0) {
            $results = $qb->getQuery()->getResult();
            return $this->json([
                'items' => $results,
                'totalItems' => count($results),
            ], 200, [], ['groups' => ['warehouse:list']]);
        }

        // Si une limite est définie, utiliser la pagination
        $paginator = new Pagerfanta(new QueryAdapter($qb));
        $paginator->setMaxPerPage($limit);
        $paginator->setCurrentPage($page);

        return $this->json([
            'items' => iterator_to_array($paginator->getCurrentPageResults()),
            'totalItems' => $paginator->getNbResults(),
            'currentPage' => $paginator->getCurrentPage(),
            'totalPages' => $paginator->getNbPages(),
        ], 200, [], ['groups' => ['warehouse:list']]);
    }

    #[Route("/api/warehouses/{id}", methods: ["GET"])]
    public function getWarehouse(WarehouseRepository $warehouseRepository, int $id): JsonResponse
    {
        $warehouse = $warehouseRepository->find($id);
        if (!$warehouse) {
            return new JsonResponse(['error' => 'Unit of Measure not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($warehouse, 200, [], ['groups' => ['warehouse:read']]);
    }

    #[isGranted('ROLE_SUPER_ADMIN')]
    #[Route("/api/warehouses", methods: ["POST"])]
    public function createWarehouse(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            // Désérialiser les données envoyées dans la requête POST
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Créer une nouvelle unité de mesure
            $warehouse = new Warehouse();
            $warehouse->setName($data['name']);

            $geocodedAddress = new GeocodedAddress();
            $geocodedAddress->setLatitude($data['address']['latitude']);
            $geocodedAddress->setLongitude($data['address']['longitude']);
            $geocodedAddress->setCity($data['address']['city']);
            $geocodedAddress->setPostalCode($data['address']['postalCode']);
            $geocodedAddress->setDepartment($data['address']['department']);
            $geocodedAddress->setCountry($data['address']['country']);
            $geocodedAddress->setSource('api');
            $geocodedAddress->setStreetName($data['address']['streetName']);
            $geocodedAddress->setStreetNumber($data['address']['streetNumber']);
            $geocodedAddress->setCreatedAt(new \DateTimeImmutable());
            $geocodedAddress->setUpdatedAt(new \DateTimeImmutable());

            $warehouse->setAddress($geocodedAddress);

            // Sauvegarder l'unité de mesure dans la base de données
            $entityManager->persist($warehouse);
            $entityManager->flush();

            // Renvoyer la commande créée avec les groupes appropriés
            return $this->json($warehouse, Response::HTTP_CREATED, [], ['groups' => ['warehouse:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[isGranted('ROLE_SUPER_ADMIN')]
    #[Route("/api/warehouses/{id}", methods: ["PUT", "PATCH"])]
    public function updateWarehouse(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        WarehouseRepository $warehouseRepository
    ): JsonResponse {
        // Vérification des rôles
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        try {
            // Récupérer l'unité de mesure par son ID
            $warehouse = $warehouseRepository->find($id);
            if (!$warehouse) {
                return new JsonResponse(['error' => 'Unit of Measure not found'], Response::HTTP_NOT_FOUND);
            }

            // Désérialiser les données de la requête
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Mettre à jour les propriétés de l'unité de mesure
            if (isset($data['name'])) {
                $warehouse->setName($data['name']);
            }
            if (isset($data['symbol'])) {
                $warehouse->setSymbol($data['symbol']);
            }

            // Mise à jour automatique de la date
            $warehouse->setUpdatedAt(new \DateTimeImmutable());

            // Persister les changements dans la base de données
            $entityManager->flush();

            // Retourner l'unité de mesure mise à jour
            return $this->json($warehouse, Response::HTTP_OK, [], ['groups' => ['warehouse:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route("/api/warehouses/{id}", methods: ["DELETE"])]
    public function deleteWarehouse(int $id, EntityManagerInterface $entityManager, WarehouseRepository $warehouseRepository): JsonResponse
    {
        // Vérification des rôles
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        try {
            // Récupérer l'unité de mesure par son ID
            $warehouse = $warehouseRepository->find($id);
            if (!$warehouse) {
                return new JsonResponse(['error' => 'Unit of Measure not found'], Response::HTTP_NOT_FOUND);
            }

            // Supprimer l'unité de mesure
            $entityManager->remove($warehouse);
            $entityManager->flush();

            // Retourner une réponse de succès
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
