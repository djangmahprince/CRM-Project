<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Attachment;
use App\Models\Lead;
use App\Models\Note;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class NoteAttachmentTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_user_can_add_note_and_attachment_to_a_lead(): void
    {
        Storage::fake('local');
        $user = $this->userWithRole('Sales Representative');
        $lead = Lead::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user)->post('/notes', [
            'notable_type' => 'lead',
            'notable_id' => $lead->id,
            'title' => 'Discovery',
            'body' => 'Interested in enterprise plan.',
        ])->assertRedirect();

        $this->assertDatabaseHas('notes', [
            'notable_type' => 'lead',
            'notable_id' => $lead->id,
            'title' => 'Discovery',
        ]);

        $this->actingAs($user)->post('/attachments', [
            'attachable_type' => 'lead',
            'attachable_id' => $lead->id,
            'file' => UploadedFile::fake()->create('brief.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $this->assertSame(1, Attachment::query()->count());
        $this->assertSame(1, Note::query()->count());
    }

    public function test_user_can_add_note_to_an_account(): void
    {
        $user = $this->userWithRole('Sales Representative');
        $account = Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user)->post('/notes', [
            'notable_type' => 'account',
            'notable_id' => $account->id,
            'body' => 'Account-level follow-up.',
        ])->assertRedirect();

        $this->assertDatabaseHas('notes', [
            'notable_type' => 'account',
            'notable_id' => $account->id,
            'body' => 'Account-level follow-up.',
        ]);

        $this->actingAs($user)
            ->get(route('accounts.show', $account))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Accounts/Show')
                ->has('account.notes', 1)
            );
    }
}
