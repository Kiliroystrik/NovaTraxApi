<?php

namespace App\Controller\API;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CompanyController extends AbstractController
{
    #[Route("/api/companies", methods: ["GET"])]
    public function getAll(CompanyRepository $companyRepository): JsonResponse
    {
        $companies = $companyRepository->findAll();

        return $this->json($companies, 200, [], ['groups' => ['companies:read']]);
    }

    #[Route("/api/company", methods: ["GET"])]
    public function getUserCompany(CompanyRepository $companyRepository, UserRepository $userRepository): JsonResponse
    {
        try {
            // Get user
            $user = $this->getUser();
            if (!$user) {
                throw new \Exception('User not found');
            }

            $user = $userRepository->findOneBy(['email' => $user->getUserIdentifier()]);
            if (!$user) {
                throw new \Exception('User not found in database');
            }

            // Get Company
            $userCompany = $user->getCompany();
            if (!$userCompany) {
                throw new \Exception('User company not found');
            }

            $company = $companyRepository->findOneBy(['id' => $userCompany->getId()]);
            if (!$company) {
                throw new \Exception('Company not found');
            }

            return $this->json($company, 200, [], ['groups' => ['companies:read']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route("/api/company", methods: ["PATCH"])]
    public function updateUserCompany(Request $request, CompanyRepository $companyRepository, UserRepository $userRepository): JsonResponse
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
            $company = $user->getCompany();
            if (!$company) {
                throw new \Exception('User company not found');
            }

            // Désérialiser les données envoyées dans la requête PATCH
            $data = json_decode($request->getContent(), true);

            // Mettre à jour les champs nécessaires
            if (isset($data['contactEmail'])) {
                $company->setContactEmail($data['contactEmail']);
            }
            if (isset($data['contactPhone'])) {
                $company->setContactPhone($data['contactPhone']);
            }

            // Sauvegarder les changements en base de données
            $companyRepository->save($company, true);

            // Retourner une réponse 204 No Content
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
