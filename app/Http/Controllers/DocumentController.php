<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class DocumentController extends Controller
{
    public function index(): View
    {
        $documents = Document::query()->with(['pic:id,name', 'creator:id,name'])->withCount('revisions')
            ->when(! request()->user()->can('manage-documents'), fn ($query) => $query->where('status', 'approved'))
            ->when(request('category'), fn ($query, $category) => $query->where('category', $category))
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest('updated_at')->orderByDesc('id')->paginate(15)->withQueryString();

        $categories = Document::query()
            ->when(! request()->user()->can('manage-documents'), fn ($query) => $query->where('status', 'approved'))
            ->distinct()->orderBy('category')->pluck('category');

        return view('documents.index', compact('documents', 'categories'));
    }

    public function create(): View
    {
        Gate::authorize('manage-documents');

        return view('documents.create', ['users' => User::query()->orderBy('name')->get(['id', 'name'])]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $path = $request->file('file')->store('documents');
        try {
            $document = DB::transaction(function () use ($request, $path): Document {
                $document = Document::create([...$request->safe()->only(['document_number', 'title', 'category', 'effective_date', 'pic_id', 'description']), 'status' => 'draft', 'current_version' => $request->string('version')->toString(), 'created_by' => $request->user()->id]);
                $document->revisions()->create(['version' => $request->string('version')->toString(), 'file_path' => $path, 'original_name' => $request->file('file')->getClientOriginalName(), 'mime_type' => $request->file('file')->getMimeType() ?? 'application/octet-stream', 'file_size' => $request->file('file')->getSize(), 'change_notes' => $request->string('change_notes')->toString() ?: 'Dokumen awal', 'uploaded_by' => $request->user()->id]);

                return $document;
            });
        } catch (Throwable $exception) {
            Storage::delete($path);
            throw $exception;
        }

        return redirect()->route('documents.show', $document)->with('success', 'Dokumen berhasil diunggah sebagai draft.');
    }

    public function show(Document $document): View
    {
        $this->authorizeView($document);
        $document->load(['pic:id,name', 'creator:id,name', 'approver:id,name', 'revisions.uploader:id,name']);

        return view('documents.show', compact('document'));
    }

    public function edit(Document $document): View
    {
        Gate::authorize('manage-documents');

        return view('documents.edit', ['document' => $document, 'users' => User::query()->orderBy('name')->get(['id', 'name'])]);
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $data = $request->validated();
        if ($data['status'] === 'approved' && $document->status !== 'approved') {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
        } elseif ($data['status'] !== 'approved') {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
        $document->update($data);

        return redirect()->route('documents.show', $document)->with('success', 'Metadata dan status dokumen diperbarui.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        Gate::authorize('manage-documents');
        $paths = $document->revisions()->pluck('file_path');
        DB::transaction(fn () => $document->delete());
        Storage::delete($paths->all());

        return redirect()->route('documents.index')->with('success', 'Dokumen dan seluruh revisinya dihapus.');
    }

    private function authorizeView(Document $document): void
    {
        abort_unless($document->status === 'approved' || request()->user()->can('manage-documents'), 403);
    }
}
