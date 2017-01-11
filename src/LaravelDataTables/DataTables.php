<?php namespace SevenD\LaravelDataTables;

use SevenD\LaravelDataTables\Columns\ColumnRender;
use SevenD\LaravelDataTables\Columns\GroupedJoinColumn;
use SevenD\LaravelDataTables\Config\DataTableConfig;
use SevenD\LaraveLDataTables\Exceptions\NoDriverFoundException;
use Illuminate\Http\Request;
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

    public function makeResponse()
    {
        $this->loadDriver();

        $response = $this->driver->makeResponse();

        foreach ($response['data'] as $key => $data) {
            foreach ($this->config->getColumns() as $subkey => $column) {
                if ($column->getRender() instanceof ColumnRender) {
                    $response['data'][$key][$subkey] = View::make($column->getRender()->getRender())->with($data)->render();
                }
            }

        }
        return $response;
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
