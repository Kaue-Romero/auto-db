<?php

namespace Leivingson\AutoDB\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;

class AutoDBCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autodb:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    /**
     * Filesystem instance
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->files->exists(app_path('Models'))) {
            $this->makeDirectory(app_path('Models'));
        }

        $db = new \Leivingson\AutoDB\Controllers\AutoDBController();

        $tablesNames = $db->getTablesName($this);

        foreach ($tablesNames as $tableName) {
            $path = $this->getSourceFilePath($tableName);
            $contents = $this->getSourceFile($tableName);

            if (!$this->files->exists($path)) {
                $result = $db->fillModel($contents, $tableName);
                $this->files->put($path, $result);
                $this->info("Model : {$tableName} created");
            } else {
                $this->info("Model : {$tableName} already exits");
            }


        }
    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getStubPath()
    {
        return __DIR__ . '/../../Stubs/DefaultModel.stub';
    }

    /**
     **
     * Map the stub variables present in stub to its value
     *
     * @return array
     *
     */
    public function getStubVariables($tableName)
    {
        return [
            'namespace' => 'App\\Models',
            'class' => $this->getSingularClassName($tableName),
        ];
    }

    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public function getSourceFile($tableName)
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables($tableName));
    }


    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return bool|mixed|string
     */
    public function getStubContents($stub, $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('{{ ' . $search . ' }}', $replace, $contents);
        }

        return $contents;
    }

    /**
     * Get the full path of generate class
     *
     * @return string
     */
    public function getSourceFilePath($tableName)
    {
        return app_path('Models/' . $this->getSingularClassName($tableName) . '.php');
    }

    /**
     * Return the Singular Capitalize Name
     * @param $name
     * @return string
     */
    public function getSingularClassName($name)
    {
        return ucwords(Pluralizer::singular($name));
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }
}
