<?php

namespace App\Controller\API;

use App\DTO\RegistrationDTO;
use App\Service\CompanyRegistrationService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{

    public function __construct(private CompanyRegistrationService $companyRegistrationService, private EntityManagerInterface $entityManager) {}

    #[Route("/api/register_company", methods: ["POST"])]
    public function registerCompany(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        // Désérialisation des données en un DTO
        $data = $request->getContent();
        $registrationDTO = $serializer->deserialize($data, RegistrationDTO::class, 'json');

        // Validation des données
        $errors = $validator->validate($registrationDTO);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        // Utilisation du service pour créer l'entreprise et l'utilisateur
        try {
            $this->companyRegistrationService->init($registrationDTO);
            $company = $this->companyRegistrationService->getCompany();
            $user = $this->companyRegistrationService->getUser();

            // Je set l'entreprise pour l'utilisateur
            $user->setCompany($company);

            // Sauvegarde de la entreprise et de l'utilisateur (via l'entity manager)
            $this->entityManager->persist($company);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la création de la entreprise et de l\'utilisateur.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Retourne la réponse avec les détails de l'entreprise créée
        return $this->json($company, Response::HTTP_CREATED, [], ['groups' => ['companies:create']]);
    }
}
