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
    protected $signature = 'autodb:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate models from database tables given by the .env database connection.';

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

        $controller = new \Leivingson\AutoDB\Controllers\AutoDBController();

        $tablesNames = $controller->getTablesName($this);

        $progress = $this->getOutput()->createProgressBar(count($tablesNames));
        $progress->setFormat("%message% %current%/%max% [%bar%] %percent:3s%%");
        $progress->setMessage("Creating database settings for tables...");
        $progress->setProgress(0);
        $progress->minSecondsBetweenRedraws(.1);
        $progress->start();
        foreach ($tablesNames as $tableName) {
            $modelPath = $this->getSourceFilePath($tableName);

            if (!$this->files->exists($modelPath)) {
                $result = $controller->fillModel($tableName);
                $this->files->put($modelPath, '<?php' . PHP_EOL . $result);
            }

            $migrationPath = $this->getMigrationFilePath($tableName);
            if (!$this->files->exists($migrationPath)) {
                $result = $controller->fillMigration($tableName);
                $this->files->put($migrationPath, '<?php' . PHP_EOL . $result);
            }
            $progress->advance();
        }
        $progress->setMessage("<fg=green>Database settings created successfully</>");
        $progress->finish();
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

    public function getMigrationFilePath($tableName)
    {
        $formatedDateTime = str_replace([" ", ":", "-", "T"], "_", now()->toDateTimeLocalString());

        return database_path("migrations/".
            $formatedDateTime .
                "_create_" .
                $this->getPluralMigrationName($tableName) .
                "_table" .
                ".php"
        );
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

    public function getPluralMigrationName($name)
    {
        return strtolower(Pluralizer::plural($name));
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
