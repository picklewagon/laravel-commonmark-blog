<?php

namespace Spekulatius\LaravelCommonmarkBlog\Tests;

use Spekulatius\LaravelCommonmarkBlog\CommonmarkBlogServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');
        $app['config']->set('app.url', 'https://example.com');
        
        // Disable mix in testing to avoid manifest issues
        $app['config']->set('blog.mix.active', false);
    }

    protected function getPackageProviders($app)
    {
        return [CommonmarkBlogServiceProvider::class];
    }
}
