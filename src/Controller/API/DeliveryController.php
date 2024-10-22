<?php

namespace App\Controller\API;

use App\Entity\Delivery;
use App\Entity\DeliveryProduct;
use App\Repository\DeliveryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route; // Nécessaire pour les attributs
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/api/deliveries')]
class DeliveryController extends AbstractController
{
    private DeliveryRepository $deliveryRepository;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        DeliveryRepository $deliveryRepository,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->deliveryRepository = $deliveryRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('', name: 'get_deliveries', methods: ['GET'])]
    public function getDeliveries(Request $request): JsonResponse
    {
        // Récupérer les paramètres de pagination et de filtre
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 10)));
        $start = $request->query->get('start'); // Format attendu: 'YYYY-MM-DD'
        $end = $request->query->get('end'); // Format attendu: 'YYYY-MM-DD'

        $criteria = [];
        if ($start && $end) {
            try {
                $startDate = new \DateTimeImmutable($start);
                $endDate = new \DateTimeImmutable($end);
                $criteria['expectedDeliveryDate'] = [
                    'from' => $startDate->setTime(0, 0, 0),
                    'to' => $endDate->setTime(23, 59, 59)
                ];
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Invalid date format. Use YYYY-MM-DD.'], 400);
            }
        } elseif ($start || $end) {
            return new JsonResponse(['error' => 'Both start and end dates are required.'], 400);
        }

        // Obtenir le QueryBuilder du repository
        $qb = $this->deliveryRepository->getQueryByCriteria($criteria);

        // Configurer Pagefanta pour la pagination
        $adapter = new QueryAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        // Récupérer les résultats paginés
        $items = iterator_to_array($pagerfanta->getCurrentPageResults());

        // Sérialiser les données
        $data = $this->serializer->serialize($items, 'json', ['groups' => ['delivery:read']]);

        // Retourner la réponse avec les métadonnées de pagination
        return new JsonResponse([
            'items' => json_decode($data, true),
            'totalItems' => $pagerfanta->getNbResults(),
            'currentPage' => $pagerfanta->getCurrentPage(),
            'totalPages' => $pagerfanta->getNbPages(),
        ], 200);
    }


    #[Route('/{id}', name: 'get_delivery', methods: ['GET'])]
    public function getDelivery(int $id): JsonResponse
    {
        $delivery = $this->deliveryRepository->find($id);

        if (!$delivery) {
            return new JsonResponse(['error' => 'Delivery not found.'], 404);
        }

        return $this->json($delivery, 200, [], ['groups' => ['delivery:read']]);
    }

    #[Route('', name: 'create_delivery', methods: ['POST'])]
    public function createDelivery(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON.'], 400);
        }

        $delivery = new Delivery();

        // Remplir les champs de la livraison
        try {
            // Expected Delivery Date (Obligatoire)
            if (isset($data['expectedDeliveryDate'])) {
                $delivery->setExpectedDeliveryDate(new \DateTimeImmutable($data['expectedDeliveryDate']));
            } else {
                return new JsonResponse(['error' => 'expectedDeliveryDate is required.'], 400);
            }

            // Actual Delivery Date (Optionnel)
            if (isset($data['actualDeliveryDate'])) {
                if ($data['actualDeliveryDate'] === null) {
                    $delivery->setActualDeliveryDate(null);
                } else {
                    $delivery->setActualDeliveryDate(new \DateTimeImmutable($data['actualDeliveryDate']));
                }
            }

            // Associer la tournée si fournie (Optionnel)
            if (isset($data['tour'])) {
                $tour = $this->entityManager->getRepository(\App\Entity\Tour::class)->find($data['tour']);
                if (!$tour) {
                    return new JsonResponse(['error' => 'Tour not found.'], 404);
                }
                $delivery->setTour($tour);
            }

            // Associer la compagnie (Obligatoire)
            if (isset($data['company'])) {
                $company = $this->entityManager->getRepository(\App\Entity\Company::class)->find($data['company']);
                if (!$company) {
                    return new JsonResponse(['error' => 'Company not found.'], 404);
                }
                $delivery->setCompany($company);
            } else {
                return new JsonResponse(['error' => 'Company is required.'], 400);
            }

            // Associer la commande client (Obligatoire)
            if (isset($data['clientOrder'])) {
                $clientOrder = $this->entityManager->getRepository(\App\Entity\ClientOrder::class)->find($data['clientOrder']);
                if (!$clientOrder) {
                    return new JsonResponse(['error' => 'ClientOrder not found.'], 404);
                }
                $delivery->setClientOrder($clientOrder);
            } else {
                return new JsonResponse(['error' => 'ClientOrder is required.'], 400);
            }

            // Associer l'adresse géocodée (Obligatoire)
            if (isset($data['geocodedAddress'])) {
                $geocodedAddress = $this->entityManager->getRepository(\App\Entity\GeocodedAddress::class)->find($data['geocodedAddress']);
                if (!$geocodedAddress) {
                    return new JsonResponse(['error' => 'GeocodedAddress not found.'], 404);
                }
                $delivery->setGeocodedAddress($geocodedAddress);
            } else {
                return new JsonResponse(['error' => 'GeocodedAddress is required.'], 400);
            }

            // Associer le statut (Obligatoire)
            if (isset($data['status'])) {
                $status = $this->entityManager->getRepository(\App\Entity\Status::class)->find($data['status']);
                if (!$status) {
                    return new JsonResponse(['error' => 'Status not found.'], 404);
                }
                $delivery->setStatus($status);
            } else {
                return new JsonResponse(['error' => 'Status is required.'], 400);
            }

            // Gérer les livraisons de produits (Optionnel)
            if (isset($data['productDeliveries']) && is_array($data['productDeliveries'])) {
                foreach ($data['productDeliveries'] as $pdData) {
                    $deliveryProduct = new DeliveryProduct();

                    // Valider et associer le produit
                    if (isset($pdData['product'])) {
                        $product = $this->entityManager->getRepository(\App\Entity\Product::class)->find($pdData['product']);
                        if (!$product) {
                            return new JsonResponse(['error' => 'Product not found.'], 404);
                        }
                        $deliveryProduct->setProduct($product);
                    } else {
                        return new JsonResponse(['error' => 'Product is required for DeliveryProduct.'], 400);
                    }

                    // Définir la quantité
                    if (isset($pdData['quantity'])) {
                        $deliveryProduct->setQuantity((int) $pdData['quantity']);
                    } else {
                        return new JsonResponse(['error' => 'Quantity is required for DeliveryProduct.'], 400);
                    }

                    // Ajouter d'autres champs si nécessaire (ex: température)
                    if (isset($pdData['temperature'])) {
                        $deliveryProduct->setTemperature((float) $pdData['temperature']);
                    }

                    // Associer à la livraison
                    $delivery->addProductDelivery($deliveryProduct);
                }
            }

            // Valider l'entité
            $errors = $this->validator->validate($delivery);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorMessages], 400);
            }

            // Persister la livraison
            $this->entityManager->persist($delivery);
            $this->entityManager->flush();

            // Sérialiser la réponse
            $data = $this->serializer->serialize($delivery, 'json', ['groups' => ['delivery:read']]);

            return new JsonResponse(json_decode($data, true), 201, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'update_delivery', methods: ['PUT', 'PATCH'])]
    public function updateDelivery(int $id, Request $request): JsonResponse
    {
        $delivery = $this->deliveryRepository->find($id);

        if (!$delivery) {
            return new JsonResponse(['error' => 'Delivery not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON.'], 400);
        }

        try {
            // Mettre à jour les champs de la livraison
            if (isset($data['expectedDeliveryDate'])) {
                $delivery->setExpectedDeliveryDate(new \DateTimeImmutable($data['expectedDeliveryDate']));
            }

            if (array_key_exists('actualDeliveryDate', $data)) { // Permet de mettre à jour à null
                if ($data['actualDeliveryDate']) {
                    $delivery->setActualDeliveryDate(new \DateTimeImmutable($data['actualDeliveryDate']));
                } else {
                    $delivery->setActualDeliveryDate(null);
                }
            }

            if (isset($data['tour'])) {
                $tour = $this->entityManager->getRepository(\App\Entity\Tour::class)->find($data['tour']);
                if (!$tour) {
                    return new JsonResponse(['error' => 'Tour not found.'], 404);
                }
                $delivery->setTour($tour);
            }

            if (isset($data['company'])) {
                $company = $this->entityManager->getRepository(\App\Entity\Company::class)->find($data['company']);
                if (!$company) {
                    return new JsonResponse(['error' => 'Company not found.'], 404);
                }
                $delivery->setCompany($company);
            }

            if (isset($data['clientOrder'])) {
                $clientOrder = $this->entityManager->getRepository(\App\Entity\ClientOrder::class)->find($data['clientOrder']);
                if (!$clientOrder) {
                    return new JsonResponse(['error' => 'ClientOrder not found.'], 404);
                }
                $delivery->setClientOrder($clientOrder);
            }

            if (isset($data['geocodedAddress'])) {
                $geocodedAddress = $this->entityManager->getRepository(\App\Entity\GeocodedAddress::class)->find($data['geocodedAddress']);
                if (!$geocodedAddress) {
                    return new JsonResponse(['error' => 'GeocodedAddress not found.'], 404);
                }
                $delivery->setGeocodedAddress($geocodedAddress);
            }

            if (isset($data['status'])) {
                $status = $this->entityManager->getRepository(\App\Entity\Status::class)->find($data['status']);
                if (!$status) {
                    return new JsonResponse(['error' => 'Status not found.'], 404);
                }
                $delivery->setStatus($status);
            }

            // Gérer les livraisons de produits
            if (isset($data['productDeliveries']) && is_array($data['productDeliveries'])) {
                // Remplacer les livraisons de produits existantes
                foreach ($delivery->getProductDeliveries() as $existingPD) {
                    $delivery->removeProductDelivery($existingPD);
                    $this->entityManager->remove($existingPD);
                }

                foreach ($data['productDeliveries'] as $pdData) {
                    $deliveryProduct = new DeliveryProduct();

                    // Valider et associer le produit
                    if (isset($pdData['product'])) {
                        $product = $this->entityManager->getRepository(\App\Entity\Product::class)->find($pdData['product']);
                        if (!$product) {
                            return new JsonResponse(['error' => 'Product not found.'], 404);
                        }
                        $deliveryProduct->setProduct($product);
                    } else {
                        return new JsonResponse(['error' => 'Product is required for DeliveryProduct.'], 400);
                    }

                    // Définir la quantité
                    if (isset($pdData['quantity'])) {
                        $deliveryProduct->setQuantity((int) $pdData['quantity']);
                    } else {
                        return new JsonResponse(['error' => 'Quantity is required for DeliveryProduct.'], 400);
                    }

                    // Ajouter d'autres champs si nécessaire (ex: température)
                    if (isset($pdData['temperature'])) {
                        $deliveryProduct->setTemperature((float) $pdData['temperature']);
                    }

                    // Associer à la livraison
                    $delivery->addProductDelivery($deliveryProduct);
                }
            }

            // Valider l'entité
            $errors = $this->validator->validate($delivery);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorMessages], 400);
            }

            // Mettre à jour l'entité
            $this->entityManager->flush();

            // Sérialiser la réponse
            $data = $this->serializer->serialize($delivery, 'json', ['groups' => ['delivery:read']]);

            return new JsonResponse(json_decode($data, true), 200, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'delete_delivery', methods: ['DELETE'])]
    public function deleteDelivery(int $id): JsonResponse
    {
        $delivery = $this->deliveryRepository->find($id);

        if (!$delivery) {
            return new JsonResponse(['error' => 'Delivery not found.'], 404);
        }

        try {
            $this->entityManager->remove($delivery);
            $this->entityManager->flush();

            return new JsonResponse(null, 204);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unable to delete delivery.'], 500);
        }
    }
}
