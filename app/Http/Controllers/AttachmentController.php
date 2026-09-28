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
        Gate::authorize('view', $record);

        $file = $request->file('file');
        $path = $file->store('attachments/'.$type.'/'.$record->getKey(), 'local');

        Attachment::query()->create([
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => 'local',
            'mime' => $file->getClientMimeType(),
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

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    public function preview(Attachment $attachment): StreamedResponse|Response
    {
        $this->authorizeAttachment($attachment);

        $mime = (string) $attachment->mime;
        $previewable = str_starts_with($mime, 'image/') || $mime === 'application/pdf' || str_starts_with($mime, 'text/');
        abort_unless($previewable, 415, 'Preview is only available for images, PDF, and text files.');

        return response(
            Storage::disk($attachment->disk)->get($attachment->path),
            200,
            [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.$attachment->original_name.'"',
            ]
        );
    }

    public function destroy(Attachment $attachment): RedirectResponse
    {
        abort_unless((int) $attachment->owner_id === (int) auth()->id() || auth()->user()?->can('records.manage-all'), 403);
        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }

    private function authorizeAttachment(Attachment $attachment): void
    {
        abort_unless(
            (int) $attachment->owner_id === (int) auth()->id()
            || auth()->user()?->can('records.view-all'),
            403
        );
    }
}
