<?php

use DateTimeImmutable;

class TypeManager
{
    /**
     * Fonction pour obtenir le type converti
     *
     * @param int $fieldType Le type du champ
     * @param mixed $fieldValue La valeur du champ
     * @return mixed La valeur convertie en fonction du type
     */
    public static function convert(int $fieldType, mixed $fieldValue): mixed
    {
        if (!Cfg::get('db.auto_convert_types')) {
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

    /**
     * Convertit une chaîne en UTF-8 lors de la récupération des données.
     * Cela permet de s'assurer que les accents sont correctement gérés.
     *
     * @param string $value La valeur à convertir
     * @return string La valeur convertie
     */

    public static function convertToString(mixed $value): string
    {
        if ($value !== null) {
            return mb_convert_encoding($value, 'UTF-8', LANGUAGE);
        } else {
            return "";
        }
    }

    /**
     * Convertit une chaîne UTF-8 avant de l'envoyer à la base de données.
     * Cela permet de s'assurer que les accents sont correctement stockés.
     *
     * @param string $value La valeur à convertir
     * @return string La valeur convertie
     */
    public static function encodeToString(mixed $value): string
    {
        if ($value !== null) {
            return mb_convert_encoding($value, LANGUAGE, 'UTF-8');
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
