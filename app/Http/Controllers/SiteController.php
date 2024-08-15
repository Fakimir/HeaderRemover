<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use App\Services\Service;

use GuzzleHttp\Client as HttpClient;

class SiteController extends Controller
{
    public function analyze(Request $request)
    {
        $url = $request->input('url');
    
        $httpClient = new HttpClient();

        $response = $httpClient->get($url);
        $pageContent = (string)$response->getBody();

        $crawler = new Crawler($pageContent);

        $pageText = $crawler->filter('body')->text();

        return Service::serve($crawler, $url);
    }
}
