<?php

namespace App\Repository\Core;

use Doctrine\ORM\Query;

interface CoreRepositoryInterface
{

    /**
     * @param array<mixed> $searchFormData
     */
    public function getPaginatedQuery(array $searchFormData): Query;
}
