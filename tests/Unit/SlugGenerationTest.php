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
    public function it_generates_url_from_filename_by_default()
    {
        Config::set('blog.slug_source', 'filename');
        
        $buildBlog = new BuildBlog();
        $reflection = new ReflectionClass($buildBlog);
        $method = $reflection->getMethod('generateTargetURL');
        $method->setAccessible(true);
        
        $file = $this->createMockFile('blog/my-test-post.md');
        $data = ['title' => 'My Test Post'];
        
        $result = $method->invoke($buildBlog, $file, $data);
        
        $this->assertEquals('blog/my-test-post/', $result);
    }

    /** @test */
    public function it_generates_url_from_frontmatter_slug_when_configured()
    {
        Config::set('blog.slug_source', 'frontmatter');
        
        $buildBlog = new BuildBlog();
        $reflection = new ReflectionClass($buildBlog);
        $method = $reflection->getMethod('generateTargetURL');
        $method->setAccessible(true);
        
        $file = $this->createMockFile('blog/2024-01-15-detailed-filename.md');
        $data = [
            'title' => 'My Clean Post Title',
            'slug' => 'clean-post-title'
        ];
        
        $result = $method->invoke($buildBlog, $file, $data);
        
        $this->assertEquals('blog/clean-post-title/', $result);
    }

    /** @test */
    public function it_falls_back_to_filename_when_no_slug_in_frontmatter()
    {
        Config::set('blog.slug_source', 'frontmatter');
        
        $buildBlog = new BuildBlog();
        $reflection = new ReflectionClass($buildBlog);
        $method = $reflection->getMethod('generateTargetURL');
        $method->setAccessible(true);
        
        $file = $this->createMockFile('blog/my-test-post.md');
        $data = ['title' => 'My Test Post']; // No slug field
        
        $result = $method->invoke($buildBlog, $file, $data);
        
        $this->assertEquals('blog/my-test-post/', $result);
    }

    /** @test */
    public function it_falls_back_to_filename_when_slug_is_empty()
    {
        Config::set('blog.slug_source', 'frontmatter');
        
        $buildBlog = new BuildBlog();
        $reflection = new ReflectionClass($buildBlog);
        $method = $reflection->getMethod('generateTargetURL');
        $method->setAccessible(true);
        
        $file = $this->createMockFile('blog/my-test-post.md');
        $data = [
            'title' => 'My Test Post',
            'slug' => '' // Empty slug
        ];
        
        $result = $method->invoke($buildBlog, $file, $data);
        
        $this->assertEquals('blog/my-test-post/', $result);
    }

    /** @test */
    public function it_sanitizes_frontmatter_slug()
    {
        Config::set('blog.slug_source', 'frontmatter');
        
        $buildBlog = new BuildBlog();
        $reflection = new ReflectionClass($buildBlog);
        $method = $reflection->getMethod('generateTargetURL');
        $method->setAccessible(true);
        
        $file = $this->createMockFile('blog/test.md');
        $data = [
            'title' => 'My Test Post',
            'slug' => 'My Messy Slug with CAPS & Special Characters!'
        ];
        
        $result = $method->invoke($buildBlog, $file, $data);
        
        $this->assertEquals('blog/my-messy-slug-with-caps-special-characters/', $result);
    }

    /** @test */
    public function it_handles_files_in_root_directory()
    {
        Config::set('blog.slug_source', 'frontmatter');
        
        $buildBlog = new BuildBlog();
        $reflection = new ReflectionClass($buildBlog);
        $method = $reflection->getMethod('generateTargetURL');
        $method->setAccessible(true);
        
        $file = $this->createMockFile('my-post.md');
        $data = [
            'title' => 'My Post',
            'slug' => 'custom-slug'
        ];
        
        $result = $method->invoke($buildBlog, $file, $data);
        
        $this->assertEquals('custom-slug/', $result);
    }

    /** @test */
    public function it_handles_nested_directory_structure()
    {
        Config::set('blog.slug_source', 'frontmatter');
        
        $buildBlog = new BuildBlog();
        $reflection = new ReflectionClass($buildBlog);
        $method = $reflection->getMethod('generateTargetURL');
        $method->setAccessible(true);
        
        $file = $this->createMockFile('blog/tutorials/advanced/test.md');
        $data = [
            'title' => 'Advanced Tutorial',
            'slug' => 'custom-tutorial-slug'
        ];
        
        $result = $method->invoke($buildBlog, $file, $data);
        
        $this->assertEquals('blog/tutorials/advanced/custom-tutorial-slug/', $result);
    }

    /** @test */
    public function it_detects_slug_conflicts()
    {
        $buildBlog = new BuildBlog();
        $reflection = new ReflectionClass($buildBlog);
        $method = $reflection->getMethod('detectSlugConflicts');
        $method->setAccessible(true);
        
        $generatedArticles = [
            ['generated_url' => 'blog/test-post/', 'title' => 'Test Post 1'],
            ['generated_url' => 'blog/test-post/', 'title' => 'Test Post 2'], // Conflict
            ['generated_url' => 'blog/unique-post/', 'title' => 'Unique Post'],
        ];
        
        // Capture output
        $this->expectOutputRegex('/WARNING: Slug conflicts detected!/');
        $this->expectOutputRegex('/URL \'blog\/test-post\/\' is used by 2 articles/');
        
        $method->invoke($buildBlog, $generatedArticles);
    }

    /** @test */
    public function it_does_not_warn_when_no_conflicts_exist()
    {
        $buildBlog = new BuildBlog();
        $reflection = new ReflectionClass($buildBlog);
        $method = $reflection->getMethod('detectSlugConflicts');
        $method->setAccessible(true);
        
        $generatedArticles = [
            ['generated_url' => 'blog/test-post-1/', 'title' => 'Test Post 1'],
            ['generated_url' => 'blog/test-post-2/', 'title' => 'Test Post 2'],
            ['generated_url' => 'blog/unique-post/', 'title' => 'Unique Post'],
        ];
        
        // Should not output any warning
        $this->expectOutputString('');
        
        $method->invoke($buildBlog, $generatedArticles);
    }

    /**
     * Create a mock SplFileInfo object for testing
     *
     * @param string $relativePath
     * @return SplFileInfo
     */
    private function createMockFile(string $relativePath): SplFileInfo
    {
        $mock = $this->createMock(SplFileInfo::class);
        $mock->method('getRelativePathname')->willReturn($relativePath);
        return $mock;
    }
}