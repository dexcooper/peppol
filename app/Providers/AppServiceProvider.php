<?php

namespace App\Providers;

use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\ServiceProvider;
use Tiptap\Nodes\Text;

class AppServiceProvider extends ServiceProvider
{
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
        TextColumn::configureUsing(function (TextColumn $textColumn) {
            $textColumn->default('-');
        });
    }
}
