<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegistrationDTO
{
    #[Assert\NotBlank(message: "Le nom de l'entreprise ne doit pas être vide.")]
    #[Assert\Length(max: 100, maxMessage: "Le nom de l'entreprise ne peut pas dépasser 100 caractères.")]
    public ?string $companyName = null;

    #[Assert\NotBlank(message: "L'email de contact est obligatoire.")]
    #[Assert\Email(message: "L'email de contact n'est pas valide.")]
    #[Assert\Length(max: 180, maxMessage: "L'email de contact ne peut pas dépasser 180 caractères.")]
    public ?string $contactEmail = null;

    #[Assert\NotBlank(message: "Le numéro de téléphone est obligatoire.")]
    #[Assert\Length(max: 50, maxMessage: "Le numéro de téléphone ne peut pas dépasser 50 caractères.")]
    public ?string $contactPhone = null;

    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Length(min: 8, max: 255, minMessage: "Le mot de passe doit comporter au moins 8 caractères.")]
    public ?string $userPassword = null;

    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Length(max: 50, maxMessage: "Le prénom ne peut pas dépasser 50 caractères.")]
    public ?string $userFirstName = null;

    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(max: 50, maxMessage: "Le nom ne peut pas dépasser 50 caractères.")]
    public ?string $userLastName = null;

    #[Assert\NotBlank(message: "L'email de l'utilisateur est obligatoire.")]
    #[Assert\Email(message: "L'email de l'utilisateur n'est pas valide.")]
    #[Assert\Length(max: 180, maxMessage: "L'email de l'utilisateur ne peut pas dépasser 180 caractères.")]
    public ?string $userEmail = null;
}
