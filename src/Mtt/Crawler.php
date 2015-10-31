<?php
/**
 * Created by PhpStorm.
 * User: morontt
 * Date: 09.01.15
 * Time: 23:14
 */

namespace Mtt;

use Goutte\Client;
use GuzzleHttp\Exception\TransferException;
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

        if (!file_exists($config['downloads_path']) || is_file($config['downloads_path'])) {
            echo "download directory not exists\n";
            exit(1);
        }

        if (!is_writable($config['downloads_path'])) {
            echo "download directory not writable\n";
            exit(1);
        }
    }

    /**
     * @param boolean $allPages
     */
    public function run($allPages)
    {
        $client = new Client();

        $client->getClient()->setDefaultOption('config/curl/' . CURLOPT_TIMEOUT, 30);
        $client->setHeader('User-Agent', $this->config['user_agent']);

        try {
            $crawler = $client->request('GET', $this->config['url']);
        } catch (TransferException $e) {
            echo $e->getMessage() . PHP_EOL;
            exit(1);
        }

        if ($client->getResponse()->getStatus() == 200) {
            $this->getUrlsAndDownload($crawler);

            if ($allPages) {
                $link = $this->getNextLink($crawler);

                while ($link) {
                    $crawler = $client->click($link);
                    $this->getUrlsAndDownload($crawler);

                    $link = $this->getNextLink($crawler);
                };
            }
        } else {
            echo "site not available\n";
        }
    }

    /**
     * @param SymfonyCrawler $crawler
     * @return \Symfony\Component\DomCrawler\Link|null
     */
    protected function getNextLink(SymfonyCrawler $crawler)
    {
        $linkNode = $crawler->selectLink('След');

        return count($linkNode) ? $linkNode->link() : null;
    }

    /**
     * @param SymfonyCrawler $crawler
     */
    protected function getUrlsAndDownload(SymfonyCrawler $crawler)
    {
        $nodeValues = $crawler->filter('a.download-button')->each(function (SymfonyCrawler $node) {
            return $this->config['base_url'] . $node->attr('href');
        });

        $this->download($nodeValues);
    }

    /**
     * @param array $urls
     */
    protected function download(array $urls)
    {
        foreach ($urls as $url) {
            $parts = explode('/', $url);
            $filename = preg_replace('/\?.*/', '', $parts[count($parts) - 1]);

            $process = $this->createProcess(sprintf('wget --spider %s  2>&1 | awk \'/Location/ {print $2}\'', $url));

            if (!$process->isSuccessful()) {
                continue;
            } 

            $fileLocation = $process->getOutput();
            $fileLocationParts = explode("\xA", $fileLocation);

            $process = $this->createProcess(sprintf('wget --spider %s  2>&1 | awk \'/Length/ {print $4}\'', $fileLocationParts[0]));

            if (!$process->isSuccessful()) {
                continue;
            } 

            $fileType = $process->getOutput();
            $fileTypeParts = explode("\xA", $fileType);

            $fileExt = $this->getFileExtension($fileTypeParts[0]);

            if ($fileExt === false) {
                continue;
            }

            $target = $this->config['downloads_path'] . DIRECTORY_SEPARATOR . $filename . $fileExt;
            if (!file_exists($target)) {
                $process = $this->createProcess(sprintf('wget -O %s %s', $target, $url));

                if (!$process->isSuccessful()) {
                    echo sprintf("%s - error\n", $target);
                    unlink($target);
                } else {
                    echo sprintf("%s - done\n", $target);
                }

                $this->randomSleep();
            }
        }
    }

    protected function createProcess($process)
    {
        $process = new Process($process);
        $process->setTimeout($this->config['fetch_time']);
        $process->run();

        return $process;
    }

    protected function getFileExtension($fileType)
    {
        switch ($fileType) {
            case '[application/pdf]':
                $fileExt = '.pdf';
                break;

            default:
                $fileExt = false;
                break;
        }

        return $fileExt;
    }

    protected function randomSleep()
    {
        $p = rand(2, 6);
        sleep($p);
    }
}
