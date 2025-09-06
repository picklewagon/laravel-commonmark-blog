# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Common Development Commands

**Testing:**
```bash
composer test                    # Run PHPUnit tests
composer test-coverage           # Run tests with coverage report
vendor/bin/phpunit              # Direct PHPUnit execution

# Docker Testing (for consistent environment)
docker build -t laravel-commonmark-blog .     # Build test environment
docker run --rm laravel-commonmark-blog       # Run all tests in Docker
docker run --rm laravel-commonmark-blog composer test-coverage  # Run with coverage
```

**Blog Building:**
```bash
php artisan blog:build          # Build blog from markdown files
php artisan blog:build /path    # Build from custom source path
```

**Package Development:**
```bash
composer install               # Install dependencies
php artisan vendor:publish --provider="Spekulatius\LaravelCommonmarkBlog\CommonmarkBlogServiceProvider" --tag="blog-config"  # Publish config
php artisan vendor:publish --provider="Spekulatius\LaravelCommonmarkBlog\CommonmarkBlogServiceProvider" --tag="blog-views"   # Publish taxonomy view templates
php artisan vendor:publish --provider="Spekulatius\LaravelCommonmarkBlog\CommonmarkBlogServiceProvider" --tag="blog-all"     # Publish config and views

# CI/CD Testing (GitHub Actions runs automatically)
# Tests across PHP 8.2/8.3 and Laravel 9/10/11/12 matrix
# Includes both native PHP and Docker test environments
```

## Architecture Overview

This is a Laravel package that converts Markdown files into static HTML blog pages for optimal SEO performance. The core architecture:

**Service Provider (`src/CommonmarkBlogServiceProvider.php`):**
- Registers the `blog:build` command
- Publishes configuration to consuming applications
- **Publishes customizable taxonomy view templates**
- Integrates with Laravel's service container
- Registers the Blog helper class for taxonomy operations

**Build Command (`src/Commands/BuildBlog.php`):**
- Main orchestrator that processes markdown files
- Converts `.md` files to HTML using CommonMark
- Supports frontmatter for metadata (YAML) including tags and categories
- Handles embargo files (`*.emb.md`) for timed publishing
- Creates article pages and listing pages
- **Generates taxonomy archive pages** for tags and categories
- Integrates with Laravel SEO for meta tags and structured data
- Outputs static HTML files to `public/` directory for direct serving

**Blog Helper Class (`src/Blog.php`):**
- Provides methods for filtering posts by tags and categories
- Enables programmatic access to cached blog content
- Supports search functionality and pagination
- Available as singleton through service container

**Key Features:**
- **Static Generation**: Creates `index.htm` files in directories for web server compatibility
- **SEO Optimization**: Uses `romanzipp/laravel-seo` for meta tags, OpenGraph, Twitter cards
- **Taxonomy Support**: Full tags and categories with automatic archive page generation
- **Multi-language Support**: hreflang implementation for internationalization
- **Embargo System**: Delayed publishing with `*.emb.md` files and `modified` dates
- **Listing Pages**: Automatic pagination for article collections via `index.md` files
- **Asset Integration**: Laravel Mix asset inclusion for CSS/JS

**Configuration (`config/blog.php`):**
- Source path for markdown files (default: `resources/content`)
- Blade template mappings for articles and lists
- **Taxonomy configuration** for tags and categories (route prefixes, templates)
- CommonMark extensions
- Default frontmatter values
- Cache settings for generated articles

**File Processing Flow:**
1. Mirror source directory structure to `public/`
2. Process embargo files (release if `modified` date passed)
3. Convert articles (`.md` files, excluding embargo files)
4. Convert listing pages (`index.md` files)
5. **Generate taxonomy archive pages** for tags and categories
6. Generate paginated HTML files
7. Cache article metadata if configured

**Template System:**
- Article template: `blog.templates.{folder}.article` 
- List template: `blog.templates.{folder}.list`
- **Archive templates**: Configurable per taxonomy type with fallback to list templates
- Templates receive frontmatter data, converted content, and pagination info
- **Tags and categories automatically available** as `$tags` and `$categories` variables

**Taxonomy Features:**
- Frontmatter arrays: `tags: ["tag1", "tag2"]` and `categories: ["Cat1", "Cat2"]`
- Auto-generated archive pages: `/tags/{tag}/` and `/categories/{category}/`
- Blog helper methods: `Blog::getPostsByTag()`, `Blog::getPostsByCategory()`, etc.
- **SEO enhancement**: Auto-generated meta keywords from tags/categories when not explicitly set
- Configurable route prefixes and custom archive templates
- **Publishable view templates**: Beautiful, responsive default templates for customization
- Can be disabled independently via configuration