<?php
require_once 'app/Config.php';
require_once 'app/Database.php';
require_once 'app/NewsApiClient.php';
require_once 'app/ArticleRepository.php';

try {
    $client = new NewsApiClient();
    $repository = new ArticleRepository();
    
    $totalSaved = 0;
    
    foreach (Config::CATEGORIES as $category => $name) {
        echo "Processing {$name}...\n";
        
        $response = $client->getHeadlines($category, 20);
        
        if ($response['status'] === 'ok' && !empty($response['articles'])) {
            // Add category info to articles
            $articles = array_map(function($article) use ($category) {
                $article['category'] = $category;
                return $article;
            }, $response['articles']);
            
            $saved = $repository->save($articles);
            $totalSaved += $saved;
            
            echo "Saved: {$saved}\n";
        }
        
        usleep(500000); // 0.5 second delay
    }
    
    echo "\nTotal articles saved: {$totalSaved}\n";
    
} catch (Exception $e) {
    error_log("Fetch error: " . $e->getMessage());
    echo "Error occurred. Check logs.\n";
}
