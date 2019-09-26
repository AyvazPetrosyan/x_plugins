<?php

namespace ZilProductImport\Services\Validation;

use Psr\Container\ContainerInterface;

class ValidationTypesService
{
    const NOT_EMPTY = 'not_empty';

    const ONLY_NUMBER = 'only_number';

    const ONLY_LETTER = 'only_letter';

    const LENGTH_LIMIT = 'length_limit';

    const FIXED_VALUE = 'fixed_value';

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}