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
            $dataTable->setConfig($configuration);
            $request = Request::duplicate();

            if ($request->get('csv')) {
                $request->merge([
                    'start' => null,
                    'length' => null,
                ]);

                $csv = $dataTable->setRequest($request)
                    ->makeResponseCsv();

                $callback = function() use ($csv) {
                    file_put_contents('php://output', $csv);
                };

                return response()->stream($callback, 200, [
                    "Content-type" => "text/csv",
                    "Content-Disposition" => sprintf("attachment; filename=data.csv"),
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                ]);


            } else {
                return Response::json(
                    $dataTable->setRequest($request)
                        ->makeResponse()
                );
            }
        });
    }
}