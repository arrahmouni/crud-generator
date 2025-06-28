<?php

namespace ArRahmouni\CrudGenerator\Console;

use Illuminate\Support\Str;
use ArRahmouni\CrudGenerator\Classes\Stub;
use ArRahmouni\CrudGenerator\Traits\ModuleCommandTrait;
use ArRahmouni\CrudGenerator\Config\GenerateConfigReader;
use Symfony\Component\Console\Input\InputArgument;
class CreateApiControllerCommand extends GeneratorCommand
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
    protected $name = 'create-api-controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new restful api controller for the specified model.';

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
            ['resource'         , InputArgument::REQUIRED, 'The name of resource will be used.'],
            ['request'          , InputArgument::REQUIRED, 'The name of request will be used.'],
            ['module'           , InputArgument::OPTIONAL, 'The name of module will be used.'],
            ['isPaginate'       , InputArgument::OPTIONAL, 'The name of module will be used.'],
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

        $controllerPath = GenerateConfigReader::read('controller-api');

        return $path . $controllerPath->getPath() . '/' . $this->getControllerName() . '.php';
    }

    /**
     * @return string
     */
    protected function getTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub('/controller/controller-api.stub', [
            'CLASS_NAMESPACE'       => $this->getClassNamespace($module),
            'MODEL_NAMESPACE'       => $this->argument('modelNameSpace'),
            'MODULE'                => $this->getModuleName(),
            'SERVICE_NAME'          => $this->argument('service'),
            'MODEL_RESOURCE'        => $this->argument('resource') . '::class',
            'MODEL_RESOURCE_NAME'   => $this->argument('resource'),
            'CLASS'                 => $this->getControllerNameWithoutNamespace(),
            'MODEL'                 => $this->argument('model'),
            'SERVICE'               => $this->argument('service'),
            'CREATE_REQUEST'        => $this->argument('request') . '::class',
            'CREATE_REQUEST_NAME'   => $this->argument('request'),
            'IS_PAGINATE'           => $this->argument('isPaginate') == true ? 'true' : 'false',

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

        return $module->config('paths.generator.controller-api.namespace') ?: $module->config('paths.generator.controller-api.path', 'Http/Controllers/Api');
    }
}
