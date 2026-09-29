<?php

namespace App\Observers;

use App\Models\Lead;
use App\Services\WorkflowAutomationService;

class LeadObserver
{
    public function __construct(private WorkflowAutomationService $workflows) {}

    public function updated(Lead $lead): void
    {
        $this->workflows->handleUpdated($lead);
    }
}
