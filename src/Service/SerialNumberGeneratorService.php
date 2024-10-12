<?php

namespace App\Service;

class SerialNumberGeneratorService
{
    public function generateOrderNumber(): string
    {
        $prefix = 'ORD-';
        // Réduit la longueur de l'ID unique
        $uniqueId = strtoupper(substr(uniqid(), -7)); // Génère un identifiant unique de 8 caractères max
        $date = (new \DateTime())->format('Ymd');

        return $prefix . $date . '-' . $uniqueId;
    }

    public function generateDeliveryNumber(): string
    {
        $prefix = 'DLV-';
        // Réduit la longueur de l'ID unique
        $uniqueId = strtoupper(substr(uniqid(), -7)); // Génère un identifiant unique de 8 caractères max
        $date = (new \DateTime())->format('Ymd');

        return $prefix . $date . '-' . $uniqueId;
    }

    public function generateTourNumber(): string
    {
        $prefix = 'TOR-';
        // Réduit la longueur de l'ID unique
        $uniqueId = strtoupper(substr(uniqid(), -7)); // Génère un identifiant unique de 8 caractères max
        $date = (new \DateTime())->format('Ymd');

        return $prefix . $date . '-' . $uniqueId;
    }
}
