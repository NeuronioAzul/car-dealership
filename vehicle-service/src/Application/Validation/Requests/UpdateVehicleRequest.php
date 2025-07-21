<?php

namespace App\Application\Validation\Requests;

use App\Application\Validation\BaseRequest;

class UpdateVehicleRequest extends BaseRequest
{
    protected function rules(): array
    {
        return [
            'brand' => 'string|min:2|max:50',
            'model' => 'string|min:2|max:50',
            'year' => 'integer|year_range:1990,' . date('Y'),
            'color' => 'string|min:3|max:30',
            'fuel_type' => 'fuel_type',
            'transmission_type' => 'transmission',
            'mileage' => 'integer|min:0',
            'price' => 'numeric|positive',
            'description' => 'string|max:1000',
            'status' => 'vehicle_status',
            'features' => 'array',
            'engine_size' => 'string|max:10',
            'doors' => 'integer|min:2|max:5',
            'seats' => 'integer|min:1|max:9',
            'trunk_capacity' => 'integer|min:0|max:2000',
            'purchase_price' => 'numeric|positive',
            'profit_margin' => 'numeric|min:0|max:100',
            'supplier' => 'string|max:100',
            'chassis_number' => 'string|min:17|max:17',
            'license_plate' => 'string|min:7|max:8',
            'renavam' => 'string|min:8|max:11'
        ];
    }

    protected function messages(): array
    {
        return [
            'brand.min' => 'A marca deve ter pelo menos 2 caracteres.',
            'model.min' => 'O modelo deve ter pelo menos 2 caracteres.',
            'year.year_range' => 'O ano deve estar entre 1990 e ' . date('Y') . '.',
            'price.positive' => 'O preço deve ser positivo.',
        ];
    }
}
