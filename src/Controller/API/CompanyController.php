<?php

namespace App\Controller\API;

use App\Repository\CompanyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CompanyController extends AbstractController
{
    #[Route("/api/companies", methods: ["GET"])]
    public function getAll(CompanyRepository $companyRepository): JsonResponse
    {
        $companies = $companyRepository->findAll();

        return $this->json($companies, 200, [], ['groups' => ['companies:read']]);
    }
}
