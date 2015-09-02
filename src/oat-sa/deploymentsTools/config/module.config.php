<?php

return array(

     'router' => array(
         'routes' => array(
            'phing' => array(
                'type'    => 'segment',
                'options' => array(
                     'route'    => '/phing[/:action][/:id]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                         'id'     => '[0-9]+',
                     ),
                     'defaults' => array(
                         'controller' => 'BsbPhingService\Controller\Index',
                         'action'     => 'index',
                     ),
                ),
            ),
         ),
     ),
     'view_manager'    => array(
        'not_found_template'   => 'error/404',
        'exception_template'   => 'error/index',
        'template_map' => array(
                'error/404'      => __DIR__ . '/../view/error/404.phtml',
                'error/index'    => __DIR__ . '/../view/error/index.phtml',
                'layout/layout'  => __DIR__ . '/../view/layout/layout.phtml',
        ),
    ),
);