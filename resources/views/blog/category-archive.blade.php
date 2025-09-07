<!DOCTYPE html>
<html lang="en">
<head>
    {!! $header !!}
</head>
<body>
    <div class="category-archive">
        <header class="archive-header">
            <h1>{{ $taxonomy_term }} Articles</h1>
            <p class="archive-description">
                {{ count($articles) }} article(s) in the "{{ $taxonomy_term }}" category
            </p>
            
            {{-- Breadcrumb navigation --}}
            <nav class="breadcrumb">
                <a href="/">Home</a> 
                <span class="separator">→</span> 
                <a href="/categories">Categories</a> 
                <span class="separator">→</span> 
                <span class="current">{{ $taxonomy_term }}</span>
            </nav>
        </header>

        <main class="archive-content">
            @forelse($articles as $article)
                <article class="archive-article">
                    <header class="article-header">
                        <h2 class="article-title">
                            <a href="{{ $article['absolute_url'] }}">{{ $article['title'] }}</a>
                        </h2>
                        
                        <div class="article-meta">
                            <time class="published-date" datetime="{{ $article['published'] ?? $article['modified'] }}">
                                {{ \Carbon\Carbon::parse($article['published'] ?? $article['modified'])->format('F j, Y') }}
                            </time>
                            
                            @if(isset($article['categories']) && count($article['categories']) > 1)
                                <div class="article-categories">
                                    <span class="meta-label">Also in:</span>
                                    @foreach($article['categories'] as $category)
                                        @if($category !== $taxonomy_term)
                                            <a href="/categories/{{ Str::slug($category) }}" class="category-link">{{ $category }}</a>@if(!$loop->last), @endif
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </header>
                    
                    @if(isset($article['description']) && $article['description'])
                        <div class="article-excerpt">
                            <p>{{ $article['description'] }}</p>
                        </div>
                    @endif
                    
                    <footer class="article-footer">
                        @if(isset($article['tags']) && count($article['tags']) > 0)
                            <div class="article-tags">
                                <span class="meta-label">Tags:</span>
                                @foreach($article['tags'] as $tag)
                                    <a href="/tags/{{ Str::slug($tag) }}" class="tag-link">{{ $tag }}</a>
                                @endforeach
                            </div>
                        @endif
                        
                        <a href="{{ $article['absolute_url'] }}" class="read-more">Read article →</a>
                    </footer>
                </article>
            @empty
                <div class="no-articles">
                    <h2>No articles found</h2>
                    <p>There are no articles in the "{{ $taxonomy_term }}" category yet.</p>
                    <a href="/categories" class="back-link">← Browse all categories</a>
                </div>
            @endforelse
        </main>

        {{-- Pagination (if implemented) --}}
        @if(isset($total_pages) && $total_pages > 1)
            <nav class="pagination">
                @if(isset($current_page) && $current_page > 1)
                    <a href="{{ $base_url }}{{ $current_page - 1 > 1 ? ($current_page - 1) . '/' : '' }}" class="prev-page">
                        ← Previous
                    </a>
                @endif
                
                <span class="page-info">
                    Page {{ $current_page ?? 1 }} of {{ $total_pages }}
                </span>
                
                @if(isset($current_page) && $current_page < $total_pages)
                    <a href="{{ $base_url }}{{ ($current_page ?? 1) + 1 }}/" class="next-page">
                        Next →
                    </a>
                @endif
            </nav>
        @endif

        {{-- Related categories --}}
        @if(function_exists('app') && app()->bound('blog'))
            @php
                $allCategories = \Spekulatius\LaravelCommonmarkBlog\Blog::getAllCategories();
                $relatedCategories = array_filter($allCategories, function($category) use ($taxonomy_term) {
                    return $category !== $taxonomy_term;
                });
                $relatedCategories = array_slice($relatedCategories, 0, 8);
            @endphp
            
            @if(count($relatedCategories) > 0)
                <aside class="related-categories">
                    <h3>Other Categories</h3>
                    <div class="category-grid">
                        @foreach($relatedCategories as $category)
                            @php
                                $categoryPosts = \Spekulatius\LaravelCommonmarkBlog\Blog::getPostsByCategory($category);
                                $postCount = count($categoryPosts);
                            @endphp
                            <a href="/categories/{{ Str::slug($category) }}" class="category-card">
                                <span class="category-name">{{ $category }}</span>
                                <span class="category-count">{{ $postCount }} article{{ $postCount !== 1 ? 's' : '' }}</span>
                            </a>
                        @endforeach
                    </div>
                </aside>
            @endif
        @endif
    </div>

    <style>
        /* Basic styling for category archive pages */
        .category-archive {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .archive-header {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 2rem;
            margin-bottom: 3rem;
        }

        .archive-header h1 {
            margin: 0 0 1rem 0;
            color: #1f2937;
        }

        .archive-description {
            color: #6b7280;
            margin: 0 0 1rem 0;
        }

        .breadcrumb {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .breadcrumb a {
            color: #7c3aed;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .separator {
            margin: 0 0.5rem;
        }

        .current {
            font-weight: 600;
        }

        .archive-article {
            border-bottom: 1px solid #e5e7eb;
            padding: 2rem 0;
        }

        .archive-article:last-child {
            border-bottom: none;
        }

        .article-title {
            margin: 0 0 1rem 0;
        }

        .article-title a {
            color: #1f2937;
            text-decoration: none;
        }

        .article-title a:hover {
            color: #7c3aed;
        }

        .article-meta {
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .meta-label {
            font-weight: 600;
        }

        .category-link {
            color: #7c3aed;
            text-decoration: none;
            margin-right: 0.5rem;
        }

        .category-link:hover {
            text-decoration: underline;
        }

        .tag-link {
            display: inline-block;
            background: #f3f4f6;
            color: #374151;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            text-decoration: none;
            margin-right: 0.5rem;
            font-size: 0.875rem;
        }

        .tag-link:hover {
            background: #e5e7eb;
        }

        .article-excerpt {
            margin: 1rem 0;
        }

        .article-footer {
            margin-top: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .read-more {
            color: #7c3aed;
            text-decoration: none;
            font-weight: 600;
        }

        .read-more:hover {
            text-decoration: underline;
        }

        .no-articles {
            text-align: center;
            padding: 3rem 0;
        }

        .back-link {
            color: #7c3aed;
            text-decoration: none;
        }

        .pagination {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pagination a {
            color: #7c3aed;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
        }

        .pagination a:hover {
            background: #f3f4f6;
        }

        .related-categories {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }

        .related-categories h3 {
            margin-bottom: 1rem;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }

        .category-card {
            display: block;
            background: #faf5ff;
            border: 1px solid #e9d5ff;
            padding: 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .category-card:hover {
            background: #f3e8ff;
            border-color: #c084fc;
            transform: translateY(-1px);
        }

        .category-name {
            display: block;
            font-weight: 600;
            color: #581c87;
            margin-bottom: 0.25rem;
        }

        .category-count {
            display: block;
            font-size: 0.875rem;
            color: #7c3aed;
        }
    </style>
</body>
</html>