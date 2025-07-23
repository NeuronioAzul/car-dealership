<?php

namespace App\Application\Validation\Requests;

use App\Application\Validation\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateVehicleRequest extends BaseRequest
{
    protected function constraints(): array
    {
        return [
            'brand' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'string', 'message' => 'A marca deve ser um texto']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'A marca deve ter pelo menos {{ limit }} caracteres',
                        'maxMessage' => 'A marca não pode ter mais de {{ limit }} caracteres'
                    ])
                ])
            ],
            'model' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'string', 'message' => 'O modelo deve ser um texto']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'O modelo deve ter pelo menos {{ limit }} caracteres',
                        'maxMessage' => 'O modelo não pode ter mais de {{ limit }} caracteres'
                    ])
                ])
            ],
            'year' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'integer', 'message' => 'O ano deve ser um número inteiro']),
                    new Assert\Range([
                        'min' => 1950,
                        'max' => date('Y') + 1,
                        'notInRangeMessage' => 'O ano deve estar entre {{ min }} e {{ max }}'
                    ])
                ])
            ],
            'color' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'string', 'message' => 'A cor deve ser um texto']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 30,
                        'minMessage' => 'A cor deve ter pelo menos {{ limit }} caracteres',
                        'maxMessage' => 'A cor não pode ter mais de {{ limit }} caracteres'
                    ])
                ])
            ],
            'fuel_type' => [
                new Assert\Optional([
                    new Assert\Choice([
                        'choices' => ['Gasolina', 'Etanol', 'Flex', 'Diesel', 'Híbrido', 'Elétrico'],
                        'message' => 'Tipo de combustível inválido. Valores aceitos: {{ choices }}'
                    ])
                ])
            ],
            'transmission_type' => [
                new Assert\Optional([
                    new Assert\Choice([
                        'choices' => ['Manual', 'Automático', 'CVT'],
                        'message' => 'Tipo de transmissão inválido. Valores aceitos: {{ choices }}'
                    ])
                ])
            ],
            'mileage' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'integer', 'message' => 'A quilometragem deve ser um número inteiro']),
                    new Assert\PositiveOrZero(['message' => 'A quilometragem deve ser zero ou positiva'])
                ])
            ],
            'price' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'numeric', 'message' => 'O preço deve ser um número']),
                    new Assert\Positive(['message' => 'O preço deve ser maior que zero'])
                ])
            ],
            'description' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'string', 'message' => 'A descrição deve ser um texto']),
                    new Assert\Length([
                        'min' => 10,
                        'max' => 1000,
                        'minMessage' => 'A descrição deve ter pelo menos {{ limit }} caracteres',
                        'maxMessage' => 'A descrição não pode ter mais de {{ limit }} caracteres'
                    ])
                ])
            ],
            'status' => [
                new Assert\Optional([
                    new Assert\Choice([
                        'choices' => ['available', 'reserved', 'sold', 'maintenance'],
                        'message' => 'Status inválido. Valores aceitos: {{ choices }}'
                    ])
                ])
            ],
            'features' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'array', 'message' => 'As características devem ser uma lista']),
                    new Assert\All([
                        new Assert\Type(['type' => 'string', 'message' => 'Cada característica deve ser um texto'])
                    ])
                ])
            ],
            'engine_size' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'string', 'message' => 'O tamanho do motor deve ser um texto']),
                    new Assert\Regex([
                        'pattern' => '/^\d+\.\d+$/',
                        'message' => 'Formato do motor inválido. Use formato como "1.0", "2.0", etc.'
                    ])
                ])
            ],
            'doors' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'integer', 'message' => 'O número de portas deve ser um número inteiro']),
                    new Assert\Range([
                        'min' => 1,
                        'max' => 10,
                        'notInRangeMessage' => 'O número de portas deve estar entre {{ min }} e {{ max }}'
                    ])
                ])
            ],
            'seats' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'integer', 'message' => 'O número de assentos deve ser um número inteiro']),
                    new Assert\Range([
                        'min' => 1,
                        'max' => 20,
                        'notInRangeMessage' => 'O número de assentos deve estar entre {{ min }} e {{ max }}'
                    ])
                ])
            ],
            'trunk_capacity' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'integer', 'message' => 'A capacidade do porta-malas deve ser um número inteiro']),
                    new Assert\PositiveOrZero(['message' => 'A capacidade do porta-malas deve ser zero ou positiva'])
                ])
            ],
            'purchase_price' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'numeric', 'message' => 'O preço de compra deve ser um número']),
                    new Assert\PositiveOrZero(['message' => 'O preço de compra deve ser zero ou positivo'])
                ])
            ],
            'profit_margin' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'numeric', 'message' => 'A margem de lucro deve ser um número']),
                    new Assert\Range([
                        'min' => 0,
                        'max' => 100,
                        'notInRangeMessage' => 'A margem de lucro deve estar entre {{ min }}% e {{ max }}%'
                    ])
                ])
            ],
            'supplier' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'string', 'message' => 'O fornecedor deve ser um texto']),
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'O fornecedor não pode ter mais de {{ limit }} caracteres'
                    ])
                ])
            ],
            'chassis_number' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'string', 'message' => 'O número do chassi deve ser um texto']),
                    new Assert\Length([
                        'min' => 17,
                        'max' => 17,
                        'exactMessage' => 'O número do chassi deve ter exatamente 17 caracteres'
                    ])
                ])
            ],
            'license_plate' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'string', 'message' => 'A placa deve ser um texto']),
                    new Assert\Regex([
                        'pattern' => '/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/',
                        'message' => 'Formato de placa inválido. Use formato brasileiro (ABC1234 ou ABC1D23)'
                    ])
                ])
            ],
            'renavam' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'string', 'message' => 'O RENAVAM deve ser um texto']),
                    new Assert\Length([
                        'min' => 11,
                        'max' => 11,
                        'exactMessage' => 'O RENAVAM deve ter exatamente 11 caracteres'
                    ])
                ])
            ]
        ];
    }
}
