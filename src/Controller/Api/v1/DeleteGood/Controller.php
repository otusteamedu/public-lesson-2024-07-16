<?php

namespace App\Controller\Api\v1\DeleteGood;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class Controller
{
    public function __construct(
        private readonly Manager $manager,
    ) {
    }

    #[Route(path: '/api/v1/delete-good/{id}', requirements: ['id'=>'\d+'], methods: ['DELETE'])]
    public function __invoke(int $id): Response
    {
        $result = $this->manager->deleteGood($id);

        return $result ? new JsonResponse(['success' => true]) :
            new JsonResponse(['success' => false], Response::HTTP_NOT_FOUND);
    }
}
