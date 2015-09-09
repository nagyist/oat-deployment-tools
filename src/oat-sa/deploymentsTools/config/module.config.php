<?php

return array(
    
    'controllers'     => array(
        'invokables' => array(
            'oat\deploymentsTools\Controller\Deploy' => 'oat\deploymentsTools\Controller\DeployController',
        ),
    ),

    'router' => array(
         'routes' => array(
            'phingService' => array(
                'type'    => 'segment',
                'options' => array(
                     'route'    => '/phingService',
                     'defaults' => array(
                         'controller' => 'BsbPhingService\Controller\Index',
                         'action'     => 'index',
                     ),
                ),
            ),
            'deploy' => array(
                'type'    => 'segment',
                'options' => array(
                     'route'    => '/deploy',
                     'defaults' => array(
                         'controller' => 'oat\deploymentsTools\Controller\Deploy',
                         'action'     => 'run',
                     ),
                ),
            ),
         ),
     ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'deploy' => array(
                    'options' => array(
                         'route'    => 'help',
                         'defaults' => array(
                             'controller' => 'oat\deploymentsTools\Controller\Deploy',
                             'action'     => 'help',
                         ),
                    ),
                ),
            ),
        ),
    
          
    ),
    'view_manager'    => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'not_found_template'   => 'error/404',
        'exception_template'   => 'error/index',
        'template_map' => array(
                'error/404'      => __DIR__ . '/../view/error/404.phtml',
                'error/index'    => __DIR__ . '/../view/error/index.phtml',
                'layout/layout'  => __DIR__ . '/../view/layout/layout.phtml',
        ),

        'template_path_stack' => array(
                 __DIR__ . '/../view',
        ),

    ),

);