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

    public function getIpInfo()
    {
        $ip = '201.221.172.69';

        // $this->ip_stack->setIp($ip);

        // $data = $this->ip_stack->getData();
        $data = null;
        return $data;
    }

    /**
     * Decode result.
     *
     * @param string $data
     *   Ipstack data.
     * @param bool $associative
     *   Is result an array instead of object.
     * @param array|null $options
     *   Options array.
     *
     * @return array|object
     *   Decoded result.
     */
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
