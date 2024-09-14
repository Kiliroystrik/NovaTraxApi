<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Service\PasswordHashService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHashService) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        // Init SuperAdmin user
        $superAdminUser = new User();
        $superAdminUser->setEmail("superadmin@gmail.com");
        $superAdminUser->setRoles(['ROLE_SUPER_ADMIN']);
        $hashedPassword = $this->passwordHashService->hashPassword($superAdminUser, 'password');
        $superAdminUser->setPassword($hashedPassword);
        $superAdminUser->setFirstName($faker->firstName());
        $superAdminUser->setLastName($faker->lastName());
        $superAdminUser->setCompany($this->getReference('super-admin-company'));
        $manager->persist($superAdminUser);

        // Init random users
        for ($i = 0; $i < 10; $i++) {
            $randomCompany = $this->getReference('company-' . $i); // Récupérer chaque entreprise par référence
            for ($j = 0; $j < 10; $j++) {
                $user = new User();
                $user->setEmail($faker->email());
                $user->setRoles(['ROLE_MEMBER']);
                $hashedPassword = $this->passwordHashService->hashPassword($user, 'password');
                $user->setPassword($hashedPassword);
                $user->setFirstName($faker->firstName());
                $user->setLastName($faker->lastName());
                $user->setCompany($randomCompany);
                $manager->persist($user);
            }
        }

        $manager->flush();
    }
}
