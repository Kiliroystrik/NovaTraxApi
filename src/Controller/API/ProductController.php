<?php

namespace App\Controller\API;

use App\Entity\LiquidProduct;
use App\Entity\SolidProduct;
use App\Repository\ProductRepository;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\Entity\Client;
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
        $qb = $productRepository->createQueryBuilder('o');

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
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
            }

            $productCompany = $user->getCompany();
            if (!$productCompany) {
                throw new \Exception('No company associated with the user');
            }

            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Choisir le type de produit en fonction des proprétés recues
            if (isset($data['densityKgPerLiter']) && isset($data['isTemperatureSensitive'])) {
                // Si les données contiennent une densité et des infos sur la température, c'est un produit liquide
                $product = new LiquidProduct();
                $product->setDensityKgPerLiter($data['densityKgPerLiter']);
                $product->setIsTemperatureSensitive($data['isTemperatureSensitive']);
                $product->setThermalExpansionCoefficientPerDegreeCelsius($data['thermalExpansionCoefficientPerDegreeCelsius'] ?? null);
            } elseif (isset($data['lengthCm']) && isset($data['widthCm']) && isset($data['heightCm'])) {
                // Si les données contiennent des dimensions, c'est un produit solide
                $product = new SolidProduct();
                $product->setLengthCm($data['lengthCm']);
                $product->setWidthCm($data['widthCm']);
                $product->setHeightCm($data['heightCm']);
            } else {
                throw new \Exception('Unsupported product type or missing data');
            }

            $product->setCompany($productCompany);
            $product->setName($data['name']);
            $product->setDescription($data['description']);
            $product->setWeightKg($data['weight'] ?? null);

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->json($product, Response::HTTP_CREATED, [], ['groups' => ['product:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route("/api/products/{id}", methods: ["PUT", "PATCH"])]
    public function updateProduct(int $id, Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository): JsonResponse
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

            $product = $productRepository->find($id);
            if (!$product || $product->getCompany() !== $userCompany) {
                return new JsonResponse(['error' => 'Product not found or does not belong to your company'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            $product->setName($data['name'] ?? $product->getName());
            $product->setDescription($data['description'] ?? $product->getDescription());
            $product->setWeightKg($data['weight'] ?? $product->getWeightKg());

            if ($product instanceof LiquidProduct) {
                $product->setDensityKgPerLiter($data['densityKgPerLiter'] ?? $product->getDensityKgPerLiter());
                $product->setIsTemperatureSensitive($data['isTemperatureSensitive'] ?? $product->isTemperatureSensitive());
                $product->setThermalExpansionCoefficientPerDegreeCelsius($data['thermalExpansionCoefficientPerDegreeCelsius'] ?? $product->getThermalExpansionCoefficientPerDegreeCelsius());
            } elseif ($product instanceof SolidProduct) {
                $product->setLengthCm($data['lengthCm'] ?? $product->getLengthCm());
                $product->setWidthCm($data['widthCm'] ?? $product->getWidthCm());
                $product->setHeightCm($data['heightCm'] ?? $product->getHeightCm());
            }

            $product->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->json($product, Response::HTTP_OK, [], ['groups' => ['product:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route("/api/products/{id}", methods: ["DELETE"])]
    public function deleteProduct(int $id, EntityManagerInterface $entityManager, ProductRepository $productRepository): JsonResponse
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

            $product = $productRepository->find($id);
            if (!$product || $product->getCompany() !== $userCompany) {
                return new JsonResponse(['error' => 'Product not found or does not belong to your company'], Response::HTTP_NOT_FOUND);
            }

            $entityManager->remove($product);
            $entityManager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
