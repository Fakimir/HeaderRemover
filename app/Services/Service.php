<?php
namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;
use DOMDocument;
use DOMXPath;
use App\Services\CssParser;

class Service
{
    /*
        url param required to fetch css styles due to href type be like /dir/dir/file.css
    */
    public static function serve(Crawler $crawler, string $url, int $limit = null): string
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($crawler->html(), LIBXML_NOBLANKS | LIBXML_NOERROR | LIBXML_NOWARNING);

        $xpath = new DOMXPath($dom);

        self::deleteNodeByStyle($xpath, 'display: none');
        self::deleteNodeByStyle($xpath, 'visibility: hidden');
        self::deleteNodeByStyle($xpath, 'opacity: 0');
        self::deleteNodeByStyle($xpath, 'font-size: 0'); 

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

        self::deleteNodesByTagName($xpath, 'nav');
        self::deleteNodesByTagName($xpath, 'header');

        $cssUrls = CssParser::getCssUrls($crawler, $url);
        self::deleteNodesByCssFiles($cssUrls, $xpath);

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

    public static function deleteNodesByCssFiles(array $cssUrls, DOMXPath $xpath): void
    {
        foreach ($cssUrls as $cssUrl) {
            $cssContent = CssParser::fetchCssFile($cssUrl);
            if ($cssContent) {
                $hiddenElements = CssParser::getHiddenElements($cssContent);
                foreach ($hiddenElements as $el) {
                    $selector = CssParser::convertSelectorToXPath($el);
                    if (!empty($selector)) {
                        self::deleteNode($selector, $xpath);
                    }
                }
            }
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