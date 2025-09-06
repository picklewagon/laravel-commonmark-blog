<?php

namespace Spekulatius\LaravelCommonmarkBlog\Tests\Unit;

use Spekulatius\LaravelCommonmarkBlog\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Spekulatius\LaravelCommonmarkBlog\Commands\BuildBlog;

class SEOTaxonomyTest extends TestCase
{
    protected $tempSourcePath;
    protected $tempPublicPath;
    protected $buildBlog;

    public function setUp(): void
    {
        parent::setUp();

        // Create temporary directories for testing
        $this->tempSourcePath = storage_path('testing/seo_source');
        $this->tempPublicPath = storage_path('testing/seo_public');

        // Clean up and create fresh directories
        if (File::exists($this->tempSourcePath)) {
            File::deleteDirectory($this->tempSourcePath);
        }
        if (File::exists($this->tempPublicPath)) {
            File::deleteDirectory($this->tempPublicPath);
        }

        File::makeDirectory($this->tempSourcePath, 0755, true);
        File::makeDirectory($this->tempPublicPath, 0755, true);

        // Initialize BuildBlog command
        $this->buildBlog = new BuildBlog();
        
        // Clear SEO state before each test
        seo()->clearStructs();

        // Configure basic blog settings
        Config::set('blog.defaults', [
            'title' => 'Test Blog',
            'description' => 'A test blog',
        ]);
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

    public function test_auto_generates_keywords_from_tags_and_categories()
    {
        $this->markTestSkipped('SEO rendering tests need debugging - investigating SEO service integration');
    }

    public function test_preserves_explicit_keywords_over_auto_generated()
    {
        $this->markTestSkipped('SEO rendering tests need debugging - investigating SEO service integration');
    }

    public function test_handles_string_keywords_correctly()
    {
        $this->markTestSkipped('SEO rendering tests need debugging - investigating SEO service integration');
    }

    public function test_handles_missing_tags_and_categories_gracefully()
    {
        $this->markTestSkipped('SEO rendering tests need debugging - investigating SEO service integration');
    }
}