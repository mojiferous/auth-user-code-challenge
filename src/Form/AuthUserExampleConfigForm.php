<?php

namespace Drupal\auth_user_example\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides custom admin config form for auth user example module.
 */
class AuthUserExampleConfigForm extends ConfigFormBase {

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames() {
    return ['auth_user_example.settings'];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'auth_user_example_config_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('auth_user_example.settings');
    $form['user_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Custom user message'),
      '#description' => $this->t('This message will be shown to users immediately below their user-specific information within the Auth User Example block.'),
      '#default_value' => $config->get('user_message'),
    ];

    $form['hide_message_for_anonymous'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide this message for anonymous users'),
      '#default_value' => $config->get('hide_message_for_anonymous'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('auth_user_example.settings');
    $values = $form_state->getUserInput();

    foreach ($values as $key => $value) {
      if ($key !== 'op' && strstr($key, 'form_') === FALSE) {
        // Ignore the 'op' and form_build, id, etc values.
        $config->set($key, $value);
      }
    }
    $config->save();
  }

}
