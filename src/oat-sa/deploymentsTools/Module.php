<?php
namespace oat\deploymentsTools;

use Monolog\Logger;
use oat\deploymentsTools\Controller\DeployController;
use SlmQueue\Worker\WorkerEvent;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\Controller\ControllerManager;
use Zend\Mvc\MvcEvent;

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

                    $queue = $queueManager->get('deploy');

                    $controller = new DeployController($queue);

                    return $controller;
                },
            ],
        ];
    }

    public function onBootstrap(MvcEvent $e)
    {
        $em       = $e->getApplication()->getEventManager();
        $sharedEm = $em->getSharedManager();

        /** @var Logger $logger */
        $logger = $e->getApplication()->getServiceManager()->get('BuildLogService');

        $sharedEm->attach('SlmQueue\Worker\WorkerInterface', WorkerEvent::EVENT_PROCESS_JOB,
            function (WorkerEvent $e) use ($logger) {
                $result = $e->getResult();
                if (WorkerEvent::JOB_STATUS_FAILURE === $result) {
                    $job = $e->getJob();
                    $logger->addError(sprintf(
                        'Job #%s (%s) failed executing', $job->getId(), get_class($job)
                    ));
                }else{
                    $job = $e->getJob();
                    $logger->addInfo(sprintf(
                        'Job #%s (%s) executing', $job->getId(), get_class($job)
                    ));
                }
            }, 1000);
    }
}