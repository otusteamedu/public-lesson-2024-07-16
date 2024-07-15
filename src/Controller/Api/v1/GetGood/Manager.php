<?php

namespace App\Controller\Api\v1\GetGood;

use App\Entity\Good;
use Doctrine\ORM\EntityManagerInterface;

class Manager
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function getGood(int $id): ?Good
    {
        $goodRepository = $this->entityManager->getRepository(Good::class);
        /** @var Good|null $good */
        $good = $goodRepository->find($id);

        return $good;
    }
}
