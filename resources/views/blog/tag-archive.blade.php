<!DOCTYPE html>
<html lang="en">
<head>
    {!! $header !!}
</head>
<body>
    <div class="tag-archive">
        <header class="archive-header">
            <h1>Posts tagged with "{{ $taxonomy_term }}"</h1>
            <p class="archive-description">
                {{ count($articles) }} article(s) tagged with "{{ $taxonomy_term }}"
            </p>
            
            {{-- Breadcrumb navigation --}}
            <nav class="breadcrumb">
                <a href="/">Home</a> 
                <span class="separator">→</span> 
                <a href="/tags">Tags</a> 
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
                            
                            @if(isset($article['categories']) && count($article['categories']) > 0)
                                <div class="article-categories">
                                    <span class="meta-label">In:</span>
                                    @foreach($article['categories'] as $category)
                                        <a href="/categories/{{ Str::slug($category) }}" class="category-link">{{ $category }}</a>@if(!$loop->last), @endif
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
                                    @if($tag !== $taxonomy_term)
                                        <a href="/tags/{{ Str::slug($tag) }}" class="tag-link">{{ $tag }}</a>
                                    @else
                                        <span class="tag-current">{{ $tag }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        
                        <a href="{{ $article['absolute_url'] }}" class="read-more">Read more →</a>
                    </footer>
                </article>
            @empty
                <div class="no-articles">
                    <h2>No articles found</h2>
                    <p>There are no articles tagged with "{{ $taxonomy_term }}" yet.</p>
                    <a href="/tags" class="back-link">← Browse all tags</a>
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

        {{-- Related tags --}}
        @if(function_exists('app') && app()->bound('blog'))
            @php
                $allTags = \Spekulatius\LaravelCommonmarkBlog\Blog::getAllTags();
                $relatedTags = array_filter($allTags, function($tag) use ($taxonomy_term) {
                    return $tag !== $taxonomy_term;
                });
                $relatedTags = array_slice($relatedTags, 0, 10);
            @endphp
            
            @if(count($relatedTags) > 0)
                <aside class="related-tags">
                    <h3>Other Tags</h3>
                    <div class="tag-cloud">
                        @foreach($relatedTags as $tag)
                            <a href="/tags/{{ Str::slug($tag) }}" class="tag-link">{{ $tag }}</a>
                        @endforeach
                    </div>
                </aside>
            @endif
        @endif
    </div>

    <style>
        /* Basic styling for tag archive pages */
        .tag-archive {
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
            color: #3b82f6;
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
            color: #3b82f6;
        }

        .article-meta {
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .meta-label {
            font-weight: 600;
        }

        .category-link, .tag-link {
            color: #3b82f6;
            text-decoration: none;
            margin-right: 0.5rem;
        }

        .category-link:hover, .tag-link:hover {
            text-decoration: underline;
        }

        .tag-current {
            background: #dbeafe;
            color: #1e40af;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            margin-right: 0.5rem;
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
            color: #3b82f6;
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
            color: #3b82f6;
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
            color: #3b82f6;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
        }

        .pagination a:hover {
            background: #f3f4f6;
        }

        .related-tags {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }

        .related-tags h3 {
            margin-bottom: 1rem;
        }

        .tag-cloud .tag-link {
            display: inline-block;
            background: #f3f4f6;
            color: #374151;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            margin: 0.25rem;
            font-size: 0.875rem;
        }

        .tag-cloud .tag-link:hover {
            background: #e5e7eb;
            text-decoration: none;
        }
    </style>
</body>
</html>