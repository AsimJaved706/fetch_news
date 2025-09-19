<?php
class NewsApiClient 
{
    private $apiKey;
    private $baseUrl;
    private $ch;
    
    public function __construct() 
    {
        $this->apiKey = Config::NEWS_API_KEY;
        $this->baseUrl = Config::NEWS_API_URL;
        $this->initCurl();
    }
    
    private function initCurl() 
    {
        $this->ch = curl_init();
        curl_setopt_array($this->ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'NewsAggregator/1.0',
            CURLOPT_HTTPHEADER => ['Accept: application/json']
        ]);
    }
    
    public function getHeadlines($category = null, $limit = 20) 
    {
        $params = [
            'apiKey' => $this->apiKey,
            'country' => 'us',
            'pageSize' => min($limit, 100)
        ];
        
        if ($category && isset(Config::CATEGORIES[$category])) {
            $params['category'] = $category;
        }
        
        return $this->makeRequest('top-headlines', $params);
    }
    
    private function makeRequest($endpoint, $params) 
    {
        $url = $this->baseUrl . $endpoint . '?' . http_build_query($params);
        curl_setopt($this->ch, CURLOPT_URL, $url);
        
        $response = curl_exec($this->ch);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        
        if ($response === false) {
            throw new Exception('Network error: ' . curl_error($this->ch));
        }
        
        if ($httpCode !== 200) {
            throw new Exception("API returned HTTP $httpCode");
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response');
        }
        
        return $data;
    }
    
    public function __destruct() 
    {
        if ($this->ch) {
            curl_close($this->ch);
        }
    }
}