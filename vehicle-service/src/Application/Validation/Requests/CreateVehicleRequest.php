<?php

namespace App\Application\Validation\Requests;

use App\Application\Validation\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class CreateVehicleRequest extends BaseRequest
{
    protected function constraints(): array
    {
        return [
            'brand' => [
                new Assert\NotBlank(['message' => 'A marca é obrigatória']),
                new Assert\Type(['type' => 'string', 'message' => 'A marca deve ser um texto']),
                new Assert\Length([
                    'min' => 2,
                    'max' => 50,
                    'minMessage' => 'A marca deve ter pelo menos {{ limit }} caracteres',
                    'maxMessage' => 'A marca não pode ter mais de {{ limit }} caracteres'
                ])
            ],
            'model' => [
                new Assert\NotBlank(['message' => 'O modelo é obrigatório']),
                new Assert\Type(['type' => 'string', 'message' => 'O modelo deve ser um texto']),
                new Assert\Length([
                    'min' => 2,
                    'max' => 50,
                    'minMessage' => 'O modelo deve ter pelo menos {{ limit }} caracteres',
                    'maxMessage' => 'O modelo não pode ter mais de {{ limit }} caracteres'
                ])
            ],
            'year' => [
                new Assert\NotBlank(['message' => 'O ano é obrigatório']),
                new Assert\Type(['type' => 'integer', 'message' => 'O ano deve ser um número inteiro']),
                new Assert\Range([
                    'min' => 1950,
                    'max' => date('Y') + 1,
                    'minMessage' => 'O ano deve ser pelo menos {{ limit }}',
                    'maxMessage' => 'O ano não pode ser maior que {{ limit }}'
                ])
            ],
            'color' => [
                new Assert\NotBlank(['message' => 'A cor é obrigatória']),
                new Assert\Type(['type' => 'string', 'message' => 'A cor deve ser um texto']),
                new Assert\Length([
                    'min' => 3,
                    'max' => 30,
                    'minMessage' => 'A cor deve ter pelo menos {{ limit }} caracteres',
                    'maxMessage' => 'A cor não pode ter mais de {{ limit }} caracteres'
                ])
            ],
            'fuel_type' => [
                new Assert\NotBlank(['message' => 'O tipo de combustível é obrigatório']),
                new Assert\Choice([
                    'choices' => ['Gasolina', 'Etanol', 'Flex', 'Diesel', 'Hibrido', 'Eletrico'],
                    'message' => 'Tipo de combustível inválido. Valores aceitos: {{ choices }}'
                ])
            ],
            'transmission_type' => [
                new Assert\NotBlank(['message' => 'O tipo de transmissão é obrigatório']),
                new Assert\Choice([
                    'choices' => ['Manual', 'Automatico', 'CVT'],
                    'message' => 'Tipo de transmissão inválido. Valores aceitos: {{ choices }}'
                ])
            ],
            'mileage' => [
                new Assert\NotBlank(['message' => 'A quilometragem é obrigatória']),
                new Assert\Type(['type' => 'integer', 'message' => 'A quilometragem deve ser um número inteiro']),
                new Assert\PositiveOrZero(['message' => 'A quilometragem deve ser zero ou positiva'])
            ],
            'price' => [
                new Assert\NotBlank(['message' => 'O preço é obrigatório']),
                new Assert\Type(['type' => 'numeric', 'message' => 'O preço deve ser um número']),
                new Assert\Positive(['message' => 'O preço deve ser maior que zero'])
            ],
            'description' => [
                new Assert\NotBlank(['message' => 'A descrição é obrigatória']),
                new Assert\Type(['type' => 'string', 'message' => 'A descrição deve ser um texto']),
                new Assert\Length([
                    'min' => 10,
                    'max' => 1000,
                    'minMessage' => 'A descrição deve ter pelo menos {{ limit }} caracteres',
                    'maxMessage' => 'A descrição não pode ter mais de {{ limit }} caracteres'
                ])
            ],
            'status' => [
                new Assert\Choice([
                    'choices' => ['available', 'reserved', 'sold', 'maintenance'],
                    'message' => 'Status inválido. Valores aceitos: {{ choices }}'
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
                        'minMessage' => 'O número de portas deve ser pelo menos {{ limit }}',
                        'maxMessage' => 'O número de portas não pode ser maior que {{ limit }}'
                    ])
                ])
            ],
            'seats' => [
                new Assert\Optional([
                    new Assert\Type(['type' => 'integer', 'message' => 'O número de assentos deve ser um número inteiro']),
                    new Assert\Range([
                        'min' => 2,
                        'max' => 9,
                        'minMessage' => 'O número de assentos deve ser pelo menos {{ limit }}',
                        'maxMessage' => 'O número de assentos não pode ser maior que {{ limit }}'
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
                        'minMessage' => 'A margem de lucro deve ser pelo menos {{ limit }}%',
                        'maxMessage' => 'A margem de lucro não pode ser maior que {{ limit }}%'
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
                        'min' => 9,
                        'max' => 11,
                        'minMessage' => 'O RENAVAM deve ter pelo menos {{ limit }} caracteres',
                        'maxMessage' => 'O RENAVAM não pode ter mais de {{ limit }} caracteres'
                    ])
                ])
            ]
        ];
    }
}
