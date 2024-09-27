<?php

namespace App\Controller\API;

use App\Entity\ClientOrder;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\Service\ClientOrderNumberGenerator;
use Doctrine\ORM\EntityManagerInterface;
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

class ClientController extends AbstractController
{
    #[Route("/api/clients", methods: ["GET"])]
    public function getClientOrders(ClientRepository $clientRepository, Request $request): JsonResponse
    {
        $qb = $clientRepository->createQueryBuilder('c')->orderBy('c.name', 'ASC');

        // Récupérer la limite passée dans la requête, avec une valeur par défaut de 10
        $limit = $request->query->getInt('limit', 10);
        $page = $request->query->getInt('page', 1);

        // Si la limite est définie à 0, désactiver la pagination
        if ($limit === 0) {
            $results = $qb->getQuery()->getResult();
            return $this->json([
                'items' => $results,
                'totalItems' => count($results), // Compter les résultats dans le cas sans pagination
            ], 200, [], ['groups' => ['client:list']]);
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
        ], 200, [], ['groups' => ['client:list']]);
    }


    #[Route("/api/clients/{id}", methods: ["GET"])]
    public function getClientOrder(ClientRepository $clientRepository, int $id): JsonResponse
    {
        $clientOrder = $clientRepository->find($id);
        if (!$clientOrder) {
            return new JsonResponse(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($clientOrder, 200, [], ['groups' => ['client:read']]);
    }

    #[Route("/api/client/orders", methods: ["POST"])]
    public function createClientOrder(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
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

            // Créer une nouvelle commande client
            $client = new Client();
            $client->setName($data['name']);

            // Persister la nouvelle commande dans la base de données
            $entityManager->persist($client);
            $entityManager->flush();

            // Renvoyer la commande créée avec les groupes appropriés
            return $this->json($client, Response::HTTP_CREATED, [], ['groups' => ['client:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }



    #[Route("/api/clientOrder", methods: ["PATCH"])]
    public function updateUserClientOrder(Request $request, ClientRepository $clientRepository, UserRepository $userRepository): JsonResponse
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
            $clientRepository->save($clientOrder, true);

            // Retourner une réponse 204 No Content
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
