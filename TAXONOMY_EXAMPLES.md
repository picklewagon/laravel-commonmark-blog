# Laravel CommonMark Blog - Taxonomy Usage Examples

This document provides practical examples of using the tags and categories features.

## Quick Start with Published Templates

The easiest way to get started is to publish the default templates and customize them:

```bash
# Publish customizable taxonomy view templates
php artisan vendor:publish --provider="Spekulatius\LaravelCommonmarkBlog\CommonmarkBlogServiceProvider" --tag="blog-views"
```

This creates the following files in `resources/views/blog/`:
- `tag-archive.blade.php` - Beautiful tag archive pages with tag clouds
- `category-archive.blade.php` - Category archive pages with related categories  
- `tags-overview.blade.php` - Tag overview page with size-based tag cloud
- `categories-overview.blade.php` - Category overview with card-based layout

These templates are fully responsive, SEO-optimized, and include embedded CSS styling.

## Basic Example Blog Post

Create a blog post with tags and categories in `resources/content/blog/laravel-tutorial.md`:

```yaml
---
title: "Getting Started with Laravel 11"
description: "A comprehensive guide to building your first Laravel application"
image: "/images/laravel-tutorial.jpg"
published: "2025-01-15"
modified: "2025-01-15"
tags: ["laravel", "php", "tutorial", "web-development", "beginner"]
categories: ["Development", "Tutorials", "Laravel"]
---

# Getting Started with Laravel 11

Laravel is a powerful PHP framework that makes web development enjoyable and creative...

## What You'll Learn

- Setting up a Laravel project
- Understanding MVC architecture
- Creating your first route and controller
- Working with Blade templates
```

## Generated Archive Pages

When you run `php artisan blog:build`, the following archive pages will be automatically generated:

### Tag Archives:
- `/tags/laravel/index.htm` - All posts tagged with "laravel"
- `/tags/php/index.htm` - All posts tagged with "php"
- `/tags/tutorial/index.htm` - All posts tagged with "tutorial"
- `/tags/web-development/index.htm` - All posts tagged with "web-development"
- `/tags/beginner/index.htm` - All posts tagged with "beginner"

### Category Archives:
- `/categories/development/index.htm` - All posts in "Development" category
- `/categories/tutorials/index.htm` - All posts in "Tutorials" category
- `/categories/laravel/index.htm` - All posts in "Laravel" category

## Article Template Example

Update your article template (`resources/views/blog/article.blade.php`) to display tags and categories:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    {!! $header !!}
</head>
<body>
    <article>
        <header>
            <h1>{{ $title }}</h1>
            <p class="meta">
                Published on {{ \Carbon\Carbon::parse($published)->format('F j, Y') }}
                @if(isset($modified) && $modified !== $published)
                    • Updated {{ \Carbon\Carbon::parse($modified)->format('F j, Y') }}
                @endif
            </p>
        </header>

        <div class="content">
            {!! $content !!}
        </div>

        <footer class="article-footer">
            {{-- Display Categories --}}
            @if(isset($categories) && count($categories) > 0)
                <div class="article-categories">
                    <strong>Categories:</strong>
                    @foreach($categories as $category)
                        <a href="/categories/{{ Str::slug($category) }}" 
                           class="category-link">{{ $category }}</a>@if(!$loop->last), @endif
                    @endforeach
                </div>
            @endif

            {{-- Display Tags --}}
            @if(isset($tags) && count($tags) > 0)
                <div class="article-tags">
                    <strong>Tags:</strong>
                    @foreach($tags as $tag)
                        <a href="/tags/{{ Str::slug($tag) }}" 
                           class="tag-link">#{{ $tag }}</a>
                    @endforeach
                </div>
            @endif
        </footer>
    </article>

    {{-- Related Posts Section --}}
    @if(isset($relatedPosts) && count($relatedPosts) > 0)
        <section class="related-posts">
            <h3>Related Articles</h3>
            <div class="related-posts-grid">
                @foreach($relatedPosts as $relatedPost)
                    <article class="related-post">
                        <h4>
                            <a href="{{ $relatedPost['absolute_url'] }}">{{ $relatedPost['title'] }}</a>
                        </h4>
                        @if(isset($relatedPost['description']))
                            <p>{{ Str::limit($relatedPost['description'], 100) }}</p>
                        @endif
                        <div class="related-post-meta">
                            <time>{{ \Carbon\Carbon::parse($relatedPost['published'])->format('M j, Y') }}</time>
                            @if(isset($relatedPost['categories']))
                                <span class="categories">
                                    @foreach(array_slice($relatedPost['categories'], 0, 2) as $category)
                                        <span class="category">{{ $category }}</span>
                                    @endforeach
                                </span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</body>
