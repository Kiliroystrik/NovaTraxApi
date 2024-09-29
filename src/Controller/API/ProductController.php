<?php

namespace App\Controller\API;

use App\Entity\ClientProduct;
use App\Repository\ProductRepository;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\Entity\Client;
use App\Entity\Product;
use App\Entity\UnitOfMeasure;
use App\Service\ClientProductNumberGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route("/api/products", methods: ["GET"])]
    public function getProducts(ProductRepository $productRepository, Request $request): JsonResponse
    {
        $qb = $productRepository->createQueryBuilder('o')->orderBy('o.createdAt', 'DESC');

        // Adapter pour la pagination
        $paginator = new Pagerfanta(new QueryAdapter($qb));
        $paginator->setMaxPerPage($request->query->getInt('limit', 10));
        $paginator->setCurrentPage($request->query->getInt('page', 1));

        return $this->json([
            'items' => iterator_to_array($paginator->getCurrentPageResults()),
            'totalItems' => $paginator->getNbResults(),
            'currentPage' => $paginator->getCurrentPage(),
            'totalPages' => $paginator->getNbPages(),
        ], 200, [], ['groups' => ['product:list']]);
    }

    #[Route("/api/products/{id}", methods: ["GET"])]
    public function getProduct(ProductRepository $productRepository, int $id): JsonResponse
    {
        $product = $productRepository->find($id);
        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($product, 200, [], ['groups' => ['product:read']]);
    }

    #[Route("/api/products", methods: ["POST"])]
    public function createProduct(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            // Récupérer l'utilisateur actuellement connecté
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
            }

            // Récupérer la compagnie associée à l'utilisateur
            $productCompany = $user->getCompany();
            if (!$productCompany) {
                throw new \Exception('No company associated with the user');
            }

            // Désérialiser les données envoyées dans la requête POST
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Créer un nouveau product
            $product = new Product();
            $product->setCompany($productCompany);
            $product->setName($data['name']);
            $product->setDescription($data['description']);

            $unitOfMeasure = $entityManager->getRepository(UnitOfMeasure::class)->find($data['unitOfMeasure']);

            $product->setUnitOfMeasure($unitOfMeasure);

            // Persister la nouvelle commande dans la base de données
            $entityManager->persist($product);
            $entityManager->flush();

            // Renvoyer la commande créée avec les groupes appropriés
            return $this->json($product, Response::HTTP_CREATED, [], ['groups' => ['product:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route("/api/products/{id}", methods: ["PUT", "PATCH"])]
    public function updateProduct(int $id, Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository): JsonResponse
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
            $product = $productRepository->find($id);
            if (!$product || $product->getCompany() !== $userCompany) {
                return new JsonResponse(['error' => 'Product not found or does not belong to your company'], Response::HTTP_NOT_FOUND);
            }

            // Désérialiser les données de la requête
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Mettre à jour les champs si des données sont fournies
            if (isset($data['name'])) {
                $product->setName($data['name']);
            }
            if (isset($data['description'])) {
                $product->setDescription($data['description']);
            }
            if (isset($data['unitOfMeasure'])) {
                $unitOfMeasure = $entityManager->getRepository(UnitOfMeasure::class)->find($data['unitOfMeasure']);
                $product->setUnitOfMeasure($unitOfMeasure);
            }

            // Persister les changements
            $entityManager->flush();

            // Retourner la commande mise à jour
            return $this->json($product, Response::HTTP_OK, [], ['groups' => ['product:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }


    #[Route("/api/products/{id}", methods: ["DELETE"])]
    public function deleteProduct(int $id, EntityManagerInterface $entityManager, ProductRepository $productRepository): JsonResponse
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
            $product = $productRepository->find($id);
            if (!$product || $product->getCompany() !== $userCompany) {
                return new JsonResponse(['error' => 'Product not found or does not belong to your company'], Response::HTTP_NOT_FOUND);
            }

            // Supprimer la commande
            $entityManager->remove($product);
            $entityManager->flush();

            // Retourner une réponse de succès
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
