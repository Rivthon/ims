<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTopologyLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage-topology') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'source_node_id' => ['required', 'integer'],
            'target_node_id' => ['required', 'integer', 'different:source_node_id'],
            'label' => ['nullable', 'string', 'max:150'],
            'connection_type' => ['required', Rule::in(['ethernet', 'fiber', 'wireless', 'poe', 'cloud'])],
            'source_port' => ['nullable', 'string', 'max:100'],
            'target_port' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive', 'warning'])],
        ];
    }
}
