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

# CI/CD Testing (GitHub Actions runs automatically)
# Tests across PHP 8.2/8.3 and Laravel 9/10/11/12 matrix
# Includes both native PHP and Docker test environments
```

## Architecture Overview

This is a Laravel package that converts Markdown files into static HTML blog pages for optimal SEO performance. The core architecture:

**Service Provider (`src/CommonmarkBlogServiceProvider.php`):**
- Registers the `blog:build` command
- Publishes configuration to consuming applications
- Integrates with Laravel's service container

**Build Command (`src/Commands/BuildBlog.php`):**
- Main orchestrator that processes markdown files
- Converts `.md` files to HTML using CommonMark
- Supports frontmatter for metadata (YAML)
- Handles embargo files (`*.emb.md`) for timed publishing
- Creates article pages and listing pages
- Integrates with Laravel SEO for meta tags and structured data
- Outputs static HTML files to `public/` directory for direct serving

**Key Features:**
- **Static Generation**: Creates `index.htm` files in directories for web server compatibility
- **SEO Optimization**: Uses `romanzipp/laravel-seo` for meta tags, OpenGraph, Twitter cards
- **Multi-language Support**: hreflang implementation for internationalization
- **Embargo System**: Delayed publishing with `*.emb.md` files and `modified` dates
- **Listing Pages**: Automatic pagination for article collections via `index.md` files
- **Asset Integration**: Laravel Mix asset inclusion for CSS/JS

**Configuration (`config/blog.php`):**
- Source path for markdown files (default: `resources/content`)
- Blade template mappings for articles and lists
- CommonMark extensions
- Default frontmatter values
- Cache settings for generated articles

**File Processing Flow:**
1. Mirror source directory structure to `public/`
2. Process embargo files (release if `modified` date passed)
3. Convert articles (`.md` files, excluding embargo files)
4. Convert listing pages (`index.md` files)
5. Generate paginated HTML files
6. Cache article metadata if configured

**Template System:**
- Article template: `blog.templates.{folder}.article` 
- List template: `blog.templates.{folder}.list`
- Templates receive frontmatter data, converted content, and pagination info