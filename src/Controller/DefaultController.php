<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Proxy\Adapter\Guzzle\GuzzleAdapter;
use App\Proxy\Filter\RemoveEncodingFilter;
use App\Proxy\Proxy;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zend\Diactoros\ServerRequestFactory;

class DefaultController
{
    /**
     * @Route("/get")
     */
    public function number(SessionInterface $session, \Predis\Client $redis, Request $request)
    {

        putenv('HTTP_PROXY=host.docker.internal:8888');
        putenv('HTTPS_PROXY=host.docker.internal:8888');

        // Create a PSR7 request based on the current browser request.
        $request = ServerRequestFactory::fromGlobals();

        $key = 'REQUEST_'.microtime();

        $redis->hset($key, 'request_headers', json_encode($request->getHeaders()));
        $redis->hset($key, 'request_body', json_encode($request->getBody()->getContents()));

        // Create a guzzle client
        $guzzle = new \GuzzleHttp\Client();

        // Create the proxy instance
        $proxy = new Proxy(new GuzzleAdapter($guzzle));

        // Add a response filter that removes the encoding headers.
        $proxy->filter(new RemoveEncodingFilter());

        // Forward the request and get the response.
        $response = $proxy->forward($request)->to('http://httpbin.org/');

        $redis->hset($key, 'response_headers', json_encode($request->getHeaders()));
        $redis->hset($key, 'response_body', json_encode($request->getBody()));

        return new Response($response->getBody()->getContents());
    }


    /**
     * @Route("/test")
     */
    public function test(SessionInterface $session, \Predis\Client $redis)
    {

        $session->start();
        $redis->incr('test:test');

        $number = random_int(0, 100);

        return new Response(
            '<html><body>Lucky number: ' . $number . '</body></html>'
        );
    }


}