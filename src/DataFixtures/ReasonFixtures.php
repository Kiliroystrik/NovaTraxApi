<?php

namespace App\DataFixtures;

use App\Entity\Reason;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ReasonFixtures extends Fixture
{
    // Constantes de référence pour faciliter l'utilisation dans d'autres fixtures
    public const REASON_DRIVER_MALADIE = 'reason-driver-maladie';
    public const REASON_DRIVER_CONGE = 'reason-driver-conge';
    public const REASON_DRIVER_FORMATION = 'reason-driver-formation';

    public const REASON_VEHICLE_MAINTENANCE = 'reason-vehicle-maintenance';
    public const REASON_VEHICLE_REPARATION = 'reason-vehicle-reparation';
    public const REASON_VEHICLE_NETTOYAGE = 'reason-vehicle-nettoyage';

    public function load(ObjectManager $manager): void
    {
        // Raisons pour les Conducteurs
        $driverReasons = [
            'Maladie',
            'Congé',
            'Formation',
        ];

        foreach ($driverReasons as $reasonName) {
            $reason = new Reason();
            $reason->setName($reasonName);
            $reason->setType(Reason::TYPE_DRIVER);
            $reason->setDescription("Raison pour un conducteur : {$reasonName}");
            $manager->persist($reason);

            // Ajouter une référence pour une utilisation facile dans d'autres fixtures
            $referenceName = 'reason-driver-' . strtolower($reasonName);
            $this->addReference($referenceName, $reason);
        }

        // Raisons pour les Véhicules
        $vehicleReasons = [
            'Maintenance',
            'Réparation',
            'Nettoyage',
        ];

        foreach ($vehicleReasons as $reasonName) {
            $reason = new Reason();
            $reason->setName($reasonName);
            $reason->setType(Reason::TYPE_VEHICLE);
            $reason->setDescription("Raison pour un véhicule : {$reasonName}");
            $manager->persist($reason);

            // Ajouter une référence pour une utilisation facile dans d'autres fixtures
            $referenceName = 'reason-vehicle-' . strtolower($reasonName);
            $this->addReference($referenceName, $reason);
        }

        $manager->flush();
    }
}
