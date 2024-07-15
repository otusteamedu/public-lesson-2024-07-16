<?php

namespace App\Service;

use App\Controller\Api\v1\CreateGood\Input\UpdateGoodRequest;
use App\Entity\Good;
use Doctrine\ORM\EntityManagerInterface;

class GoodService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function saveGood(UpdateGoodRequest $request): bool
    {
        $currentState = OrderStateEnum::from($order->getState());
        if (
                ($currentState === OrderStateEnum::New && $newState === OrderStateEnum::Accepted) ||
                ($currentState === OrderStateEnum::Accepted && $newState === OrderStateEnum::Paid) ||
                ($currentState === OrderStateEnum::Paid && $newState === OrderStateEnum::Sent) ||
                ($currentState === OrderStateEnum::Sent && $newState === OrderStateEnum::Finished)
            ) {
                $order->setState($newState->value);
                $this->entityManager->flush();

                return true;
        }

        return false;
    }

    public function getOrder(int $id): ?Good
    {
        /** @var Good|null $order */
        $order = $this->entityManager->getRepository(Good::class)->find($id);

        return $order;
    }
}
