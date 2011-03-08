<?php

/**
 * @file
 * Admin page callbacks for the front page module.
 */

/**
 * Form for configuring front page settings.
 */
function front_page_admin($form, &$form_state) {
  $form['front_page_enable'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enabled'),
    '#description' => t('Enable this if you want the front page module to manage the home page.'),
    '#default_value' => variable_get('front_page_enable', 0),
  );
  // Load any existing settings and build the by redirect by role form
  $form['roles'] = array(
    '#type' => 'fieldset',
    '#title' => t('Roles'),
    '#description' => t('Designate a home page for each role.'),
    //'#theme' => 'front_page_roles',
    '#collapsible' => TRUE,
    '#tree' => TRUE,
  );

  // build the form for roles
  $roles = user_roles();
  $front_page_data = front_page_get_all();

  // Set the type options common for all roles.
  $options = array(
    '' => t('skip'),
    'themed' => t('themed'),
    'full' => t('full'),
    'redirect' => t('redirect'),
    'alias' => t('alias'),
  );

  // Set the description common for all roles.
  $description = '<dl><dt>' . t('themed') . '</dt>
      <dd>' . t('means your default layout, theme and stylesheet will be loaded with your custom front_page.') . '</dd></dl>';
  $description .= '<dl><dt>' . t('full') . '</dt>
      <dd>' . t('allows you to have a completely different layout, style sheet etc.') . '</dd></dl>';
  $description .= '<dl><dt>' . t('redirect') . '</dt>
      <dd>' . t('will automatically redirect visitors already logged in to a specific page specified in the REDIRECT TO box.') . '</dd></dl>';
  $description .= '<dl><dt>' . t('alias') . '</dt>
      <dd>' . t('will display the page listed in path as if it were the home page. This option does not redirect.') . '</dd></dl>';

  foreach ($roles as $rid => $role) {

    $form['roles'][$rid] = array(
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#title' => t('Front Page for !rolename.', array('!rolename' => $role)),
      '#weight' => isset($front_page_data[$rid]['weight']) ? $front_page_data[$rid]['weight'] : 0,
    );

    $form['roles'][$rid]['mode'] = array(
      '#type' => 'select',
      '#title' => t('Select mode'),
      '#default_value' => isset($front_page_data[$rid]['mode']) ? $front_page_data[$rid]['mode'] : '',
      '#options' => $options,
      '#description' => '<p>' . $description . '</p>',
    );

    $form['roles'][$rid]['data'] = array(
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
    );
  }

  $form['front_offline'] = array(
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#title' => t('Custom "Temporarily Offline" message and disable site for all except admins'),
  );

  $form['front_offline']['site_offline'] = array(
    '#type' => 'radios',
    '#title' => t('Site maintenance'),
    '#default_value' => variable_get('site_offline', 0),
    '#options' => array(t('Online'), t('Off-line')),
    '#description' => t('When set to "Online", all visitors will be able to browse your site normally. When set to "Off-line", only users with the "administer site configuration" permission will be able to access your site to perform maintenance; all other visitors will see the site off-line message configured below. Authorized users can log in during "Off-line" mode directly via the <a href="@user-login">user login</a> page.', array('@user-login' => url('user', array('query' => drupal_get_destination())))),
  );

  $form['front_offline']['site_offline_message'] = array(
    '#type' => 'textarea',
    '#title' => t('Site off-line message'),
    '#default_value' => variable_get('site_offline_message', t('@site is currently under maintenance. We should be back shortly. Thank you for your patience.', array('@site' => variable_get('site_name', t('This Drupal site'))))),
    '#description' => t('Message to show visitors when the site is in off-line mode.')
  );

  $form['special_notice'] = array(
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#title' => t('Custom mission for Authenticated Users who haven\'t visited the site for a set period'),
  );

  $form['special_notice']['special_notice_time'] = array(
    '#type' => 'select',
    '#title' => t('Been away for'),
    '#default_value' => variable_get('special_notice_time', ''),
    '#options' => array(
      'one day' => t('one day'),
      'one week' => t('one week'),
      'one month' => t('one month'),
      'three months' => t('three months'),
      'six months' => t('six months'),
      'one year' => t('one year'),
    ),
    '#description' => t('<p>The length of time to check for when users see the special notice which will be displayed as a <em>mission</em> style notice on the front page.<p>'),
  );

  $form['special_notice']['special_notice_text'] = array(
    '#type' => 'textarea',
    '#title' => t('Body'),
    '#default_value' => variable_get('special_notice_text', ''),
    '#cols' => 60,
    '#rows' => 10,
    '#description' => t('Paste your HTML or TEXT here. It will be displayed as a <em>mission</em> style notice on the front page.'),
  );

  $form['override'] = array(
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#title' => t('Override your HOME breadcrumb links'),
  );

  $form['override']['front_page_breadcrumb'] = array(
    '#type' => 'checkbox',
    '#title' => t('Redirect your site HOME links'),
    '#return_value' => 1,
    '#default_value' => variable_get('front_page_breadcrumb', 0),
    '#description' => t('Check this box if you want to redirect users who click on any HOME links (including the HOME breadcrumb link). Useful if you have a splash page you don\'t want users brought back to when they click on HOME links from within your site.')
  );

  $form['override']['front_page_breadcrumb_redirect'] = array(
    '#type' => 'textfield',
    '#title' => t('Redirect your site HOME links to'),
    '#default_value' => variable_get('front_page_breadcrumb_redirect', ''),
    '#cols' => 20, '#rows' => 1,
    '#description' => t('Specify where the user should be redirected to. An example would be <em>node/12</em>. Leave blank when you\'re not using HOME redirect.'),
    '#field_prefix' => url(NULL, array('absolute' => TRUE)) . (variable_get('clean_url', 0) ? '' : '?q=')
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Settings'),
  );


  return $form;
}

