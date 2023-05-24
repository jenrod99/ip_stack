<?php

namespace Drupal\ip_stack\Services;

use Drupal\ipstack\Ipstack;

class GetIpStackInfo
{

    private $ip_stack;

    public function __construct(Ipstack $ipstack)
    {
        $this->ip_stack = $ipstack;
    }

    public function getIpInfo($ip)
    {
        $this->ip_stack->setIp($ip);
        $data = $this->ip_stack->getData();

        return $data;
    }

    public function decode($data, $associative = FALSE, $options = NULL)
    {

        if (empty($options['output']) || $options['output'] === 'json') {
            $data = json_decode($data, $associative);
        } else {
            $xml = simplexml_load_string($data);
            $data = json_decode(json_encode((array) $xml), $associative);
        }
        return $data;
    }
}
