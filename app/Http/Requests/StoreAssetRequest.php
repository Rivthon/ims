<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage-assets') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('type', 'asset')],
            'location_id' => ['nullable', 'exists:locations,id'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'pic' => ['nullable', 'string', 'max:150'],
            'purchase_date' => ['nullable', 'date'],
            'warranty_until' => ['nullable', 'date', 'after_or_equal:purchase_date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'condition' => ['required', Rule::in(['new', 'good', 'needs_repair', 'damaged', 'disposed'])],
            'status' => ['required', Rule::in(['active', 'maintenance', 'loaned', 'disposed'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
