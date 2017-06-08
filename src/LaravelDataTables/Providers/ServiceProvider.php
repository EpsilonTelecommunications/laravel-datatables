<?php namespace SevenD\LaravelDataTables\Providers;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use SevenD\LaravelDataTables\DataTables;
use Carbon\Carbon;
use Response;
use Request;
use Storage;

class ServiceProvider extends LaravelServiceProvider
{
    const CSV_FILE_PATH = 'laravel-datatables/csv/%s.csv';

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

            if ($request->get('csv') == 'prepare') {
                $request->merge([
                    'start' => null,
                    'length' => null,
                ]);

                $csv = $dataTable->setRequest($request)
                    ->makeResponseCsv();

                $hash = md5(microtime(true) . serialize($request->all()));

                Storage::put(sprintf(ServiceProvider::CSV_FILE_PATH, $hash), $csv);

                return Response::json([
                    'success' => true,
                    'hash' => $hash,
                    'url' => sprintf('%s?%s', $request->getPathInfo(), http_build_query([ 'csv' => 'download', 'hash' => $hash ])),
                ]);

            } elseif ($request->get('csv') == 'download') {

                $file = sprintf(ServiceProvider::CSV_FILE_PATH, $request->get('hash'));

                if (Storage::exists($file)) {

                    $date = Carbon::now();
                    $dateFormatted = $date->format('Y-m-d H:i:s');

                    if ($dataTable->getConfig()->getCsvTitle()) {
                        $csvName = sprintf('CSV Export - %s [%s].csv', $dataTable->getConfig()->getCsvTitle(), $dateFormatted);
                    } else {
                        $csvName = sprintf('CSV Export [%s].csv', $dateFormatted);
                    }
                    return Response::download(storage_path(sprintf('app/%s', $file)), $csvName)
                        ->deleteFileAfterSend(true);
                }

                abort(404);

            } else {
                return Response::json(
                    $dataTable->setRequest($request)
                        ->makeResponse()
                );
            }
        });
    }
}