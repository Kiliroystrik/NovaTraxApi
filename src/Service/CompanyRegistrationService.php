<?php

namespace App\Service;

use App\DTO\RegistrationDTO;
use App\Entity\Company;
use App\Entity\User;

class CompanyRegistrationService
{
    private $company;
    private $user;

    public function __construct(private PasswordHashService $passwordHashService) {}

    public function init(RegistrationDTO $registrationDTO): void
    {

        $this->company = $this->setCompanyFromDTO($registrationDTO);
        $this->user = $this->setUserRegistrationFromDTO($registrationDTO);
    }

    /**
     * Retourne l'entité Company après initialisation.
     */
    public function getCompany(): Company
    {
        return $this->company;
    }

    /**
     * Retourne l'entité User après initialisation.
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Crée et retourne une entité Company à partir du DTO.
     */
    private function setCompanyFromDTO(RegistrationDTO $registrationDTO): Company
    {
        $company = new Company();
        $company->setName($registrationDTO->companyName);
        $company->setContactEmail($registrationDTO->contactEmail);
        $company->setContactPhone($registrationDTO->contactPhone);

        return $company;
    }

    /**
     * Crée et retourne une entité User (administrateur) à partir du DTO.
     */
    private function setUserRegistrationFromDTO(RegistrationDTO $registrationDTO): User
    {
        $user = new User();
        $user->setEmail($registrationDTO->userEmail);
        $user->setFirstName($registrationDTO->userFirstName);
        $user->setLastName($registrationDTO->userLastName);

        // Hash du MDP
        $password = $this->passwordHashService->hashPassword($user, $registrationDTO->userPassword);
        $user->setPassword($password);

        // Définit le rôle d'administrateur
        $user->setRoles(['ROLE_ADMIN']);

        return $user;
    }
}
