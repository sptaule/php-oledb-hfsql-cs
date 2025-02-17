<?php

use COM;
use Exception;

final class Database
{
    private static ?Database $instance = null;
    private COM $connection;

    private function __construct() {
        try {
            $this->connection = new COM("ADODB.Connection") or die("Impossible d'instancier un objet ADO");
            $this->connection->ConnectionString = $this->getConnectionString();
            $this->connection->Open();
        } catch (Exception $ex) {
            throw new Exception("Erreur de connexion : " . $ex->getMessage());
        }
    }

    private static function getConnectionString(): string
    {
        // Format de la chaîne des propriétés étendues
        $extendedProperties = sprintf(
            '%s;Language=%s;Compression=%s;Cryptage=%s;',
            FILE_PASSWORD_STRING, LANGUAGE, COMPRESSION, ENCRYPTION
        );

        // Chaîne de connexion finale
        return sprintf(
            'Provider=PCSOFT.HFSQL; Data Source=%s; Initial Catalog=%s; User ID=%s; Password=%s; Extended Properties="%s"',
            HOSTNAME, DATABASE, USERNAME, PASSWORD, $extendedProperties
        );
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    private static function getConnection(): COM
    {
        return self::getInstance()->connection;
    }

    private static function createCommand(string $query, array $params = []): COM
    {
        $cmd = new COM("ADODB.Command");
        $cmd->ActiveConnection = self::getConnection();
        $cmd->CommandText = $query;

        foreach ($params as $index => $param) {
            $cmd->Parameters->Append($cmd->CreateParameter("param" . $index, 8, 1, 255, $param));
        }

        return $cmd;
    }

    private static function fetchRow($rs): array
    {
        $row = [];
        for ($i = 0; $i < $rs->Fields->Count; $i++) {
            $fieldName = $rs->Fields->Item($i)->Name;
            $fieldType = $rs->Fields->Item($i)->Type;
            $fieldValue = $rs->Fields->Item($i)->Value;
            $row[$fieldName] = TypeManager::convert($fieldType, $fieldValue);
        }
        return $row;
    }

    public static function get(string $query, array $params = []): ?array
    {
        $cmd = self::createCommand($query, $params);
        $rs = $cmd->Execute();
        $result = null;

        if (!$rs->EOF) {
            $result = self::fetchRow($rs);
        }

        $rs->Close();
        return $result;
    }

    public static function getAll(string $query, array $params = []): array
    {
        $cmd = self::createCommand($query, $params);
        $rs = $cmd->Execute();
        $results = [];

        while (!$rs->EOF) {
            $results[] = self::fetchRow($rs);
            $rs->MoveNext();
        }

        $rs->Close();
        return $results;
    }

    public static function insert(string $table, array $data): bool
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = TypeManager::encodeToString($value);
            }
        }

        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        $cmd = self::createCommand($query, array_values($data));
        $cmd->Execute();

        return true;
    }

    public static function update(string $table, array $data, string $whereClause, array $whereParams): bool
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = TypeManager::encodeToString($value);
            }
        }

        $setClauses = [];
        foreach ($data as $column => $value) {
            $setClauses[] = "$column = ?";
        }
        $setClause = implode(", ", $setClauses);
        $query = "UPDATE $table SET $setClause WHERE $whereClause";

        $params = array_merge(array_values($data), $whereParams);
        $cmd = self::createCommand($query, $params);
        $cmd->Execute();

        return true;
    }

    public static function delete(string $table, string $whereClause, array $whereParams): bool
    {
        $query = "DELETE FROM $table WHERE $whereClause";
        $cmd = self::createCommand($query, $whereParams);
        $cmd->Execute();

        return true;
    }
}
