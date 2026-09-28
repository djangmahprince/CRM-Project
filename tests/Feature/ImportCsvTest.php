<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class ImportCsvTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_csv_lead_import_creates_rows(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $csv = "Last Name,Company,Email\nSmith,Acme,smith@acme.test\n";
        $file = UploadedFile::fake()->createWithContent('leads.csv', $csv);

        $this->actingAs($user)->post('/import', [
            'object' => 'leads',
            'file' => $file,
            'mapping' => [
                0 => 'last_name',
                1 => 'company',
                2 => 'email',
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'last_name' => 'Smith',
            'company' => 'Acme',
            'email' => 'smith@acme.test',
            'owner_id' => $user->id,
        ]);
        $this->assertSame(1, Lead::query()->count());
    }
}
