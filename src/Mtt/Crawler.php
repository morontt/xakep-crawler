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
use Symfony\Component\Process\Process;

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

            $this->download($nodeValues);
        }
    }

    /**
     * @param array $urls
     */
    protected function download(array $urls)
    {
        foreach ($urls as $url) {
            $parts = explode('/', $url);
            $filename = preg_replace('/\?.*/', '', $parts[count($parts) - 1]);

            $target = $this->config['downloads_path'] . DIRECTORY_SEPARATOR . $filename;

            if (!file_exists($target)) {
                $process = new Process(sprintf('wget -O %s %s', $target, $url));
                $process->run();

                if (!$process->isSuccessful()) {
                    echo sprintf("%s - error\n", $target);
                    unlink($target);
                } else {
                    echo sprintf("%s - done\n", $target);
                }
            }
        }
    }
}
