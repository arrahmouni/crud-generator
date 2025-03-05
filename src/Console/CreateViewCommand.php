<?php

namespace  ArRahmouni\CrudGenerator\Console;

use Illuminate\Support\Str;
use ArRahmouni\CrudGenerator\Classes\Stub;
use ArRahmouni\CrudGenerator\Traits\ModuleCommandTrait;
use ArRahmouni\CrudGenerator\Config\GenerateConfigReader;
use Symfony\Component\Console\Input\InputArgument;

class CreateViewCommand extends GeneratorCommand
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
    protected $name = 'create:view';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new view for the specified module.';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name'         , InputArgument::REQUIRED, 'The migration name will be created.'],
            ['module'       , InputArgument::REQUIRED, 'The name of module will be created.'],
            ['model'        , InputArgument::REQUIRED, 'The model name will be created.'],
            ['route-prefix' , InputArgument::REQUIRED, 'The route prefix.'],
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

        return Stub::create('/view/'. $this->argument('name') . '.stub', [
            'MODEL_PLUR'    => Str::plural(Str::snake($this->argument('model'))),
            'MODEL_SNAKE'   => Str::snake($this->argument('model')),
            'ROUTE_PREFIX'  => $this->argument('route-prefix'),
        ]);
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $generatorPath = GenerateConfigReader::read('views');

        return $path . $generatorPath->getPath() . '/' . Str::plural(Str::snake($this->argument('model'))) . '/' . $this->getFileName() .  '.blade.php';
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return Str::snake($this->argument('name'));
    }

    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace(): string
    {
        $module = $this->laravel['modules'];

        return $module->config('paths.generator.views.namespace') ?: $module->config('paths.generator.views.path', 'Views');
    }

    /**
     * Run the command.
     */
    public function handle(): int
    {
        $this->components->info('Create a new view for the specified model.');

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        if (app()->environment() === 'testing') {
            return 0;
        }

        return 0;
    }
}
