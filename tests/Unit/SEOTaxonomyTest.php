<?php

namespace Tests\Unit;

use Tests\TestCase;
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

        $this->buildBlog = new BuildBlog();
        $this->tempSourcePath = storage_path('testing/seo_test');

        if (File::exists($this->tempSourcePath)) {
            File::deleteDirectory($this->tempSourcePath);
        }
        File::makeDirectory($this->tempSourcePath, 0755, true);

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
        // Create a test file with tags and categories but no explicit keywords
        $content = <<<'MD'
---
title: "Test Article"
description: "A test article"
published: "2025-01-15"
modified: "2025-01-15"
tags: ["laravel", "php", "tutorial"]
categories: ["Development", "Tutorials"]
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

        // Verify that tags and categories are included in the data
        $this->assertEquals(['laravel', 'php', 'tutorial'], $data['tags']);
        $this->assertEquals(['Development', 'Tutorials'], $data['categories']);

        // The header should contain meta keywords generated from tags and categories
        $this->assertStringContainsString('laravel, php, tutorial, Development, Tutorials', $data['header']);
    }

    public function test_preserves_explicit_keywords_over_auto_generated()
    {
        // Create a test file with explicit keywords and tags/categories
        $content = <<<'MD'
---
title: "Test Article"
description: "A test article"
published: "2025-01-15"
modified: "2025-01-15"
keywords: ["custom", "keywords"]
tags: ["laravel", "php"]
categories: ["Development"]
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

        // Verify that explicit keywords are used, not auto-generated ones
        $this->assertStringContainsString('custom, keywords', $data['header']);
        $this->assertStringNotContainsString('laravel', $data['header']); // Should not appear in keywords
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
        // Create a test file with string keywords instead of array
        $content = <<<'MD'
---
title: "Test Article"
description: "A test article"
published: "2025-01-15"
modified: "2025-01-15"
keywords: "string, keywords, format"
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

        // Verify that string keywords are handled correctly
        $this->assertStringContainsString('string, keywords, format', $data['header']);
    }
}