<?php

/**
 * PhingService Options
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it. (remove the .dist extention to enable it)
 */
/**
 * Configuration service
 */
$serviceOptions = array(
    'phpBin'    => null, /* null will attempt auto detection */
    'phingBin'  => null, /* null will attempt auto-detection, defaults to ./vendor/bin/phing */
);

/**
 * Defaults that are used to configure the phing binary
 */
$phingOptions = array(
    'logger'       => 'phing.listener.DefaultLogger',
    'logFile'      => null, /* use given file for log, note that no output will be available from the exec call, also note that phing does not append to the log file, it will be replaced */
    'propertyFile' => null, /* load all properties from file */
    'properties'   => array(),
    'inputHandler' => null,  /* the class to use to handle user input */
    'longTargets'  => false, /* show target descriptions during build */
    'find'         => null, /* search for buildfile towards the root of the filesystem and use it */
    'list'         => false, /* list available targets in this project */
);


return array(
    'bsbphingservice' => array(
        'service' => $serviceOptions,
        'phing' => $phingOptions,
    ),

    /* Enable the following section to get instant gratification at http://localhost/phingservice
     *
    'router' => array(
        'routes' => array(
            'PhingService' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/phingservice',
                    'defaults' => array(
                        '__NAMESPACE__' => 'BsbPhingService\Controller',
                        'controller' => 'Index',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    /**/

);
