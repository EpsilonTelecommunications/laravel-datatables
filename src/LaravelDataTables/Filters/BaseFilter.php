<?php

namespace SevenD\LaravelDataTables\Filters;

abstract class BaseFilter
{
    protected $requestPath;
    protected $relationshipPath;
    protected $filterCriteria;
    protected $cast;

    public function __construct($relationshipPath, $requestPath)
    {
        $this->setRelationshipPath($relationshipPath);
        $this->setRequestPath($requestPath);
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
        return md5($this->requestPath, $this->relationshipPath);
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
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'string':
                return (string) $value;
            default:
                return $value;
        }
    }
}
