<?php

namespace App\Controller\Api\v1\FindGoods\Input;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\Validator\Constraints as Assert;

#[Exclude]
class FindGoodsRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $search,
    ) {
    }
}
