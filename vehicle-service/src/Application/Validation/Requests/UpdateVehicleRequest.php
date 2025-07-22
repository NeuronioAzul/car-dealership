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
                        'range' => [2, 50],
                        'notInRangeMessage' => 'A marca deve ter pelo menos {{ min }} caracteres, e não pode ter mais de {{ max }} caracteres'
                    ])
                ])
            ],
            'model' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'string', 'message' => 'O modelo deve ser um texto']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50,
                        'notInRangeMessage' => 'O modelo deve ter pelo menos {{ min }} caracteres, e não pode ter mais de {{ max }} caracteres'
                    ])
                ])
            ],
            'year' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'integer', 'message' => 'O ano deve ser um número inteiro']),
                    new Assert\Range([
                        'min' => 1950,
                        'max' => date('Y') + 1,
                        'notInRangeMessage' => 'O ano deve ser pelo menos {{ min }} e não pode ser maior que {{ max }}'
                    ])
                ])
            ],
            'color' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'string', 'message' => 'A cor deve ser um texto']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 30,
                        'notInRangeMessage' => 'A cor deve ter pelo menos {{ min }} caracteres, e não pode ter mais de {{ max }} caracteres'
                    ])
                ])
            ],
            'fuel_type' => [
                new Assert\Optional([
                    new Assert\Choice([
                        'choices' => ['Gasolina', 'Etanol', 'Flex', 'Diesel', 'Hibrido', 'Eletrico'],
                        'message' => 'Tipo de combustível inválido. Valores aceitos: {{ choices }}'
                    ])
                ])
            ],
            'transmission_type' => [
                new Assert\Optional([
                    new Assert\Choice([
                        'choices' => ['Manual', 'Automatico', 'CVT'],
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
                        'notInRangeMessage' => 'A descrição deve ter pelo menos {{ min }} caracteres, e não pode ter mais de {{ max }} caracteres'
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
                        'min' => 2,
                        'max' => 6,
                        'notInRangeMessage' => 'O número de portas deve ser pelo menos {{ min }} e não pode ser maior que {{ max }}'
                    ])
                ])
            ],
            'seats' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'integer', 'message' => 'O número de assentos deve ser um número inteiro']),
                    new Assert\Range([
                        'min' => 2,
                        'max' => 9,
                        'notInRangeMessage' => 'O número de assentos deve ser pelo menos {{ min }} e não pode ser maior que {{ max }}'
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
                        'notInRangeMessage' => 'A margem de lucro deve ser pelo menos {{ min }}% e não pode ser maior que {{ max }}%'
                    ])
                ])
            ],
            'supplier' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'string', 'message' => 'O fornecedor deve ser um texto']),
                    new Assert\Length([
                        'max' => 100,
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
                        'exactMessage' => 'O número do chassi deve ter exatamente {{ limit }} caracteres'
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
                        'exactMessage' => 'O RENAVAM deve ter pelo menos {{ min }} caracteres e não pode ter mais de {{ max }} caracteres'
                    ])
                ])
            ]
        ];
    }
}
