<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Policies\InvoicePolicy;
use App\Services\ApiLogger;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Invoice::class => InvoicePolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        TextColumn::configureUsing(function (TextColumn $textColumn) {
            $textColumn->default('-');
        });

        Http::macro('loggable', function () {
            return Http::withOptions([
                'on_stats' => function ($stats) {
                    $response = $stats->getResponse();
                    $requestData = json_decode((string) $stats->getRequest()->getBody(), true);

                    $responseData = null;
                    $statusCode = null;
                    if ($response) {
                        $statusCode = $response->getStatusCode();
                        $body = (string) $response->getBody();
                        $decoded = json_decode($body, true);
                        $responseData = $decoded ?: $body;
                    }

                    ApiLogger::log(
                        $stats->getRequest()->getMethod(),
                        (string) $stats->getEffectiveUri(),
                        $requestData ? $requestData : null,
                        $responseData ? (array) $responseData : null,
                        $statusCode,
                        $stats->getTransferTime() * 1000
                    );
                },
            ]);
        });


    }
}
