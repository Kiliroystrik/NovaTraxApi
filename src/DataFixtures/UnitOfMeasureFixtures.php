<?php

namespace App\DataFixtures;

use App\Entity\UnitOfMeasure;
use App\Service\PasswordHashService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UnitOfMeasureFixtures extends Fixture
{
    public function __construct(private PasswordHashService $passwordHashService) {}

    public function load(ObjectManager $manager): void
    {

        $Measures = ['Unit', 'Kilogram', 'Gallon', 'Liter', 'Pint', 'Tone', 'Cup', 'Pound', 'Ounce'];

        for ($i = 0; $i < 9; $i++) {
            $unitOfMeasure = new UnitOfMeasure();
            $unitOfMeasure->setName($Measures[$i]);
            $manager->persist($unitOfMeasure);

            $this->addReference('unit-of-measure-' . $i, $unitOfMeasure);
        }

        $manager->flush();
    }
}
