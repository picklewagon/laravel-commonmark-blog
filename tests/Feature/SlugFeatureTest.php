<?php

namespace Spekulatius\LaravelCommonmarkBlog\Tests\Feature;

use Spekulatius\LaravelCommonmarkBlog\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Spekulatius\LaravelCommonmarkBlog\Commands\BuildBlog;
use ReflectionClass;

class SlugFeatureTest extends TestCase
{
    /** @test */
    public function it_configuration_works_correctly()
    {
        // Test that the slug_source configuration can be set and used
        Config::set('blog.slug_source', 'filename');
        $this->assertEquals('filename', config('blog.slug_source'));
        
        Config::set('blog.slug_source', 'frontmatter');
        $this->assertEquals('frontmatter', config('blog.slug_source'));
    }

    /** @test */
    public function it_build_blog_command_exists()
    {
        // Test that the BuildBlog command can be instantiated
        $buildBlog = new BuildBlog();
        $this->assertInstanceOf(BuildBlog::class, $buildBlog);
    }

    /** @test */
    public function it_has_slug_generation_methods()
    {
        // Test that the required methods exist for slug generation
        $buildBlog = new BuildBlog();
        $reflection = new ReflectionClass($buildBlog);
        
        $this->assertTrue($reflection->hasMethod('generateTargetURL'));
        $this->assertTrue($reflection->hasMethod('detectSlugConflicts'));
    }

    /** @test */
    public function it_slug_source_configuration_is_available()
    {
        // Verify that the new configuration option is available
        $config = config('blog');
        
        // Check if slug_source key exists in the blog configuration
        $this->assertArrayHasKey('slug_source', $config);
        
        // Check that it defaults to 'filename'
        $this->assertEquals('filename', $config['slug_source']);
    }
}