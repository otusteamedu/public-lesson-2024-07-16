<?php

namespace App\Controller\Api\v1\FindGoods;

use App\Controller\Api\v1\FindGoods\Input\FindGoodsRequest;
use App\Controller\Api\v1\FindGoods\Output\GoodResult;
use FOS\ElasticaBundle\Finder\HybridFinderInterface;
use FOS\ElasticaBundle\HybridResult;

class Manager
{
    public function __construct(private readonly HybridFinderInterface $finder)
    {
    }

    /**
     * @return GoodResult[]
     */
    public function findGoods(FindGoodsRequest $request): array
    {
        return array_map(
            static fn (HybridResult $result): GoodResult => new GoodResult(
                $result->getTransformed(),
                $result->getResult()->getScore(),
            ),
            $this->finder->findHybrid($request->search.'~2')
        );
    }
}
