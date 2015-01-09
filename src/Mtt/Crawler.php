<?php
/**
 * Created by PhpStorm.
 * User: morontt
 * Date: 09.01.15
 * Time: 23:14
 */

namespace Mtt;


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
        var_dump($this->config);
    }
}
