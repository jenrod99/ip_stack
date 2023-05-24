<?php

namespace Drupal\ip_stack\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ip_stack\Services\GetIpStackInfo;
use Psr\Container\ContainerInterface;

class IpStackForm extends FormBase {
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

    public function getFormId()
    {
        return 'ip_stack_ip_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {

        $form['ip'] =
            [
                '#type' => 'textfield',
                '#title' => 'título',
                '#size' => 60,
                '#maxlength' => 128,
                '#required' => TRUE,
                '#pattern' => '^[a-zA-ZäöüßÄÖÜ ]+$',
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
        if (strlen($form_state->getValue('textfield') < 4)) {
            $form_state->setErrorByName('phone', 'El campo debe contener mínimo 4 caracteres');
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $values = $form_state->getValues();
        dpm($values);

        $this->messenger()->addStatus('el campo title tiene el texto ' . $values['title']);
        $this->messenger()->addStatus('el campo phone tiene el texto ' . $values['phone']);
        $this->messenger()->addStatus('el campo checkbox tiene el texto ' . $values['checkbox']);
    }
}