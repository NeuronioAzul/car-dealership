<?php

namespace App\Application\Validation\Requests;

use App\Application\Validation\BaseRequest;

class CreateVehicleRequest extends BaseRequest
{
    protected function rules(): array
    {
        return [
            'brand' => 'required|string|min:2|max:50',
            'model' => 'required|string|min:2|max:50',
            'year' => 'required|integer|year_range:1990,' . date('Y'),
            'color' => 'required|string|min:3|max:30',
            'fuel_type' => 'required|fuel_type',
            'transmission_type' => 'required|transmission',
            'mileage' => 'required|integer|min:0',
            'price' => 'required|numeric|positive',
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
            'brand.required' => 'A marca é obrigatória.',
            'model.required' => 'O modelo é obrigatório.',
            'year.required' => 'O ano é obrigatório.',
            'color.required' => 'A cor é obrigatória.',
            'fuel_type.required' => 'O tipo de combustível é obrigatório.',
            'transmission_type.required' => 'O tipo de transmissão é obrigatório.',
            'mileage.required' => 'A quilometragem é obrigatória.',
            'price.required' => 'O preço é obrigatório.',
        ];
    }
}
