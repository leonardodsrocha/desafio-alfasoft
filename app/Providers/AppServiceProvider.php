<?php

namespace App\Providers;

use App\Models\Contact;
use App\Observers\ContactObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

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
        Paginator::useBootstrapFive();

        // Registra o observer que cria registros de auditoria a cada
        // criação, atualização ou exclusão de um contato.
        Contact::observe(ContactObserver::class);
    }
}
