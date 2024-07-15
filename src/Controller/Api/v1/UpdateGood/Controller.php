<?php

namespace App\Controller\Api\v1\UpdateGood;

use App\Controller\Api\v1\UpdateGood\Input\UpdateGoodRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class Controller
{
    public function __construct(
        private readonly Manager $manager,
    ) {
    }

    #[Route(path: '/api/v1/update-good', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] UpdateGoodRequest $request): Response
    {
        $result = $this->manager->updateGood($request);

        return $result ? new JsonResponse(['success' => true]) :
            new JsonResponse(['success' => false], Response::HTTP_NOT_FOUND);
    }
}
