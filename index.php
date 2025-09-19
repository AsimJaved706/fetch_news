<?php
require_once 'app/Config.php';
require_once 'app/Database.php';
require_once 'app/ArticleRepository.php';
require_once 'app/helpers.php';

// Input handling
$filters = [
    'category' => $_GET['category'] ?? '',
    'search' => trim($_GET['q'] ?? '')
];

$page = max(1, (int)($_GET['page'] ?? 1));


$repository = new ArticleRepository();
$articles = $repository->findWithFilters($filters, $page);
$totalCount = $repository->countWithFilters($filters);
$totalPages = ceil($totalCount / Config::ARTICLES_PER_PAGE);
$categoryCounts = $repository->getCategoryCounts();


$hasFilters = !empty($filters['category']) || !empty($filters['search']);
$currentCategory = $filters['category'];
$currentSearch = $filters['search'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $hasFilters ? 'Search Results - ' : '' ?>News Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
    :root {
        --primary: #1a73e8;
        --text-primary: #202124;
        --text-secondary: #5f6368;
        --border: #dadce0;
        --surface: #ffffff;
        --background: #fafbfc;
        --shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        --shadow-hover: 0 3px 6px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23);
    }
    
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    
    body {
        font-family: 'Inter', -apple-system, sans-serif;
        background: var(--background);
        color: var(--text-primary);
        line-height: 1.5;
    }
    
    .header {
        background: var(--surface);
        border-bottom: 1px solid var(--border);
        position: sticky;
        top: 0;
        z-index: 100;
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 16px;
    }
    
    .header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 64px;
        gap: 24px;
    }
    
    .logo {
        font-size: 24px;
        font-weight: 600;
        color: var(--primary);
        text-decoration: none;
    }
    
    .search-section {
        display: flex;
        gap: 16px;
        flex: 1;
        max-width: 600px;
    }
    
    .search-input {
        flex: 1;
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
    }
    
    .search-input:focus {
        border-color: var(--primary);
    }
    
    .category-select {
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 14px;
        background: var(--surface);
        outline: none;
        cursor: pointer;
        min-width: 140px;
    }
    
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s;
    }
    
    .btn-primary {
        background: var(--primary);
        color: white;
    }
    
    .btn-primary:hover {
        background: #1557b0;
    }
    
    .btn-secondary {
        background: #f8f9fa;
        color: var(--text-secondary);
        border: 1px solid var(--border);
    }
    
    .main {
        padding: 24px 0;
    }
    
    .results-info {
        margin-bottom: 24px;
        color: var(--text-secondary);
        font-size: 14px;
    }
    
    .articles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 24px;
        margin-bottom: 48px;
    }
    
    .article {
        background: var(--surface);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: all 0.2s;
        height: fit-content;
    }
    
    .article:hover {
        box-shadow: var(--shadow-hover);
        transform: translateY(-2px);
    }
    
    .article-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: #f0f0f0;
    }
    
    .article-content {
        padding: 20px;
    }
    
    .article-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        font-size: 12px;
        color: var(--text-secondary);
    }
    
    .category-tag {
        background: #e8f0fe;
        color: var(--primary);
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .article-title {
        font-size: 16px;
        font-weight: 600;
        line-height: 1.3;
        margin-bottom: 8px;
    }
    
    .article-title a {
        color: var(--text-primary);
        text-decoration: none;
    }
    
    .article-title a:hover {
        color: var(--primary);
    }
    
    .article-description {
        color: var(--text-secondary);
        font-size: 14px;
        margin-bottom: 12px;
        line-height: 1.4;
    }
    
    .article-source {
        font-size: 12px;
        color: var(--text-secondary);
        font-weight: 500;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        align-items: center;
    }
    
    .pagination a,
    .pagination span {
        padding: 8px 12px;
        text-decoration: none;
        border-radius: 6px;
        font-size: 14px;
        color: var(--text-primary);
        border: 1px solid var(--border);
    }
    
    .pagination .current {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    
    .pagination a:hover {
        background: #f8f9fa;
    }
    
    .no-results {
        text-align: center;
        padding: 64px 20px;
        color: var(--text-secondary);
    }
    
    .no-results h3 {
        margin-bottom: 8px;
        color: var(--text-primary);
    }
    
    .image-placeholder {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 32px;
    }
    
    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
            height: auto;
            padding: 16px 0;
        }
        
        .search-section {
            width: 100%;
            max-width: none;
            flex-direction: column;
        }
        
        .articles-grid {
            grid-template-columns: 1fr;
        }
        
        .container {
            padding: 0 12px;
        }
    }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo">NewsHub</a>
                
                <form class="search-section" method="GET">
                    <input type="text" 
                           name="q" 
                           class="search-input"
                           placeholder="Search articles..."
                           value="<?= e($currentSearch) ?>">
                    
                    <select name="category" class="category-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach (Config::CATEGORIES as $key => $name): ?>
                            <?php $count = $categoryCounts[$key] ?? 0; ?>
                            <option value="<?= $key ?>" <?= $currentCategory === $key ? 'selected' : '' ?>>
                                <?= $name ?> (<?= $count ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
                
                <?php if ($hasFilters): ?>
                    <a href="/" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="results-info">
                <?php if ($currentSearch): ?>
                    <?= number_format($totalCount) ?> results for "<?= e($currentSearch) ?>"
                    <?php if ($currentCategory): ?>
                        in <?= Config::CATEGORIES[$currentCategory] ?>
                    <?php endif; ?>
                <?php elseif ($currentCategory): ?>
                    <?= number_format($totalCount) ?> articles in <?= Config::CATEGORIES[$currentCategory] ?>
                <?php else: ?>
                    Latest <?= number_format($totalCount) ?> articles
                <?php endif; ?>
            </div>

            <?php if (empty($articles)): ?>
                <div class="no-results">
                    <h3>No articles found</h3>
                    <p>Try different search terms or browse all categories</p>
                </div>
            <?php else: ?>
                <div class="articles-grid">
                    <?php foreach ($articles as $article): ?>
                        <article class="article">
                            <?php if ($article['image_url']): ?>
                                <img src="<?= e($article['image_url']) ?>" 
                                     alt="" 
                                     class="article-image"
                                     onerror="this.outerHTML='<div class=\'article-image image-placeholder\'>📰</div>'">
                            <?php else: ?>
                                <div class="article-image image-placeholder">📰</div>
                            <?php endif; ?>
                            
                            <div class="article-content">
                                <div class="article-meta">
                                    <span class="category-tag">
                                        <?= Config::CATEGORIES[$article['category']] ?? ucfirst($article['category']) ?>
                                    </span>
                                    <span><?= timeElapsed($article['published_at']) ?></span>
                                </div>
                                
                                <h2 class="article-title">
                                    <a href="<?= e($article['url']) ?>" 
                                       target="_blank" 
                                       rel="noopener">
                                        <?= e($article['title']) ?>
                                    </a>
                                </h2>
                                
                                <?php if ($article['description']): ?>
                                    <p class="article-description">
                                        <?= e(truncateText($article['description'])) ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="article-source">
                                    <?= e($article['source']) ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="<?= buildUrl(['page' => $page - 1]) ?>">← Previous</a>
                        <?php endif; ?>
                        
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        
                        for ($i = $start; $i <= $end; $i++): 
                        ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= buildUrl(['page' => $i]) ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="<?= buildUrl(['page' => $page + 1]) ?>">Next →</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>