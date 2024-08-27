<?php
namespace App\Services;

class XPathConverter {
    public static function cssToXPath(string $selector): string
    {
        $parts = explode(' ', $selector);
        $xpath = '';
    
        foreach ($parts as $part) {
            if (strpos($part, '.') !== false) {
                $xpath .= static::convertClassSelector($part);
            } elseif (strpos($part, '#') !== false) {
                $xpath .= static::convertIdSelector($part);
            } elseif (strpos($part, '[') !== false) {
                $xpath .= static::convertAttributeSelector($part);
            } else {
                $xpath .= static::convertTagSelector($part) . '/';
            }
        }
    
        return '//' . trim($xpath, '/');
    }
    
    protected static function convertClassSelector(string $selector): string
    {
        $classes = explode('.', $selector);
        $xpath = '';
    
        foreach ($classes as $class) {
            if ($class) {
                $xpath .= "[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]";
            }
        }
    
        return $xpath;
    }
    
    protected static function convertIdSelector(string $selector): string
    {
        return "[@id='" . substr($selector, 1) . "']";
    }
    
    protected static function convertAttributeSelector(string $selector): string
    {
        $attribute = substr($selector, 1, -1);
        return "[@$attribute]";
    }
    
    protected static function convertTagSelector(string $selector): string
    {
        return $selector;
    }
}