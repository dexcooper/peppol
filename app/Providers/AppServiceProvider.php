<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Policies\InvoicePolicy;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
    }
}
