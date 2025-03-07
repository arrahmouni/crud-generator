<?php

namespace ArRahmouni\CrudGenerator;

use Illuminate\Support\ServiceProvider;
use ArRahmouni\CrudGenerator\Classes\Stub;
use Illuminate\Support\Str;

class CrudGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        if($this->app->runningInConsole()) $this->registerCommands();

        // Set custom stub path from config
        $stubPath = config('crud.stub_path');
        if ($stubPath) {
            // Handle both relative and absolute paths
            if (!Str::startsWith($stubPath, ['/', '\\']) && !preg_match('/^[A-Z]:\\\\/i', $stubPath)) {
                $stubPath = base_path($stubPath);
            }
            Stub::setBasePath($stubPath);
        }

        // Publish the package's config.
        $this->publishes([
            __DIR__.'/../config/crud.php' => config_path('crud.php'),
        ], 'config');

        // Publish the package's stubs.
        $this->publishes([
            __DIR__.'/Console/stubs' => base_path('stubs'),
        ], 'stubs');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $exceptCommands = ['GeneratorCommand'];

        // Get all commands in the Console directory that ends with Command
        $commands = array_filter(scandir(__DIR__.'/Console'), function ($file) {
            // Check if the file ends with "Command.php" and is a file (not a directory)
            return is_file(__DIR__.'/Console/' . $file) && str_ends_with($file, 'Command.php');
        });

        // Remove extensions from commands
        foreach ($commands as $key => $command) {
            $commands[$key] = str_replace('.php', '', $command);
        }

        foreach ($commands as $command) {
            if (in_array($command, $exceptCommands)) continue;

            $this->commands([
                'ArRahmouni\\CrudGenerator\\Console\\'.$command,
            ]);
        }
    }
}
