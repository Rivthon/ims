<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! ($this->user()?->can('manage-documents') ?? false)) {
            return false;
        }

        return $this->string('status')->toString() !== 'approved' || $this->user()->can('approve-documents');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document_number' => ['required', 'string', 'max:100', Rule::unique('documents')->ignore($this->route('document'))],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in(['draft', 'approved', 'archived'])],
            'effective_date' => ['nullable', 'date'],
            'pic_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
