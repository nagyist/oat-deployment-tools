<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\deploymentsTools\Service\Factory;

use oat\deploymentsTools\Queue\DoctrineQueue;
use SlmQueueDoctrine\Options\DoctrineOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * DoctrineQueueFactory
 */
class DoctrineQueueFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator, $name = '', $requestedName = '')
    {
        $parentLocator = $serviceLocator->getServiceLocator();

        $config = $parentLocator->get('Config');
        $queuesOptions = $config['slm_queue']['queues'];
        $options = isset($queuesOptions[$requestedName]) ? $queuesOptions[$requestedName] : array();
        $queueOptions = new DoctrineOptions($options);

        /** @var $connection \Doctrine\DBAL\Connection */
        $connection = $parentLocator->get($queueOptions->getConnection());
        $jobPluginManager = $parentLocator->get('SlmQueue\Job\JobPluginManager');

        $queue = new DoctrineQueue($connection, $queueOptions, $requestedName, $jobPluginManager);
        $queue->setServiceLocator($parentLocator);

        return $queue;
    }
}
