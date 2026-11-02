<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_ict_staff_can_upload_document_to_private_storage(): void
    {
        Storage::fake('local');
        $staff = User::factory()->create(['role' => 'ict_staff']);

        $response = $this->actingAs($staff)->post(route('documents.store'), [
            'document_number' => 'SOP-ICT-001', 'title' => 'SOP Backup Server',
            'category' => 'SOP ICT', 'version' => '1.0', 'file' => UploadedFile::fake()->create('sop-backup.pdf', 100, 'application/pdf'),
        ]);

        $document = Document::query()->where('document_number', 'SOP-ICT-001')->firstOrFail();
        $response->assertRedirect(route('documents.show', $document));
        $this->assertSame('draft', $document->status);
        $this->assertDatabaseHas('document_revisions', ['document_id' => $document->id, 'version' => '1.0']);
        Storage::disk('local')->assertExists($document->revisions()->value('file_path'));
    }

    public function test_reporter_cannot_upload_document_and_only_sees_approved_documents(): void
    {
        $reporter = User::factory()->create(['role' => 'reporter']);
        Document::factory()->create(['title' => 'Dokumen Draft Rahasia', 'status' => 'draft']);
        Document::factory()->create(['title' => 'Dokumen Publik', 'status' => 'approved']);

        $this->actingAs($reporter)->get(route('documents.index'))->assertOk()->assertSee('Dokumen Publik')->assertDontSee('Dokumen Draft Rahasia');
        $this->actingAs($reporter)->post(route('documents.store'), [])->assertForbidden();
    }

    public function test_only_head_or_super_admin_can_approve_document(): void
    {
        $staff = User::factory()->create(['role' => 'ict_staff']);
        $head = User::factory()->create(['role' => 'ict_head']);
        $document = Document::factory()->create(['status' => 'draft']);
        $payload = ['document_number' => $document->document_number, 'title' => $document->title, 'category' => $document->category, 'status' => 'approved'];

        $this->actingAs($staff)->put(route('documents.update', $document), $payload)->assertForbidden();
        $this->actingAs($head)->put(route('documents.update', $document), $payload)->assertRedirect(route('documents.show', $document));

        $document->refresh();
        $this->assertSame('approved', $document->status);
        $this->assertSame($head->id, $document->approved_by);
    }

    public function test_new_revision_returns_approved_document_to_draft(): void
    {
        Storage::fake('local');
        $staff = User::factory()->create(['role' => 'ict_staff']);
        $document = Document::factory()->create(['status' => 'approved', 'current_version' => '1.0']);

        $response = $this->actingAs($staff)->post(route('documents.revisions.store', $document), [
            'version' => '1.1', 'change_notes' => 'Perubahan jadwal backup', 'file' => UploadedFile::fake()->create('sop-v1.1.pdf', 80, 'application/pdf'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('documents', ['id' => $document->id, 'status' => 'draft', 'current_version' => '1.1']);
        $this->assertDatabaseHas('document_revisions', ['document_id' => $document->id, 'version' => '1.1']);
    }
}
