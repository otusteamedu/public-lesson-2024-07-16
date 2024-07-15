<?php

namespace App\Controller\Api\v1\CreateGood;

use App\Controller\Api\v1\CreateGood\Input\CreateGoodRequest;
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

    #[Route(path: '/api/v1/create-good', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] CreateGoodRequest $request): Response
    {
        return new JsonResponse(['id' => $this->manager->createGood($request)]);
    }
}
