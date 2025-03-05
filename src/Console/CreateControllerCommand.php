<?php

namespace ArRahmouni\CrudGenerator\Console;

use Illuminate\Support\Str;
use ArRahmouni\CrudGenerator\Classes\Stub;
use ArRahmouni\CrudGenerator\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputOption;
use ArRahmouni\CrudGenerator\Config\GenerateConfigReader;
use Symfony\Component\Console\Input\InputArgument;

class CreateControllerCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The name of argument being used.
     *
     * @var string
     */
    protected $argumentName = 'controller';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'create-controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new restful controller for the specified model.';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['controller'       , InputArgument::REQUIRED, 'The name of the controller class.'],
            ['model'            , InputArgument::REQUIRED, 'The name of model will be used.'],
            ['modelNameSpace'   , InputArgument::REQUIRED, 'The name of model will be used.'],
            ['service'          , InputArgument::REQUIRED, 'The name of service will be used.'],
            ['permission'       , InputArgument::REQUIRED, 'The name of permission will be used.'],
            ['request'          , InputArgument::REQUIRED, 'The name of request will be used.'],
            ['routePrefix'      , InputArgument::REQUIRED, 'The route prefix will be used.'],
            ['module'           , InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['has-permissions'  , null, InputOption::VALUE_NONE, 'Create a new permissions for the model', null],
            ['has-soft-delete'  , null, InputOption::VALUE_NONE, 'Create a new soft delete for the model', null],
            ['has-disabled'     , null, InputOption::VALUE_NONE, 'Create a new disabled for the model', null],
        ];
    }

    /**
     * Get controller name.
     *
     * @return string
     */
    public function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $controllerPath = GenerateConfigReader::read('controller');

        return $path . $controllerPath->getPath() . '/' . $this->getControllerName() . '.php';
    }

    /**
     * @return string
     */
    protected function getTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub('/controller/controller.stub', [
            'CLASS_NAMESPACE'       => $this->getClassNamespace($module),
            'MODEL_NAMESPACE'       => $this->argument('modelNameSpace'),
            'MODULE_LOWER'          => $module->getLowerName(),
            'MODULE'                => $this->getModuleName(),
            'SERVICE_NAME'          => $this->argument('service'),
            'PERMISSION_NAME'       => $this->argument('permission'),
            'PERMISSION'            => $this->argument('permission') . '::class',
            'CLASS'                 => $this->getControllerNameWithoutNamespace(),
            'MODEL'                 => $this->argument('model'),
            'SERVICE'               => $this->argument('service'),
            'ROUTE_PREFIX'          => $this->argument('routePrefix'),
            'CREATE_REQUEST'        => $this->argument('request') . '::class',
            'CREATE_REQUEST_NAME'   => $this->argument('request'),
            'HAS_PERMISSION'        => $this->option('has-permissions') == true ? 'true' : 'false',
            'HAS_SOFT_DELETE'       => $this->option('has-soft-delete') == true ? 'true' : 'false',
            'HAS_DISABLED'          => $this->option('has-disabled') == true ? 'true' : 'false',
            'MODEL_PLUR'            => Str::plural(Str::snake($this->argument('model'))),
            'MODEL_SNAKE'           => Str::snake($this->argument('model')),
        ]))->render();
    }

    /**
     * @return array|string
     */
    protected function getControllerName()
    {
        $controller = Str::studly($this->argument('controller'));

        if (Str::contains(strtolower($controller), 'controller') === false) {
            $controller .= 'Controller';
        }

        return $controller;
    }

    /**
     * @return array|string
     */
    private function getControllerNameWithoutNamespace()
    {
        return class_basename($this->getControllerName());
    }

    public function getDefaultNamespace(): string
    {
        $module = $this->laravel['modules'];

        return $module->config('paths.generator.controller.namespace') ?: $module->config('paths.generator.controller.path', 'Http/Controllers/Admin');
    }
}
