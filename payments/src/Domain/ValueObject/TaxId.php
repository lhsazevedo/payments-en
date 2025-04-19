<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidTaxIdException;

/**
 * General value object for CPF and CNPJ.
 */
readonly class TaxId
{
    public function __construct(
        public string $value,
    ) {
        if (preg_match('/\D/', $value)) {
            $this->invalid();
        }

        match (strlen($value)) {
            11 => $this->validateCpf(),
            14 => $this->validateCnpj(),
            default => $this->invalid(),
        };
    }

    private function validateCpf(): void
    {
        // TODO(Lucas): Implement CPF check digit algorithm
        // See https://pt.wikipedia.org/wiki/Cadastro_de_Pessoas_F%C3%ADsicas#C%C3%A1lculo_do_d%C3%ADgito_verificador
    }

    private function validateCnpj(): void
    {
        // TODO(Lucas): Implement CNPJ check digit validation algorithm
        // See https://pt.wikipedia.org/wiki/Cadastro_Nacional_da_Pessoa_Jur%C3%ADdica#Algoritmo_de_Valida%C3%A7%C3%A3o[carece_de_fontes?]
        // Note: Alphanumeric CNPJ format will be adopted in 2026.
    }

    /**
     * @throws InvalidTaxIdException
     */
    private function invalid(): void
    {
        throw new InvalidTaxIdException();
    }
}
