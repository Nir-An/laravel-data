<?php

namespace Spatie\LaravelData\Tests\Fakes\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class ValueDefinedCast implements Cast
{
    public function __construct(
        private mixed $value
    ) {
    }

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        return $this->value;
    }
}
