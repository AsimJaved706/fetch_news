<?php
class ArticleRepository 
{
    private $db;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
    }
    
    public function save(array $articles) 
    {
        $sql = "INSERT IGNORE INTO articles 
                (title, description, url, image_url, source, category, published_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $saved = 0;
        
        foreach ($articles as $article) {
            if ($this->isValidArticle($article)) {
                $data = $this->sanitizeArticle($article);
                if ($stmt->execute($data)) {
                    $saved++;
                }
            }
        }
        
        return $saved;
    }
    
    public function findWithFilters($filters = [], $page = 1, $limit = null) 
    {
        $limit = $limit ?: Config::ARTICLES_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM articles WHERE 1=1";
        $params = [];
        
        if (!empty($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (title LIKE ? OR description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY published_at DESC, id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function countWithFilters($filters = []) 
    {
        $sql = "SELECT COUNT(*) as total FROM articles WHERE 1=1";
        $params = [];
        
        if (!empty($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (title LIKE ? OR description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch()['total'];
    }
    
    public function getCategoryCounts() 
    {
        $sql = "SELECT category, COUNT(*) as count 
                FROM articles 
                WHERE category IS NOT NULL 
                GROUP BY category 
                ORDER BY count DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch()) {
            $results[$row['category']] = $row['count'];
        }
        
        return $results;
    }
    
    private function isValidArticle($article) 
    {
        return !empty($article['title']) && 
               !empty($article['url']) && 
               filter_var($article['url'], FILTER_VALIDATE_URL);
    }
    
    private function sanitizeArticle($article) 
    {
        $publishedAt = !empty($article['publishedAt']) ? 
                      date('Y-m-d H:i:s', strtotime($article['publishedAt'])) : 
                      date('Y-m-d H:i:s');
        
        return [
            trim(strip_tags($article['title'])),
            trim(strip_tags($article['description'] ?? '')),
            filter_var($article['url'], FILTER_SANITIZE_URL),
            !empty($article['urlToImage']) ? filter_var($article['urlToImage'], FILTER_SANITIZE_URL) : null,
            trim(strip_tags($article['source']['name'] ?? 'Unknown')),
            $article['category'] ?? 'general',
            $publishedAt
        ];
    }
}