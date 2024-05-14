<?php

namespace Onkihara\B3;

use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider as Baseprovider;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem;

class ServiceProvider extends Baseprovider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }
    
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // config -> merge to filesystems.php
        
        $this->mergeConfigFrom(
            __DIR__.'/../config/filesystems.php', 'filesystems'
        );

        // extend storage

        Storage::extend('b3', function (Application $app, array $config) {
            $adapter = new B3FileAdapter($config);
            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });

    }
}
