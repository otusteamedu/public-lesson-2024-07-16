<?php

namespace App\Controller\Api\v1\UpdateGood;

use App\Controller\Api\v1\UpdateGood\Input\UpdateGoodRequest;
use App\Entity\Good;
use Doctrine\ORM\EntityManagerInterface;

class Manager
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function updateGood(UpdateGoodRequest $request): bool
    {
        $goodRepository = $this->entityManager->getRepository(Good::class);
        $good = $goodRepository->find($request->id);
        if ($good === null) {
            return false;
        }
        $good->setName($request->name);
        $good->setPrice($request->price);
        $good->setDescription($request->description);
        $good->setIsActive($request->isActive);
        $this->entityManager->flush();

        return true;
    }
}
