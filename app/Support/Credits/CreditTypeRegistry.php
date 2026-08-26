<?php

namespace App\Support\Credits;

use InvalidArgumentException;

class CreditTypeRegistry
{
    protected array $types = [
        'ai_credits' => [
            'label' => 'AI Credits',
            'balance_column' => 'ai_credits',
            'icon' => 'ai',
        ],

        'sms_credits' => [
            'label' => 'SMS Credits',
            'balance_column' => 'sms_credits',
            'icon' => 'sms',
        ],

        'email_credits' => [
            'label' => 'Email Credits',
            'balance_column' => 'email_credits',
            'icon' => 'email',
        ],

        'whatsapp_credits' => [
            'label' => 'WhatsApp Credits',
            'balance_column' => 'whatsapp_credits',
            'icon' => 'whatsapp',
        ],
    ];


    public function all(): array
    {
        return $this->types;
    }


    public function has(
        string $type
    ): bool {
        return isset(
            $this->types[$type]
        );
    }


    public function get(
        string $type
    ): array {

        if (!$this->has($type)) {
            throw new InvalidArgumentException(
                "Unsupported credit type [{$type}]."
            );
        }

        return $this->types[$type];
    }


    public function balanceColumn(
        string $type
    ): string {
        return $this->get(
            $type
        )['balance_column'];
    }


    /**
     * Future modules can register new credit types
     * without modifying the payment engine.
     */
    public function register(
        string $type,
        array $definition
    ): void {

        if (
            empty(
                $definition['balance_column']
            )
        ) {
            throw new InvalidArgumentException(
                'A credit type requires balance_column.'
            );
        }

        $this->types[$type] =
            $definition;
    }
}
