<?php

namespace App\Controller\API;

use App\Entity\ClientOrder;
use App\Entity\User;
use App\Enum\StatusName;
use App\Enum\StatusType;
use App\Repository\ClientOrderRepository;
use App\Repository\ClientRepository;
use App\Service\SerialNumberGeneratorService;
use App\Service\StatusService;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientOrderController extends AbstractController
{
    private StatusService $statusService;
    private SerialNumberGeneratorService $serialNumberGeneratorService;

    public function __construct(StatusService $statusService, SerialNumberGeneratorService $serialNumberGeneratorService)
    {
        $this->statusService = $statusService;
        $this->serialNumberGeneratorService = $serialNumberGeneratorService;
    }

    #[Route("/api/orders", methods: ["GET"])]
    public function getClientOrders(ClientOrderRepository $clientOrderRepository, Request $request): JsonResponse
    {
        $qb = $clientOrderRepository->createQueryBuilder('o')->orderBy('o.createdAt', 'DESC');

        // Adapter pour la pagination
        $paginator = new Pagerfanta(new QueryAdapter($qb));
        $paginator->setMaxPerPage($request->query->getInt('limit', 10));
        $paginator->setCurrentPage($request->query->getInt('page', 1));

        return $this->json([
            'items' => iterator_to_array($paginator->getCurrentPageResults()),
            'totalItems' => $paginator->getNbResults(),
            'currentPage' => $paginator->getCurrentPage(),
            'totalPages' => $paginator->getNbPages(),
        ], 200, [], ['groups' => ['clientOrder:list']]);
    }

    #[Route("/api/orders/{id}", methods: ["GET"])]
    public function getClientOrder(ClientOrderRepository $clientOrderRepository, int $id): JsonResponse
    {
        $clientOrder = $clientOrderRepository->find($id);
        if (!$clientOrder) {
            return new JsonResponse(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($clientOrder, 200, [], ['groups' => ['clientOrder:read']]);
    }

    #[Route("/api/orders", methods: ["POST"])]
    public function createClientOrder(
        Request $request,
        EntityManagerInterface $entityManager,
        ClientRepository $clientRepository
    ): JsonResponse {
        try {
            // Récupérer l'utilisateur actuellement connecté
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
            }

            // Récupérer la compagnie associée à l'utilisateur
            $clientOrderCompany = $user->getCompany();
            if (!$clientOrderCompany) {
                throw new \Exception('No company associated with the user');
            }

            // Désérialiser les données envoyées dans la requête POST
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Récupérer le client à partir de son ID (assuré que 'client' est bien un ID dans les données)
            if (isset($data['client'])) {
                $client = $clientRepository->find($data['client']);
                if (!$client) {
                    throw new \Exception('Client not found');
                }
            } else {
                throw new \Exception('Client ID is required');
            }

            // Générer un numéro de commande client
            $clientOrderNumber = $this->serialNumberGeneratorService->generateOrderNumber();

            // Créer une nouvelle commande client
            $clientOrder = new ClientOrder();
            if (isset($data['expectedDeliveryDate']) && !empty($data['expectedDeliveryDate'])) {
                $clientOrder->setExpectedDeliveryDate(new \DateTimeImmutable($data['expectedDeliveryDate']));
            }
            $clientOrder->setOrderNumber($clientOrderNumber);

            // Remplacer l'assignation de statut par l'entité Status
            if (isset($data['status'])) {
                $statusName = $data['status'];

                // Utiliser le StatusService pour récupérer le statut
                $status = $this->statusService->getStatus(StatusType::CLIENT_ORDER, $statusName);

                $clientOrder->setStatus($status);
            } else {
                // Définir un statut par défaut si nécessaire, par exemple 'Pending'
                $status = $this->statusService->getStatus(StatusType::CLIENT_ORDER, StatusName::PENDING);
                $clientOrder->setStatus($status);
            }

            $clientOrder->setClient($client); // Associer le client trouvé à la commande
            $clientOrder->setCompany($clientOrderCompany);

            // Persister la nouvelle commande dans la base de données
            $entityManager->persist($clientOrder);
            $entityManager->flush();

            // Renvoyer la commande créée avec les groupes appropriés
            return $this->json($clientOrder, Response::HTTP_CREATED, [], ['groups' => ['clientOrder:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route("/api/orders/{id}", methods: ["PUT", "PATCH"])]
    public function updateClientOrder(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ClientOrderRepository $clientOrderRepository,
        ClientRepository $clientRepository
    ): JsonResponse {
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
            $clientOrder = $clientOrderRepository->find($id);
            if (!$clientOrder || $clientOrder->getCompany() !== $userCompany) {
                return new JsonResponse(['error' => 'Order not found or does not belong to your company'], Response::HTTP_NOT_FOUND);
            }

            // Désérialiser les données de la requête
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON');
            }

            // Mettre à jour les champs
            if (isset($data['expectedDeliveryDate']) && !empty($data['expectedDeliveryDate'])) {
                $clientOrder->setExpectedDeliveryDate(new \DateTimeImmutable($data['expectedDeliveryDate']));
            } else {
                $clientOrder->setExpectedDeliveryDate(null);
            }

            if (isset($data['status'])) {
                $statusName = $data['status'];

                // Utiliser le StatusService pour récupérer le statut
                $status = $this->statusService->getStatus(StatusType::CLIENT_ORDER, $statusName);

                $clientOrder->setStatus($status);
            }

            if (isset($data['client'])) {
                $client = $clientRepository->find($data['client']);
                if (!$client || $client->getCompany() !== $userCompany) {
                    throw new \Exception('Client not found or does not belong to your company');
                }
                $clientOrder->setClient($client);
            }

            // Persister les changements
            $entityManager->flush();

            // Retourner la commande mise à jour
            return $this->json($clientOrder, Response::HTTP_OK, [], ['groups' => ['clientOrder:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route("/api/orders/{id}", methods: ["DELETE"])]
    public function deleteClientOrder(
        int $id,
        EntityManagerInterface $entityManager,
        ClientOrderRepository $clientOrderRepository
    ): JsonResponse {
        try {
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
            $clientOrder = $clientOrderRepository->find($id);
            if (!$clientOrder || $clientOrder->getCompany() !== $userCompany) {
                return new JsonResponse(['error' => 'Order not found or does not belong to your company'], Response::HTTP_NOT_FOUND);
            }

            // Supprimer la commande
            $entityManager->remove($clientOrder);
            $entityManager->flush();

            // Retourner une réponse de succès
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
