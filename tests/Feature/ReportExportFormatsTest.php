<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class ReportExportFormatsTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_csv_excel_and_pdf_exports_are_available(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $this->actingAs($user)
            ->get('/reports/leads-by-source/export?format=csv')
            ->assertSuccessful()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->actingAs($user)
            ->get('/reports/leads-by-source/export?format=xlsx')
            ->assertSuccessful()
            ->assertHeader('content-type', 'application/vnd.ms-excel');

        $this->actingAs($user)
            ->get('/reports/leads-by-source/export?format=pdf')
            ->assertSuccessful()
            ->assertHeader('content-type', 'application/pdf');
    }
}
