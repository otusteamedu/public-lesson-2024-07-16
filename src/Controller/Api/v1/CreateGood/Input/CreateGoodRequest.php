<?php

namespace App\Controller\Api\v1\CreateGood\Input;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\Validator\Constraints as Assert;

#[Exclude]
class CreateGoodRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $name,
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public readonly int $price,
        #[Assert\NotBlank]
        public readonly string $description,
    ) {
    }
}
