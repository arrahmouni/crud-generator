<?php

namespace ArRahmouni\CrudGenerator\Console;

use Illuminate\Support\Str;
use ArRahmouni\CrudGenerator\Classes\Stub;
use ArRahmouni\CrudGenerator\Traits\ModuleCommandTrait;
use ArRahmouni\CrudGenerator\Config\GenerateConfigReader;
use Symfony\Component\Console\Input\InputArgument;

class CreateCrudCommand extends GeneratorCommand
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
    protected $name = 'create:crud';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a crud files';

    protected $hasSoftDelete;

    protected $hasDisabled;

    protected $shouldCreateTranslation ;

    protected $shouldCreateMigration ;

    protected $shouldCreateFactory ;

    protected $shouldCreateSeeder ;

    protected $shouldCreateRequest ;

    protected $shouldCreateController ;

    protected $shouldCreateApiController ;

    protected $isPaginate ;

    protected $shouldCreatePermissions ;

    protected $shouldCreateView ;

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model'    , InputArgument::REQUIRED, 'The name of model will be created.'],
            ['module'   , InputArgument::REQUIRED, 'The name of module will be used.'],
        ];
    }

    public function handle(): int
    {
        $this->hasSoftDelete            = $this->confirm('Would you like to use Soft Delete?', true);
        $this->hasDisabled              = $this->confirm('Would you like to use Disabled?', true);
        $this->shouldCreateMigration    = $this->confirm('Would you like to create associated migrations?', true);
        $this->shouldCreateTranslation  = $this->confirm('Would you like to create associated translations?', true);
        $this->shouldCreateFactory      = $this->confirm('Would you like to create a factory for the model?', true);
        $this->shouldCreateSeeder       = $this->confirm('Would you like to create a seeder for the model?', true);
        $this->shouldCreateRequest      = $this->confirm('Would you like to create a request for the model?', true);
        $this->shouldCreateController   = $this->confirm('Would you like to create a controller for the model?', true);
        $this->shouldCreateApiController= $this->confirm('Would you like to create an api controller for the model?', true);
        if($this->shouldCreateApiController) {
            $this->isPaginate = $this->confirm('Would you like to paginate the api controller?', true);
        }
        $this->shouldCreatePermissions  = $this->confirm('Would you like to create permissions for the model?', true);
        $this->shouldCreateView         = $this->confirm('Would you like to create views for the model?', true);

        $this->line('====================');
        $this->info('Starting Model Creation Process');
        $this->line('====================');

        if (parent::handle() === E_ERROR) {
            $this->error('Failed to create the model.');
            return E_ERROR;
        }

        $this->info('Model created successfully.');
        $this->line('--------------------');

        if($this->shouldCreateTranslation) {
            $this->handleModelTranslation();
            $this->line('Translation created successfully.');
        }

        if ($this->shouldCreatePermissions) {
            $this->handleModelPermissions();
            $this->line('Permissions created successfully.');
            $this->line('--------------------');
            $this->info('Appending Seeder To PermissionDatabaseSeeder.php');
            $this->appendPermissionConfig();
        }

        if ($this->shouldCreateMigration) {
            $this->handleOptionalMigrationOption();
            $this->line('Migration created successfully.');
        }

        if ($this->shouldCreateMigration && $this->shouldCreateTranslation) {
            $this->handleOptionalMigrationTranslationOption();
            $this->line('Translation migration created successfully.');
        }

        if ($this->shouldCreateFactory) {
            $this->handleOptionalFactoryOption();
            $this->line('Factory created successfully.');
        }

        if ($this->shouldCreateSeeder) {
            $this->handleOptionalSeedOption();
            $this->line('Seeder created successfully.');
        }

        if ($this->shouldCreateRequest) {
            $this->handleOptionalRequestOption();
            $this->line('Request created successfully.');
        }

        if ($this->shouldCreateController) {
            $this->handleOptionalControllerOption();
            $this->line('Controller created successfully.');
            $this->line('--------------------');
            $this->info('Appending Routes To web.php file in the module');
            $this->appendRoutes();
            $this->line('--------------------');
        }

        if($this->shouldCreateApiController) {
            $this->handleOptionalApiControllerOption();
            $this->line('Api Controller created successfully.');
        }

        if ($this->shouldCreateView) {
            $this->handleOptionalViewOption();
            $this->line('Views created successfully.');
        }

        $this->line('====================');
        $this->info('Process Completed Successfully');
        $this->line('====================');

        $this->notes();

        return 0;
    }

    /**
     * Notes After Model Creation
     */
    private function notes()
    {
        $this->line('--------------------');
        $this->info('Notes:');
        $this->line('--------------------');
        $this->line('1. If the model have permission, add the translation key to the seeder file in ability_group section.');
        $this->line('2. Add the model icon in the config file in Permission Module.');
        $this->line('3. If the model have additional permissions, add them in the config file in Permission Module.');
        $this->line('4. Add Tranlsation keys in admin::dashboard File. In the aside_menu section. ' . Str::snake($this->getModelName()) . '_management');
        $this->line('5. Add Tranlsation keys in admin::cruds File.');
        $this->line('6. Add Tranlsation keys in admin::datatables File.');
        $this->line('7. Finally, run the following command to sync the permissions:');
        $this->info('php artisan module:seed Permission');
        $this->line('--------------------');
    }

    /**
     * Create a proper migration name:
     * ProductDetail: product_details
     * Product: products
     * @return string
     */
    private function createMigrationName()
    {
        $pieces = preg_split('/(?=[A-Z])/', $this->argument('model'), -1, PREG_SPLIT_NO_EMPTY);

        $string = '';
        foreach ($pieces as $i => $piece) {
            if ($i+1 < count($pieces)) {
                $string .= strtolower($piece) . '_';
            } else {
                $string .= Str::plural(strtolower($piece));
            }
        }

        return $string;
    }

    /**
     * Create a proper migration translation name:
     * ProductDetail: product_detail_translations
     * Product: product_translations
     */
    private function createMigrationTranslationName()
    {
        $pieces = preg_split('/(?=[A-Z])/', $this->argument('model'), -1, PREG_SPLIT_NO_EMPTY);

        $string = '';
        foreach ($pieces as $i => $piece) {
            if ($i+1 < count($pieces)) {
                $string .= strtolower($piece) . '_';
            } else {
                $string .= strtolower($piece);
            }
        }

        return $string . '_translations';
    }

    /**
     * Append Routes To web.php file in the module
     */
    private function appendRoutes()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        $routeFile = $module->getExtraPath('Routes/web.php');

        $routeContent = file_get_contents($routeFile);

        // Get Controller NameSpace And Append It To The Route File As Use Statement
        $controllerNamespace = $this->getClassNamespace($module) . '\\' . $this->getModelName() . 'Controller';

        // Replace app\Models with app\Http\Controllers
        $controllerNamespace = str_replace('Models', 'Http\Controllers\Admin', $controllerNamespace);

        if (strpos($routeContent, $controllerNamespace) === false) {
            $routeContent = str_replace("<?php", "<?php\n\nuse $controllerNamespace;", $routeContent);
            // Remove empty line after the use statement
            $routeContent = str_replace("use $controllerNamespace;\n\n", "use $controllerNamespace;\n", $routeContent);
        }

        $routeContent .= "\n// {$this->getModelName()} Crud Section\n";
        $routeContent .= "Route::prefix('{$this->createMigrationName()}')->name('{$this->createMigrationName()}.')->controller({$this->getModelName()}Controller::class)->group(function () {\n";
        $routeContent .= "    Route::get('list'                           , 'index')->name('index');\n";
        $routeContent .= "    Route::get('datatable'                      , 'datatable')->name('datatable');\n";
        $routeContent .= "    Route::get('ajax-list'                      , 'ajaxList')->name('ajaxList');\n";
        $routeContent .= "    Route::get('create'                         , 'create')->name('create');\n";
        $routeContent .= "    Route::get('view/{model}'                   , 'view')->name('view');\n";
        $routeContent .= "    Route::post('create'                        , 'postCreate')->name('postCreate');\n";
        $routeContent .= "    Route::get('update/{model}'                 , 'update')->name('update');\n";
        $routeContent .= "    Route::put('update/{model}'                 , 'postUpdate')->name('postUpdate');\n";
        $routeContent .= "    Route::delete('soft-delete/{model}'         , 'softDelete')->name('softDelete');\n";
        $routeContent .= "    Route::delete('hard-delete/{model}'         , 'hardDelete')->name('hardDelete');\n";
        $routeContent .= "    Route::post('restore/{model}'               , 'restore')->name('restore');\n";
        $routeContent .= "    Route::post('disable/{model}'               , 'disable')->name('disable');\n";
        $routeContent .= "    Route::post('enable/{model}'                , 'enable')->name('enable');\n";
        $routeContent .= "    // Bulk Actions\n";
        $routeContent .= "    Route::delete('bulk-soft-delete'            , 'bulkSoftDelete')->name('bulkSoftDelete');\n";
        $routeContent .= "    Route::delete('bulk-hard-delete'            , 'bulkHardDelete')->name('bulkHardDelete');\n";
        $routeContent .= "    Route::post('bulk-restore'                  , 'bulkRestore')->name('bulkRestore');\n";
        $routeContent .= "    Route::post('bulk-disable'                  , 'bulkDisable')->name('bulkDisable');\n";
        $routeContent .= "    Route::post('bulk-enable'                   , 'bulkEnable')->name('bulkEnable');\n";
        $routeContent .= "});\n";

        file_put_contents($routeFile, $routeContent);

        $this->line('Routes appended successfully.');
    }

    /**
     * Append ['name' => 'model', 'icon' => 'fas']; to the config/config.php
     */
    private function appendPermissionConfig()
    {
        $module = $this->laravel['modules']->findOrFail('Permission');

        $configFile = $module->getExtraPath('config/config.php');

        if (!file_exists($configFile)) {
            $this->error('Config file not found.');
            return;
        }

        $configContent = file_get_contents($configFile);

        $pattern = '/(\'models\'\s*=>\s*\[\s*\n)/';

        $newModel = [
            'name' => Str::lower(Str::snake($this->getModelName())),
            'icon' => 'fas fa-folder',
        ];

        $newCode = "        [\n            'name' => '" . $newModel['name'] . "',\n            'icon' => '" . $newModel['icon'] . "',\n        ],\n";

        $updatedContent = preg_replace($pattern, "$1$newCode", $configContent);

        if ($updatedContent === null) {
            $this->error('Failed to update the config file.');
            return;
        }

        file_put_contents($configFile, $updatedContent);

        $this->info('Config file updated successfully.');
    }


    /**
     * Create the view files for the given model if view flag was used
     */
    private function handleOptionalViewOption()
    {
        $this->call('create:view', [
            'name'          => 'index',
            'route-prefix'  => $this->getRoutePrefix(),
            'module'        => $this->argument('module'),
            'model'         => $this->getModelName(),
        ]);

        $this->call('create:view', [
            'name'          => 'create',
            'route-prefix'  => $this->getRoutePrefix(),
            'module'        => $this->argument('module'),
            'model'         => $this->getModelName(),
        ]);

        $this->call('create:view', [
            'route-prefix'  => $this->getRoutePrefix(),
            'name'          => 'update',
            'module'        => $this->argument('module'),
            'model'         => $this->getModelName(),
        ]);
    }

    /**
     * Create the migration file with the given model if migration flag was used
     */
    private function handleOptionalMigrationOption()
    {
        $migrationName = 'create_' . $this->createMigrationName() . '_table';
        $this->call('create:migration', [
            'name'          => $migrationName,
            'module'        => $this->argument('module'),
        ]);
    }

    /**
     * Create the migration translation file with the given model if translation flag was used
     */
    private function handleOptionalMigrationTranslationOption()
    {
        $migrationTranslationName = 'create_' . $this->createMigrationTranslationName() . '_table';

        $this->call('create:migration-translation', [
            'name'          => $migrationTranslationName,
            'module'        => $this->argument('module'),
            'foreign_key'   => $this->getModelForeignName(),
        ]);
    }

    /**
     * Create the controller file for the given model if controller flag was used
     */
    private function handleOptionalControllerOption()
    {
        $serviceName = "{$this->getModelName()}Service";

        $this->call('create-service', array_filter([
            'name'              => $serviceName,
            'modelNameSpace'    => $this->getModelNamespaceWithName(),
            'module'            => $this->argument('module'),
        ]));

        $controllerName = "{$this->getModelName()}Controller";

        $this->call('create-controller', array_filter([
            'controller'        => $controllerName,
            'model'             => $this->getModelName(),
            'modelNameSpace'    => $this->getModelNamespaceWithName(),
            'service'           => $serviceName,
            'permission'        => $this->getModelPermissionName(),
            'request'           => $this->shouldCreateRequest ? "{$this->getModelName()}Request" : 'Request',
            'routePrefix'       => $this->getRoutePrefix(),
            'module'            => $this->argument('module'),
            '--has-permissions' => $this->shouldCreatePermissions,
            '--has-soft-delete' => $this->hasSoftDelete,
            '--has-disabled'    => $this->hasDisabled,
        ]));
    }

    /**
     * Create api controller file for the given model if api controller flag was used
     */
    private function handleOptionalApiControllerOption()
    {
        $serviceName = "{$this->getModelName()}Service";

        if(! $this->shouldCreateController) {
            $this->call('create-service', array_filter([
                'name'              => $serviceName,
                'modelNameSpace'    => $this->getModelNamespaceWithName(),
                'module'            => $this->argument('module'),
            ]));
        }

        $resourceName = "{$this->getModelName()}Resource";

        $this->call('create-resource', array_filter([
            'name'   => $resourceName,
            'module' => $this->argument('module')
        ]));

        $controllerName = "{$this->getModelName()}Controller";

        $this->call('create-api-controller', array_filter([
            'controller'        => $controllerName,
            'model'             => $this->getModelName(),
            'modelNameSpace'    => $this->getModelNamespaceWithName(),
            'service'           => $serviceName,
            'resource'          => $resourceName,
            'request'           => $this->shouldCreateRequest ? "{$this->getModelName()}Request" : 'Request',
            'module'            => $this->argument('module'),
            'isPaginate'        => $this->isPaginate,
        ]));
    }

    /**
     * Create a seeder file for the model.
     *
     * @return void
     */
    protected function handleOptionalSeedOption()
    {
        $seedName = "{$this->getModelName()}Seeder";

        $this->call('module:make-seed', array_filter([
            'name'   => $seedName,
            'module' => $this->argument('module')
        ]));
    }

    /**
     * Create a seeder file for the model.
     *
     * @return void
     */
    protected function handleOptionalFactoryOption()
    {
        $this->call('module:make-factory', array_filter([
            'name'   => $this->getModelName(),
            'module' => $this->argument('module')
        ]));
    }

    /**
     * Create a request file for the model.
     *
     * @return void
     */
    protected function handleOptionalRequestOption()
    {
        $requestName = "{$this->getModelName()}Request";

        $this->call('module:make-request', array_filter([
            'name' => $requestName,
            'module' => $this->argument('module')
        ]));
    }

    /**
     * Create a permission class for the model.
     */
    protected function handleModelPermissions()
    {
        $permissionName = $this->getModelPermissionName();

        $this->call('create:model-permission', array_filter([
            'name'   => $permissionName,
            'module' => $this->argument('module'),
            'model'  => Str::upper(Str::snake($this->getModelName())),
        ]));
    }

    /**
     * Create a translation model for the model.
     */
    protected function handleModelTranslation()
    {
        $this->call('create:model-translation', array_filter([
            'model'         => $this->getModelTranslationName(),
            'module'        => $this->argument('module'),
            'foreign_key'   => $this->getModelForeignName(),
        ]));
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        $newFactoryMethod       = '';
        $translationProperties  = '';
        $modelDatatable         = '';

        if ($this->shouldCreateFactory) {
            $newFactoryMethod = <<<EOD
            /**
                 * Create a new factory instance for the model.
                 *
                 * @return \Illuminate\Database\Eloquent\Factories\Factory
                 */
                protected static function newFactory()
                {
                    return \\{$this->getFactoryNamespace()}\\{$this->getModelName()}Factory::new();
                }
            EOD;
        }

        if ($this->shouldCreateTranslation) {
            $translationProperties = <<<EOD
            public \$translatedAttributes = [];

                protected \$with = [
                    'translations'
                ];
            EOD;
        }

        if($this->hasDisabled) {
            $modelDatatable = <<<EOD
            \$model = \$this::query()->withDisabled();
            EOD;
        } else {
            $modelDatatable = <<<EOD
            \$model = \$this::query();
            EOD;
        }

        return (new Stub('/model/model.stub', [
            'NAME'                  => $this->getModelName(),
            'NAMESPACE'             => $this->getClassNamespace($module),
            'CLASS'                 => $this->getClass(),
            'LOWER_NAME'            => $module->getLowerName(),
            'MODULE'                => $this->getModuleName(),
            'STUDLY_NAME'           => $module->getStudlyName(),
            'MODULE_NAMESPACE'      => $this->laravel['modules']->config('namespace'),
            'VIEW_PATH'             => $this->createMigrationName(),
            'MODEL_NAME'            => Str::snake($this->getModelName()),
            'MODEL_PERMISSION'      => $this->getModelPermissionName(),
            'ROUTE_PREFIX'          => $this->getRoutePrefix(),
            'NEW_FACTORY_METHOD'    => $newFactoryMethod,
            'TRANSLATION_PROPERTIES'=> $translationProperties,
            'MODEL_DATATABLE'       => $modelDatatable,
        ]))->render();
    }

    /**
     * @return string
     */
    protected function getModelNamespaceWithName()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return $this->getClassNamespace($module) . '\\' . $this->getClass();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $modelPath = GenerateConfigReader::read('model');

        return $path . $modelPath->getPath() . '/' . $this->getModelName() . '.php';
    }

    /**
     * @return mixed
     */
    protected function getFactoryNamespace()
    {
        return 'Modules\\' . $this->getModuleName() . '\\Database\\Factories';
    }

    /**
     * @return mixed|string
     */
    private function getModelName()
    {
        return Str::studly($this->argument('model'));
    }

    /**
     * Create Model Translation Name
     *
     * @return string
     */
    private function getModelTranslationName()
    {
        return $this->getModelName() . 'Translation';
    }

    /**
     * @return string
     */
    private function getRoutePrefix()
    {
        return Str::snake($this->argument('module')) . '.' . $this->createMigrationName();
    }

    /**
     * @return string
     */
    private function getModelPermissionName()
    {
        return "{$this->getModelName()}Permissions";
    }

    /**
     * Ex: Product => product_id
     *
     * @return mixed|string
     */
    private function getModelForeignName()
    {
        return Str::singular(Str::snake($this->argument('model'))) . '_id';
    }

    /**
     * @return string
     */
    private function getFillable()
    {
        $fillable = $this->option('fillable');

        if (!is_null($fillable)) {
            $arrays = explode(',', $fillable);

            return json_encode($arrays);
        }

        return '[]';
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
}
