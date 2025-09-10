<?php

namespace Spekulatius\LaravelCommonmarkBlog\Tests\Feature;

use Spekulatius\LaravelCommonmarkBlog\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

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
        
        // Create blog subdirectory
        File::makeDirectory($this->tempSourcePath . '/blog', 0755, true);

        // Configure the blog to use our test directories
        Config::set('blog.source_path', $this->tempSourcePath);
        Config::set('blog.templates.blog.article', 'test-article-template');
        Config::set('blog.templates.blog.list', 'test-list-template');
        Config::set('blog.templates.blog.tags_overview', 'test-taxonomy-overview-template');
        Config::set('blog.templates.blog.categories_overview', 'test-taxonomy-overview-template');

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
        
        // Ensure views directory exists
        if (!File::exists($viewPath)) {
            File::makeDirectory($viewPath, 0755, true);
        }
        
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

        if (!File::exists($viewPath . '/test-taxonomy-overview-template.blade.php')) {
            File::put($viewPath . '/test-taxonomy-overview-template.blade.php', 
                '<html><head>{!! $header !!}</head><body><h1>{{ $title }}</h1>@if(isset($taxonomy_data))@foreach($taxonomy_data as $term)<div>{{ $term["name"] }} ({{ $term["count"] }})</div>@endforeach @endif</body></html>'
            );
        }
    }

    public function test_builds_taxonomy_archives_with_tags_and_categories()
    {
        // Create test blog posts with tags and categories
        $postContent1 = "---\ntitle: Post One\ntags: [\"php\", \"laravel\"]\ncategories: [\"web-dev\"]\npublished: '2020-01-01 00:00:00'\nmodified: '2020-01-01 00:00:00'\n---\n\n# Post One\n\nContent here.";
        File::put($this->tempSourcePath . '/blog/post-1.md', $postContent1);
        
        $postContent2 = "---\ntitle: Post Two\ntags: [\"php\"]\ncategories: [\"tutorials\"]\npublished: '2020-01-02 00:00:00'\nmodified: '2020-01-02 00:00:00'\n---\n\n# Post Two\n\nMore content.";
        File::put($this->tempSourcePath . '/blog/post-2.md', $postContent2);

        // Configure taxonomies
        Config::set('blog.taxonomies.tags.enabled', true);
        Config::set('blog.taxonomies.categories.enabled', true);
        Config::set('blog.taxonomies.tags.route_prefix', 'blog/tags');
        Config::set('blog.taxonomies.categories.route_prefix', 'blog/categories');
        Config::set('blog.cache.key', 'test-generated-articles');

        // Override public path
        if (method_exists($this->app, 'usePublicPath')) {
            $this->app->usePublicPath($this->tempPublicPath);
        } else {
            $this->app->instance('path.public', $this->tempPublicPath);
        }

        // Run build command
        $this->artisan('blog:build', ['source_path' => $this->tempSourcePath]);

        // Check individual tag archive pages were created
        $this->assertTrue(File::exists($this->tempPublicPath . '/blog/tags/php/index.htm'));
        $this->assertTrue(File::exists($this->tempPublicPath . '/blog/tags/laravel/index.htm'));
        
        // Check individual category archive pages were created
        $this->assertTrue(File::exists($this->tempPublicPath . '/blog/categories/web-dev/index.htm'));
        $this->assertTrue(File::exists($this->tempPublicPath . '/blog/categories/tutorials/index.htm'));
        
        // Check content of tag archive
        $phpTagContent = File::get($this->tempPublicPath . '/blog/tags/php/index.htm');
        $this->assertStringContainsString('Post One', $phpTagContent);
        $this->assertStringContainsString('Post Two', $phpTagContent);
        
        $laravelTagContent = File::get($this->tempPublicPath . '/blog/tags/laravel/index.htm');
        $this->assertStringContainsString('Post One', $laravelTagContent);
        $this->assertStringNotContainsString('Post Two', $laravelTagContent);
    }

    public function test_handles_posts_without_taxonomies()
    {
        // Create test blog post WITHOUT tags and categories
        $postContent = "---\ntitle: Plain Post\npublished: '2020-01-01 00:00:00'\nmodified: '2020-01-01 00:00:00'\n---\n\n# Plain Post\n\nNo tags or categories.";
        File::put($this->tempSourcePath . '/blog/plain-post.md', $postContent);

        // Configure taxonomies
        Config::set('blog.taxonomies.tags.enabled', true);
        Config::set('blog.taxonomies.categories.enabled', true);
        Config::set('blog.cache.key', 'test-generated-articles');

        // Override public path
        if (method_exists($this->app, 'usePublicPath')) {
            $this->app->usePublicPath($this->tempPublicPath);
        } else {
            $this->app->instance('path.public', $this->tempPublicPath);
        }

        // Run build command
        $this->artisan('blog:build', ['source_path' => $this->tempSourcePath]);

        // Check that the post was still created
        $this->assertTrue(File::exists($this->tempPublicPath . '/blog/plain-post/index.htm'));
        
        // Check that NO taxonomy directories were created (no tags/categories to archive)
        $this->assertFalse(File::exists($this->tempPublicPath . '/blog/tags'));
        $this->assertFalse(File::exists($this->tempPublicPath . '/blog/categories'));
        
        // Verify post content
        $postHtml = File::get($this->tempPublicPath . '/blog/plain-post/index.htm');
        $this->assertStringContainsString('Plain Post', $postHtml);
        $this->assertStringContainsString('No tags or categories', $postHtml);
    }

    public function test_respects_taxonomy_configuration()
    {
        // Create test blog post with tags and categories
        $postContent = "---\ntitle: Config Test Post\ntags: [\"test-tag\"]\ncategories: [\"test-category\"]\npublished: '2020-01-01 00:00:00'\nmodified: '2020-01-01 00:00:00'\n---\n\n# Config Test\n\nTesting configuration.";
        File::put($this->tempSourcePath . '/blog/config-test.md', $postContent);

        // Configure taxonomies - DISABLE categories, enable tags with custom prefix
        Config::set('blog.taxonomies.tags.enabled', true);
        Config::set('blog.taxonomies.categories.enabled', false); // DISABLED
        Config::set('blog.taxonomies.tags.route_prefix', 'custom/tag-archive');
        Config::set('blog.cache.key', 'test-generated-articles');

        // Override public path
        if (method_exists($this->app, 'usePublicPath')) {
            $this->app->usePublicPath($this->tempPublicPath);
        } else {
            $this->app->instance('path.public', $this->tempPublicPath);
        }

        // Run build command
        $this->artisan('blog:build', ['source_path' => $this->tempSourcePath]);

        // Check that tags were created with CUSTOM PREFIX
        $this->assertTrue(File::exists($this->tempPublicPath . '/custom/tag-archive/test-tag/index.htm'));
        $this->assertTrue(File::exists($this->tempPublicPath . '/custom/tag-archive/index.htm'));
        
        // Check that categories were NOT created (disabled)
        $this->assertFalse(File::exists($this->tempPublicPath . '/blog/categories'));
        
        // Verify tag content
        $tagHtml = File::get($this->tempPublicPath . '/custom/tag-archive/test-tag/index.htm');
        $this->assertStringContainsString('Config Test Post', $tagHtml);
    }

    public function test_generates_taxonomy_overview_index_files()
    {
        // Create multiple test blog posts with tags (use a past date to ensure it gets published)
        $postContent1 = "---\ntitle: Test Post One\ntags: [\"test-tag\", \"another-tag\"]\ncategories: [\"test-category\"]\npublished: '2020-01-01 00:00:00'\nmodified: '2020-01-01 00:00:00'\n---\n\n# Test Post One\n\nThis is a test post.";
        File::put($this->tempSourcePath . '/blog/test-post-1.md', $postContent1);
        
        $postContent2 = "---\ntitle: Test Post Two\ntags: [\"test-tag\"]\ncategories: [\"test-category\"]\npublished: '2020-01-02 00:00:00'\nmodified: '2020-01-02 00:00:00'\n---\n\n# Test Post Two\n\nThis is another test post.";
        File::put($this->tempSourcePath . '/blog/test-post-2.md', $postContent2);

        // Enable taxonomies and caching
        Config::set('blog.taxonomies.tags.enabled', true);
        Config::set('blog.taxonomies.categories.enabled', true);
        Config::set('blog.taxonomies.tags.route_prefix', 'blog/tags');
        Config::set('blog.taxonomies.categories.route_prefix', 'blog/categories');
        Config::set('blog.cache.key', 'test-generated-articles');
        Config::set('blog.cache.expiry', 3600);

        // Override the public_path helper to use our temp directory (Laravel 9/10+ compatible)
        if (method_exists($this->app, 'usePublicPath')) {
            // Laravel 10+
            $this->app->usePublicPath($this->tempPublicPath);
        } else {
            // Laravel 9 - use the container binding approach
            $this->app->instance('path.public', $this->tempPublicPath);
        }

        // Run the blog build command
        $this->artisan('blog:build', ['source_path' => $this->tempSourcePath]);

        // Check if taxonomy overview index files were created
        $this->assertTrue(
            File::exists($this->tempPublicPath . '/blog/tags/index.htm'),
            'Tags overview index file should be generated'
        );

        $this->assertTrue(
            File::exists($this->tempPublicPath . '/blog/categories/index.htm'),
            'Categories overview index file should be generated'
        );

        // Verify content of the tags index file - should be actual content, not redirect
        $tagsIndexContent = File::get($this->tempPublicPath . '/blog/tags/index.htm');
        $this->assertStringContainsString('<html>', $tagsIndexContent);
        $this->assertStringContainsString('Tags', $tagsIndexContent);
        $this->assertStringContainsString('test-tag', $tagsIndexContent);
        $this->assertStringContainsString('another-tag', $tagsIndexContent);
        
        // Should NOT contain redirect meta tags anymore
        $this->assertStringNotContainsString('meta http-equiv="refresh"', $tagsIndexContent);

        // Verify content of the categories index file - should be actual content, not redirect
        $categoriesIndexContent = File::get($this->tempPublicPath . '/blog/categories/index.htm');
        $this->assertStringContainsString('<html>', $categoriesIndexContent);
        $this->assertStringContainsString('Categories', $categoriesIndexContent);
        $this->assertStringContainsString('test-category', $categoriesIndexContent);
        
        // Should NOT contain redirect meta tags anymore
        $this->assertStringNotContainsString('meta http-equiv="refresh"', $categoriesIndexContent);
    }
}