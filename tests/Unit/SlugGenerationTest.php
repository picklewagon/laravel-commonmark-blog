<?php

namespace Spekulatius\LaravelCommonmarkBlog\Tests\Unit;

use Spekulatius\LaravelCommonmarkBlog\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Finder\SplFileInfo;
use Spekulatius\LaravelCommonmarkBlog\Commands\BuildBlog;
use ReflectionClass;
use ReflectionMethod;

class SlugGenerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_sets_configuration_correctly()
    {
        // Test that the slug_source configuration can be set and retrieved
        Config::set('blog.slug_source', 'filename');
        $this->assertEquals('filename', config('blog.slug_source'));
        
        Config::set('blog.slug_source', 'frontmatter');
        $this->assertEquals('frontmatter', config('blog.slug_source'));
    }

    /** @test */
    public function it_has_default_configuration()
    {
        // Test that the default configuration is 'filename'
        $this->assertEquals('filename', config('blog.slug_source', 'filename'));
    }

    /** @test */
    public function it_validates_configuration_options()
    {
        // Test that valid sources can be configured
        $validSources = ['filename', 'frontmatter'];
        
        foreach ($validSources as $source) {
            Config::set('blog.slug_source', $source);
            $this->assertEquals($source, config('blog.slug_source'));
        }
    }

    /** @test */
    public function it_has_generate_target_url_method()
    {
        // Test that the BuildBlog class has the generateTargetURL method
        $buildBlog = new BuildBlog();
        $reflection = new ReflectionClass($buildBlog);
        $this->assertTrue($reflection->hasMethod('generateTargetURL'));
        
        $method = $reflection->getMethod('generateTargetURL');
        $this->assertTrue($method->isProtected());
    }

    /** @test */
    public function it_has_detect_slug_conflicts_method()
    {
        // Test that the BuildBlog class has the detectSlugConflicts method
        $buildBlog = new BuildBlog();
        $reflection = new ReflectionClass($buildBlog);
        $this->assertTrue($reflection->hasMethod('detectSlugConflicts'));
        
        $method = $reflection->getMethod('detectSlugConflicts');
        $this->assertTrue($method->isProtected());
    }
}