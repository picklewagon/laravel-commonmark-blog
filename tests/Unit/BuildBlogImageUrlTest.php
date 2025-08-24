<?php

namespace Spekulatius\LaravelCommonmarkBlog\Tests\Unit;

use Spekulatius\LaravelCommonmarkBlog\Commands\BuildBlog;
use Spekulatius\LaravelCommonmarkBlog\Tests\TestCase;
use ReflectionClass;
use ReflectionMethod;

class BuildBlogImageUrlTest extends TestCase
{
    /**
     * Test that relative image URLs are converted to absolute URLs
     *
     * @return void
     */
    public function testConvertImageURLsToAbsolute()
    {
        // Set app URL for testing
        config(['app.url' => 'https://example.com']);
        
        // Create BuildBlog instance
        $buildBlog = new BuildBlog();
        
        // Use reflection to access protected method
        $reflection = new ReflectionClass($buildBlog);
        $method = $reflection->getMethod('convertImageURLsToAbsolute');
        $method->setAccessible(true);
        
        // Test data with relative image URLs
        $frontmatter = [
            'title' => 'Test Article',
            'image' => '/blog/images/hero.jpg',
            'twitter' => [
                'image' => '/blog/images/twitter-card.jpg',
                'card' => 'summary_large_image'
            ],
            'og' => [
                'image' => '/blog/images/og-image.jpg',
                'type' => 'article'
            ]
        ];
        
        // Call the method
        $result = $method->invoke($buildBlog, $frontmatter);
        
        // Assert that URLs were converted to absolute
        $this->assertEquals('https://example.com/blog/images/hero.jpg', $result['image']);
        $this->assertEquals('https://example.com/blog/images/twitter-card.jpg', $result['twitter']['image']);
        $this->assertEquals('https://example.com/blog/images/og-image.jpg', $result['og']['image']);
        
        // Assert other fields remain unchanged
        $this->assertEquals('Test Article', $result['title']);
        $this->assertEquals('summary_large_image', $result['twitter']['card']);
        $this->assertEquals('article', $result['og']['type']);
    }
    
    /**
     * Test that absolute URLs are not modified
     *
     * @return void
     */
    public function testAbsoluteUrlsAreNotModified()
    {
        // Set app URL for testing
        config(['app.url' => 'https://example.com']);
        
        // Create BuildBlog instance
        $buildBlog = new BuildBlog();
        
        // Use reflection to access protected method
        $reflection = new ReflectionClass($buildBlog);
        $method = $reflection->getMethod('convertImageURLsToAbsolute');
        $method->setAccessible(true);
        
        // Test data with absolute image URLs
        $frontmatter = [
            'image' => 'https://cdn.example.com/images/hero.jpg',
            'twitter' => [
                'image' => 'http://other-domain.com/twitter-card.jpg'
            ],
            'og' => [
                'image' => 'https://assets.example.com/og-image.jpg'
            ]
        ];
        
        // Call the method
        $result = $method->invoke($buildBlog, $frontmatter);
        
        // Assert that absolute URLs remain unchanged
        $this->assertEquals('https://cdn.example.com/images/hero.jpg', $result['image']);
        $this->assertEquals('http://other-domain.com/twitter-card.jpg', $result['twitter']['image']);
        $this->assertEquals('https://assets.example.com/og-image.jpg', $result['og']['image']);
    }
    
    /**
     * Test that missing image fields don't cause errors
     *
     * @return void
     */
    public function testMissingImageFieldsAreHandled()
    {
        // Set app URL for testing
        config(['app.url' => 'https://example.com']);
        
        // Create BuildBlog instance
        $buildBlog = new BuildBlog();
        
        // Use reflection to access protected method
        $reflection = new ReflectionClass($buildBlog);
        $method = $reflection->getMethod('convertImageURLsToAbsolute');
        $method->setAccessible(true);
        
        // Test data without image fields
        $frontmatter = [
            'title' => 'Test Article',
            'description' => 'Test description',
            'twitter' => [
                'card' => 'summary'
            ],
            'og' => [
                'type' => 'article'
            ]
        ];
        
        // Call the method
        $result = $method->invoke($buildBlog, $frontmatter);
        
        // Assert that structure remains unchanged
        $this->assertEquals('Test Article', $result['title']);
        $this->assertEquals('Test description', $result['description']);
        $this->assertEquals('summary', $result['twitter']['card']);
        $this->assertEquals('article', $result['og']['type']);
        
        // Assert that no image fields were added
        $this->assertArrayNotHasKey('image', $result);
        $this->assertArrayNotHasKey('image', $result['twitter']);
        $this->assertArrayNotHasKey('image', $result['og']);
    }
}