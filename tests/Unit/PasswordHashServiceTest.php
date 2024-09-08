<?php

namespace App\Tests;

use App\Entity\User;
use App\Service\PasswordHashService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordHashServiceTest extends TestCase
{
    /**
     * @var UserPasswordHasherInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $passwordHasherMock;
    private $passwordService;

    protected function setUp(): void
    {
        // Créer un mock de l'interface UserPasswordHasherInterface
        $this->passwordHasherMock = $this->createMock(UserPasswordHasherInterface::class);

        // Injecter le mock dans le PasswordHashService
        $this->passwordService = new PasswordHashService($this->passwordHasherMock);
    }

    public function testHashPassword(): void
    {
        $user = new User();
        $plainPassword = 'securePassword';
        $hashedPassword = 'hashedPassword123';

        // Configurer le mock pour retourner un mot de passe haché simulé
        $this->passwordHasherMock
            ->expects($this->once()) // Nous nous attendons à ce que la méthode hashPassword soit appelée une fois
            ->method('hashPassword')
            ->with($user, $plainPassword)
            ->willReturn($hashedPassword);

        // Appeler la méthode à tester
        $result = $this->passwordService->hashPassword($user, $plainPassword);

        // Vérifier que le résultat est le mot de passe haché simulé
        $this->assertEquals($hashedPassword, $result);
    }

    public function testIsPasswordValid(): void
    {
        $user = new User();
        $plainPassword = 'securePassword';

        // Configurer le mock pour retourner true (mot de passe valide)
        $this->passwordHasherMock
            ->expects($this->once()) // Nous nous attendons à ce que la méthode isPasswordValid soit appelée une fois
            ->method('isPasswordValid')
            ->with($user, $plainPassword)
            ->willReturn(true);

        // Appeler la méthode à tester
        $result = $this->passwordService->isPasswordValid($user, $plainPassword);

        // Vérifier que la méthode retourne true
        $this->assertTrue($result);
    }

    public function testInvalidPassword(): void
    {
        $user = new User();
        $plainPassword = 'securePassword';

        // Configurer le mock pour retourner false (mot de passe invalide)
        $this->passwordHasherMock
            ->expects($this->once()) // Nous nous attendons à ce que la méthode isPasswordValid soit appelée une fois
            ->method('isPasswordValid')
            ->with($user, $plainPassword)
            ->willReturn(false);

        // Appeler la méthode à tester
        $result = $this->passwordService->isPasswordValid($user, $plainPassword);

        // Vérifier que la méthode retourne false
        $this->assertFalse($result);
    }

    public function testNeedsRehash(): void
    {
        $user = new User();

        // Configurer le mock pour retourner true (mot de passe nécessite un rehash)
        $this->passwordHasherMock
            ->expects($this->once()) // Nous nous attendons à ce que la méthode needsRehash soit appelée une fois
            ->method('needsRehash')
            ->with($user)
            ->willReturn(true);

        // Appeler la méthode à tester
        $result = $this->passwordService->needsRehash($user);

        // Vérifier que la méthode retourne true
        $this->assertTrue($result);
    }
}
