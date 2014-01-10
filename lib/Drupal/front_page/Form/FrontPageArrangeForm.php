<?php

/**
 * @file
 * Contains \Drupal\front_page\Form\FrontPageArrangeForm.
 */

namespace Drupal\front_page\Form;

use Drupal\Core\Form\ConfigFormBase;

/**
 * Configure site information settings for this site.
 */
class FrontPageArrangeForm extends ConfigFormBase {

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'front_page_admin_arrange_form';
  }

  /**
   * BuildForm.
   */
  public function buildForm(array $form, array &$form_state) {
    $roles = user_roles();
    $front_page_data = front_page_get_all();
    foreach ($roles as $rid => $role) {
      $front_page_data[$rid]['name'] = $role->label;
    }

    $form['roles'] = array('#tree' => TRUE);
    foreach ($front_page_data as $role_id => $role) {
      $form['roles'][$role_id]['title']['#markup'] = $role['name'];
      $form['roles'][$role_id]['mode']['#markup'] = !empty($role['mode'])
        ? $role['mode']
        : 'skip';
      $form['roles'][$role_id]['preview']['#markup'] = !empty($role['mode'])
        ? l(t('preview'), 'main/preview/' . $role_id, array('attributes' => array('target' => '_blank')))
        : '';
      if (!empty($role['mode'])) {
        $form['roles'][$role_id]['enabled'] = array(
          '#type' => 'checkbox',
          '#title' => t('Enable'),
          '#title_display' => 'invisible',
          '#default_value' => TRUE,
        );
      }
      else {
        $form['roles'][$role_id]['enabled']['#markup'] = 'disabled';
      }
      $form['roles'][$role_id]['weight'] = array(
        '#type' => 'weight',
        '#title' => t('Weight'),
        '#title_display' => 'invisible',
        '#delta' => 10,
        '#default_value' => isset($role['weight']) ? $role['weight'] : 0,
      );
    }

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save Order'),
    );
    return $form;
  }

  /**
   * Submit Form.
   */
  public function submitForm(array &$form, array &$form_state) {
    $front_page_data = front_page_get_all();
    foreach ($form_state['values']['roles'] as $rid => $role) {
      if (isset($role['mode']) && !$role['mode'] || !isset($front_page_data[$rid])) {
        db_merge('front_page')
          ->key(array('rid' => $rid))
          ->fields(array(
            'mode' => '',
            'data' => '',
            'filter_format' => '',
            'weight' => $role['weight'],
          ))
          ->execute();
      }
      else {
        db_merge('front_page')
          ->key(array('rid' => $rid))
          ->fields(array(
            'weight' => $role['weight'],
          ))
          ->execute();
      }
    }
    parent::submitForm($form, $form_state);
  }
}
