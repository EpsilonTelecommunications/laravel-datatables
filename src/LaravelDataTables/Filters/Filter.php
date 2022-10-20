<?php

namespace SevenD\LaravelDataTables\Filters;

class Filter extends BaseFilter
{
    public static function create($relationshipPath, $requestPath = null)
    {
        return new self($relationshipPath, $requestPath);
    }
}
