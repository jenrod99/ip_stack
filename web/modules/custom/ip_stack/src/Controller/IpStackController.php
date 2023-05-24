<?php

namespace Drupal\ip_stack\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ip_stack\Services\GetIpStackInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class IpStackController extends ControllerBase
{

    private $ip_stack;

    public function __construct(GetIpStackInfo $ip_stack)
    {
        $this->ip_stack = $ip_stack;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('ip_stack.getInfo')
        );
    }


    public function getInfo()
    {

        $data = $this->ip_stack->getIpInfo();
        $ip = $this->ip_stack->decode($data['data'])->ip;
        $type = $this->ip_stack->decode($data['data'])->type;
        $continent_code = $this->ip_stack->decode($data['data'])->continent_code;
        $continent_name = $this->ip_stack->decode($data['data'])->continent_name;
        $country_code = $this->ip_stack->decode($data['data'])->country_code;
        $country_name = $this->ip_stack->decode($data['data'])->country_name;
        $region_code = $this->ip_stack->decode($data['data'])->region_code;
        $region_name = $this->ip_stack->decode($data['data'])->region_name;
        $region_name = $this->ip_stack->decode($data['data'])->country_name;
        $city = $this->ip_stack->decode($data['data'])->city;
        $zip = $this->ip_stack->decode($data['data'])->zip;
        $latitude = $this->ip_stack->decode($data['data'])->latitude;
        $longitude = $this->ip_stack->decode($data['data'])->longitude;
        $location = $this->ip_stack->decode($data['data'])->location->capital;
        dpm($data);

        return [
            '#theme' => 'ip_stack_template',
            '#current_ip' => $ip,
            '#type' => $type,
            '#continent_code' => $continent_code,
            '#continent_name' => $continent_name,
            '#country_code' => $country_code,
            '#country_name' => $country_name,
            '#region_code' => $region_code,
            '#region_name' => $region_name,
            '#city' => $city,
            '#zip' => $zip,
            '#latitude' => $latitude,
            '#longitude' => $longitude,
            '#location' => $location,            
        ];
    }
}
