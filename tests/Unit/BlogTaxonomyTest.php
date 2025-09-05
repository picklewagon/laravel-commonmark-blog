<?php

namespace Tests\Unit;

use Tests\TestCase;
use Spekulatius\LaravelCommonmarkBlog\Blog;
use Illuminate\Support\Facades\Cache;

class BlogTaxonomyTest extends TestCase
{
    protected $sampleArticles;

    public function setUp(): void
    {
        parent::setUp();

        $this->sampleArticles = [
            [
                'title' => 'Laravel Tutorial',
                'description' => 'Learn Laravel basics',
                'content' => '<p>This is a Laravel tutorial</p>',
                'tags' => ['laravel', 'php', 'tutorial'],
                'categories' => ['Development', 'Tutorials'],
                'modified' => '2025-01-15',
            ],
            [
                'title' => 'PHP Best Practices',
                'description' => 'PHP coding standards',
                'content' => '<p>Best practices for PHP development</p>',
                'tags' => ['php', 'best-practices'],
                'categories' => ['Development'],
                'modified' => '2025-01-14',
            ],
            [
                'title' => 'Vue.js Components',
                'description' => 'Building reusable components',
                'content' => '<p>Learn Vue.js component development</p>',
                'tags' => ['vue', 'javascript', 'frontend'],
                'categories' => ['Frontend', 'Tutorials'],
                'modified' => '2025-01-13',
            ],
            [
                'title' => 'Database Design',
                'description' => 'Designing efficient databases',
                'content' => '<p>Database design principles</p>',
                'tags' => ['database', 'sql'],
                'categories' => ['Database'],
                'modified' => '2025-01-12',
            ],
        ];
    }

    public function test_can_get_posts_by_tag()
    {
        $phpPosts = Blog::getPostsByTag('php', $this->sampleArticles);
        
        $this->assertCount(2, $phpPosts);
        $this->assertStringContainsString('Laravel Tutorial', $phpPosts[0]['title']);
        $this->assertStringContainsString('PHP Best Practices', $phpPosts[1]['title']);
    }

    public function test_can_get_posts_by_category()
    {
        $developmentPosts = Blog::getPostsByCategory('Development', $this->sampleArticles);
        
        $this->assertCount(2, $developmentPosts);
        $this->assertStringContainsString('Laravel Tutorial', $developmentPosts[0]['title']);
        $this->assertStringContainsString('PHP Best Practices', $developmentPosts[1]['title']);
    }

    public function test_can_get_all_tags()
    {
        $allTags = Blog::getAllTags($this->sampleArticles);
        
        $expectedTags = ['laravel', 'php', 'tutorial', 'best-practices', 'vue', 'javascript', 'frontend', 'database', 'sql'];
        
        $this->assertCount(9, $allTags);
        foreach ($expectedTags as $tag) {
            $this->assertContains($tag, $allTags);
        }
    }

    public function test_can_get_all_categories()
    {
        $allCategories = Blog::getAllCategories($this->sampleArticles);
        
        $expectedCategories = ['Development', 'Tutorials', 'Frontend', 'Database'];
        
        $this->assertCount(4, $allCategories);
        foreach ($expectedCategories as $category) {
            $this->assertContains($category, $allCategories);
        }
    }

    public function test_returns_empty_array_for_nonexistent_tag()
    {
        $posts = Blog::getPostsByTag('nonexistent', $this->sampleArticles);
        $this->assertEmpty($posts);
    }

    public function test_returns_empty_array_for_nonexistent_category()
    {
        $posts = Blog::getPostsByCategory('NonExistent', $this->sampleArticles);
        $this->assertEmpty($posts);
    }

    public function test_handles_posts_without_tags()
    {
        $articlesWithoutTags = [
            [
                'title' => 'Post Without Tags',
                'description' => 'A post without any tags',
                'content' => '<p>Content here</p>',
                'modified' => '2025-01-15',
            ]
        ];

        $allTags = Blog::getAllTags($articlesWithoutTags);
        $this->assertEmpty($allTags);
    }

    public function test_handles_posts_without_categories()
    {
        $articlesWithoutCategories = [
            [
                'title' => 'Post Without Categories',
                'description' => 'A post without any categories',
                'content' => '<p>Content here</p>',
                'modified' => '2025-01-15',
            ]
        ];

        $allCategories = Blog::getAllCategories($articlesWithoutCategories);
        $this->assertEmpty($allCategories);
    }

    public function test_pagination_works_correctly()
    {
        $paginatedResult = Blog::getPaginatedPosts(2, 1, $this->sampleArticles);
        
        $this->assertEquals(1, $paginatedResult['current_page']);
        $this->assertEquals(2, $paginatedResult['total_pages']);
        $this->assertEquals(4, $paginatedResult['total_posts']);
        $this->assertEquals(2, $paginatedResult['per_page']);
        $this->assertCount(2, $paginatedResult['articles']);
    }

    public function test_search_posts_by_content()
    {
        $searchResults = Blog::searchPosts('Laravel', $this->sampleArticles);
        
        $this->assertCount(1, $searchResults);
        $this->assertStringContainsString('Laravel Tutorial', $searchResults[0]['title']);
    }

    public function test_search_posts_case_insensitive()
    {
        $searchResults = Blog::searchPosts('vue.js', $this->sampleArticles);
        
        $this->assertCount(1, $searchResults);
        $this->assertStringContainsString('Vue.js Components', $searchResults[0]['title']);
    }

    public function test_can_use_cached_articles()
    {
        // Mock the cache
        Cache::shouldReceive('get')
            ->with(config('blog.cache.key'), [])
            ->andReturn($this->sampleArticles);

        $phpPosts = Blog::getPostsByTag('php');
        $this->assertCount(2, $phpPosts);
    }
}