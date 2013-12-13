<?php

namespace Drupal\front_page\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FrontPageMain extends ControllerBase {

  public static function create(ContainerInterface $container) {
    return new static($container->get('module_handler'));
  }

  /**
   * This will return the output of the example page.
   */
  public function examplePage() {
    return array(
      '#markup' => t('Это тестовая страничка.'),
    );
  }
}
