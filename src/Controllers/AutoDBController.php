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
            'teste' => 'App\Models',
            'tableName' => ucwords(Pluralizer::singular($tableName)),
            'fillables' => $fillable,
            'casts' => $casts
        ])->render();

        return $page;
    }
}
