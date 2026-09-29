<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Attachment;
use App\Models\Lead;
use App\Models\Note;
use App\Models\OrgSetting;
use App\Models\RecordShare;
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

    public function test_read_only_user_cannot_upload_attachments(): void
    {
        Storage::fake('local');
        OrgSetting::put('default_sharing', 'public_read');

        $owner = $this->userWithRole('Sales Representative', ['email' => 'owner-attach@example.com']);
        $readonly = $this->userWithRole('Read-Only User', ['email' => 'ro-attach@example.com']);

        $lead = Lead::factory()->create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
        ]);

        $this->actingAs($readonly)->post('/attachments', [
            'attachable_type' => 'lead',
            'attachable_id' => $lead->id,
            'file' => UploadedFile::fake()->create('brief.pdf', 100, 'application/pdf'),
        ])->assertForbidden();
    }

    public function test_user_who_can_view_parent_can_download_attachment(): void
    {
        Storage::fake('local');
        OrgSetting::put('default_sharing', 'private');

        $owner = $this->userWithRole('Sales Representative', ['email' => 'owner-dl@example.com']);
        $viewer = $this->userWithRole('Sales Representative', ['email' => 'viewer-dl@example.com']);

        $lead = Lead::factory()->create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
        ]);

        RecordShare::query()->create([
            'shareable_type' => 'lead',
            'shareable_id' => $lead->id,
            'user_id' => $viewer->id,
            'access' => 'read',
        ]);

        $this->actingAs($owner)->post('/attachments', [
            'attachable_type' => 'lead',
            'attachable_id' => $lead->id,
            'file' => UploadedFile::fake()->create('brief.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $attachment = Attachment::query()->firstOrFail();

        $this->actingAs($viewer)
            ->get(route('attachments.download', $attachment))
            ->assertSuccessful();
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
