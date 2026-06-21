<?php

namespace App\Http\Requests\Concerns;

trait NormalizesCurrencyInput
{
    protected function normalizeCurrencyInput(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $cleanValue = preg_replace('/[^\d,.\-]/', '', (string) $value);

        if ($cleanValue === null || $cleanValue === '') {
            return $cleanValue;
        }

        $lastComma = strrpos($cleanValue, ',');
        $lastDot = strrpos($cleanValue, '.');

        if ($lastComma !== false && $lastDot !== false) {
            if ($lastComma > $lastDot) {
                $cleanValue = str_replace('.', '', $cleanValue);
                return str_replace(',', '.', $cleanValue);
            }

            return str_replace(',', '', $cleanValue);
        }

        if ($lastComma !== false) {
            $parts = explode(',', $cleanValue);
            $lastPartLength = strlen((string) end($parts));

            return $lastPartLength <= 2 && count($parts) === 2
                ? str_replace(',', '.', $cleanValue)
                : str_replace(',', '', $cleanValue);
        }

        if ($lastDot !== false) {
            $parts = explode('.', $cleanValue);
            $lastPartLength = strlen((string) end($parts));

            return $lastPartLength <= 2 && count($parts) === 2
                ? $cleanValue
                : str_replace('.', '', $cleanValue);
        }

        return $cleanValue;
    }
}
