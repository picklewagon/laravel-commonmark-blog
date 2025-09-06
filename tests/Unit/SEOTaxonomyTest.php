<?php

namespace Spekulatius\LaravelCommonmarkBlog\Tests\Unit;

use Spekulatius\LaravelCommonmarkBlog\Tests\TestCase;
use Spekulatius\LaravelCommonmarkBlog\Commands\BuildBlog;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class SEOTaxonomyTest extends TestCase
{
    protected $buildBlog;
    protected $tempSourcePath;

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
        seo()->clear();

        // Configure basic blog settings
        Config::set('blog.defaults', [
            'title' => 'Test Blog',
            'description' => 'A test blog',
        ]);
    }

    public function tearDown(): void
    {
        if (File::exists($this->tempSourcePath)) {
            File::deleteDirectory($this->tempSourcePath);
        }
        parent::tearDown();
    }

    public function test_auto_generates_keywords_from_tags_and_categories()
    {
        // Create a test markdown file with tags and categories but no keywords
        $content = <<<'MD'
---
title: "Test Post"
description: "A test post"
tags: ["laravel", "php", "tutorial"]
categories: ["Development", "Tutorials"]
---

# Test Content
MD;

        $testFile = $this->tempSourcePath . '/test-post.md';
        File::put($testFile, $content);

        // Use reflection to access the protected fillIn method
        $reflection = new \ReflectionClass($this->buildBlog);
        $method = $reflection->getMethod('fillIn');
        $method->setAccessible(true);

        // Parse the frontmatter 
        $article = \Spatie\YamlFrontMatter\YamlFrontMatter::parse($content);
        $frontmatter = $article->matter();

        // Call fillIn method which should set SEO keywords
        $method->invoke($this->buildBlog, $frontmatter);

        // Check that keywords meta tag was added to SEO instance
        $seoItems = seo()->getItems();
        $keywordsTag = collect($seoItems)->first(function($item) {
            return isset($item->tag) && 
                   $item->tag === 'meta' && 
                   isset($item->data['name']) && 
                   $item->data['name'] === 'keywords';
        });

        $this->assertNotNull($keywordsTag, 'Keywords meta tag should be added');
        $this->assertEquals('laravel, php, tutorial, Development, Tutorials', $keywordsTag->data['content']);
    }

    public function test_preserves_explicit_keywords_over_auto_generated()
    {
        // Create a test file with explicit keywords and tags/categories
        $content = <<<'MD'
---
title: "Test Post"
description: "A test post"
keywords: ["custom", "keywords"]
tags: ["laravel", "php"]
categories: ["Development"]
---

# Test Content
MD;

        $testFile = $this->tempSourcePath . '/test-explicit-keywords.md';
        File::put($testFile, $content);

        // Use reflection to access the protected fillIn method
        $reflection = new \ReflectionClass($this->buildBlog);
        $method = $reflection->getMethod('fillIn');
        $method->setAccessible(true);

        // Parse the frontmatter 
        $article = \Spatie\YamlFrontMatter\YamlFrontMatter::parse($content);
        $frontmatter = $article->matter();

        // Call fillIn method
        $method->invoke($this->buildBlog, $frontmatter);

        // Check that explicit keywords are preserved, not auto-generated ones
        $seoItems = seo()->getItems();
        $keywordsTag = collect($seoItems)->first(function($item) {
            return isset($item->tag) && 
                   $item->tag === 'meta' && 
                   isset($item->data['name']) && 
                   $item->data['name'] === 'keywords';
        });

        $this->assertNotNull($keywordsTag, 'Keywords meta tag should be added');
        $this->assertEquals('custom, keywords', $keywordsTag->data['content']);
    }

    public function test_handles_missing_tags_and_categories_gracefully()
    {
        // Create a test file without tags or categories
        $content = <<<'MD'
---
title: "Test Article"
description: "A test article"
published: "2025-01-15"
modified: "2025-01-15"
---

# Test Article

This is test content.
MD;

        $testFile = $this->tempSourcePath . '/test-article.md';
        File::put($testFile, $content);

        // Use reflection to access the protected prepareData method
        $reflection = new \ReflectionClass($this->buildBlog);
        $method = $reflection->getMethod('prepareData');
        $method->setAccessible(true);

        $data = $method->invoke($this->buildBlog, $testFile);

        // Verify that no keywords meta tag is generated when there are no tags/categories
        $this->assertStringNotContainsString('<meta name="keywords"', $data['header']);
    }

    public function test_handles_string_keywords_correctly()
    {
        // Create a test file with string keywords (not array)
        $content = <<<'MD'
---
title: "Test Post"
description: "A test post"
keywords: "string, keywords, format"
---

# Test Content
MD;

        $testFile = $this->tempSourcePath . '/test-string-keywords.md';
        File::put($testFile, $content);

        // Use reflection to access the protected fillIn method
        $reflection = new \ReflectionClass($this->buildBlog);
        $method = $reflection->getMethod('fillIn');
        $method->setAccessible(true);

        // Parse the frontmatter 
        $article = \Spatie\YamlFrontMatter\YamlFrontMatter::parse($content);
        $frontmatter = $article->matter();

        // Call fillIn method
        $method->invoke($this->buildBlog, $frontmatter);

        // Check that string keywords are handled correctly
        $seoItems = seo()->getItems();
        $keywordsTag = collect($seoItems)->first(function($item) {
            return isset($item->tag) && 
                   $item->tag === 'meta' && 
                   isset($item->data['name']) && 
                   $item->data['name'] === 'keywords';
        });

        $this->assertNotNull($keywordsTag, 'Keywords meta tag should be added');
        $this->assertEquals('string, keywords, format', $keywordsTag->data['content']);
    }
}