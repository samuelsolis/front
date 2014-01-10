<?php

/**
 * @file
 * Contains \Drupal\front_page\Form\FrontPageHomeLinksForm.
 */

namespace Drupal\front_page\Form;

use Drupal\Core\Form\ConfigFormBase;

/**
 * Configure site information settings for this site.
 */
class FrontPageHomeLinksForm extends ConfigFormBase {

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'front_page_admin_home_links';
  }

  /**
   * BuildForm.
   */
  public function buildForm(array $form, array &$form_state) {
    $config = $this->config('front_page.settings');
    $form['front_page_home_link_path'] = array(
      '#type' => 'textfield',
      '#title' => t('Redirect your site HOME links to'),
      '#default_value' => $config->get('home_link_path'),
      '#cols' => 20,
      '#rows' => 1,
      '#description' => t('Specify where the user should be redirected to.
An example would be <em>node/12</em>. Leave blank when you\'re not using HOME redirect.'),
      '#field_prefix' => url(NULL, array('absolute' => TRUE)),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Submit Form.
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->config('front_page.settings')
      ->set('home_link_path', $form_state['values']['front_page_home_link_path'])
      ->save();
    parent::submitForm($form, $form_state);
  }
}
