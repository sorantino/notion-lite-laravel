<?php

namespace App\Providers;

use App\Domain\Page\PageRepository;
use App\Infrastructure\Persistence\EloquentPageRepository;
use App\Services\PageService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PageRepository::class, EloquentPageRepository::class);
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            $view->with('tree', $this->app->make(PageService::class)->tree());
        });
    }
}
