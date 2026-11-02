<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRevisionRequest;
use App\Models\Document;
use App\Models\DocumentRevision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DocumentRevisionController extends Controller
{
    public function store(StoreDocumentRevisionRequest $request, Document $document): RedirectResponse
    {
        $path = $request->file('file')->store('documents');
        try {
            DB::transaction(function () use ($request, $document, $path): void {
                $document->revisions()->create(['version' => $request->string('version')->toString(), 'file_path' => $path, 'original_name' => $request->file('file')->getClientOriginalName(), 'mime_type' => $request->file('file')->getMimeType() ?? 'application/octet-stream', 'file_size' => $request->file('file')->getSize(), 'change_notes' => $request->string('change_notes')->toString(), 'uploaded_by' => $request->user()->id]);
                $document->update(['current_version' => $request->string('version')->toString(), 'status' => 'draft', 'approved_by' => null, 'approved_at' => null]);
            });
        } catch (Throwable $exception) {
            Storage::delete($path);
            throw $exception;
        }

        return back()->with('success', 'Revisi baru diunggah dan status dikembalikan menjadi draft.');
    }

    public function download(Document $document, DocumentRevision $revision): StreamedResponse
    {
        abort_unless($revision->document_id === $document->id, 404);
        abort_unless($document->status === 'approved' || request()->user()->can('manage-documents'), 403);
        abort_unless(Storage::exists($revision->file_path), 404);

        return Storage::download($revision->file_path, $revision->original_name, ['Content-Type' => $revision->mime_type]);
    }
}
