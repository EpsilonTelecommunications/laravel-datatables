<?php namespace SevenD\LaravelDataTables\Columns;

use SevenD\LaravelDataTables\Columns\BaseColumn;

class JoinColumn extends BaseColumn
{
    const LEFT_JOIN = 'LEFT';
    const INNER_JOIN = 'INNER';

    protected $joins = [];

    public function __construct($columnDefinition = null, $settings = [])
    {
        if ($columnDefinition) {
            $name = implode('', array_map(function($item) {
                return (is_array($item)) ? $item['Name'] : $item;
            }, $columnDefinition));

            $this->setName($name);
            $this->setColumnName(array_pop($columnDefinition));

            $this->setJoins($columnDefinition);
        }

        parent::__construct(null, $settings);
    }

	public static function create($columnDefinition = null, $settings = [])
	{
		return new JoinColumn($columnDefinition, $settings);
	}

    public function setJoins($joins)
    {
        $newJoins = [];
        $defaultJoinData = [
            'JoinType' => self::LEFT_JOIN,
        ];
        foreach ($joins as $key => $join) {
            if (is_array($join)) {
                $newJoins[] = array_merge($defaultJoinData, [
                    'Name' => $key,
                ], $join);
            } else {
                $newJoins[] = array_merge($defaultJoinData, ['Name' => $join]);
            }
        }
        $this->joins = $newJoins;
    }

    public function getJoins()
    {
        return $this->joins;
    }

    public function getJoinSettings($index = null)
    {
        if (is_null($index)) {
            return $this->joins;
        } elseif (isset($this->joins[$index])) {
            return $this->joins[$index];
        }

        return null;
    }

    public function getJoinName()
    {
        $joinNames = [];
        foreach ($this->getJoins() as $join) {
            $joinNames[] = $join['Name'];
        }
        return implode('.', $joinNames);
    }

    public function setJoinName($joinName)
    {
        $this->joinName = $joinName;
        $this->setColumnName($this->joinName);
    }
}