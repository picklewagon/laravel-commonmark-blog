<!DOCTYPE html>
<html lang="en">
<head>
    {!! $header !!}
</head>
<body>
    <div class="taxonomy-overview">
        <header class="page-header">
            <h1>Blog Tags</h1>
            <p class="page-description">
                Explore articles by topic and theme using our tag system.
            </p>
            
            <nav class="breadcrumb">
                <a href="/">Home</a> 
                <span class="separator">→</span> 
                <span class="current">Tags</span>
            </nav>
        </header>

        <main class="taxonomy-content">
            @if(function_exists('app') && app()->bound('blog'))
                @php
                    $allTags = \Spekulatius\LaravelCommonmarkBlog\Blog::getAllTags();
                    
                    // Create tag data with post counts
                    $tagData = [];
                    foreach($allTags as $tag) {
                        $posts = \Spekulatius\LaravelCommonmarkBlog\Blog::getPostsByTag($tag);
                        $tagData[] = [
                            'name' => $tag,
                            'slug' => Str::slug($tag),
                            'count' => count($posts)
                        ];
                    }
                    
                    // Sort by post count (descending)
                    usort($tagData, function($a, $b) {
                        return $b['count'] - $a['count'];
                    });
                @endphp
                
                @if(count($tagData) > 0)
                    <div class="tag-cloud">
                        @foreach($tagData as $tag)
                            <a href="/tags/{{ $tag['slug'] }}" 
                               class="tag-item tag-size-{{ min(5, max(1, ceil($tag['count'] / 2))) }}">
                                <span class="tag-name">{{ $tag['name'] }}</span>
                                <span class="tag-count">{{ $tag['count'] }}</span>
                            </a>
                        @endforeach
                    </div>

                    {{-- Alphabetical listing --}}
                    <section class="alphabetical-tags">
                        <h2>All Tags (Alphabetical)</h2>
                        @php
                            $alphabeticalTags = collect($tagData)->sortBy('name');
                        @endphp
                        
                        <div class="tag-list">
                            @foreach($alphabeticalTags as $tag)
                                <div class="tag-list-item">
                                    <a href="/tags/{{ $tag['slug'] }}" class="tag-link">
                                        {{ $tag['name'] }}
                                    </a>
                                    <span class="post-count">{{ $tag['count'] }} post{{ $tag['count'] !== 1 ? 's' : '' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @else
                    <div class="no-tags">
                        <h2>No tags found</h2>
                        <p>There are no tags available yet. Tags will appear here as articles are published.</p>
                        <a href="/" class="back-link">← Back to homepage</a>
                    </div>
                @endif
            @endif
        </main>

        {{-- Related links --}}
        <aside class="related-links">
            <div class="link-card">
                <h3>Categories</h3>
                <p>Browse articles by broader topic categories.</p>
                <a href="/categories" class="link-button">View Categories →</a>
            </div>
        </aside>
    </div>

    <style>
        /* Styling for tag overview page */
        .taxonomy-overview {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            text-align: center;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 2rem;
            margin-bottom: 3rem;
        }

        .page-header h1 {
            margin: 0 0 1rem 0;
            color: #1f2937;
        }

        .page-description {
            color: #6b7280;
            margin: 0 0 1rem 0;
            font-size: 1.125rem;
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

        .tag-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 4rem;
            padding: 2rem;
            background: #f8fafc;
            border-radius: 0.5rem;
        }

        .tag-item {
            display: inline-flex;
            align-items: center;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 9999px;
            padding: 0.5rem 1rem;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .tag-item:hover {
            background: #3b82f6;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .tag-size-1 { font-size: 0.875rem; }
        .tag-size-2 { font-size: 1rem; }
        .tag-size-3 { font-size: 1.125rem; }
        .tag-size-4 { font-size: 1.25rem; }
        .tag-size-5 { font-size: 1.5rem; font-weight: 600; }

        .tag-name {
            color: inherit;
        }

        .tag-count {
            margin-left: 0.5rem;
            font-size: 0.75rem;
            background: #e5e7eb;
            color: #374151;
            padding: 0.125rem 0.375rem;
            border-radius: 9999px;
        }

        .tag-item:hover .tag-count {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .alphabetical-tags {
            margin-bottom: 3rem;
        }

        .alphabetical-tags h2 {
            margin-bottom: 1.5rem;
            color: #1f2937;
        }

        .tag-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 0.5rem;
        }

        .tag-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
        }

        .tag-list-item:hover {
            background: #f3f4f6;
        }

        .tag-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .tag-link:hover {
            text-decoration: underline;
        }

        .post-count {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .no-tags {
            text-align: center;
            padding: 3rem 0;
        }

        .back-link {
            color: #3b82f6;
            text-decoration: none;
        }

        .related-links {
            border-top: 1px solid #e5e7eb;
            padding-top: 2rem;
            margin-top: 3rem;
        }

        .link-card {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
        }

        .link-card h3 {
            margin: 0 0 0.5rem 0;
            color: #92400e;
        }

        .link-card p {
            margin: 0 0 1rem 0;
            color: #a16207;
        }

        .link-button {
            display: inline-block;
            background: #f59e0b;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-weight: 600;
        }

        .link-button:hover {
            background: #d97706;
        }
    </style>
</body>
</html>