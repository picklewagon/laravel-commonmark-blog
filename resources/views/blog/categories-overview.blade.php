<!DOCTYPE html>
<html lang="en">
<head>
    {!! $header !!}
</head>
<body>
    <div class="taxonomy-overview">
        <header class="page-header">
            <h1>Blog Categories</h1>
            <p class="page-description">
                Browse articles organized by broader topic categories.
            </p>
            
            <nav class="breadcrumb">
                <a href="/">Home</a> 
                <span class="separator">→</span> 
                <span class="current">Categories</span>
            </nav>
        </header>

        <main class="taxonomy-content">
            @if(function_exists('app') && app()->bound('blog'))
                @php
                    $allCategories = \Spekulatius\LaravelCommonmarkBlog\Blog::getAllCategories();
                    
                    // Create category data with post counts
                    $categoryData = [];
                    foreach($allCategories as $category) {
                        $posts = \Spekulatius\LaravelCommonmarkBlog\Blog::getPostsByCategory($category);
                        $categoryData[] = [
                            'name' => $category,
                            'slug' => Str::slug($category),
                            'count' => count($posts)
                        ];
                    }
                    
                    // Sort by post count (descending)
                    usort($categoryData, function($a, $b) {
                        return $b['count'] - $a['count'];
                    });
                @endphp
                
                @if(count($categoryData) > 0)
                    <div class="category-grid">
                        @foreach($categoryData as $category)
                            <div class="category-card">
                                <div class="category-icon">
                                    📁
                                </div>
                                <h3 class="category-name">
                                    <a href="/categories/{{ $category['slug'] }}">{{ $category['name'] }}</a>
                                </h3>
                                <p class="category-count">
                                    {{ $category['count'] }} article{{ $category['count'] !== 1 ? 's' : '' }}
                                </p>
                                <a href="/categories/{{ $category['slug'] }}" class="category-link">
                                    Browse articles →
                                </a>
                            </div>
                        @endforeach
                    </div>

                    {{-- Alphabetical listing --}}
                    <section class="alphabetical-categories">
                        <h2>All Categories (Alphabetical)</h2>
                        @php
                            $alphabeticalCategories = collect($categoryData)->sortBy('name');
                        @endphp
                        
                        <div class="category-list">
                            @foreach($alphabeticalCategories as $category)
                                <div class="category-list-item">
                                    <a href="/categories/{{ $category['slug'] }}" class="category-link-text">
                                        {{ $category['name'] }}
                                    </a>
                                    <span class="post-count">{{ $category['count'] }} post{{ $category['count'] !== 1 ? 's' : '' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @else
                    <div class="no-categories">
                        <h2>No categories found</h2>
                        <p>There are no categories available yet. Categories will appear here as articles are published.</p>
                        <a href="/" class="back-link">← Back to homepage</a>
                    </div>
                @endif
            @endif
        </main>

        {{-- Related links --}}
        <aside class="related-links">
            <div class="link-card">
                <h3>Tags</h3>
                <p>Explore more specific topics and themes using our tag system.</p>
                <a href="/tags" class="link-button">View Tags →</a>
            </div>
        </aside>
    </div>

    <style>
        /* Styling for category overview page */
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

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 4rem;
        }

        .category-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-color: #7c3aed;
        }

        .category-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .category-name {
            margin: 0 0 0.5rem 0;
            font-size: 1.25rem;
        }

        .category-name a {
            color: #1f2937;
            text-decoration: none;
        }

        .category-name a:hover {
            color: #7c3aed;
        }

        .category-count {
            color: #6b7280;
            margin: 0 0 1.5rem 0;
            font-size: 0.875rem;
        }

        .category-link {
            display: inline-block;
            background: #7c3aed;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: background 0.2s ease;
        }

        .category-link:hover {
            background: #6d28d9;
        }

        .alphabetical-categories {
            margin-bottom: 3rem;
        }

        .alphabetical-categories h2 {
            margin-bottom: 1.5rem;
            color: #1f2937;
        }

        .category-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 0.5rem;
        }

        .category-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
        }

        .category-list-item:hover {
            background: #faf5ff;
            border-color: #c084fc;
        }

        .category-link-text {
            color: #7c3aed;
            text-decoration: none;
            font-weight: 500;
        }

        .category-link-text:hover {
            text-decoration: underline;
        }

        .post-count {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .no-categories {
            text-align: center;
            padding: 3rem 0;
        }

        .back-link {
            color: #7c3aed;
            text-decoration: none;
        }

        .related-links {
            border-top: 1px solid #e5e7eb;
            padding-top: 2rem;
            margin-top: 3rem;
        }

        .link-card {
            background: #dbeafe;
            border: 1px solid #3b82f6;
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
        }

        .link-card h3 {
            margin: 0 0 0.5rem 0;
            color: #1e40af;
        }

        .link-card p {
            margin: 0 0 1rem 0;
            color: #1e3a8a;
        }

        .link-button {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-weight: 600;
        }

        .link-button:hover {
            background: #2563eb;
        }
    </style>
</body>
</html>