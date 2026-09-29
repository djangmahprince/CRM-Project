<?php

namespace App\Observers;

use App\Models\Opportunity;
use App\Services\WorkflowAutomationService;

class OpportunityObserver
{
    public function __construct(private WorkflowAutomationService $workflows) {}

    public function updated(Opportunity $opportunity): void
    {
        $this->workflows->handleUpdated($opportunity);
    }
}
