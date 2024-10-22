<?php

namespace App\DataFixtures;

use App\Entity\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class StatusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Définition des statuts pour chaque type d'entité
        $statuses = [
            'ClientOrder' => [
                'Pending',
                'Confirmed',
                'Cancelled',
                'Completed',
            ],
            'Delivery' => [
                'Pending',
                'Scheduled',
                'In Transit',
                'Delivered',
                'Failed',
            ],
            'Tour' => [
                'Planned',
                'In Progress',
                'Completed',
                'Cancelled',
            ],
            // Vous pouvez ajouter d'autres types et leurs statuts ici
        ];

        foreach ($statuses as $type => $statusNames) {
            foreach ($statusNames as $statusName) {
                $status = new Status();
                $status->setName($statusName);
                $status->setType($type);

                // Persister l'entité Status
                $manager->persist($status);

                // (Optionnel) Ajouter une référence si d'autres fixtures en ont besoin
                $this->addReference("status_{$type}_{$statusName}", $status);
            }
        }

        // Exécuter les flush une seule fois pour optimiser les performances
        $manager->flush();
    }
}
