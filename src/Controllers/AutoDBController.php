<?php

namespace Leivingson\AutoDB\Controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\stringContains;

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

    public function fillModel($contents, $tableName)
    {
        $tableDetails = DB::select("DESCRIBE $tableName");
        $fillable = [];
        $casts = [];
        foreach ($tableDetails as $tableDetail) {
            $type = explode("(", $tableDetail->Type)[0];
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

            if ($tableDetail->Key != 'PRI' || ($tableDetail->Key == 'PRI' && $tableDetail->Extra != 'auto_increment')) {
                $casts[$tableDetail->Field] = $type;
                array_push($fillable, $tableDetail->Field);
            }
        }
        $withFillable = $this->incrementField($contents, $fillable);
        $withCasts = $this->incrementCasts($withFillable, $casts);

        return $withCasts;
    }

    private function incrementField($contents, $fillable)
    {
        $putFillable = explode("\$fillable = [", $contents);
        $fillableString = "";
        foreach ($fillable as $value) {
            $fillableString .= "'$value', \n";
        }
        $withFillable = $putFillable[0] . "\$fillable = [\n" . $fillableString . $putFillable[1];
        return $withFillable;
    }

    private function incrementCasts($contents, $casts)
    {
        $putCasts = explode("\$casts = [", $contents);
        $castsString = "";
        foreach ($casts as $key => $value) {
            $castsString .= "'$key' => '$value', \n";
        }
        $withCasts = $putCasts[0] . "\$casts = [\n" . $castsString . $putCasts[1];
        return $withCasts;
    }
}
