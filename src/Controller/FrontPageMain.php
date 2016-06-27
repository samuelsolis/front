<?php

namespace Drupal\front_page\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class FrontPageMain extends ControllerBase {

  public static function create(ContainerInterface $container) {
    return new static($container->get('module_handler'));
  }

  /**
   * Method to handle the display of the front page themed and full types.
   */
  public function frontPage() {
    // Variable $_front_page  should already have been loaded
    // in front_page_init() function.
    global $_front_page;
    if ($_front_page) {
      switch ($_front_page['mode']) {
        case 'themed':
          return check_markup($_front_page['data'], $_front_page['filter_format']);

        case 'full':
          print check_markup($_front_page['data'], $_front_page['filter_format']);
          exit;
      }
    }

    // Set page not found as there was no themed or full option set for the front page.
    throw new NotFoundHttpException();
    exit;
  }
}
