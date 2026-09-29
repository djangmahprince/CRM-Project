<?php

namespace App\Providers;

use App\Models\Lead;
use App\Models\Opportunity;
use App\Observers\LeadObserver;
use App\Observers\OpportunityObserver;
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

        Lead::observe(LeadObserver::class);
        Opportunity::observe(OpportunityObserver::class);
    }
}
