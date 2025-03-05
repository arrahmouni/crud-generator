<?php

namespace ArRahmouni\CrudGenerator\Console;

use ArRahmouni\CrudGenerator\Classes\Stub;
use ArRahmouni\CrudGenerator\Traits\ModuleCommandTrait;
use ArRahmouni\CrudGenerator\Config\GenerateConfigReader;
use Symfony\Component\Console\Input\InputArgument;

class CreateModelTranslationCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The name of argument name.
     *
     * @var string
     */
    protected $argumentName = 'model';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'create:model-translation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model translation for the specified module.';


    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model'        , InputArgument::REQUIRED, 'The name of model will be created.'],
            ['module'       , InputArgument::REQUIRED, 'The name of module will be used.'],
            ['foreign_key'  , InputArgument::REQUIRED, 'The name of foreign key will be used.'],
        ];
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub('/model/model_translation.stub', [
            'NAMESPACE'             => $this->getClassNamespace($module),
            'CLASS'                 => $this->getClass(),
            'FOREIGN_KEY'           => $this->argument('foreign_key'),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $modelPath = GenerateConfigReader::read('model');

        return $path . $modelPath->getPath() . '/' . $this->argument('model') . '.php';
    }

    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace(): string
    {
        $module = $this->laravel['modules'];

        return $module->config('paths.generator.model.namespace') ?: $module->config('paths.generator.model.path', 'Entities');
    }

    /**
     * Run the command.
     */
    public function handle(): int
    {
        $this->components->info('Creating model translation...');

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        if (app()->environment() === 'testing') {
            return 0;
        }

        return 0;
    }
}
