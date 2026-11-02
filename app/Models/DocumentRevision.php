<?php

namespace App\Models;

use Database\Factories\DocumentRevisionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRevision extends Model
{
    /** @use HasFactory<DocumentRevisionFactory> */
    use HasFactory;

    protected $fillable = ['document_id', 'version', 'file_path', 'original_name', 'mime_type', 'file_size', 'change_notes', 'uploaded_by'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
