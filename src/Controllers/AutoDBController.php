<?php

namespace Leivingson\AutoDB\Controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Pluralizer;

class AutoDBController
{
    public function getTablesName(Command $command)
    {
        $database = env('DB_DATABASE');
        $tables = DB::select("SHOW FULL TABLES WHERE Table_Type = 'BASE TABLE'");
        $key = 'Tables_in_' . $database;
        $tablesName = [];
        $command->info("Found: " . count($tables) . " tables");
        foreach ($tables as $table) {
            $command->info("Table found: " . $table->{$key});
            array_push($tablesName, $table->{$key});
        }
        return $tablesName;
    }

    public function fillModel($tableName)
    {
        $tableDetails = DB::select("DESCRIBE $tableName");
        $fillable = [];
        $casts = [];


        foreach ($tableDetails as $tableDetail) {
            $type = explode("(", $tableDetail->Type)[0];
            $key = $tableDetail->Key;
            $extra = $tableDetail->Extra;

            switch (true) {
                case strpos($tableDetail->Type, 'tinyint(1)') !== false:
                    $type = 'boolean';
                    break;

                case strpos($type, 'timestamp') !== false:
                    $type = 'timestamp';
                    break;

                case strpos($type, 'int') !== false:
                    $type = 'integer';
                    break;

                case $type == 'varchar':
                    $type = 'string';
                    break;

                case strpos($type, 'decimal') !== false:
                    $type = 'float';
                    break;

                default:
                    $type = 'string';
                    break;
            }

            if ($key != 'PRI' || ($key == 'PRI' && str_contains($extra, 'auto_increment'))) {
                $casts[$tableDetail->Field] = $type;
                array_push($fillable, $tableDetail->Field);
            }
        }

        $page = view("model", [
            'tableName' => ucwords(Pluralizer::singular($tableName)),
            'fillables' => $fillable,
            'casts' => $casts
        ])->render();

        return $page;
    }

    public function fillMigration($tableName)
    {
        $tableDetails = DB::select("DESCRIBE $tableName");
        $properties = [];

        foreach ($tableDetails as $tableDetail) {
            $type = explode("(", $tableDetail->Type)[0];
            $lengthOrEnumValues = explode(")", explode("(", $tableDetail->Type)[1] ?? null)[0] ?? null;
            $key = $tableDetail->Key;
            $extra = $tableDetail->Extra;
            $null = $tableDetail->Null == 'YES' ? true : false;
            $acceptLengthOrEnumValues = ['string', 'float', 'decimal', 'char', 'enum', 'set'];

            if (in_array($type, $acceptLengthOrEnumValues)) {
                $lengthOrEnumValues = $lengthOrEnumValues;
            } else {
                $lengthOrEnumValues = "";
            }

            $properties[$tableDetail->Field] = [
                'type' => $this->getEloquentTypeFromMysql($type),
                'lengthOrEnumValues' => $lengthOrEnumValues,
                'key' => $key,
                'extra' => $extra,
                'null' => $null
            ];
        }

        if($tableName == 'film')
        {
            $page = view("migration", [
                'migrationName' => ucwords(Pluralizer::plural($tableName)),
                'tableName' => strtolower(Pluralizer::plural($tableName)),
                'properties' => $properties
            ])->render();

            return $page;
        }

        return;


        $page = view("migration", [
            'migrationName' => ucwords(Pluralizer::plural($tableName)),
            'tableName' => strtolower(Pluralizer::plural($tableName)),
            'properties' => $tableDetails
        ])->render();

        return $page;
    }

    public function getEloquentTypeFromMysql($type)
    {
        // Exceptions types
        if($type == 'varchar')
        {
            return 'string';
        }

        $eloquentTypes = [
            'string',
            'bigInteger',
            'binary',
            'boolean',
            'char',
            'dateTime',
            'date',
            'decimal',
            'double',
            'enum',
            'float',
            'id',
            'integer',
            'json',
            'longText',
            'mediumInteger',
            'mediumText',
            'smallInteger',
            'set',
            'text',
            'time',
            'timestamp',
            'timestamps',
            'tinyInteger',
            'tinyText',
            'unsignedBigInteger',
            'unsignedInteger',
            'unsignedMediumInteger',
            'unsignedSmallInteger',
            'unsignedTinyInteger',
            'year',
        ];

        $type = strtolower($type);

        $returnType = $eloquentTypes[0];
        $count = 0;
        foreach ($eloquentTypes as $eloquentType) {
            $lev = levenshtein($type, $eloquentType, 0, 1, 1);
            if ($lev == 0) {
                $returnType = $eloquentType;
                break;
            }

            if ($lev < $count || $count == 0) {
                $count = $lev;
                $returnType = $eloquentType;
            }
        }

        return $returnType;
    }
}
