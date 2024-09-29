<?php

namespace App\Controller\API;

use App\Entity\UnitOfMeasure;
use App\Repository\UnitOfMeasureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UnitOfMeasureController extends AbstractController
{
    #[Route("/api/unit_of_measures", methods: ["GET"])]
    public function getUnitOfMeasures(UnitOfMeasureRepository $unitOfMeasureRepository, Request $request): JsonResponse
    {
        $qb = $unitOfMeasureRepository->createQueryBuilder('o')->orderBy('o.createdAt', 'DESC');

        // Récupérer la limite passée dans la requête, avec une valeur par défaut de 10
        $limit = $request->query->getInt('limit', 10);
        $page = $request->query->getInt('page', 1);

        // Si la limite est définie à 0, désactiver la pagination
        if ($limit === 0) {
            $results = $qb->getQuery()->getResult();
            return $this->json([
                'items' => $results,
                'totalItems' => count($results),
            ], 200, [], ['groups' => ['unitOfMeasure:list']]);
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
        ], 200, [], ['groups' => ['unitOfMeasure:list']]);
    }

    #[Route("/api/unit_of_measures/{id}", methods: ["GET"])]
    public function getUnitOfMeasure(UnitOfMeasureRepository $unitOfMeasureRepository, int $id): JsonResponse
    {
        $unitOfMeasure = $unitOfMeasureRepository->find($id);
        if (!$unitOfMeasure) {
            return new JsonResponse(['error' => 'Unit of Measure not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($unitOfMeasure, 200, [], ['groups' => ['unitOfMeasure:read']]);
    }

    #[isGranted('ROLE_SUPER_ADMIN')]
    #[Route("/api/unit_of_measures", methods: ["POST"])]
    public function createUnitOfMeasure(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            // Désérialiser les données envoyées dans la requête POST
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Créer une nouvelle unité de mesure
            $unitOfMeasure = new UnitOfMeasure();
            $unitOfMeasure->setName($data['name']);
            $unitOfMeasure->setSymbol($data['symbol']);

            // Sauvegarder l'unité de mesure dans la base de données
            $entityManager->persist($unitOfMeasure);
            $entityManager->flush();

            // Renvoyer la commande créée avec les groupes appropriés
            return $this->json($unitOfMeasure, Response::HTTP_CREATED, [], ['groups' => ['unitOfMeasure:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[isGranted('ROLE_SUPER_ADMIN')]
    #[Route("/api/unit_of_measures/{id}", methods: ["PUT", "PATCH"])]
    public function updateUnitOfMeasure(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        UnitOfMeasureRepository $unitOfMeasureRepository
    ): JsonResponse {
        // Vérification des rôles
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        try {
            // Récupérer l'unité de mesure par son ID
            $unitOfMeasure = $unitOfMeasureRepository->find($id);
            if (!$unitOfMeasure) {
                return new JsonResponse(['error' => 'Unit of Measure not found'], Response::HTTP_NOT_FOUND);
            }

            // Désérialiser les données de la requête
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Mettre à jour les propriétés de l'unité de mesure
            if (isset($data['name'])) {
                $unitOfMeasure->setName($data['name']);
            }
            if (isset($data['symbol'])) {
                $unitOfMeasure->setSymbol($data['symbol']);
            }

            // Mise à jour automatique de la date
            $unitOfMeasure->setUpdatedAt(new \DateTimeImmutable());

            // Persister les changements dans la base de données
            $entityManager->flush();

            // Retourner l'unité de mesure mise à jour
            return $this->json($unitOfMeasure, Response::HTTP_OK, [], ['groups' => ['unitOfMeasure:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route("/api/unit_of_measures/{id}", methods: ["DELETE"])]
    public function deleteUnitOfMeasure(int $id, EntityManagerInterface $entityManager, UnitOfMeasureRepository $unitOfMeasureRepository): JsonResponse
    {
        // Vérification des rôles
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        try {
            // Récupérer l'unité de mesure par son ID
            $unitOfMeasure = $unitOfMeasureRepository->find($id);
            if (!$unitOfMeasure) {
                return new JsonResponse(['error' => 'Unit of Measure not found'], Response::HTTP_NOT_FOUND);
            }

            // Supprimer l'unité de mesure
            $entityManager->remove($unitOfMeasure);
            $entityManager->flush();

            // Retourner une réponse de succès
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
