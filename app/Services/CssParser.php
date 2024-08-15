<?php
namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;

class CssParser
{
    public static function createXpathSelectorByStyle(array $rule, array $styles): string
    {
        foreach ($styles as $style) {
            if (strpos($rule['style'], $style) !== false) {
                $selector = $rule['selector'];
                $selector = preg_replace('/\./', '*', $selector); 
                return "//*[contains(@style, '$style')]"; 
            }
        }
        return '';
    }

    public static function parseCss(string $cssContent): array
    {
        $rules = [];
        $lines = explode("n", $cssContent);
        $currentRule = null;
    
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '{') !== false) {
                if ($currentRule) {
                    $rules[] = $currentRule;
                    $currentRule = null;
                }
                continue;
            }
    
            if ($currentRule) {
                $currentRule['style'] .= ' ' . $line;
            } else {
                $parts = explode(' {', $line);
                if (count($parts) === 2) {
                    $currentRule = [
                        'selector' => trim($parts[0]),
                        'style' => trim($parts[1], ' }'),
                    ];
                }
            }
        }
    
        return $rules;
    }

    public static function getCssUrls(Crawler $crawler): array
    {
        $cssUrls = [];
        $crawler->filter('link[rel="preload"][as="style"], link[rel="stylesheet"][href*=".css"]')->each(function (Crawler $cssLink) use (&$cssUrls) {
            $cssUrls[] = $cssLink->attr('href'); 
        });
        return $cssUrls;
    }

    public static function fetchCssFile(string $cssUrl): ?string
    {
        $ch = curl_init($cssUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $cssContent = curl_exec($ch);
        curl_close($ch);

        return $cssContent ?: null;
    }
}