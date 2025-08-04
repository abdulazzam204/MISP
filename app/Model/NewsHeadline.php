<?php
App::uses('AppModel','Model');

class NewsHeadline extends AppModel {
    public $useTable = false;

    /* query settings, will be replaced with config file
    private $query = urlencode('cybersecurity OR threats OR malware');
    private $language = 'en';
    private $sortBy = 'relevancy'; // relevancy / popularity / publishedAt
    private $pageSize = 10;
    private $includeDomains = ''; // comma separated
    private $excludeDomains = 'etfdailynews.com'; // comma separated
    */
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
        $query = urlencode('cybersecurity OR threats OR malware');
        $language = 'en';
        $sortBy = 'relevancy'; // relevancy / popularity / publishedAt
        $pageSize = 10;
        $includeDomains = ''; // comma separated
        $excludeDomains = 'etfdailynews.com'; // comma separated
        // API Key
        $apiConfig = require APP . 'Config' . DS . 'config_api.conf.php';
        $apikey = $apiConfig['NewsApiKey'];
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