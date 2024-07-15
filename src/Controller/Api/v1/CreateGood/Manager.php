<?php

namespace App\Controller\Api\v1\CreateGood;

use App\Controller\Api\v1\CreateGood\Input\CreateGoodRequest;
use App\Entity\Good;
use Doctrine\ORM\EntityManagerInterface;

class Manager
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function createGood(CreateGoodRequest $request): int
    {
        $good = new Good();
        $good->setName($request->name);
        $good->setPrice($request->price);
        $good->setDescription($request->description);
        $good->setIsActive(true);
        $this->entityManager->persist($good);
        $this->entityManager->flush();

        return $good->getId();
    }
}
