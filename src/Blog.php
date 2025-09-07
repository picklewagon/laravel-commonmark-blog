<?php

namespace Spekulatius\LaravelCommonmarkBlog;

use Illuminate\Support\Facades\Cache;

class Blog
{
    /**
     * Get all posts with a specific tag
     *
     * @param string $tag
     * @param array $articles
     * @return array
     */
    public static function getPostsByTag(string $tag, array $articles = null): array
    {
        if ($articles === null) {
            $articles = Cache::get(config('blog.cache.key'), []);
        }

        return array_filter($articles, function($article) use ($tag) {
            return isset($article['tags']) && 
                   is_array($article['tags']) && 
                   in_array($tag, $article['tags']);
        });
    }

    /**
     * Get all posts in a specific category
     *
     * @param string $category
     * @param array $articles
     * @return array
     */
    public static function getPostsByCategory(string $category, array $articles = null): array
    {
        if ($articles === null) {
            $articles = Cache::get(config('blog.cache.key'), []);
        }

        return array_filter($articles, function($article) use ($category) {
            return isset($article['categories']) && 
                   is_array($article['categories']) && 
                   in_array($category, $article['categories']);
        });
    }

    /**
     * Get all available tags from cached articles
     *
     * @param array $articles
     * @return array
     */
    public static function getAllTags(array $articles = null): array
    {
        if ($articles === null) {
            $articles = Cache::get(config('blog.cache.key'), []);
        }

        $tags = [];
        foreach ($articles as $article) {
            if (isset($article['tags']) && is_array($article['tags'])) {
                $tags = array_merge($tags, $article['tags']);
            }
        }

        return array_unique($tags);
    }

    /**
     * Get all available categories from cached articles
     *
     * @param array $articles
     * @return array
     */
    public static function getAllCategories(array $articles = null): array
    {
        if ($articles === null) {
            $articles = Cache::get(config('blog.cache.key'), []);
        }

        $categories = [];
        foreach ($articles as $article) {
            if (isset($article['categories']) && is_array($article['categories'])) {
                $categories = array_merge($categories, $article['categories']);
            }
        }

        return array_unique($categories);
    }

    /**
     * Get all cached articles
     *
     * @return array
     */
    public static function getAllPosts(): array
    {
        return Cache::get(config('blog.cache.key'), []);
    }

    /**
     * Get posts with pagination
     *
     * @param int $perPage
     * @param int $page
     * @param array $articles
     * @return array
     */
    public static function getPaginatedPosts(int $perPage = 12, int $page = 1, array $articles = null): array
    {
        if ($articles === null) {
            $articles = Cache::get(config('blog.cache.key'), []);
        }

        $offset = ($page - 1) * $perPage;
        $totalPosts = count($articles);
        $totalPages = ceil($totalPosts / $perPage);

        // Sort by date
        usort($articles, function($a, $b) {
            return strtotime($b['modified'] ?? '1970-01-01') - strtotime($a['modified'] ?? '1970-01-01');
        });

        return [
            'articles' => array_slice($articles, $offset, $perPage),
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_posts' => $totalPosts,
            'per_page' => $perPage,
        ];
    }

    /**
     * Search posts by title, description, or content
     *
     * @param string $query
     * @param array $articles
     * @return array
     */
    public static function searchPosts(string $query, array $articles = null): array
    {
        if ($articles === null) {
            $articles = Cache::get(config('blog.cache.key'), []);
        }

        $query = strtolower($query);

        return array_filter($articles, function($article) use ($query) {
            $searchableText = strtolower(
                ($article['title'] ?? '') . ' ' . 
                ($article['description'] ?? '') . ' ' . 
                strip_tags($article['content'] ?? '')
            );

            return strpos($searchableText, $query) !== false;
        });
    }

    /**
     * Get related posts based on shared tags and categories
     *
     * @param array $currentPost
     * @param int $limit
     * @param array $articles
     * @return array
     */
    public static function getRelatedPosts(array $currentPost, int $limit = 5, array $articles = null): array
    {
        if ($articles === null) {
            $articles = Cache::get(config('blog.cache.key'), []);
        }

        $currentTags = $currentPost['tags'] ?? [];
        $currentCategories = $currentPost['categories'] ?? [];
        $currentUrl = $currentPost['absolute_url'] ?? '';

        // Score articles based on shared taxonomies
        $scoredArticles = [];
        
        foreach ($articles as $article) {
            // Skip the current post
            if (($article['absolute_url'] ?? '') === $currentUrl) {
                continue;
            }

            $score = 0;
            $articleTags = $article['tags'] ?? [];
            $articleCategories = $article['categories'] ?? [];

            // Score based on shared tags (2 points each)
            foreach ($currentTags as $tag) {
                if (in_array($tag, $articleTags)) {
                    $score += 2;
                }
            }

            // Score based on shared categories (3 points each - categories are broader)
            foreach ($currentCategories as $category) {
                if (in_array($category, $articleCategories)) {
                    $score += 3;
                }
            }

            if ($score > 0) {
                $scoredArticles[] = [
                    'article' => $article,
                    'score' => $score,
                ];
            }
        }

        // Sort by score (highest first), then by date
        usort($scoredArticles, function($a, $b) {
            if ($a['score'] === $b['score']) {
                $dateA = strtotime($a['article']['modified'] ?? '1970-01-01');
                $dateB = strtotime($b['article']['modified'] ?? '1970-01-01');
                return $dateB - $dateA; // Newer first
            }
            return $b['score'] - $a['score']; // Higher score first
        });

        // Return just the articles, limited to the specified number
        return array_slice(
            array_column($scoredArticles, 'article'), 
            0, 
            $limit
        );
    }
}