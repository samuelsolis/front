<?php

namespace Drupal\front_page\EventSubscriber;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FrontPageSubscriber implements EventSubscriberInterface {

  public function initData(GetResponseEvent $event) {

    // Make sure front page module is not run when using cli (drush).
    // Make sur front page module does not run when installing Drupal either.
    if (drupal_is_cli() || drupal_installation_attempted()) {
      return;
    }
    // Don't run when site is in maintenance mode.
    if (\Drupal::state()->get('system.maintenance_mode')) {
      return;
    }
    // Ignore non index.php requests (like cron).
    if (!empty($_SERVER['SCRIPT_FILENAME']) && realpath(DRUPAL_ROOT . '/index.php') != realpath($_SERVER['SCRIPT_FILENAME'])) {
      return;
    }

    global $_front_page;

    $config = config('system.site');
    $front = $config->get('page.front');

    // Let administrator know that there is a config error.
    if ($front == 'main'
      && \Drupal::currentUser()->hasPermission('administer menu')) {

      drupal_set_message(t('There is a configuration error. The home page should not be set to the path "main". Please change this !link', array(
        '!link' => l(t('here.'), 'admin/config/system/site-information'),
      )), 'error');
    }

    if (config('front_page.settings')->get('enable', '') && drupal_is_front_page()) {
      $_front_page = front_page_get_by_role();
    }

    if (\Drupal::currentUser()->hasPermission('administer menu')
      && preg_match('@^main/preview/([a-z]+)$@', current_path(), $match)) {

      $_front_page = front_page_get_by_rid($match[1]);
    }
    if ($_front_page) {
      switch ($_front_page['mode']) {
        case 'themed':
        case 'full':
          $_GET['q'] = 'main';
          // Need to set variable site_frontpage to current path
          // so that it thinks it is the front page.
          $config->set('page.front', 'main')->save();
          break;

        case 'redirect':
          $url = front_page_parse_url($_front_page['data']);
          $event->setResponse(new RedirectResponse($url['path']));
          break;

        case 'alias':
          $url = front_page_parse_url($_front_page['data']);
          $path = \Drupal::service('path.alias_manager.cached')->getSystemPath($url['path']);

          $request = \Drupal::service('request');
          \Drupal::request()->attributes->set('_system_path', $path);
          // Need to set variable site_frontpage to current path
          // so that it thinks it is the front page.
          $config->set('page.front', $path)->save();
          break;
      }

      // Turn caching off for this page as it is dependant on role.
      $GLOBALS['conf']['cache'] = FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('initData');
    return $events;
  }
}
