<?php

namespace App\Providers;

use App\Support\CrmRegistry;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Relation::enforceMorphMap(CrmRegistry::morphMap());
    }
}
