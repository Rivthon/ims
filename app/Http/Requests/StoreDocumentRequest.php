<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage-documents') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document_number' => ['required', 'string', 'max:100', 'unique:documents,document_number'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'version' => ['required', 'string', 'max:30'],
            'effective_date' => ['nullable', 'date'],
            'pic_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:3000'],
            'change_notes' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx', 'extensions:pdf,doc,docx,xls,xlsx,ppt,pptx', 'max:20480'],
        ];
    }
}
