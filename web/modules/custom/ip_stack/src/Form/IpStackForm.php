<?php

namespace Drupal\ip_stack\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ip_stack\Services\GetIpStackInfo;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class IpStackForm extends FormBase {
    
    private $ip_stack;
    private $session;

    public function __construct(GetIpStackInfo $ip_stack, SessionInterface $session)
    {
        $this->ip_stack = $ip_stack;
        $this->session = $session;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('ip_stack.getInfo'),
            $container->get('session'),
        );
    }

    public function getFormId()
    {
        return 'ip_stack_ip_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {

        $form['ip'] =
            [
                '#type' => 'textfield',
                '#title' => 'ip',
                '#size' => 60,
                '#maxlength' => 128,
                '#minlength' => 128,
                '#required' => TRUE,
                '#pattern' => '^\d+(\.\d+)*$',
                '#attributes' =>  [
                    'class' => ['input-form']
                ],
            ];

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => 'Enviar',
        ];
        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        if (strlen($form_state->getValue('ip') < 4)) {
            $form_state->setErrorByName('ip', 'El campo debe contener mínimo 4 caracteres');
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $ip = $form_state->getValue('ip');
        $this->session->set('ip_stack_new_ip', $ip);
    }
}