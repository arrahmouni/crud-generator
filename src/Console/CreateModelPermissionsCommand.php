<?php

namespace  ArRahmouni\CrudGenerator\Console;

use Illuminate\Support\Str;
use ArRahmouni\CrudGenerator\Classes\Stub;
use ArRahmouni\CrudGenerator\Traits\ModuleCommandTrait;
use ArRahmouni\CrudGenerator\Config\GenerateConfigReader;
use Symfony\Component\Console\Input\InputArgument;

class CreateModelPermissionsCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The name of argument name.
     *
     * @var string
     */
    protected $argumentName = 'name';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'create:model-permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model permission for the specified module.';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name'     , InputArgument::REQUIRED, 'The migration name will be created.'],
            ['module'   , InputArgument::REQUIRED, 'The name of module will be created.'],
            ['model'    , InputArgument::REQUIRED, 'The model name will be created.'],
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
        ];
    }


    /**
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return Stub::create('/model/model_permissions.stub', [
            'NAMESPACE'             => $this->getClassNamespace($module),
            'class'                 => $this->getClass(),
            'PERMISSION_NAMESPACE'  => $this->argument('model'),
        ]);
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $generatorPath = GenerateConfigReader::read('permission');

        return $path . $generatorPath->getPath() . '/' . $this->getFileName() . '.php';
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return Str::studly($this->argument('name'));
    }

    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace(): string
    {
        $module = $this->laravel['modules'];

        return $module->config('paths.generator.permission.namespace') ?: $module->config('paths.generator.permission.path', 'Permissions');
    }

    /**
     * Run the command.
     */
    public function handle(): int
    {
        $this->components->info('Creating model permission...');

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        if (app()->environment() === 'testing') {
            return 0;
        }

        return 0;
    }
}
