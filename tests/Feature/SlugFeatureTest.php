<?php

namespace Spekulatius\LaravelCommonmarkBlog\Tests\Feature;

use Spekulatius\LaravelCommonmarkBlog\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class SlugFeatureTest extends TestCase
{
    protected $tempSourcePath;
    protected $tempPublicPath;

    protected function setUp(): void
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
        
        // Ensure clean slate for public path too
        if (File::exists(public_path())) {
            File::deleteDirectory(public_path());
        }
        File::makeDirectory(public_path(), 0755, true);
        
        // Configure templates to use our mock templates
        Config::set('blog.templates.blog.article', 'mock-article');
        Config::set('blog.templates.blog.list', 'mock-list');
    }

    protected function tearDown(): void
    {
        // Clean up after tests
        if (File::exists($this->tempSourcePath)) {
            File::deleteDirectory($this->tempSourcePath);
        }
        if (File::exists($this->tempPublicPath)) {
            File::deleteDirectory($this->tempPublicPath);
        }
        if (File::exists(public_path())) {
            File::deleteDirectory(public_path());
        }
        
        parent::tearDown();
    }

    /** @test */
    public function it_builds_blog_with_filename_based_urls_by_default()
    {
        Config::set('blog.source_path', $this->tempSourcePath);
        Config::set('blog.slug_source', 'filename');
        
        // Create test content
        $this->createTestArticle('blog/my-test-article.md', [
            'title' => 'My Test Article',
            'published' => '2024-01-01',
            'slug' => 'custom-slug' // This should be ignored
        ], 'Test content');
        
        // Run the build command
        Artisan::call('blog:build');
        
        // Check that the URL follows the filename
        $this->assertFileExists(public_path('blog/my-test-article/index.htm'));
        $this->assertFileDoesNotExist(public_path('blog/custom-slug/index.htm'));
    }

    /** @test */
    public function it_builds_blog_with_frontmatter_based_urls_when_configured()
    {
        Config::set('blog.source_path', $this->tempSourcePath);
        Config::set('blog.slug_source', 'frontmatter');
        
        // Create test content
        $this->createTestArticle('blog/2024-01-15-detailed-filename.md', [
            'title' => 'My Clean Post Title',
            'published' => '2024-01-01',
            'slug' => 'clean-post-title'
        ], 'Test content');
        
        // Run the build command
        Artisan::call('blog:build');
        
        // Check that the URL follows the frontmatter slug
        $this->assertFileExists(public_path('blog/clean-post-title/index.htm'));
        $this->assertFileDoesNotExist(public_path('blog/2024-01-15-detailed-filename/index.htm'));
    }

    /** @test */
    public function it_falls_back_to_filename_when_no_slug_in_frontmatter()
    {
        Config::set('blog.source_path', $this->tempSourcePath);
        Config::set('blog.slug_source', 'frontmatter');
        
        // Create test content without slug
        $this->createTestArticle('blog/fallback-test.md', [
            'title' => 'Fallback Test',
            'published' => '2024-01-01',
            // No slug field
        ], 'Test content');
        
        // Run the build command
        Artisan::call('blog:build');
        
        // Check that it falls back to filename
        $this->assertFileExists(public_path('blog/fallback-test/index.htm'));
    }

    /** @test */
    public function it_sanitizes_frontmatter_slugs_properly()
    {
        Config::set('blog.source_path', $this->tempSourcePath);
        Config::set('blog.slug_source', 'frontmatter');
        
        // Create test content with messy slug
        $this->createTestArticle('blog/test.md', [
            'title' => 'Sanitization Test',
            'published' => '2024-01-01',
            'slug' => 'My Messy Slug with CAPS & Special Characters!'
        ], 'Test content');
        
        // Run the build command
        Artisan::call('blog:build');
        
        // Check that the slug is sanitized
        $this->assertFileExists(public_path('blog/my-messy-slug-with-caps-special-characters/index.htm'));
    }

    /** @test */
    public function it_preserves_directory_structure_with_frontmatter_slugs()
    {
        Config::set('blog.source_path', $this->tempSourcePath);
        Config::set('blog.slug_source', 'frontmatter');
        
        // Create test content in nested directory
        $this->createTestArticle('blog/tutorials/advanced/complex-topic.md', [
            'title' => 'Advanced Tutorial',
            'published' => '2024-01-01',
            'slug' => 'simple-tutorial'
        ], 'Advanced content');
        
        // Run the build command
        Artisan::call('blog:build');
        
        // Check that directory structure is preserved but slug is used
        $this->assertFileExists(public_path('blog/tutorials/advanced/simple-tutorial/index.htm'));
        $this->assertFileDoesNotExist(public_path('blog/tutorials/advanced/complex-topic/index.htm'));
    }

    /** @test */
    public function it_warns_about_slug_conflicts()
    {
        Config::set('blog.source_path', $this->tempSourcePath);
        Config::set('blog.slug_source', 'frontmatter');
        
        // Create two articles with the same slug
        $this->createTestArticle('blog/article-one.md', [
            'title' => 'Article One',
            'published' => '2024-01-01',
            'slug' => 'same-slug'
        ], 'Content one');
        
        $this->createTestArticle('blog/article-two.md', [
            'title' => 'Article Two',
            'published' => '2024-01-01',
            'slug' => 'same-slug'
        ], 'Content two');
        
        // Run the build command and capture output
        $output = '';
        Artisan::call('blog:build', [], $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput());
        $output = $outputBuffer->fetch();
        
        // Check for conflict warning
        $this->assertStringContainsString('WARNING: Slug conflicts detected!', $output);
        $this->assertStringContainsString("URL 'blog/same-slug/' is used by 2 articles", $output);
    }

    /**
     * Create a test article file
     *
     * @param string $path
     * @param array $frontmatter
     * @param string $content
     * @return void
     */
    private function createTestArticle(string $path, array $frontmatter, string $content): void
    {
        $fullPath = $this->tempSourcePath . '/' . $path;
        
        // Create directory if it doesn't exist
        File::makeDirectory(dirname($fullPath), 0755, true, true);
        
        // Create frontmatter
        $yaml = "---\n";
        foreach ($frontmatter as $key => $value) {
            $yaml .= "{$key}: " . (is_string($value) ? "\"{$value}\"" : $value) . "\n";
        }
        $yaml .= "---\n\n";
        
        // Write the file
        File::put($fullPath, $yaml . $content);
    }
}