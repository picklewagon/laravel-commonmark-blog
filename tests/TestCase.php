<?php

namespace Spekulatius\LaravelCommonmarkBlog\Tests;

use Spekulatius\LaravelCommonmarkBlog\CommonmarkBlogServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock views for testing
        $this->createMockViews();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');
        $app['config']->set('app.url', 'https://example.com');
        
        // Disable mix in testing to avoid manifest issues
        $app['config']->set('blog.mix.active', false);
        
        // Set up mock templates for testing
        $app['config']->set('blog.templates.blog.article', 'mock-article');
        $app['config']->set('blog.templates.blog.list', 'mock-list');
    }

    protected function getPackageProviders($app)
    {
        return [CommonmarkBlogServiceProvider::class];
    }
    
    /**
     * Create mock view templates for testing
     */
    protected function createMockViews(): void
    {
        $viewsPath = resource_path('views');
        File::makeDirectory($viewsPath, 0755, true, true);
        
        // Create mock article template
        File::put($viewsPath . '/mock-article.blade.php', <<<'BLADE'
<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'Article' }}</title>
</head>
<body>
    <h1>{{ $title ?? 'Article' }}</h1>
    <div>{!! $content ?? '' !!}</div>
</body>
</html>
BLADE
        );
        
        // Create mock list template
        File::put($viewsPath . '/mock-list.blade.php', <<<'BLADE'
<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'List' }}</title>
</head>
<body>
    <h1>{{ $title ?? 'List' }}</h1>
    <div>{!! $content ?? '' !!}</div>
</body>
</html>
BLADE
        );
    }
}
