<?php
namespace oat\deploymentsTools;

return array(
    
    'controllers'     => [
        'invokables' => [
//            'DeployController' => 'oat\deploymentsTools\Controller\DeployController',
        ],
    ],
    'service_manager' => [
        'factories' => [
            'BsbPhingService'                => 'BsbPhingService\Service\Factory\PhingServiceFactory',
            'BsbPhingService.serviceOptions' => 'BsbPhingService\Options\Factory\ServiceOptionsFactory',
            'BsbPhingService.phingOptions'   => 'BsbPhingService\Options\Factory\PhingOptionsFactory',
            'DeployService'                  => 'oat\deploymentsTools\Service\Factory\DeployServiceFactory'
        ],
    ],

    'router' => [
         'routes' => [
            'deploy' => [
                'type'    => 'segment',
                'options' => [
                     'route'    => '/deploy',
                     'defaults' => [
                         'controller' => 'DeployController',
                         'action'     => 'run',
                     ],
                ],
            ],
         ],


    ],
    'view_manager'    => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'not_found_template'   => 'error/404',
        'exception_template'   => 'error/index',
        'template_map' => [
                'error/404'      => __DIR__ . '/../view/error/404.phtml',
                'error/index'    => __DIR__ . '/../view/error/index.phtml',
                'layout/layout'  => __DIR__ . '/../view/layout/layout.phtml',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
        'template_path_stack' => [
                 __DIR__ . '/../view',
        ],

    ],

    'doctrine' => [
        'driver' => [
             __NAMESPACE__ . '_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Entity'
                ],
             ],

            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ]
            ]
        ]
    ],

    'EnliteMonolog' => [

        'BuildLogService' => [

            'handlers' => [

                'default' => [
                    'name' => 'Monolog\Handler\RotatingFileHandler',
                    'args' => [
                        'path' => 'data/log/main.log',
                        'level' => \Monolog\Logger::INFO,
                        'bubble' => true
                    ],
                    'formatter' => [
                        'name' => 'Monolog\Formatter\LogstashFormatter',
                        'args' => [
                            'application' => 'deployment tool',
                        ],
                    ],
                ],
            ]
        ],
    ]
);