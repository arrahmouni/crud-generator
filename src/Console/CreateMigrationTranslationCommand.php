<?php

namespace  ArRahmouni\CrudGenerator\Console;

use Illuminate\Support\Str;
use ArRahmouni\CrudGenerator\Classes\Stub;
use ArRahmouni\CrudGenerator\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputOption;
use ArRahmouni\CrudGenerator\Config\GenerateConfigReader;
use Symfony\Component\Console\Input\InputArgument;
use ArRahmouni\CrudGenerator\Classes\Migrations\NameParser;
use ArRahmouni\CrudGenerator\Classes\Migrations\SchemaParser;

class CreateMigrationTranslationCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'create:migration-translation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration translation for the specified module.';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name'         , InputArgument::REQUIRED, 'The migration name will be created.'],
            ['foreign_key'  , InputArgument::REQUIRED, 'The foreign key will be created.'],
            ['module'       , InputArgument::OPTIONAL, 'The name of module will be created.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['fields'   , null, InputOption::VALUE_OPTIONAL, 'The specified fields table.', null],
            ['plain'    , null, InputOption::VALUE_NONE, 'Create plain migration.'],
        ];
    }

    /**
     * Get schema parser.
     *
     * @return SchemaParser
     */
    public function getSchemaParser()
    {
        return new SchemaParser($this->option('fields'));
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $parser = new NameParser($this->argument('name'));

        return Stub::create('/migration/create_translation.stub', [
            'class'     => $this->getClass(),
            'table'     => $parser->getTableName(),
            'foreign'   => $this->argument('foreign_key'),
        ]);
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $generatorPath = GenerateConfigReader::read('migration');

        return $path . $generatorPath->getPath() . '/' . $this->getFileName() . '.php';
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return date('Y_m_d_His_', strtotime('+5 seconds')) . $this->getSchemaName();
    }

    /**
     * @return array|string
     */
    private function getSchemaName()
    {
        return $this->argument('name');
    }

    /**
     * @return string
     */
    private function getClassName()
    {
        return Str::studly($this->argument('name'));
    }

    public function getClass()
    {
        return $this->getClassName();
    }

    /**
     * Run the command.
     */
    public function handle(): int
    {
        $this->components->info('Creating migration translation...');

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        if (app()->environment() === 'testing') {
            return 0;
        }

        return 0;
    }
}
