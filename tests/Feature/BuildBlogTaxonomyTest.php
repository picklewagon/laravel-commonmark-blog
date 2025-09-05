<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Spekulatius\LaravelCommonmarkBlog\Commands\BuildBlog;

class BuildBlogTaxonomyTest extends TestCase
{
    protected $tempSourcePath;
    protected $tempPublicPath;

    public function setUp(): void
    {
        parent::setUp();

        // Create temporary directories for testing
        $this->tempSourcePath = storage_path('testing/blog_source');
        $this->tempPublicPath = storage_path('testing/blog_public');

        // Clean up and create fresh directories
        if (File::exists($this->tempSourcePath)) {
            File::deleteDirectory($this->tempSourcePath);
        }
        if (File::exists($this->tempPublicPath)) {
            File::deleteDirectory($this->tempPublicPath);
        }

        File::makeDirectory($this->tempSourcePath, 0755, true);
        File::makeDirectory($this->tempPublicPath, 0755, true);

        // Configure the blog to use our test directories
        Config::set('blog.source_path', $this->tempSourcePath);
        Config::set('blog.templates.blog.article', 'test-article-template');
        Config::set('blog.templates.blog.list', 'test-list-template');

        // Create mock view templates
        $this->createMockViews();
    }

    public function tearDown(): void
    {
        // Clean up test directories
        if (File::exists($this->tempSourcePath)) {
            File::deleteDirectory($this->tempSourcePath);
        }
        if (File::exists($this->tempPublicPath)) {
            File::deleteDirectory($this->tempPublicPath);
        }

        parent::tearDown();
    }

    protected function createMockViews()
    {
        // Create mock view files that just return simple HTML
        $viewPath = resource_path('views');
        
        if (!File::exists($viewPath . '/test-article-template.blade.php')) {
            File::put($viewPath . '/test-article-template.blade.php', 
                '<html><head>{!! $header !!}</head><body><h1>{{ $title }}</h1>{!! $content !!}</body></html>'
            );
        }

        if (!File::exists($viewPath . '/test-list-template.blade.php')) {
            File::put($viewPath . '/test-list-template.blade.php', 
                '<html><head>{!! $header !!}</head><body><h1>{{ $title }}</h1>@foreach($articles as $article)<div>{{ $article["title"] }}</div>@endforeach</body></html>'
            );
        }
    }

    public function test_builds_taxonomy_archives_with_tags_and_categories()
    {
        // Create a sample blog post with tags and categories
        $blogContent = <<<'MD'
---
title: "Laravel Tutorial"
description: "Learn Laravel basics"
published: "2025-01-15"
modified: "2025-01-15"
tags: ["laravel", "php", "tutorial"]
categories: ["Development", "Tutorials"]
---

# Laravel Tutorial

This is a comprehensive Laravel tutorial.
MD;

        File::put($this->tempSourcePath . '/blog/laravel-tutorial.md', $blogContent);

        // Create another sample post
        $blogContent2 = <<<'MD'
---
title: "PHP Best Practices"
description: "PHP coding standards"
published: "2025-01-14"
modified: "2025-01-14"
tags: ["php", "best-practices"]
categories: ["Development"]
---

# PHP Best Practices

Learn the best practices for PHP development.
MD;

        File::put($this->tempSourcePath . '/blog/php-best-practices.md', $blogContent2);

        // Mock the public_path function to use our test directory
        $this->app->bind('path.public', function () {
            return $this->tempPublicPath;
        });

        // Run the build command
        $this->artisan('blog:build', ['source_path' => $this->tempSourcePath])
             ->assertExitCode(0);

        // Check that tag archive pages were created
        $this->assertFileExists($this->tempPublicPath . '/tags/laravel/index.htm');
        $this->assertFileExists($this->tempPublicPath . '/tags/php/index.htm');
        $this->assertFileExists($this->tempPublicPath . '/tags/tutorial/index.htm');
        $this->assertFileExists($this->tempPublicPath . '/tags/best-practices/index.htm');

        // Check that category archive pages were created
        $this->assertFileExists($this->tempPublicPath . '/categories/development/index.htm');
        $this->assertFileExists($this->tempPublicPath . '/categories/tutorials/index.htm');

        // Verify the content of a tag archive page
        $laravelArchive = File::get($this->tempPublicPath . '/tags/laravel/index.htm');
        $this->assertStringContainsString('Laravel Tutorial', $laravelArchive);
        $this->assertStringNotContainsString('PHP Best Practices', $laravelArchive);

        // Verify the content of a category archive page
        $developmentArchive = File::get($this->tempPublicPath . '/categories/development/index.htm');
        $this->assertStringContainsString('Laravel Tutorial', $developmentArchive);
        $this->assertStringContainsString('PHP Best Practices', $developmentArchive);
    }

    public function test_handles_posts_without_taxonomies()
    {
        // Create a sample blog post without tags or categories
        $blogContent = <<<'MD'
---
title: "Simple Post"
description: "A post without taxonomies"
published: "2025-01-15"
modified: "2025-01-15"
---

# Simple Post

This post has no tags or categories.
MD;

        File::put($this->tempSourcePath . '/blog/simple-post.md', $blogContent);

        // Mock the public_path function
        $this->app->bind('path.public', function () {
            return $this->tempPublicPath;
        });

        // Run the build command
        $this->artisan('blog:build', ['source_path' => $this->tempSourcePath])
             ->assertExitCode(0);

        // Verify the post was built but no archive pages were created
        $this->assertFileExists($this->tempPublicPath . '/blog/simple-post/index.htm');
        $this->assertDirectoryDoesNotExist($this->tempPublicPath . '/tags');
        $this->assertDirectoryDoesNotExist($this->tempPublicPath . '/categories');
    }

    public function test_respects_taxonomy_configuration()
    {
        // Disable tags but keep categories enabled
        Config::set('blog.taxonomies.tags.enabled', false);
        Config::set('blog.taxonomies.categories.enabled', true);

        // Create a sample blog post with both tags and categories
        $blogContent = <<<'MD'
---
title: "Test Post"
description: "A test post"
published: "2025-01-15"
modified: "2025-01-15"
tags: ["test", "sample"]
categories: ["Testing"]
---

# Test Post

This is a test.
MD;

        File::put($this->tempSourcePath . '/blog/test-post.md', $blogContent);

        // Mock the public_path function
        $this->app->bind('path.public', function () {
            return $this->tempPublicPath;
        });

        // Run the build command
        $this->artisan('blog:build', ['source_path' => $this->tempSourcePath])
             ->assertExitCode(0);

        // Verify only category archives were created
        $this->assertDirectoryDoesNotExist($this->tempPublicPath . '/tags');
        $this->assertFileExists($this->tempPublicPath . '/categories/testing/index.htm');
    }
}