<?php

namespace Tests\Unit;

use App\Models\Opportunity;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OpportunityExpectedRevenueTest extends TestCase
{
    #[Test]
    public function expected_revenue_is_amount_times_probability(): void
    {
        $opportunity = new Opportunity([
            'amount' => 10000,
            'probability' => 65,
        ]);

        $this->assertSame(6500.0, $opportunity->expected_revenue);
    }

    #[Test]
    public function expected_revenue_is_zero_when_amount_missing(): void
    {
        $opportunity = new Opportunity([
            'amount' => null,
            'probability' => 80,
        ]);

        $this->assertSame(0.0, $opportunity->expected_revenue);
    }
}
