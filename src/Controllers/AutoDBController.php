<?php

namespace Leivingson\AutoDB\Controllers;

use App\Models\User;
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
            'namespace' => 'App\Models',
            'tableName' => ucwords(Pluralizer::singular($tableName)),
            'fillables' => $fillable,
            'casts' => $casts
        ])->render();

        return $page;
    }

    public function fillMigration($tableName)
    {
        $tableDetails = DB::select("DESCRIBE $tableName");
        $foreignKeys = DB::select("SHOW CREATE TABLE $tableName");
        $properties = [];
        $primaryKeys = [];

        $foreignKeys = $foreignKeys[0]->{'Create Table'};
        $foreignKeys = explode("\n", $foreignKeys);
        $foreignKeys = array_filter($foreignKeys, function ($value) {
            return strpos($value, 'FOREIGN KEY') !== false;
        });

        $foreignKeys = $this->transcribeForeignKeys($foreignKeys);

        foreach ($tableDetails as $tableDetail) {
            $unsinged = strpos($tableDetail->Type, 'unsigned') !== false ? true : false;
            $type =  $this->getEloquentTypeFromMysql(explode("(", $tableDetail->Type)[0], $unsinged, $tableDetail->Type);
            $lengthOrEnumValues = explode(")", explode("(", $tableDetail->Type)[1] ?? null)[0] ?? null;
            $key = $tableDetail->Key;
            if ($key == 'PRI' && $tableDetail->Extra != 'auto_increment') {
                array_push($primaryKeys, $tableDetail->Field);
            }
            $extra = $tableDetail->Extra;
            $null = $tableDetail->Null == 'YES' || $type == "timestamp" ? true : false;

            $acceptLengthOrEnumValues = ['string', 'float', 'decimal', 'char', 'enum', 'set'];
            $default = ctype_alpha($tableDetail->Default) ? "'" . $tableDetail->Default . "'" : $tableDetail->Default;
            $defaultFunction = null;
            if (strpos($default, '(')) {
                $defaultFunction = $this->transcribeDefaultFunction($default);
                $default = null;
            }

            if (in_array($type, $acceptLengthOrEnumValues)) {
                $lengthOrEnumValues = $lengthOrEnumValues;
            } else {
                $lengthOrEnumValues = "";
            }

            $properties[$tableDetail->Field] = [
                'type' => $type,
                'lengthOrEnumValues' => $lengthOrEnumValues,
                'key' => $key,
                'extra' => $extra,
                'null' => $null,
                'default' => $default,
                'defaultFunction' => $defaultFunction,
            ];
        }

        $page = view("migration", [
            'tableName' => strtolower(Pluralizer::singular($tableName)),
            'properties' => $properties,
            'primaryKeys' => $primaryKeys,
            'foreignKeys' => $foreignKeys
        ])->render();

        return $page;
    }

    public function getEloquentTypeFromMysql($type, Bool $unsinged, $entireType)
    {
        // Exceptions mysql types that are not in eloquent
        switch ($type) {
            case 'varchar':
                return 'string';
                break;
            case 'blob':
                return 'binary';
                break;
            default:
                break;
        }

        if ($entireType == 'tinyint(1)') return 'boolean';

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
            'timestamp',
            'time',
            'nullableTimestamps',
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
        $type = $unsinged ? 'unsigned' .  $type : $type;

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

    public function transcribeDefaultFunction($default)
    {
        $eloquentDefaultFunctions = [
            "useCurrent",
            "useCurrentOnUpdate"
        ];

        $default = strtolower($default);

        $returnType = $eloquentDefaultFunctions[0];
        $count = 0;
        foreach ($eloquentDefaultFunctions as $eloquentDefaultFunction) {
            $lev = levenshtein($default, $eloquentDefaultFunction, 0, 1, 1);
            if ($lev == 0) {
                $returnType = $eloquentDefaultFunction;
                break;
            }

            if ($lev < $count || $count == 0) {
                $count = $lev;
                $returnType = $eloquentDefaultFunction;
            }
        }

        return "->" . $returnType . "()";
    }

    public function transcribeForeignKeys($foreignKeys)
    {
        $declarations = [];

        foreach ($foreignKeys as $foreignKey) {
            $declaration = "\$table->foreign('";
            $foreignId = explode("FOREIGN KEY", $foreignKey)[1];
            $foreignId = explode("REFERENCES", $foreignId);
            $foreignId = explode("(", $foreignId[0]);
            $foreignId = explode("`", $foreignId[1]);
            $foreignId = $foreignId[1];
            $declaration .= $foreignId . "')->references('";
            $reference = explode("(", $foreignId);
            $reference = $reference[0];
            $declaration .= $reference . "')->on('";
            $table = explode("REFERENCES", $foreignKey);
            $table = explode("`", $table[1]);
            $table = $table[1];
            $declaration .= $table . "');";
            array_push($declarations, $declaration);
        }
        return $declarations;
    }
}
