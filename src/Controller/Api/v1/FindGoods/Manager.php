<?php

namespace App\Controller\Api\v1\FindGoods;

use App\Controller\Api\v1\FindGoods\Input\FindGoodsRequest;
use App\Controller\Api\v1\FindGoods\Output\GoodResult;
use Elastica\Query;
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
        $boolQuery = new Query\BoolQuery();
        if ($request->activeOnly) {
            $boolQuery->addMust(new Query\Term(['active' => true]));
        }
        $range = [];
        if ($request->minPrice !== null) {
            $range['gte'] = $request->minPrice;
        }
        if ($request->maxPrice !== null) {
            $range['lte'] = $request->maxPrice;
        }
        if (count($range) > 0) {
            $boolQuery->addMust(new Query\Range('price', $range));
        }
        $boolQuery->addShould(new Query\Fuzzy('name', $request->search));
        $boolQuery->addShould(new Query\Fuzzy('description', $request->search));
        return array_map(
            static fn (HybridResult $result): GoodResult => new GoodResult(
                $result->getTransformed(),
                $result->getResult()->getScore(),
            ),
            $this->finder->findHybrid(new Query($boolQuery))
        );
    }
}
