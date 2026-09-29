<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Support\CrmRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function store(StoreAttachmentRequest $request): RedirectResponse
    {
        $type = $request->string('attachable_type')->toString();
        $class = CrmRegistry::modelFor($type);
        abort_unless($class, 422);

        $record = $class::query()->findOrFail($request->integer('attachable_id'));
        Gate::authorize('update', $record);

        $file = $request->file('file');
        $path = $file->store('attachments/'.$type.'/'.$record->getKey(), 'local');

        Attachment::query()->create([
            'original_name' => $this->safeFilename($file->getClientOriginalName()),
            'path' => $path,
            'disk' => 'local',
            'mime' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize() ?: 0,
            'attachable_type' => $type,
            'attachable_id' => $record->getKey(),
            'owner_id' => $request->user()->id,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Attachment uploaded.');
    }

    public function download(Attachment $attachment): StreamedResponse
    {
        $this->authorizeAttachment($attachment);

        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $this->safeFilename($attachment->original_name)
        );
    }

    public function preview(Attachment $attachment): StreamedResponse|Response
    {
        $this->authorizeAttachment($attachment);

        $mime = (string) $attachment->mime;
        $previewable = str_starts_with($mime, 'image/') || $mime === 'application/pdf' || str_starts_with($mime, 'text/');
        abort_unless($previewable, 415, 'Preview is only available for images, PDF, and text files.');

        $filename = $this->safeFilename($attachment->original_name);

        return response(
            Storage::disk($attachment->disk)->get($attachment->path),
            200,
            [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]
        );
    }

    public function destroy(Attachment $attachment): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $parent = $attachment->attachable;
        $canManageParent = $parent && $user->can('update', $parent);
        $isOwner = (int) $attachment->owner_id === (int) $user->id;
        $isAdmin = $user->can('records.manage-all');

        abort_unless($isOwner || $canManageParent || $isAdmin, 403);

        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }

    private function authorizeAttachment(Attachment $attachment): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $parent = $attachment->attachable;
        abort_unless($parent, 404);

        Gate::authorize('view', $parent);
    }

    private function safeFilename(string $name): string
    {
        $clean = str_replace(["\r", "\n", '"', '\\'], '', basename($name));

        return $clean !== '' ? $clean : 'attachment';
    }
}
