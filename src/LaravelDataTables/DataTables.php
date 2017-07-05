<?php namespace SevenD\LaravelDataTables;

use SevenD\LaravelDataTables\Columns\ColumnRender;
use SevenD\LaravelDataTables\Columns\GroupedJoinColumn;
use SevenD\LaravelDataTables\Config\DataTableConfig;
use SevenD\LaraveLDataTables\Exceptions\NoDriverFoundException;
use Illuminate\Http\Request;
use League\Csv\Writer;
use SplTempFileObject;
use View;

class DataTables
{
    private $request;
    private $config;
    private $driver;
    private $drivers = [
        '\Propel\Runtime\ActiveQuery\ModelCriteria' => 'SevenD\LaravelDataTables\Drivers\PropelDataTablesDriver',
    ];

    public function __construct(DataTableConfig $config = null, Request $request = null)
    {
        if ($config) {
            $this->setConfig($config);
        }

        if ($request) {
            $this->setRequest($request);
        }

        return $this;
    }

    public function addDriver($driver, $detection)
    {
        $this->drivers[$detection] = $driver;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function makeResponse($type = '')
    {
        $this->loadDriver();

        $response = $this->driver->makeResponse();

        foreach ($response['data'] as $key => $data) {
            foreach ($this->config->getColumns($type) as $subkey => $column) {
                if ($column->getRender() instanceof ColumnRender) {
                    $response['data'][$key][$subkey] = View::make($column->getRender()->getRender())->with($data)->render();
                } else {
                    $response['data'][$key][$subkey] = htmlentities($response['data'][$key][$subkey]);
                }
            }

        }
        return $response;
    }

    public function makeResponseCsv()
    {
        $this->loadDriver();
        $this->config->setDefaultColumnType('csv');

        $columns = $this->config->getColumns();

        if (is_null($columns) || count($columns) == 0) {
            $this->config->setDefaultColumnType('');
            $columns = $this->config->getColumns();
        }

        $response = $this->driver->makeResponse();

        $writer = Writer::createFromFileObject(new SplTempFileObject());

        foreach ($response['data'] as $key => $data) {
            foreach ($columns as $subkey => $column) {
                if ($column->getRender() instanceof ColumnRender) {
                    $response['data'][$key][$subkey] = View::make($column->getRender()->getRender())->with($data)->render();
                }
            }
        }

        foreach ($response['data'] as $key => $row) {
            foreach ($row as $subkey => $column) {
                if (is_array($column)) {
                    $response['data'][$key][$subkey] = implode(', ', $columns); // Not sure this is a good idea... but we'll see!
                } elseif (is_object($column)) {
                    unset($response['data'][$key][$subkey]);
                }
            }
        }

        $headers = [];
        foreach ($columns as $column) {
            $headers[] = $column->getTitle();
        }

        $writer->insertAll(array_merge([$headers], $response['data']));

        return $writer->__toString();
    }

    private function loadDriver()
    {
        foreach ($this->drivers as  $query => $driver) {
            if ($this->config->getQuery() instanceof $query) {
                $this->driver = new $driver;
                $this->driver->setConfig($this->config);
                $this->driver->setRequest($this->request);
                return true;
            }
        }

        throw new NoDriverFoundException(sprintf("There are no drivers for object with class '%s'", get_class($this->query)));
    }
}
