<?php

namespace Modules\Garage\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    /**
     * Authorization is handled by the controller's authorizeResource().
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'vehicle_size_id' => ['nullable', 'exists:garage_vehicle_sizes,id'],
            'plate_number' => ['nullable', 'string', 'max:50'],
            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
            'year' => ['nullable', 'integer', 'between:1900,2100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_default' => ['boolean'],
            'active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_id.exists' => __('The selected customer does not exist.'),
            'vehicle_size_id.exists' => __('The selected size is invalid.'),
            'year.between' => __('Please enter a valid year.'),
        ];
    }
}
