<?php

namespace App\Controller\Api\v1\FindGoods;

use App\Controller\Api\v1\FindGoods\Input\FindGoodsRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class Controller
{
    public function __construct(
        private readonly Manager $manager,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route(path: '/api/v1/find-goods', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] FindGoodsRequest $request): Response
    {
        $result = $this->manager->findGoods($request);

        return new JsonResponse($this->serializer->serialize(['goods' => $result], JsonEncoder::FORMAT), Response::HTTP_OK, [], true);
    }
}
