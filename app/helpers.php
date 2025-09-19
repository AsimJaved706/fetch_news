<?php
function timeElapsed($datetime) 
{
    $elapsed = time() - strtotime($datetime);
    
    if ($elapsed < 3600) {
        $minutes = floor($elapsed / 60);
        return $minutes > 0 ? "{$minutes}m ago" : "now";
    }
    
    if ($elapsed < 86400) {
        $hours = floor($elapsed / 3600);
        return "{$hours}h ago";
    }
    
    if ($elapsed < 604800) {
        $days = floor($elapsed / 86400);
        return "{$days}d ago";
    }
    
    return date('M j', strtotime($datetime));
}

function truncateText($text, $length = null) 
{
    $length = $length ?: Config::MAX_DESCRIPTION_LENGTH;
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

function buildUrl($params = []) 
{
    $current = array_merge($_GET, $params);
    $current = array_filter($current); // Remove empty values
    return '?' . http_build_query($current);
}

function e($string) 
{
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}