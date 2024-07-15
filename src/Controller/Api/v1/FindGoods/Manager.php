<?php

namespace App\Controller\Api\v1\FindGoods;

use App\Controller\Api\v1\FindGoods\Input\FindGoodsRequest;
use App\Entity\Good;
use FOS\ElasticaBundle\Finder\FinderInterface;

class Manager
{
    public function __construct(private readonly FinderInterface $finder)
    {
    }

    /**
     * @return Good[]
     */
    public function findGoods(FindGoodsRequest $request): array
    {
        return $this->finder->find($request->search);
    }
}
