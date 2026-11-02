<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentRevision>
 */
class DocumentRevisionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'version' => '1.0',
            'file_path' => 'documents/test.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'change_notes' => 'Dokumen awal',
            'uploaded_by' => User::factory(),
        ];
    }
}
