<?php

namespace App\Controller\API;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RegistrationController extends AbstractController
{
    #[Route("/api/register/company", methods: ["POST"])]
    public function registerCompany(
        CompanyRepository $companyRepository,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = $request->getContent();

        // Je désérialise mon objet en Company
        $company = $serializer->deserialize($data, Company::class, 'json');

        // Je valide les données
        $errors = $validator->validate($company);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            return new JsonResponse(['error' => $errorsString], Response::HTTP_BAD_REQUEST);
        }

        // Si les données sont valides, on sauvegarde la compagnie
        $company->setCreatedAt(new \DateTimeImmutable());

        try {
            $companyRepository->save($company, true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => "Erreur lors de la création de la compagnie."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($company, Response::HTTP_CREATED, [], ['groups' => ['companies:create']]);
    }
}
