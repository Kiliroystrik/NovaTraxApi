<?php

namespace App\Service;

use App\Entity\Status;
use App\Repository\StatusRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StatusService
{
    private StatusRepository $statusRepository;

    public function __construct(StatusRepository $statusRepository)
    {
        $this->statusRepository = $statusRepository;
    }

    /**
     * Récupère un statut par type et nom.
     *
     * @param string $type
     * @param string $name
     * @return Status
     * @throws NotFoundHttpException
     */
    public function getStatus(string $type, string $name): Status
    {
        $status = $this->statusRepository->findOneBy([
            'type' => $type,
            'name' => $name,
        ]);

        if (!$status) {
            throw new NotFoundHttpException("Status '{$name}' for type '{$type}' not found.");
        }

        return $status;
    }
}
