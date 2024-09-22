<?php

namespace App\Doctrine\Filters;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class CompanyFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        // Vérifie si l'entité possède une relation vers Company
        if (!$targetEntity->hasAssociation('company')) {
            return ''; // Pas de filtre si l'entité n'a pas de relation avec Company
        }

        // Applique le filtre sur les entités ayant une relation avec Company
        return sprintf('%s.company_id = %s', $targetTableAlias, $this->getParameter('company_id'));
    }
}
