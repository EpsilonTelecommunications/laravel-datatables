<?php namespace App\Http\Controllers;

use App\DataTables\TestDataTable;
use SevenD\LaravelDataTables\DataTables;
use Response;
use View;

class TestController extends Controller
{
    public function test()
    {
        $dataTable = new TestDataTable;
        return View::make('data-table-test')
            ->with('dataTable', $dataTable);
    }

    public function dataTable()
    {
        return Response::dataTable(new TestDataTable);
    }
}