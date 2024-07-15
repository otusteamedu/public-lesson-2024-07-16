<?php

namespace App\Controller\Api\v1\DeleteGood;

use App\Entity\Good;
use Doctrine\ORM\EntityManagerInterface;

class Manager
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function deleteGood(int $id): bool
    {
        $goodRepository = $this->entityManager->getRepository(Good::class);
        $good = $goodRepository->find($id);
        if ($good === null) {
            return false;
        }
        $this->entityManager->remove($good);
        $this->entityManager->flush();

        return true;
    }
}
