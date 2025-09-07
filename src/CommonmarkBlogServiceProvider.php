<?php

namespace Spekulatius\LaravelCommonmarkBlog;

use Spekulatius\LaravelCommonmarkBlog\Commands\BuildBlog;
use Illuminate\Support\ServiceProvider;

class CommonmarkBlogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();

        // Register the command if we are using the application via the CLI
        if ($this->app->runningInConsole()) {
            $this->commands([
                BuildBlog::class,
            ]);
        }

        // Register the facade alias
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Blog', \Spekulatius\LaravelCommonmarkBlog\Facades\Blog::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Load the configuration
        $this->mergeConfigFrom(__DIR__.'/../config/blog.php', 'blog');

        // Register the Blog helper class
        $this->app->singleton('blog', function ($app) {
            return new \Spekulatius\LaravelCommonmarkBlog\Blog();
        });
    }

    /**
     * Publishes the config
     *
     * @return void
     */
    public function publishConfig()
    {
        if ($this->app->runningInConsole()) {
            // Publish configuration
            $this->publishes([
                __DIR__ . '/../config/blog.php' => config_path('blog.php'),
            ], 'blog-config');

            // Publish taxonomy view templates
            $this->publishes([
                __DIR__ . '/../resources/views/blog' => resource_path('views/blog'),
            ], 'blog-views');

            // Publish both config and views together
            $this->publishes([
                __DIR__ . '/../config/blog.php' => config_path('blog.php'),
                __DIR__ . '/../resources/views/blog' => resource_path('views/blog'),
            ], 'blog-all');
        }
    }

    /**
     * The blog requires certain service providers to be loaded.
     *
     * @return array
     */
    public function provides()
    {
        return [
            self::class,
            \romanzipp\Seo\Providers\SeoServiceProvider::class,
        ];
    }
}