</html>
```

## List Template for Archives

Create or update your list template (`resources/views/blog/list.blade.php`) for archive pages:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    {!! $header !!}
</head>
<body>
    <div class="blog-archive">
        <header>
            <h1>{{ $title }}</h1>
            @if(isset($description))
                <p>{{ $description }}</p>
            @endif
        </header>

        <div class="articles-list">
            @forelse($articles as $article)
                <article class="article-preview">
                    <h2>
                        <a href="{{ $article['absolute_url'] }}">{{ $article['title'] }}</a>
                    </h2>
                    
                    @if(isset($article['description']))
                        <p class="excerpt">{{ $article['description'] }}</p>
                    @endif
                    
                    <div class="article-meta">
                        <time>{{ \Carbon\Carbon::parse($article['published'])->format('F j, Y') }}</time>
                        
                        @if(isset($article['tags']) && count($article['tags']) > 0)
                            <div class="tags">
                                @foreach(array_slice($article['tags'], 0, 3) as $tag)
                                    <span class="tag">#{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <p>No articles found.</p>
            @endforelse
        </div>

        {{-- Simple pagination if needed --}}
        @if($total_pages > 1)
            <nav class="pagination">
                @if($current_page > 1)
                    <a href="{{ $base_url }}{{ $current_page - 1 > 1 ? ($current_page - 1) . '/' : '' }}">&larr; Previous</a>
                @endif
                
                <span>Page {{ $current_page }} of {{ $total_pages }}</span>
                
                @if($current_page < $total_pages)
                    <a href="{{ $base_url }}{{ $current_page + 1 }}/">Next &rarr;</a>
                @endif
            </nav>
        @endif
    </div>
</body>
</html>
```

## Using the Blog Helper Class

Create a controller or use in routes to access taxonomy data:

```php
<?php

use Spekulatius\LaravelCommonmarkBlog\Blog;

class BlogController extends Controller
{
    public function index()
    {
        $recentPosts = Blog::getPaginatedPosts(5, 1);
        $popularTags = array_slice(Blog::getAllTags(), 0, 10);
        
        return view('blog.index', [
            'posts' => $recentPosts['articles'],
            'tags' => $popularTags,
        ]);
    }
    
    public function show($slug, Request $request)
    {
        // Get the current post (implementation depends on your setup)
        $currentPost = $this->getCurrentPost($slug);
        
        // Get related posts based on shared tags/categories
        $relatedPosts = Blog::getRelatedPosts($currentPost, 5);
        
        return view('blog.article', [
            'post' => $currentPost,
            'relatedPosts' => $relatedPosts,
        ]);
    }
    
    public function tagArchive($tag)
    {
        $posts = Blog::getPostsByTag($tag);
        
        return view('blog.tag-archive', [
            'tag' => $tag,
            'posts' => $posts,
        ]);
    }
    
    public function categoryArchive($category)
    {
        $posts = Blog::getPostsByCategory($category);
        
        return view('blog.category-archive', [
            'category' => $category,
            'posts' => $posts,
        ]);
    }
    
    public function search(Request $request)
    {
        $query = $request->get('q');
        $results = Blog::searchPosts($query);
        
        return view('blog.search-results', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
```

## Custom Archive Templates

Create custom archive templates by setting them in your config:

```php
// config/blog.php
'taxonomies' => [
    'tags' => [
        'enabled' => true,
        'route_prefix' => 'tags',
        'archive_template' => 'blog.custom-tag-archive',
    ],
    'categories' => [
        'enabled' => true,
        'route_prefix' => 'categories',
        'archive_template' => 'blog.custom-category-archive',
    ],
],
```

Then create `resources/views/blog/custom-tag-archive.blade.php`:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    {!! $header !!}
</head>
<body>
    <div class="tag-archive-page">
        <header class="archive-header">
            <h1>Posts tagged with "{{ $taxonomy_term }}"</h1>
            <p>{{ count($articles) }} article(s) found</p>
        </header>

        <div class="tagged-articles">
            @foreach($articles as $article)
                <div class="tagged-article">
                    <h2><a href="{{ $article['absolute_url'] }}">{{ $article['title'] }}</a></h2>
                    <p>{{ $article['description'] ?? '' }}</p>
                    
                    <div class="article-categories">
                        @if(isset($article['categories']))
                            @foreach($article['categories'] as $category)
                                <a href="/categories/{{ Str::slug($category) }}" class="category">{{ $category }}</a>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>
```

## CSS Styling Example

Add some basic styling for taxonomy elements:

```css
/* Tag styling */
.tag-link, .tag {
    display: inline-block;
    background: #e1f5fe;
    color: #0277bd;
    padding: 2px 8px;
    border-radius: 12px;
    text-decoration: none;
    font-size: 0.875rem;
    margin: 2px;
}

.tag-link:hover {
    background: #b3e5fc;
}

/* Category styling */
.category-link, .category {
    display: inline-block;
    background: #f3e5f5;
    color: #7b1fa2;
    padding: 4px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    margin: 2px;
}

.category-link:hover {
    background: #e1bee7;
}

/* Archive page styling */
.archive-header {
    border-bottom: 2px solid #eee;
    padding-bottom: 1rem;
    margin-bottom: 2rem;
}

.article-preview {
    border-bottom: 1px solid #eee;
    padding: 1.5rem 0;
}

.article-meta {
    color: #666;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.pagination {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

.pagination a {
    margin: 0 1rem;
    text-decoration: none;
    color: #0277bd;
}

/* Related posts styling */
.related-posts {
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 2px solid #eee;
}

.related-posts h3 {
    margin-bottom: 1.5rem;
    color: #333;
}

.related-posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.related-post {
    padding: 1rem;
    border: 1px solid #eee;
    border-radius: 8px;
    background: #fafafa;
}

.related-post h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
}

.related-post h4 a {
    text-decoration: none;
    color: #333;
}

.related-post h4 a:hover {
    color: #0277bd;
}

.related-post p {
    margin: 0.5rem 0;
    color: #666;
    font-size: 0.9rem;
}

.related-post-meta {
    font-size: 0.8rem;
    color: #888;
    margin-top: 0.5rem;
}

.related-post-meta .category {
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 3px;
    margin-left: 0.5rem;
}
```

This provides a complete working example of how to implement and use the taxonomy features in your Laravel CommonMark Blog!