<?php
class Config 
{
    const DB_HOST = 'localhost';
    const DB_NAME = 'fetch_news';
    const DB_USER = 'root';
    const DB_PASS = '';
    
    const NEWS_API_KEY = '76b15efb2b4f411386c95499f0e1f64b';
    const NEWS_API_URL = 'https://newsapi.org/v2/';
    
    const CACHE_TTL = 1800; // 30 minutes
    const ARTICLES_PER_PAGE = 15;
    const MAX_DESCRIPTION_LENGTH = 160;
    
    const CATEGORIES = [
        'business' => 'Business',
        'entertainment' => 'Entertainment', 
        'health' => 'Health',
        'science' => 'Science',
        'sports' => 'Sports',
        'technology' => 'Technology'
    ];
}