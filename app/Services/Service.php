<?php
namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;
use DOMDocument;
use DOMXPath;

class Service 
{
    private static $selectors = [
        '//nav | //header', '//*[contains(@style, "display: none")]', '//*[contains(@style, "opacity: 0")]', '//*[contains(@role, "banner")]', '//*[contains(@role, "navigation")]',
        '//*[starts-with(@class, "header")]', '//*[starts-with(@class, "banner")]', '//*[starts-with(@class, "menu")]', '//*[contains(@class, "banner")]',
        '//*[contains(@class, "menu")]', '//*[contains(@class, "navigation")]',
    ];

    public static function serve(Crawler $crawler, int $limit = null): string
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($crawler->html(), LIBXML_NOBLANKS | LIBXML_NOERROR | LIBXML_NOWARNING);
        
        $xpath = new DOMXPath($dom);

        foreach (self::$selectors as $selector) {
            self::deleteNode($selector, $xpath);
        }

        $cleanedText = self::cleanText($dom->textContent);

        if ($limit) {
            $cleanedText = substr($cleanedText, 0, $limit);
        }

        return $cleanedText;
    }

    private static function deleteNode(string $selector, DOMXPath $xpath): void
    {
        $nodesToRemove = $xpath->query($selector);
        foreach ($nodesToRemove as $node) {
            $node->parentNode->removeChild($node);
        }
    }

    private static function cleanText(string $text): string
    {
        $cleanedText = trim($text);

        $cleanedText = preg_replace('/\s+/', ' ', $cleanedText);
        $cleanedText = preg_replace('/\{{2}(.*?)\}{2}/', '', $cleanedText);

        return $cleanedText;
    }
}