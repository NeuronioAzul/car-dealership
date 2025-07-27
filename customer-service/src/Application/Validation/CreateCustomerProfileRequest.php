<?php

namespace App\Application\Validation;


use App\Application\Validation\BaseRequest;
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
            'phone' => [
                new Assert\NotBlank(['message' => 'O telefone é obrigatório']),
                new Assert\Length([
                    'max' => 20,
                    'maxMessage' => 'O telefone não pode ter mais de {{ limit }} caracteres',
                ]),
            ],
            'birth_date' => [
                new Assert\NotBlank(['message' => 'A data de nascimento é obrigatória']),
                new Assert\Date(['message' => 'A data de nascimento deve estar no formato YYYY-MM-DD']),
            ],
            'address.street' => [
                new Assert\NotBlank(['message' => 'A rua é obrigatória']),
                new Assert\Length([
                    'max' => 255,
                    'maxMessage' => 'A rua não pode ter mais de {{ limit }} caracteres',
                ]),
            ],
            'address.number' => [
                new Assert\NotBlank(['message' => 'O número é obrigatório']),
                new Assert\Length([
                    'max' => 20,
                    'maxMessage' => 'O número não pode ter mais de {{ limit }} caracteres',
                ]),
            ],
            'address.neighborhood' => [
                new Assert\NotBlank(['message' => 'O bairro é obrigatório']),
                new Assert\Length([
                    'max' => 100,
                    'maxMessage' => 'O bairro não pode ter mais de {{ limit }} caracteres',
                ]),
            ],
            'address.city' => [
                new Assert\NotBlank(['message' => 'A cidade é obrigatória']),
                new Assert\Length([
                    'max' => 100,
                    'maxMessage' => 'A cidade não pode ter mais de {{ limit }} caracteres',
                ]),
            ],
            'address.state' => [
                new Assert\NotBlank(['message' => 'O estado é obrigatório']),
                new Assert\Length([
                    'max' => 2,
                    'maxMessage' => 'O estado deve ter exatamente {{ limit }} caracteres',
                ]),
                new Assert\Regex([
                    'pattern' => '/^[A-Z]{2}$/',
                    'message' => 'O estado deve ser uma sigla de dois caracteres em letras maiúsculas',
                ]),
            ],
            'address.zip_code' => [
                new Assert\NotBlank(['message' => 'O CEP é obrigatório']),
                new Assert\Length([
                    'max' => 10,
                    'maxMessage' => 'O CEP não pode ter mais de {{ limit }} caracteres',
                ]),
                new Assert\Regex([
                    'pattern' => '/^\d{5}-?\d{3}$/',
                    'message' => 'O CEP deve estar no formato XXXXX-XXX ou XXXXXXX',
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
                    'choices' => ['single', 'married', 'divorced', 'widowed', 'common_law'],
                    'message' => 'O estado civil deve ser: single, married, divorced, widowed ou common_law',
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
            'total_purchases' => [
                new Assert\Type([
                    'type' => 'numeric',
                    'message' => 'O total de compras deve ser um número',
                ]),
                new Assert\GreaterThanOrEqual([
                    'value' => 0,
                    'message' => 'O total de compras deve ser maior ou igual a 0',
                ]),
            ],
            'total_spent' => [
                new Assert\Type([
                    'type' => 'numeric',
                    'message' => 'O total gasto deve ser um número',
                ]),
                new Assert\GreaterThanOrEqual([
                    'value' => 0,
                    'message' => 'O total gasto deve ser maior ou igual a 0',
                ]),
            ],
            'last_purchase_date' => [
                new Assert\Date(['message' => 'A data da última compra deve estar no formato YYYY-MM-DD']),
            ],
            'customer_score' => [
                new Assert\Type([
                    'type' => 'numeric',
                    'message' => 'A pontuação do cliente deve ser um número',
                ]),
                new Assert\Range([
                    'min' => 0,
                    'max' => 1000,
                    'notInRangeMessage' => 'A pontuação do cliente deve estar entre {{ min }} e {{ max }}',
                ]),
            ],
            'customer_tier' => [
                new Assert\Choice([
                    'choices' => ['bronze', 'silver', 'gold', 'platinum'],
                    'message' => 'O nível do cliente deve ser: bronze, silver, gold ou platinum',
                ]),
            ],
            'address.complement' => [
                new Assert\Length([
                    'max' => 100,
                    'maxMessage' => 'O complemento do endereço não pode ter mais de {{ limit }} caracteres',
                ]),
            ],
            'accept_terms' => [
                new Assert\NotBlank(['message' => 'A aceitação dos termos é obrigatória']),
                new Assert\Choice([
                    'choices' => [0, 1],
                    'message' => 'A aceitação dos termos deve ser 0 ou 1',
                ]),
            ],
            'accept_privacy' => [
                new Assert\NotBlank(['message' => 'A aceitação da política de privacidade é obrigatória']),
                new Assert\Choice([
                    'choices' => [0, 1],
                    'message' => 'A aceitação da política de privacidade deve ser 0 ou 1',
                ]),
            ],
            'accept_communications' => [
                new Assert\NotBlank(['message' => 'A aceitação das comunicações é obrigatória']),
                new Assert\Choice([
                    'choices' => [0, 1],
                    'message' => 'A aceitação das comunicações deve ser 0 ou 1',
                ]),
            ],
        ];
    }
}