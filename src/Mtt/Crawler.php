<?php
/**
 * Created by PhpStorm.
 * User: morontt
 * Date: 09.01.15
 * Time: 23:14
 */

namespace Mtt;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;

/**
 * Class Crawler
 * @package Mtt
 */
class Crawler
{
    /**
     * @var array
     */
    protected $config;


    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function run()
    {
        $client = new Client();

        $client->getClient()->setDefaultOption('config/curl/' . CURLOPT_TIMEOUT, 30);
        $client->setHeader('User-Agent', $this->config['user_agent']);

        $crawler = $client->request('GET', $this->config['url']);

        if ($client->getResponse()->getStatus() == 200) {
            $nodeValues = $crawler->filter('a.download-button')->each(function (SymfonyCrawler $node) {
                return $node->attr('href');
            });
        }
    }
}
