<?php

namespace SevenD\LaravelDataTables\Filters;

class Filter extends BaseFilter
{
    public static function create($relationshipPath, $requestPath)
    {
        return new self($relationshipPath, $requestPath);
    }
}
