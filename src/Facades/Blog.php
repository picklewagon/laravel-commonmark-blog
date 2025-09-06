<?php

namespace Spekulatius\LaravelCommonmarkBlog\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getPostsByTag(string $tag, array $articles = null)
 * @method static array getPostsByCategory(string $category, array $articles = null) 
 * @method static array getAllTags(array $articles = null)
 * @method static array getAllCategories(array $articles = null)
 * @method static array getAllPosts()
 * @method static array getPaginatedPosts(int $perPage = 12, int $page = 1, array $articles = null)
 * @method static array searchPosts(string $query, array $articles = null)
 * @method static array getRelatedPosts(array $currentPost, int $limit = 5, array $articles = null)
 *
 * @see \Spekulatius\LaravelCommonmarkBlog\Blog
 */
class Blog extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'blog';
    }
}