/**
 * Validation hook for front_page_admin.
 */
function front_page_admin_validate($form, &$form_state) {
  if (is_array($form_state['values']['roles'])) {
    foreach ($form_state['values']['roles'] as $rid => $role) {
      switch ($role['mode']) {
        case 'themed':
        case 'full':
          if (empty($role['data']['value'])) {
            form_set_error('roles][' . $rid . '][data][value', 'You must set the data field for ' . $role['mode'] . ' mode.');
          }
          break;
        case 'redirect':
          if (empty($role['path'])) {
            form_set_error('roles][' . $rid . '][path', 'You must set the path field for redirect mode.');
          }
          break;
        case 'alias':
          if (empty($role['path'])) {
            form_set_error('roles][' . $rid . '][path', 'You must set the path field for alias mode.');
          }
          elseif (!preg_match('@^[^?#]+$@', $role['path'])) {
            form_set_error('roles][' . $rid . '][path', 'You must set only the URI part of a URL in alias mode.');
          }
          break;
      }
    }
  }
}

/**
 * Submit hook for front_page_admin.
 */
function front_page_admin_submit($form, &$form_state) {
  variable_set('front_page_enable', $form_state['values']['front_page_enable']);
  variable_set('site_offline', $form_state['values']['site_offline']);
  variable_set('site_offline_message', $form_state['values']['site_offline_message']);
  variable_set('front_page_breadcrumb', $form_state['values']['front_page_breadcrumb']);
  variable_set('front_page_breadcrumb_redirect', $form_state['values']['front_page_breadcrumb_redirect']);

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
                'data' => $role['data']['value'],
                'filter_format' => $role['data']['format'],
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
  drupal_set_message(t('Your settings have been saved.'));
}

/**
 * Form for arranging the roles according to what order the roles should be processed.
 */
function front_page_admin_arrange_form($form, &$form_state) {
  $roles = user_roles();
  $front_page_data = front_page_get_all();

  $form['roles'] = array(
    '#tree' => TRUE,
  );
  foreach ($front_page_data as $role_id => $role) {
    if (!empty($role['mode']) && $role_id > 1) {
      $form['roles'][$role_id]['title']['#markup'] = $roles[$role_id];
      $form['roles'][$role_id]['mode'] = array(
        '#type' => 'checkbox',
        '#title' => t('Enable'),
        '#title_display' => 'invisible',
        '#default_value' => TRUE,
      );
      $form['roles'][$role_id]['weight'] = array(
        '#type' => 'weight',
        '#title' => t('Weight'),
        '#title_display' => 'invisible',
        '#delta' => 10,
        '#default_value' => $role['weight'],
      );
    }
  }

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Order'),
  );
  return $form;
}

/**
 * Submit hook for front_page_admin_arrange_form.
 */
function front_page_admin_arrange_form_submit($form, &$form_state) {
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
}

/**
 * Returns HTML for the front page arrange form into a table.
 *
 * @param $variables
 *   An associative array containing:
 *   - form: A render element representing the form.
 */
function theme_front_page_admin_arrange_form($variables) {
  $form = $variables['form'];

  drupal_add_tabledrag('front-page-arrange', 'order', 'sibling', 'front-page-weight');

  $header = array(
    t('Role'),
    t('Enabled'),
    t('Weight'),
  );

  $rows = array();
  foreach (element_children($form['roles']) as $rid) {
    $element = &$form['roles'][$rid];

    // Add special classes to be used for tabledrag.js.
    $element['weight']['#attributes']['class'] = array('front-page-weight');

    $row = array();
    $row[] = drupal_render($element['title']);
    $row[] = drupal_render($element['mode']);
    $row[] = drupal_render($element['weight']);

    $row = array_merge(array('data' => $row), $element['#attributes']);
    $row['class'][] = 'draggable';
    $rows[] = $row;
  }
  $output = '';
  if (empty($rows)) {
    $rows[] = array(array('data' => $form['#empty_text'], 'colspan' => '2'));
  }
  $output .= theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('id' => 'front-page-arrange')));
  $output .= drupal_render_children($form);
  return $output;
}