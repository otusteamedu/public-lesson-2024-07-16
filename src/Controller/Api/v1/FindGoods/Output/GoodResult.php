<?php

namespace App\Controller\Api\v1\FindGoods\Output;

use App\Entity\Good;

class GoodResult
{
    public function __construct(
        public readonly Good $good,
        public readonly float $score,
    )
    {
    }
}
