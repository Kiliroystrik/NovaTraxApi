<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final class AccessCompanyDatasListener
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {}

    #[AsEventListener(event: KernelEvents::CONTROLLER)]
    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        // Vérification spécifique pour l'action registerCompany dans RegistrationController
        if ($controller[0] instanceof \App\Controller\API\RegistrationController && $controller[1] === 'registerCompany') {
            return; // Pas de filtre appliqué pour cette action
        }

        // Récupérer l'utilisateur authentifié
        $user = $this->security->getUser();

        // Vérifie que l'utilisateur est bien connecté et est une instance de User
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException('User not authenticated or invalid user.');
        }

        // Rechercher l'utilisateur dans la base de données pour garantir sa validité
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $user->getUserIdentifier()]);

        if (!$user) {
            throw new AccessDeniedHttpException('User not found in the database.');
        }

        // Si l'utilisateur a le rôle de super administrateur, il peut tout voir
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return;
        }

        // Récupérer la company de l'utilisateur
        $userCompany = $user->getCompany();

        // Si l'utilisateur n'est pas associé à une entreprise, accès refusé
        if (!$userCompany) {
            throw new AccessDeniedHttpException('No company associated with this user.');
        }

        // Activer le filtre Doctrine pour restreindre l'accès aux données de la même entreprise
        $this->entityManager->getFilters()
            ->enable('company_filter')
            ->setParameter('company_id', $userCompany->getId());
    }
}
