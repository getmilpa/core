<?php

/**
 * This file is part of Milpa Core — the framework-agnostic core of the Milpa PHP framework.
 *
 * (c) Rodrigo Vicente - TeamX Agency — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 *
 * @link    https://github.com/getmilpa/core
 */

declare(strict_types=1);

namespace Milpa\Exceptions;

/**
 * Thrown when a required secret (a database/redis password, an API key, …) is absent.
 * Fail-closed: Milpa no longer falls back to insecure defaults like 'root'.
 */
final class SecretMissingException extends \RuntimeException implements MilpaExceptionInterface
{
    private function __construct(string $message, private readonly string $errorCode)
    {
        parent::__construct($message);
    }

    /**
     * El error de un secreto requerido que no llegó, nombrando la variable exacta.
     *
     * Recibe SOLO el nombre: un mensaje de "falta el secreto" no tiene por qué
     * aprender nunca a incluir el secreto que sí llegó.
     */
    public static function required(string $name): self
    {
        return new self(
            sprintf(
                "[MILPA_SECRET_MISSING] %s is required but was not provided. Milpa no longer falls "
                . "back to insecure defaults like 'root'. Set %s explicitly in your environment.\n"
                . '→ https://academy.milpa.lat/learn/fundamentos/secretos-config',
                $name,
                $name,
            ),
            'MILPA_SECRET_MISSING',
        );
    }

    /** El código estable de este error, para quien lo clasifique sin leer el texto. */
    public function errorCode(): string
    {
        return $this->errorCode;
    }
}
