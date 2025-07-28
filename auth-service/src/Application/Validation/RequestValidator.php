<?php

declare(strict_types=1);

namespace App\Application\Validation;

use App\Application\Exceptions\ValidationException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestValidator
{
    private ValidatorInterface $validator;

    public function __construct()
    {
        $this->validator = Validation::createValidator();
    }

    /**
     * @param array $data
     * @param array<string, Constraint[]> $constraints
     * @throws ValidationException
     */
    public function validate(array $data, array $constraints): void
    {
        $collectionConstraint = new Assert\Collection([
            'fields' => $constraints,
            'allowExtraFields' => false,
            'allowMissingFields' => false,
        ]);

        $violations = $this->validator->validate($data, $collectionConstraint);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $propertyPath = trim($violation->getPropertyPath(), '[]');
                $errors[] = sprintf('%s: %s', $propertyPath, $violation->getMessage());
            }

            throw new ValidationException($errors);
        }
    }

    public function getRegisterUserConstraints(): array
    {
        return [
            'name' => [
                new Assert\NotBlank(['message' => 'Nome é obrigatório']),
                new Assert\Length([
                    'min' => 2,
                    'max' => 100,
                    'minMessage' => 'Nome deve ter pelo menos {{ limit }} caracteres',
                    'maxMessage' => 'Nome deve ter no máximo {{ limit }} caracteres'
                ])
            ],
            'email' => [
                new Assert\NotBlank(['message' => 'Email é obrigatório']),
                new Assert\Email(['message' => 'Email deve ter um formato válido'])
            ],
            'password' => [
                new Assert\NotBlank(['message' => 'Senha é obrigatória']),
                new Assert\Length([
                    'min' => 8,
                    'minMessage' => 'Senha deve ter pelo menos {{ limit }} caracteres'
                ])
            ],
            'phone' => [
                new Assert\NotBlank(['message' => 'Telefone é obrigatório']),
                new Assert\Regex([
                    'pattern' => '/^\d{10,11}$/',
                    'message' => 'Telefone deve conter 10 ou 11 dígitos'
                ])
            ],
            'birth_date' => [
                new Assert\NotBlank(['message' => 'Data de nascimento é obrigatória']),
                new Assert\Date(['message' => 'Data de nascimento deve ter um formato válido (YYYY-MM-DD)'])
            ],
            'address' => [
                new Assert\Collection([
                    'fields' => [
                        'street' => [
                            new Assert\NotBlank(['message' => 'Rua é obrigatória']),
                            new Assert\Length(['max' => 255])
                        ],
                        'number' => [
                            new Assert\NotBlank(['message' => 'Número é obrigatório']),
                            new Assert\Length(['max' => 20])
                        ],
                        'neighborhood' => [
                            new Assert\NotBlank(['message' => 'Bairro é obrigatório']),
                            new Assert\Length(['max' => 100])
                        ],
                        'city' => [
                            new Assert\NotBlank(['message' => 'Cidade é obrigatória']),
                            new Assert\Length(['max' => 100])
                        ],
                        'state' => [
                            new Assert\NotBlank(['message' => 'Estado é obrigatório']),
                            new Assert\Length(['min' => 2, 'max' => 2, 'exactMessage' => 'Estado deve ter exatamente 2 caracteres'])
                        ],
                        'zip_code' => [
                            new Assert\NotBlank(['message' => 'CEP é obrigatório']),
                            new Assert\Regex([
                                'pattern' => '/^\d{5}-?\d{3}$/',
                                'message' => 'CEP deve ter o formato 12345-678 ou 12345678'
                            ])
                        ]
                    ],
                    'allowExtraFields' => false
                ])
            ],
            'role' => [
                new Assert\Choice([
                    'choices' => ['customer', 'admin'],
                    'message' => 'Role deve ser customer ou admin'
                ])
            ],
            'accept_terms' => [
                new Assert\IsTrue(['message' => 'É necessário aceitar os termos de uso'])
            ],
            'accept_privacy' => [
                new Assert\IsTrue(['message' => 'É necessário aceitar a política de privacidade'])
            ],
            'accept_communications' => [
                new Assert\Type(['type' => 'bool', 'message' => 'Campo de comunicações deve ser verdadeiro ou falso'])
            ]
        ];
    }

    public function getLoginConstraints(): array
    {
        return [
            'email' => [
                new Assert\NotBlank(['message' => 'Email é obrigatório']),
                new Assert\Email(['message' => 'Email deve ter um formato válido'])
            ],
            'password' => [
                new Assert\NotBlank(['message' => 'Senha é obrigatória'])
            ]
        ];
    }
}
