<?php

namespace App\Proxy\Adapter\Guzzle;

use GuzzleHttp\Client;
use App\Proxy\Adapter\AdapterInterface;
use Psr\Http\Message\RequestInterface;

class GuzzleAdapter implements AdapterInterface
{
    /**
     * The Guzzle client instance.
     *
     * @var Client
     */
    protected $client;

    /**
     * Construct a Guzzle based HTTP adapter.
     *
     * @param Client $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client;
    }

    /**
     * @inheritdoc
     */
    public function send(RequestInterface $request)
    {
        return $this->client->send($request);
    }
}
