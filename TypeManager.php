<?php

namespace Core;

use DateTimeImmutable;

class TypeManager
{
    public static function convert(int $fieldType, mixed $fieldValue): mixed
    {
        if (!AUTO_CONVERT_TYPES) {
            return $fieldValue;
        }
        return match ($fieldType) {
            TypeHF::SIGNED_INT, TypeHF::UNSIGNED_INT => (int) $fieldValue,
            TypeHF::FLOAT => (float) $fieldValue,
            TypeHF::DATETIME => self::convertToDateTime($fieldValue),
            TypeHF::DATE => self::convertToDate($fieldValue),
            TypeHF::BOOLEAN => (bool) $fieldValue,
            TypeHF::TEXT, TypeHF::TIME => self::convertToString($fieldValue),
            default => $fieldValue
        };
    }

    private static function convertToString(mixed $value): string
    {
        if ($value !== null) {
            return mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
        } else {
            return "";
        }
    }

    private static function convertToDate(mixed $value): ?DateTimeImmutable
    {
        if (empty($value)) {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('d/m/Y', $value);
        if ($date === false) {
            return null;
        }
        return $date->setTime(0, 0, 0, 0);
    }

    private static function convertToDateTime(mixed $value): ?DateTimeImmutable
    {
        if (empty($value)) {
            return null;
        }
        return DateTimeImmutable::createFromFormat('d/m/Y H:i:s', $value) ?: null;
    }
}
