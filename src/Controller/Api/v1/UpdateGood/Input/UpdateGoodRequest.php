<?php

namespace App\Controller\Api\v1\UpdateGood\Input;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\Validator\Constraints as Assert;

#[Exclude]
class UpdateGoodRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public readonly int $id,
        #[Assert\NotBlank]
        public readonly string $name,
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public readonly int $price,
        #[Assert\NotBlank]
        public readonly string $description,
        #[Assert\Type('boolean')]
        public readonly bool $isActive,
    ) {
    }
}
