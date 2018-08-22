<?php

namespace App\Proxy\Adapter\Dummy;

use App\Proxy\Adapter\AdapterInterface;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;

class DummyAdapter implements AdapterInterface
{
    /**
     * @inheritdoc
     */
    public function send(RequestInterface $request)
    {
        return new Response($request->getBody(), 200);
    }
}
