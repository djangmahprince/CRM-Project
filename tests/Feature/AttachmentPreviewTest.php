<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Lead;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class AttachmentPreviewTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_user_can_preview_an_image_attachment(): void
    {
        Storage::fake('local');
        $user = $this->userWithRole('Sales Representative');
        $lead = Lead::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Storage::disk('local')->put('attachments/lead/1/sample.txt', 'hello preview');

        $attachment = Attachment::query()->create([
            'original_name' => 'sample.txt',
            'path' => 'attachments/lead/1/sample.txt',
            'disk' => 'local',
            'mime' => 'text/plain',
            'size' => 13,
            'attachable_type' => 'lead',
            'attachable_id' => $lead->id,
            'owner_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('attachments.preview', $attachment))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('hello preview');
    }
}
