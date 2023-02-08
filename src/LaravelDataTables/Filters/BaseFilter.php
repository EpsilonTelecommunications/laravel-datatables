<?php

namespace SevenD\LaravelDataTables\Filters;

use Illuminate\Support\Str;
use PDO;

abstract class BaseFilter
{
    const CAST_BOOLEAN = 'boolean';
    const CAST_INTEGER = 'integer';
    const CAST_FLOAT = 'float';
    const CAST_STRING = 'string';

    protected $requestPath;
    protected $relationshipPath;
    protected $filterCriteria;
    protected $cast;

    public function __construct($relationshipPath, $requestPath = null)
    {
        $this->setRelationshipPath($relationshipPath);
        $this->setRequestPath(
            $requestPath ?? Str::of(
                collect(explode('.', $relationshipPath))->last()
            )->snake()

        );
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequestPath()
    {
        return $this->requestPath;
    }

    /**
     * @param mixed $requestPath
     * @return BaseFilter
     */
    public function setRequestPath($requestPath)
    {
        $this->requestPath = $requestPath;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRelationshipPath()
    {
        return $this->relationshipPath;
    }

    /**
     * @param mixed $relationshipPath
     * @return BaseFilter
     */
    public function setRelationshipPath($relationshipPath)
    {
        $this->relationshipPath = $relationshipPath;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilterCriteria()
    {
        return $this->filterCriteria ?: ' IN ';
    }

    /**
     * @param mixed $filterCriteria
     * @return BaseFilter
     */
    public function setFilterCriteria($filterCriteria)
    {
        $this->filterCriteria = $filterCriteria;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCast()
    {
        return $this->cast ?: 'string';
    }

    /**
     * @param mixed $cast
     * @return BaseFilter
     */
    public function setCast($cast)
    {
        $this->cast = $cast;
        return $this;
    }

    public function getHash()
    {
        return md5($this->requestPath . $this->relationshipPath);
    }

    public function castValue($value)
    {
        if (is_array($value)) {
            return collect($value)
                ->map(function ($item) {
                    return $this->castValue($item);
                });
        }

        switch ($this->getCast()) {
            case self::CAST_BOOLEAN:
                return $value === 'false' ? false : (bool) $value;
            case self::CAST_INTEGER:
                return (int) $value;
            case self::CAST_FLOAT:
                return (float) $value;
            case self::CAST_STRING:
                return (string) $value;
            default:
                return $value;
        }
    }

    public function getPdoParam()
    {
        switch ($this->getCast()) {
            case self::CAST_BOOLEAN:
                return PDO::PARAM_BOOL;
            case self::CAST_INTEGER:
                return PDO::PARAM_INT;
            case self::CAST_FLOAT:
                return PDO::PARAM_STR;
            case self::CAST_STRING:
                return PDO::PARAM_STR;
            default:
                return PDO::PARAM_STR;
        }
    }
}
