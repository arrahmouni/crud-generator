<?php

namespace ArRahmouni\CrudGenerator\Console;

use Illuminate\Support\Str;
use ArRahmouni\CrudGenerator\Classes\Stub;
use ArRahmouni\CrudGenerator\Traits\ModuleCommandTrait;
use ArRahmouni\CrudGenerator\Config\GenerateConfigReader;
use Symfony\Component\Console\Input\InputArgument;

class CreateServiceCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The name of argument being used.
     *
     * @var string
     */
    protected $argumentName = 'name';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'create-service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new service for the specified model.';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name'     , InputArgument::REQUIRED, 'The migration name will be created.'],
            ['modelNameSpace'   , InputArgument::REQUIRED, 'The name of model will be used.'],
            ['module'           , InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    /**
     * Get service name.
     *
     * @return string
     */
    public function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $servicePath = GenerateConfigReader::read('service');

        return $path . $servicePath->getPath() . '/' . $this->getServiceName() . '.php';
    }

    /**
     * @return string
     */
    protected function getTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub('/service/service.stub', [
            'CLASS_NAMESPACE'   => $this->getClassNamespace($module),
            'CLASS'             => $this->getServiceNameWithoutNamespace(),
            'MODEL_NAMESPACE'   => $this->argument('modelNameSpace'),
        ]))->render();
    }


    /**
     * @return array|string
     */
    protected function getServiceName()
    {
        $service = Str::studly($this->argument('name'));

        if (Str::contains(strtolower($service), 'service') === false) {
            $service .= 'service';
        }

        return $service;
    }

    /**
     * @return array|string
     */
    private function getServiceNameWithoutNamespace()
    {
        return class_basename($this->getServiceName());
    }

    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace(): string
    {
        $module = $this->laravel['modules'];

        return $module->config('paths.generator.service.namespace') ?: $module->config('paths.generator.service.path', 'Http/Services');
    }

}
