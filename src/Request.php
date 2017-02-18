<?php


//
// Oussama Elgoumri
// ktsnepyg9igfz1@gmail.com
//
// Thu Nov 24 12:13:45 WET 2016
//


namespace OussamaElgoumri;

use Symfony\Component\DomCrawler\Crawler;

class Request
{
    public static function __callStatic($m, $args)
    {
        return call_user_func_array([new self, "_{$m}"], $args);
    }

    /**
     * Send get request.
     *
     * @param  string   $link
     * @return Crawler
     */
    private function _get($link, $options = [], $get_crawler = true)
    {
        $html = Curl__get($link, $this->getOptions($options));

        if ($get_crawler) {
            return new Crawler($html);
        }

        return $html;
    }

    /**
     * Send POST request.
     *
     * @param string    $link
     * @param array     $fields
     *
     * @return Crawler
     */
    private function _post($link, $fields = [], $options = [], $get_crawler = true)
    {
        $html = Curl__post($link, $fields, $this->getOptions($options));

        if ($get_crawler) {
            return new Crawler($html);
        }

        return $html;
    }

    /**
     * Merge user provided options with the default options.
     *
     * @param  array     $options
     * @return array
     */
    private function getOptions($options)
    {
        return $options + [
            CURLOPT_COOKIEJAR  => getCookieFile(),
            CURLOPT_COOKIEFILE => getCookieFile(),
        ];
    }
}
