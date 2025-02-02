<?php

namespace Hanzo\Bundle\ConsignorBundle;

use Guzzle\Service\Client;
use Psr\Log\LoggerInterface;

class Consignor
{
    /**
     * @var \Guzzle\Service\Client
     */
    private $guzzle;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $options;


    /**
     * @param Client          $guzzle_client
     * @param LoggerInterface $logger
     */
    public function __construct(Client $guzzle_client, LoggerInterface $logger)
    {
        $this->guzzle = $guzzle_client;
        $this->logger = $logger;
    }


    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }


    /**
     * @param $key
     * @return mixed
     */
    public function getOption($key)
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }

        return null;
    }


    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }


    /**
     * @return Client
     */
    public function getGuzzleClient()
    {
        return $this->guzzle;
    }
}
