<?php

namespace Drupal\ipstack\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\ipstack\Ipstack;

/**
 * Controller for test page /admin/config/system/ipstack/test/page .
 */
class IpstackController extends ControllerBase {

  /**
   * Ipstack manager variable.
   *
   * @var \Drupal\ipstack\Ipstack
   */
  protected $ipstack;

  /**
   * Request stack variable.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new IpstackSettingsForm object.
   *
   * @param \Drupal\ipstack\Ipstack $ipstack
   *   Ipstack object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Store RequestStack manager.
   */
  public function __construct(Ipstack $ipstack, RequestStack $request_stack) {
    $this->ipstack = $ipstack;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ipstack'),
      $container->get('request_stack')
    );
  }

  /**
   * Show test page.
   */
  public function page() {
    $config = $this->config('ipstack.settings');
    $ip = $this->requestStack->getCurrentRequest()->query->get('ip');
    if (!empty($ip)) {
      $output = $this->t('Ipstack test for IP %ip', ['%ip' => $ip]);

      // Get options from config.
      $options = [];
      $options_keys = ['fields', 'hostname', 'security', 'language', 'output'];
      foreach ($options_keys as $key) {
        $value = $config->get($key);
        if (!empty($value)) {
          if (is_array($value)) {
            $value = implode(',', $value);
          }
          $options[$key] = $value;
        }
      }

      // Make Ipstack request and show result.
      $this->ipstack
        ->setIp($ip)
        ->setOptions($options)
        ->showResult();
    }
    else {
      $ip = $config->get('ip') ?: $this->requestStack->getCurrentRequest()->getClientIp();
      $url = Url::fromRoute('ipstack.testpage', ['ip' => $ip])->setAbsolute();
      $link = Link::fromTextAndUrl($url->toString(), $url)->toString();
      $output = $this->t('Need IP parameter like ?ip=@ip. For example: @link', [
        '@ip' => $ip,
        '@link' => $link,
      ]);
    }

    return ['#markup' => $output];
  }

}
