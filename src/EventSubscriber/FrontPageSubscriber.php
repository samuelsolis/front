<?php

namespace Drupal\front_page\EventSubscriber;

use Drupal\Core\Routing\RequestHelper;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FrontPageSubscriber implements EventSubscriberInterface {

  public function initData(GetResponseEvent $event) {

    // Make sure front page module is not run when using cli (drush).
    // Make sur front page module does not run when installing Drupal either.
    if (PHP_SAPI === 'cli' || drupal_installation_attempted()) {
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

    $configEdit = \Drupal::configFactory()->getEditable('system.site');
    $front = \Drupal::config('system.site')->get('page.front');

    // Let administrator know that there is a config error.
    if ($front == 'main' && \Drupal::currentUser()->hasPermission('administer menu')) {
      drupal_set_message(t('There is a configuration error. The home page should not be set to the path "main". Please change this !link', array('!link' => \Drupal::l(t('here.'), URL::fromUserInput('/admin/config/system/site-information')))), 'error');
    }

    if (\Drupal::config('front_page.settings')->get('enable', '') && \Drupal::service('path.matcher')->isFrontPage()) {
      $_front_page = front_page_get_by_role();
    }

    $url = Url::fromRoute('<current>');
    if (\Drupal::currentUser()->hasPermission('administer menu') && preg_match('@^main/preview/([a-z]+)$@', $url->toString(), $match)) {
      $_front_page = front_page_get_by_rid($match[1]);
    }

    if ($_front_page) {
      switch ($_front_page['mode']) {
        case 'themed':
        case 'full':
          $request = \Drupal::service('request_stack');
          $current_request = $request->getCurrentRequest();
          Request::create('/main', 'GET', $current_request->query->all(), $current_request->cookies->all(), array(), $current_request->server->all());

          $configEdit->set('page.front', '/main')->save();
          break;

        case 'redirect':
          $url = front_page_parse_url($_front_page['data']);
          $event->setResponse(new RedirectResponse($url['path']));
          break;

        case 'alias':
          $url = front_page_parse_url($_front_page['data']);
          $path = \Drupal::service('path.alias_storage')->lookupPathSource('/' . $url['path'], 'en');

          $request = \Drupal::service('request_stack');
          $current_request = $request->getCurrentRequest();
          Request::create($path, 'GET', $current_request->query->all(), $current_request->cookies->all(), array(), $current_request->server->all());

          $configEdit->set('page.front', $path)->save();
          break;
      }

      // Turn caching off for this page as it is dependant on role.
      \Drupal::service('page_cache_kill_switch')->trigger();
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
