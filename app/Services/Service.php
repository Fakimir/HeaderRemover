<?php
namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;
use DOMDocument;
use DOMXPath;

class Service
{
    public static function serve(Crawler $crawler, int $limit = null): string
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($crawler->html(), LIBXML_NOBLANKS | LIBXML_NOERROR | LIBXML_NOWARNING);

        $xpath = new DOMXPath($dom);

        // стиль
        self::deleteNodeByStyle($xpath, 'display: none');
        self::deleteNodeByStyle($xpath, 'visibility: hidden');
        self::deleteNodeByStyle($xpath, 'opacity: 0');
        self::deleteNodeByStyle($xpath, 'font-size: 0'); 

        // атрибуты
        self::deleteNodesByAttribute($xpath, 'role', 'banner');
        self::deleteNodesByAttribute($xpath, 'role', 'navigation');
        self::deleteNodesByAttribute($xpath, 'role', 'tablist');
        self::deleteNodesByStartsWithAttribute($xpath, 'class', 'header');
        self::deleteNodesByStartsWithAttribute($xpath, 'class', 'banner');
        self::deleteNodesByStartsWithAttribute($xpath, 'class', 'menu');
        self::deleteNodesByStartsWithAttribute($xpath, 'class', 'nav');
        self::deleteNodesByAttribute($xpath, 'class', 'banner');
        self::deleteNodesByAttribute($xpath, 'class', 'menu');
        self::deleteNodesByAttribute($xpath, 'class', 'navigation');
        self::deleteNodesByAttribute($xpath, 'class', 'nav');
        self::deleteNodesByAttribute($xpath, 'class', 'scroll');

        //тэги
        self::deleteNodesByTagName($xpath, 'nav');
        self::deleteNodesByTagName($xpath, 'header');

        $cleanedText = self::cleanText($dom->textContent);

        if ($limit) {
            $cleanedText = substr($cleanedText, 0, $limit);
        }

        return $cleanedText;
    }

    private static function deleteNodeByStyle(DOMXPath $xpath, string $style): void
    {
        $selector = "//*[contains(@style, '$style')]"; 
        self::deleteNode($selector, $xpath);
    }

    private static function deleteNodesByAttribute(DOMXPath $xpath, string $attribute, string $value): void
    {
        $selector = "//*[contains(@$attribute, '$value')]";
        self::deleteNode($selector, $xpath);
    }

    private static function deleteNodesByStartsWithAttribute(DOMXPath $xpath, string $attribute, string $value): void
    {
        $selector = "//*[starts-with(@$attribute, '$value')]";
        self::deleteNode($selector, $xpath);
    }

    private static function deleteNodesByTagName(DOMXPath $xpath, string $tagName): void
    {
        $selector = "//$tagName";
        self::deleteNode($selector, $xpath);
    }

    private static function deleteNode(string $selector, DOMXPath $xpath): void
    {
        $nodesToRemove = $xpath->query($selector);
        foreach ($nodesToRemove as $node) {
            $node->parentNode->removeChild($node);
        }
    }

    private static function cleanText(string $text): string //пока что нет решения
    {
        // $cleanedText = trim(preg_replace('/s+/', ' ', $text));

        // $cleanedText = preg_replace('/<!--(.*?)-->/s', '', $cleanedText);

        // return $cleanedText;
        return $text;
    }
}