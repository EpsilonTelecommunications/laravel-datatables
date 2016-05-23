<?php namespace SevenD\LaravelDataTables\Providers;

use SevenD\LaravelDataTables\DataTables;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Response;
use Request;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Response::macro('dataTable', function ($configuration) {
            $dataTable = new DataTables;
            $dataTable->setRequest(Request::duplicate())
                ->setConfig($configuration);

            return Response::json($dataTable->makeResponse());
        });
    }
}