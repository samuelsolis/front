<?php

/**
 * @file
 * Contains \Drupal\front_page\Form\FrontPageSettingsForm.
 */

namespace Drupal\front_page\Form;

use Drupal\Core\Form\ConfigFormBase;

/**
 * Configure site information settings for this site.
 */
class FrontPageSettingsForm extends ConfigFormBase {

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'front_page_admin';
  }

  public function buildForm(array $form, array &$form_state) {
    $form['front_page_enable'] = array(
      '#type' => 'checkbox',
      '#title' => t('Front Page Override'),
      '#description' => t('Enable this if you want the front page module to manage the home page.'),
      '#default_value' => config('front_page.settings')->get('enable', 0),
    );

    // Load any existing settings and build the by redirect by role form
    $form['roles'] = array(
      '#tree' => TRUE,
      '#type' => 'fieldset',
      '#title' => t('Roles'),
      '#description' => t('These are the settings for each role. If Front Page Override is enabled when a user reaches the home page the site will iterate through the roles below from top to bottom until firstly the user has the role and secondly the role is not set to SKIP. If no roles get selected the site front page will be shown. To rearrange the order in which the roles are processed you may do this at the !link.', array('!link' => l(t('Arrange tab'), 'admin/config/front/arrange'))),
      '#collapsible' => FALSE,
    );

    // build the form for roles
    $roles = user_roles();
    $front_page_data = front_page_get_all();

    // Map the available modes common for all roles.
    $modes = array(
      '' => t('Skip'),
      'themed' => t('Themed'),
      'full' => t('Full'),
      'redirect' => t('Redirect'),
      'alias' => t('Alias'),
    );

    // Set the description common for all roles.
    $descriptions = array(
      '' => t('Disable functionality for this role.'),
      'themed' => t('Means your default layout, theme and stylesheet will be loaded with your custom front_page.'),
      'full' => t('Allows you to have a completely different layout, style sheet etc.'),
      'redirect' => t('Will automatically redirect visitors to a specific page specified in the REDIRECT TO box.'),
      'alias' => t('Will display the page listed in path as if it were the home page. This option does not redirect.'),
    );

    // Format the options to use as radio buttons
    $options = array();
    foreach ($modes as $key => $mode) {
      $options[$key] = "<strong>{$mode}:</strong> {$descriptions[$key]}";
    }

    // Iterate each role
    foreach ($roles as $rid => $role) {

      // Determine the mode for this role
      $mode = isset($front_page_data[$rid]['mode']) ? $front_page_data[$rid]['mode'] : '';

      $form['roles'][$rid] = array(
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#title' => t('Front Page for !rolename (%mode)', array('!rolename' => $role->label, '%mode' => $modes[$mode])),
        '#weight' => isset($front_page_data[$rid]['weight']) ? $front_page_data[$rid]['weight'] : 0,
      );

      $form['roles'][$rid]['mode'] = array(
        '#type' => 'radios',
        '#title' => t('Select mode'),
        '#default_value' => $mode,
        '#options' => $options,
      );

      // We need a wrapper because of a core bug that prevents the visible
      // #state from properly hiding text_format widgets:
      // @see: https://drupal.org/node/997826
      $form['roles'][$rid]['data_wrapper'] = array(
        '#type' => 'container',
        '#states' => array(
          'visible' => array(
            ':input[name="roles[' . $rid . '][mode]"]' => array(
              array('value' => 'full'),
              array('value' => 'themed')
            ),
          ),
        ),
      );

      $form['roles'][$rid]['data_wrapper']['data'] = array(
        '#type' => 'text_format',
        '#title' => t('Data'),
        '#default_value' => (isset($front_page_data[$rid]['data']) && isset($front_page_data[$rid]['mode']) && ($front_page_data[$rid]['mode'] == 'themed' || $front_page_data[$rid]['mode'] == 'full')) ? $front_page_data[$rid]['data'] : NULL,
        '#format' => !empty($front_page_data[$rid]['filter_format']) ? $front_page_data[$rid]['filter_format'] : NULL,
        '#description' => t('Paste your HTML or TEXT here.') . '<br /><br />' . t('You can paste in the full HTML code for a complete page and include a different style sheet in the HEAD of the document if you want a completely different layout and style to the rest of your site.'),
      );

      $form['roles'][$rid]['path'] = array(
        '#type' => 'textfield',
        '#title' => t('Path'),
        '#default_value' => (isset($front_page_data[$rid]['data']) && isset($front_page_data[$rid]['mode']) && ($front_page_data[$rid]['mode'] == 'redirect' || $front_page_data[$rid]['mode'] == 'alias')) ? $front_page_data[$rid]['data'] : NULL,
        '#cols' => 20,
        '#rows' => 1,
        '#description' => t('If you are using <strong>Redirect</strong> or <strong>Alias</strong> you need to specify the path. An alias path should only include the URL part of a URL (eg "node/51"). A redirect path can contain a full URL including get parameters and fragment string (eg "node/51?page=5#anchor").'),
        '#states' => array(
          'visible' => array(
            ':input[name="roles[' . $rid . '][mode]"]' => array(
              array('value' => 'redirect'),
              array('value' => 'alias')
            ),
          ),
        ),
      );
    }

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save Settings'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
    if (is_array($form_state['values']['roles'])) {
      foreach ($form_state['values']['roles'] as $rid => $role) {
        switch ($role['mode']) {
          case 'themed':
          case 'full':
            if (empty($role['data_wrapper']['data']['value'])) {
              \Drupal::formBuilder()->setErrorByName('roles][' . $rid . '][data][value', $form_state, 'You must set the data field for ' . $role['mode'] . ' mode.');
            }
            break;
          case 'redirect':
            if (empty($role['path'])) {
              \Drupal::formBuilder()->setErrorByName('roles][' . $rid . '][path', $form_state, 'You must set the path field for redirect mode.');
            }
            break;
          case 'alias':
            if (empty($role['path'])) {
              \Drupal::formBuilder()->setErrorByName('roles][' . $rid . '][path', $form_state, 'You must set the path field for alias mode.');
            }
            elseif (!preg_match('@^[^?#]+$@', $role['path'])) {
              \Drupal::formBuilder()->setErrorByName('roles][' . $rid . '][path', $form_state, 'You must set only the URI part of a URL in alias mode.');
            }
            break;
        }
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $config = config('front_page.settings')->set('enable', $form_state['values']['front_page_enable']);
    $config->save();

    // variable_set('front_page_enable', $form_state['values']['front_page_enable']);
    db_query("UPDATE {front_page} SET mode=''");
    if (is_array($form_state['values']['roles'])) {
      foreach ($form_state['values']['roles'] as $rid => $role) {
        switch ($role['mode']) {
          case 'themed':
          case 'full':
            db_merge('front_page')
                ->key(array('rid' => $rid))
                ->fields(array(
                  'mode' => $role['mode'],
                  'data' => $role['data_wrapper']['data']['value'],
                  'filter_format' => $role['data_wrapper']['data']['format'],
                ))
                ->execute();
            break;
          case 'redirect':
          case 'alias':
            db_merge('front_page')
                ->key(array('rid' => $rid))
                ->fields(array(
                  'mode' => $role['mode'],
                  'data' => $role['path'],
                  'filter_format' => '',
                ))
                ->execute();
            break;
          default:
            db_merge('front_page')
                ->key(array('rid' => $rid))
                ->fields(array(
                  'mode' => '',
                  'data' => '',
                  'filter_format' => '',
                ))
                ->execute();
            break;
        }
      }
    }

    parent::submitForm($form, $form_state);
  }
}
