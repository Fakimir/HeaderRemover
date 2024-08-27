<?php
namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;

class CssParser
{
    public static function getHiddenElements(string $cssContent): array
    {
        $hiddenElements = [];

        $lines = explode("}", $cssContent);
        foreach ($lines as $line) {
            $line = explode("{", $line);
            if (count($line) == 2 && strpos($line[1], "display:none") !== false) {
                array_push($hiddenElements, $line[0]);
            }
        }

        return $hiddenElements;
    }

    public static function cssToXPath(string $cssSelector): string
    {
        $selectors = preg_split('/\s*(,|\s+)\s*/', trim($cssSelector));
        $xpathSelectors = [];

        foreach ($selectors as $selector) {
            $xpath = self::convertSelectorToXPath($selector);
            $xpathSelectors[] = $xpath;
        }

        return implode('|', $xpathSelectors);
    }

    public static function convertSelectorToXPath(string $selector): string
    {

        $selector = preg_replace('/\.(\w[\w\-]*)/', '[@class="$1"]', $selector);
        
        $selector = preg_replace('/\#(\w[\w\-]*)/', '[@id="$1"]', $selector);

        $selector = preg_replace('/\s+/', '/', $selector);

        return '//' . trim($selector);
    }
    public static function getCssUrls(Crawler $crawler, string $url): array
    {
        $cssUrls = [];
        $crawler->filter('link[rel="preload"][as="style"], link[rel="stylesheet"][href*=".css"]')->each(function (Crawler $cssLink) use (&$cssUrls, $url) {
            $href = $cssLink->attr('href');

            $urlWithoutTrailingSlash = rtrim($url, '/');
            $hrefWithoutLeadingSlash = ltrim($href, '/');
    
            if (!str_contains($href, "http")) {
                $cssUrls[] = $urlWithoutTrailingSlash . '/' . $hrefWithoutLeadingSlash; 
            } else {
                $cssUrls[] = $href; 
            }
        });
        return $cssUrls;
    }

    public static function fetchCssFile(string $cssUrl): ?string
    {
        $ch = curl_init($cssUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $cssContent = curl_exec($ch);//полная ссылка + host
        curl_close($ch);

        return $cssContent ?: null;
    }
}