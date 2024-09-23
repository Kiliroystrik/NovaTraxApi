<?php

namespace App\Service;

class ClientOrderNumberGenerator
{
    public function __construct() {}

    public function generate(): string
    {
        $prefix = 'ORD-';
        // Réduit la longueur de l'ID unique
        $uniqueId = strtoupper(substr(uniqid(), -7)); // Génère un identifiant unique de 8 caractères max
        $date = (new \DateTime())->format('Ymd');

        return $prefix . $date . '-' . $uniqueId;
    }
}
