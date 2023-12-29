<?php

namespace CarbonPHP\Classes\Types;

class ProgramArgumentsArray
{
    public function __construct(public array  $flags,
                                public string $description,
                                public mixed  $callable)
    {
    }

}
