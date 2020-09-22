<?php

namespace Encore\MediaSelector;

use Encore\Admin\Admin;
use Encore\Admin\Form;
use Encore\MediaSelector\Commands\InstallCommand;
use Illuminate\Support\ServiceProvider;

class MediaSelectorServiceProvider extends ServiceProvider
{
    protected $commands = [
        InstallCommand::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function boot(MediaSelector $extension)
    {
        if (!MediaSelector::boot()) {
            return;
        }

        if ($views = $extension->views()) {
            $this->loadViewsFrom($views, 'media-selector');
        }

        if ($this->app->runningInConsole() && $assets = $extension->assets()) {
            $this->publishes(
                [$assets => public_path('vendor/de-memory/media-selector')],
                'media-selector'
            );
        }

        // 加载插件
        Admin::booting(function () {
            Form::extend('mediaSelector', FormMediaSelector::class);
        });

        $this->app->booted(function () {
            MediaSelector::routes(__DIR__ . '/../routes/web.php');
        });
    }

    public function register()
    {
        $this->commands($this->commands);
    }
}