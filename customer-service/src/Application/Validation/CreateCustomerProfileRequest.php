<?php

declare(strict_types=1);

namespace App\Application\Validation;

use Symfony\Component\Validator\Constraints as Assert;

class CreateCustomerProfileRequest extends BaseRequest
{
    protected function constraints(): array
    {
        return [
            'user_id' => [
                new Assert\NotBlank(['message' => 'O ID do usuário é obrigatório']),
                new Assert\Uuid(['message' => 'O ID do usuário deve ser um UUID válido']),
            ],
            'full_name' => [
                new Assert\NotBlank(['message' => 'O nome completo é obrigatório']),
                new Assert\Length([
                    'max' => 255,
                    'maxMessage' => 'O nome completo não pode ter mais de {{ limit }} caracteres',
                ]),
            ],
            'email' => [
                new Assert\NotBlank(['message' => 'O e-mail é obrigatório']),
                new Assert\Email(['message' => 'O e-mail deve ser um endereço de e-mail válido']),
                new Assert\Length([
                    'max' => 255,
                    'maxMessage' => 'O e-mail não pode ter mais de {{ limit }} caracteres',
                ]),
            ],
            'cpf' => [
                new Assert\NotBlank(['message' => 'O CPF é obrigatório']),
                new Assert\Length([
                    'max' => 11,
                    'maxMessage' => 'O CPF não pode ter mais de {{ limit }} caracteres',
                ]),
                new Assert\Regex([
                    'pattern' => '/^\d{11}$/',
                    'message' => 'O CPF deve conter apenas números e ter 11 dígitos',
                ]),
            ],
            'birth_date' => [
                new Assert\Date(['message' => 'A data de nascimento deve estar no formato YYYY-MM-DD']),
            ],
            'rg' => [
                new Assert\Length([
                    'max' => 20,
                    'maxMessage' => 'O RG não pode ter mais de {{ limit }} caracteres',
                ]),
            ],
            'gender' => [
                new Assert\Choice([
                    'choices' => ['M', 'F', 'Other'],
                    'message' => 'O gênero deve ser M, F ou Other',
                ]),
            ],
            'marital_status' => [
                new Assert\Choice([
                    'choices' => ['Single', 'Married', 'Divorced', 'Widowed', 'Common Law'],
                    'message' => 'O estado civil deve ser: Single, Married, Divorced, Widowed ou Common Law',
                ]),
            ],
            'phone' => [
                new Assert\Length([
                    'max' => 20,
                    'maxMessage' => 'O telefone não pode ter mais de {{ limit }} caracteres',
                ]),
            ],
            'mobile' => [
                new Assert\Length([
                    'max' => 20,
                    'maxMessage' => 'O celular não pode ter mais de {{ limit }} caracteres',
                ]),
            ],
            'whatsapp' => [
                new Assert\Length([
                    'max' => 20,
                    'maxMessage' => 'O WhatsApp não pode ter mais de {{ limit }} caracteres',
                ]),
            ],
            'address' => [
                new Assert\Collection([
                    'fields' => [
                        'street' => [
                            new Assert\Length([
                                'max' => 255,
                                'maxMessage' => 'A rua não pode ter mais de {{ limit }} caracteres',
                            ]),
                        ],
                        'number' => [
                            new Assert\Length([
                                'max' => 20,
                                'maxMessage' => 'O número não pode ter mais de {{ limit }} caracteres',
                            ]),
                        ],
                        'complement' => [
                            new Assert\Length([
                                'max' => 100,
                                'maxMessage' => 'O complemento não pode ter mais de {{ limit }} caracteres',
                            ]),
                        ],
                        'neighborhood' => [
                            new Assert\Length([
                                'max' => 100,
                                'maxMessage' => 'O bairro não pode ter mais de {{ limit }} caracteres',
                            ]),
                        ],
                        'city' => [
                            new Assert\Length([
                                'max' => 100,
                                'maxMessage' => 'A cidade não pode ter mais de {{ limit }} caracteres',
                            ]),
                        ],
                        'state' => [
                            new Assert\Length([
                                'max' => 2,
                                'maxMessage' => 'O estado deve ter exatamente {{ limit }} caracteres',
                            ]),
                            new Assert\Regex([
                                'pattern' => '/^[A-Z]{2}$/',
                                'message' => 'O estado deve ser uma sigla de dois caracteres em letras maiúsculas',
                            ]),
                        ],
                        'zip_code' => [
                            new Assert\Length([
                                'max' => 10,
                                'maxMessage' => 'O CEP não pode ter mais de {{ limit }} caracteres',
                            ]),
                            new Assert\Regex([
                                'pattern' => '/^\d{5}-?\d{3}$/',
                                'message' => 'O CEP deve estar no formato XXXXX-XXX ou XXXXXXXX',
                            ]),
                        ],
                    ],
                    'allowExtraFields' => false,
                    'allowMissingFields' => true,
                ]),
            ],
            'occupation' => [
                new Assert\Length([
                    'max' => 255,
                    'maxMessage' => 'A ocupação não pode ter mais de {{ limit }} caracteres',
                ]),
            ],
            'company' => [
                new Assert\Length([
                    'max' => 255,
                    'maxMessage' => 'A empresa não pode ter mais de {{ limit }} caracteres',
                ]),
            ],
            'monthly_income' => [
                new Assert\Type([
                    'type' => 'numeric',
                    'message' => 'A renda mensal deve ser um número',
                ]),
                new Assert\GreaterThanOrEqual([
                    'value' => 0,
                    'message' => 'A renda mensal deve ser maior ou igual a 0',
                ]),
            ],
            'preferred_contact' => [
                new Assert\Choice([
                    'choices' => ['email', 'phone', 'whatsapp'],
                    'message' => 'O contato preferido deve ser: email, phone ou whatsapp',
                ]),
            ],
            'newsletter_subscription' => [
                new Assert\Type([
                    'type' => 'bool',
                    'message' => 'A inscrição no newsletter deve ser verdadeiro ou falso',
                ]),
            ],
            'sms_notifications' => [
                new Assert\Type([
                    'type' => 'bool',
                    'message' => 'As notificações SMS devem ser verdadeiro ou falso',
                ]),
            ],
            'accept_terms' => [
                new Assert\NotBlank(['message' => 'A aceitação dos termos é obrigatória']),
                new Assert\Type([
                    'type' => 'bool',
                    'message' => 'A aceitação dos termos deve ser verdadeiro ou falso',
                ]),
            ],
            'accept_privacy' => [
                new Assert\NotBlank(['message' => 'A aceitação da política de privacidade é obrigatória']),
                new Assert\Type([
                    'type' => 'bool',
                    'message' => 'A aceitação da política de privacidade deve ser verdadeiro ou falso',
                ]),
            ],
            'accept_communications' => [
                new Assert\NotBlank(['message' => 'A aceitação das comunicações é obrigatória']),
                new Assert\Type([
                    'type' => 'bool',
                    'message' => 'A aceitação das comunicações deve ser verdadeiro ou falso',
                ]),
            ],
        ];
    }
}
