<?php

namespace App\Controller\API;

use App\Repository\StatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StatusController extends AbstractController
{
    #[Route("/api/statuses", methods: ["GET"])]
    public function getStatuses(StatusRepository $statusRepository): JsonResponse
    {
        $statuses = $statusRepository->findAll();

        return $this->json($statuses, 200, [], ['groups' => ['status:read']]);
    }

    #[Route("/api/statuses/{id}", methods: ["GET"])]
    public function getStatus(StatusRepository $statusRepository, int $id): JsonResponse
    {
        $status = $statusRepository->find($id);
        if (!$status) {
            return new JsonResponse(['error' => 'Status not found'], 404);
        }

        return $this->json($status, 200, [], ['groups' => ['status:read']]);
    }

    // GetStatusesByType
    #[Route("/api/statuses/type/{type}", methods: ["GET"])]
    public function getStatusesByType(string $type, StatusRepository $statusRepository): JsonResponse
    {
        $statuses = $statusRepository->findBy(['type' => $type]);

        return $this->json($statuses, 200, [], ['groups' => ['status:read']]);
    }
}
