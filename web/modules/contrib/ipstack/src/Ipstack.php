<?php

namespace Drupal\ipstack;

/**
 * @file
 * Contains \Drupal\ipstack\Ipstack.
 */

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;
use Drupal\Core\Cache\CacheBackendInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client as HttpClient;

/**
 * Class Ipstack.
 *
 * Provides ipstack.com API.
 *
 * @ingroup ipstack
 */
class Ipstack extends ControllerBase {

  const IPSTACK_URL = 'api.ipstack.com';

  /**
   * IP address.
   *
   * @var string
   */
  protected $ip = '';

  /**
   * Ipstack options.
   *
   * @var array
   */
  protected $options = [];

  /**
   * Is ipstack data from cache?
   *
   * @var bool
   */
  protected $cacheData = FALSE;

  /**
   * GuzzleHttp\Client definition.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Ipstack constructor.
   */
  public function __construct(HttpClient $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }

  /**
   * Set IP address.
   *
   * @param string $ip
   *   IP address.
   */
  public function setIp($ip) {
    $this->ip = trim($ip);
    return $this;
  }

  /**
   * Set ipstack options. (config value if empty).
   *
   * @param array $options
   *   Ipstack options.
   */
  public function setOptions(array $options) {
    $this->options = $options;
    return $this;
  }

  /**
   * Set access key.
   *
   * @param string $access_key
   *   Access key string.
   */
  public function setAccessKey($access_key = FALSE) {
    if (!$access_key) {
      $access_key = $this->config('ipstack.settings')->get('access_key');
    }
    $this->options['access_key'] = $access_key;
    return $this;
  }

  /**
   * Set fields.
   *
   * @param string $fields
   *   Fields parameter.
   */
  public function setFields($fields = 'main') {
    $this->options['fields'] = $fields;
    return $this;
  }

  /**
   * Enable Hostname lookup.
   */
  public function enableHostname() {
    $this->options['hostname'] = 1;
    return $this;
  }

  /**
   * Enable Security module.
   */
  public function enableSecurity() {
    $this->options['security'] = 1;
    return $this;
  }

  /**
   * Set language.
   *
   * @param string $language
   *   Language code.
   */
  public function setLanguage($language = 'en') {
    $this->options['language'] = $language;
    return $this;
  }

  /**
   * Set ouput.
   *
   * @param string $output
   *   Set output format.
   */
  public function setOutput($output = 'json') {
    $this->options['output'] = $output;
    return $this;
  }

  /**
   * Get ipstack URL.
   *
   * @return string
   *   Ipstack URL for data retrieving.
   */
  public function getUrl() {
    $prot = !empty($this->config('ipstack.settings')->get('use_https')) ? 'https' : 'http';
    $url_options = ['absolute' => TRUE, 'query' => $this->options];
    $uri = sprintf('%s://%s/%s', $prot, self::IPSTACK_URL, $this->ip);
    $url = Url::fromUri($uri, $url_options);
    return $url->toString();
  }

  /**
   * Get ipstack data.
   *
   * @return array
   *   Ipstack data responce.
   */
  public function getData() {
    $use_cache = $this->config('ipstack.settings')->get('use_cache');
    $this->cacheData = FALSE;

    // Get data from cache.
    $cid = 'ipstack:ip_' . $this->ip;
    if ($use_cache) {
      $cache = $this->cache()->get($cid);
      if (!empty($cache)) {
        $this->cacheData = TRUE;
        return ['data' => $cache->data];
      }
    }

    // Access Key is required.
    $this->setAccessKey();
    if (empty($this->options['access_key'])) {
      return ['error' => $this->t('Empty Ipstack access key.')];
    }

    // Get data from ipstack site.
    try {
      $responce = $this->httpClient->get($this->getUrl());
    }
    catch (\Exception $exception) {
      return [
        'error' => $this->t('Error retrieving data: @message', [
          '@message' => $exception->getMessage(),
        ]),
      ];
    }

    $status = $responce->getStatusCode();
    if ($status != Response::HTTP_OK) {
      return ['error' => $this->t('Got responce status @status', ['@status' => $status])];
    }

    $data = (string) $responce->getBody();
    if ($use_cache) {
      // Set ipstack cache.
      $this->cache()->set($cid, $data, CacheBackendInterface::CACHE_PERMANENT, ['ipstack']);
    }

    return ['data' => $data];
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
  public function decode($data, $associative = FALSE, $options = NULL) {
    if (!$options) {
      $options = $this->options;
    }

    if (empty($options['output']) || $options['output'] === 'json') {
      $data = json_decode($data, $associative);
    }
    else {
      $xml = simplexml_load_string($data);
      $data = json_decode(json_encode((array) $xml), $associative);
    }
    return $data;
  }

  /**
   * Show result.
   */
  public function showResult() {
    $url = $this->getUrl();
    $messenger = $this->messenger();
    $msg = $this->t("Request: <a href=':url' target='_new'>:url</a>", [':url' => $url]);
    $messenger->addMessage($msg);

    $data = $this->getData();

    if (!empty($data['error'])) {
      $messenger->addError($data['error']);
    }

    if (!empty($data['data'])) {
      $data = $data['data'];
      if ($this->cacheData) {
        $messenger->addMessage($this->t('From cache'));
      }

      // Decode JSON object.
      $data = $this->decode($data);

      $status = 'status';
      if (!empty($data->error)) {
        $data = $data->error;
        $status = 'error';
      }

      $msg = $this->t('Responce:');
      if (is_array($data) || is_object($data)) {
        $msg .= ' <pre>' . print_r($data, 1) . '</pre>';
        $msg = Markup::create($msg);
      }
      else {
        $msg .= $data;
      }
      $messenger->addMessage($msg, $status);
    }

    $messenger->all();
  }

}
