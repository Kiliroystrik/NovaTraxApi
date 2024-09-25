<?php

namespace App\Controller\API;

use App\Entity\ClientOrder;
use App\Repository\ClientOrderRepository;
use App\Repository\UserRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClientOrderController extends AbstractController
{
    #[Route("/api/client/orders", methods: ["GET"])]
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

    #[Route("/api/client/orders/{id}", methods: ["GET"])]
    public function getClientOrder(ClientOrderRepository $clientOrderRepository, int $id): JsonResponse
    {
        $clientOrder = $clientOrderRepository->find($id);
        if (!$clientOrder) {
            return new JsonResponse(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($clientOrder, 200, [], ['groups' => ['clientOrder:read']]);
    }


    #[Route("/api/clientOrder", methods: ["PATCH"])]
    public function updateUserClientOrder(Request $request, ClientOrderRepository $clientOrderRepository, UserRepository $userRepository): JsonResponse
    {
        try {
            // Récupérer l'utilisateur actuel
            $user = $this->getUser();
            if (!$user) {
                throw new \Exception('User not found');
            }

            // Récupérer l'utilisateur en base de données
            $user = $userRepository->findOneBy(['email' => $user->getUserIdentifier()]);
            if (!$user) {
                throw new \Exception('User not found in database');
            }

            // Récupérer la compagnie associée
            $clientOrder = $user->getClientOrder();
            if (!$clientOrder) {
                throw new \Exception('User clientOrder not found');
            }

            // Désérialiser les données envoyées dans la requête PATCH
            $data = json_decode($request->getContent(), true);

            // Mettre à jour les champs nécessaires
            if (isset($data['contactEmail'])) {
                $clientOrder->setContactEmail($data['contactEmail']);
            }
            if (isset($data['contactPhone'])) {
                $clientOrder->setContactPhone($data['contactPhone']);
            }

            // Sauvegarder les changements en base de données
            $clientOrderRepository->save($clientOrder, true);

            // Retourner une réponse 204 No Content
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
