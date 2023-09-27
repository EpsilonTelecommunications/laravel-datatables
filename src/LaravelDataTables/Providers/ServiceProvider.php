<?php namespace SevenD\LaravelDataTables\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use SevenD\LaravelDataTables\DataTables;
use Carbon\Carbon;
use Response;
use Request;
use Storage;
use Auth;
use Opis\Closure;
use Throwable;

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
        Response::macro('dataTable', function ($configuration) {

            $dataTable = new DataTables;
            $dataTable->setConfig($configuration);
            $request = Request::duplicate();

            if (in_array($request->get('csv'), ['prepare', 'email'])) {
                $request->merge([
                    'start' => null,
                    'length' => null,
                ]);

                $dataTable->setRequest($request);

                if($request->get('csv') == 'email') {
                    $user = Auth::user();
                    $params = [
                        'userId' => $user->getId(),
                        'configuration' => $configuration,
                        'filters' => $request->all(),
                    ];

                    dispatch(function () use ($params, $user) {
                        ini_set('memory_limit','2G');
                        set_time_limit(0);

                        Auth::authAsSystemThen($user);

                        $dataTable = new DataTables;
                        $dataTable->setConfig($params['configuration']);
                        $request = (new \Illuminate\Http\Request())->merge($params['filters']);
                        $request->merge([
                            'start' => null,
                            'length' => null,
                        ]);

                        $dataTable->setRequest($request);
                        $csvData = $dataTable->makeResponseCsv();

                        $mailable = config('laravel-datatables.mailable-class', \Illuminate\Mail\Mailable::class);

                        Mail::send(new $mailable(
                            $params['userId'],
                            $dataTable->getConfig()->getTitle(),
                            $csvData
                        ));
                    })->catch(function (Throwable $e) {
                        \Log::error($e->getMessage());
                    });

                    return Response::json([
                        'success' => true,
                    ]);
                }

                $csv = $dataTable->makeResponseCsv();

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

        $this->publishes([
            __DIR__.'/../../../config/laravel-datatables.php' => config_path('laravel-datatables.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}