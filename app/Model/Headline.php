<?php
App::uses('AppModel','Model');

class Headline extends AppModel {
    public $useTable = false;

    //private $newsSettings = require APP . 'Config' . DS . 'config_news.conf.php';
    //private $queryParams = this->$newsSettings['NewsSettings'];


    private function getUrlContent($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => [
                'User-Agent: MISP-NewsWidget/1.0'  // 👈 Required by NewsAPI
            ]
        ]);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

    // returns associative array of articles
    public function fetchHeadlines()
    {
        // Headline config
        //require APP . 'Config' . DS . 'headline.php';
        $query = urlencode(!empty(Configure::read('MISP.news_headline_query')) ? Configure::read('MISP.news_headline_query') : '+cyberscurity');
        $language = !empty(Configure::read('MISP.news_headline_language')) ? Configure::read('MISP.news_headline_language') : 'en';
        $sortBy = !empty(Configure::read('MISP.news_headline_sort_by')) ? Configure::read('MISP.news_headline_sort_by') : 'publishedAt'; 
        $pageSize = !empty(Configure::read('MISP.news_headline_limit')) ? Configure::read('MISP.news_headline_limit') : 10;
        $includeDomains = !empty(Configure::read('MISP.news_headline_include_domains')) ? Configure::read('MISP.news_headline_include_domains') : ''; 
        $excludeDomains = !empty(Configure::read('MISP.news_headline_exclude_domains')) ? Configure::read('MISP.news_headline_exclude_domains') : ''; 
        // API Key
        $apikey = !empty(Configure::read('MISP.news_headline_newsapi_key')) ? Configure::read('MISP.news_headline_newsapi_key') : '7a8d5d4fccb94debb98be099d377cbdb';
        // API Query
        $newsRequestUrl = "https://newsapi.org/v2/everything?q={$query}&language={$language}&sortBy={$sortBy}&pageSize={$pageSize}" .
            (!empty($includeDomains) ? '&domains=' . $includeDomains : '') .
            (!empty($excludeDomains) ? '&excludeDomains=' . $excludeDomains : '') .
            '&searchIn=title,description&apiKey=' . $apikey;
        // fetch json    
        $jsonResponse = $this->getUrlContent($newsRequestUrl);
        // if fetch fails
        if ($jsonResponse === false) {
            $error = error_get_last();
            echo "Error fetching API: " . $error['message'];
            return;
        }
        // otherwise encode data
        $data = json_decode($jsonResponse, true);
        $articles = [];
        if (!empty($data['articles'])) {
            foreach ($data['articles'] as $article) {
                $isoDate = $article['publishedAt'];
                $date = new DateTime($isoDate, new DateTimeZone('Z'));
                $date->setTimezone(new DateTimeZone('UTC'));
                $formattedDate = $date->format('j F Y, H:i e');
                $articles[] = [
                    'title' => $article['title'],
                    'url' => $article['url'],
                    'imageurl' => $article['urlToImage'],
                    'source' => $article['source']['name'],
                    'datePublished' => $formattedDate
                ];
            }
        }
        return $articles;
    }
}