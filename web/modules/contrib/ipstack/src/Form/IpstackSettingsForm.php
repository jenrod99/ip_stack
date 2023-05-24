<?php

namespace Drupal\ipstack\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;

/**
 * Configure ipstack settings for this site.
 */
class IpstackSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ipstack_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ipstack.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['access_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ipstack Access Key'),
      '#default_value' => $this->config('ipstack.settings')->get('access_key'),
      '#required' => TRUE,
      '#description' => $this->t("Get Access Key by register at
        <a href='@url' rel='nofollow' target='_new'>ipstack.com</a>.",
        ['@url' => 'https://ipstack.com']
      ),
    ];

    $form['use_https'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use HTTPS'),
      '#default_value' => !empty($this->config('ipstack.settings')->get('use_https')),
      '#description' => $this->t('Connect to the API via HTTPS. For premium plans only.'),
    ];

    $form['use_cache'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use cache'),
      '#default_value' => !empty($this->config('ipstack.settings')->get('use_cache')),
      '#description' => $this->t('Use cache data for IP instead IPstack API request.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('ipstack.settings');

    if ($config->get('use_cache')) {
      // Invalidate ipstack cache.
      Cache::invalidateTags(['ipstack']);
      $this->messenger()->addMessage($this->t('Ipstack cash was cleared.'));
    }

    $config->set('access_key', $form_state->getValue('access_key'))
      ->set('use_https', $form_state->getValue('use_https'))
      ->set('use_cache', $form_state->getValue('use_cache'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
