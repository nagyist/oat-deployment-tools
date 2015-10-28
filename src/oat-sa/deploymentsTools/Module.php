<?php 
namespace oat\deploymentsTools;

use oat\deploymentsTools\Controller\DeployController;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\Controller\ControllerManager;

class Module implements ConfigProviderInterface
 {
     
     public function getConfig()
     {
         return include __DIR__ . '/config/module.config.php';
     }

     public function getControllerConfig()
     {
         return [
             'factories' => [
                 'DeployController' => function (ControllerManager $cm) {
                     $parentLocator = $cm->getServiceLocator();
                     $queueManager  = $parentLocator->get('SlmQueue\Queue\QueuePluginManager');

                     $queue        = $queueManager->get('default');

                     $controller = new DeployController($queue);

                     return $controller;
                 },
             ],
         ];
     }
 }