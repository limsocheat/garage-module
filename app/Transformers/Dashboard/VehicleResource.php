<?php

namespace Modules\Garage\Transformers\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Customer\Transformers\Dashboard\CustomerDataResource;

class VehicleResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'customer_id' => $this->customer_id,
            'customer' => $this->customer ? new CustomerDataResource($this->customer) : null,
            'vehicle_size_id' => $this->vehicle_size_id,
            'size' => $this->size ? ['id' => $this->size->id, 'name' => $this->size->name] : null,
            'plate_number' => $this->plate_number,
            'make' => $this->make,
            'model' => $this->model,
            'color' => $this->color,
            'year' => $this->year,
            'label' => $this->label,
            'notes' => $this->notes,
            'is_default' => $this->is_default,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
