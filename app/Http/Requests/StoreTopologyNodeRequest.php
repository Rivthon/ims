<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTopologyNodeRequest extends FormRequest
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
            'asset_id' => ['nullable', 'exists:assets,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'code' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'device_type' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in(['online', 'offline', 'warning', 'unknown'])],
            'ip_address' => ['nullable', 'ip'],
            'mac_address' => ['nullable', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/'],
            'port' => ['nullable', 'string', 'max:100'],
            'provider' => ['nullable', 'string', 'max:150'],
            'ssid' => ['nullable', 'string', 'max:150'],
            'vlan' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:150'],
            'serial_number' => ['nullable', 'string', 'max:150'],
            'channel' => ['nullable', 'integer', 'min:1', 'max:256'],
            'resolution' => ['nullable', 'string', 'max:100'],
            'recording_mode' => ['nullable', 'string', 'max:100'],
            'retention_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'connection_type' => ['nullable', 'string', 'max:100'],
            'position_x' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'position_y' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
