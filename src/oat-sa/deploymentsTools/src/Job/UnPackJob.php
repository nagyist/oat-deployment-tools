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
 * @author Mikhail Kamarouski, <kamarouski@1pt.com>
 */

namespace oat\deploymentsTools\Job;

use oat\deploymentsTools\Service\DeployService;
use SlmQueue\Queue\QueueAwareInterface;
use SlmQueue\Worker\WorkerEvent;
use SlmQueue\Job\AbstractJob;
use SlmQueue\Queue\QueueAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class UnPackJob extends AbstractJob implements ServiceLocatorAwareInterface, QueueAwareInterface
{
    use QueueAwareTrait;
    use ServiceLocatorAwareTrait;

    public function execute()
    {
        $payload = $this->getContent();
        /** @var DeployService $deployService */
        $deployService = $this->getServiceLocator()->getServiceLocator()->get('DeployService');

        $result = $deployService->extractBuild($payload['filename'], $payload['destination']);
        $deployService->setBuildFolder($payload['buildFolder']);

        if ( ! $result['success']) {
            return WorkerEvent::JOB_STATUS_FAILURE_RECOVERABLE;
        }

        $result = $deployService->validatePackage($payload);
        $packageInfo = isset($result['packageInfo']) ? $result['packageInfo'] : null;

        if (!$result['success']) {
            $this->getServiceLocator()->get('BuildLogService')->addDebug('incorrect package info provided',
                ['packageInfo' => $packageInfo]);
        }

        $dbProperties = file_get_contents($payload['destination'] . 'db.properties');
        $config = $this->getServiceLocator()->get('config');
        $password = $config['doctrine']['connection']['orm_default']['params']['password']; 
        $dbProperties = str_replace('db.pass=', 'db.pass='. $password, $dbProperties);
        file_put_contents($payload['destination'] . 'db.properties', $dbProperties);

        if ($result['success']) {
            $job = $deployService->isTaoInstalled() ? new BackupJob() : new SyncJob();
            $job->setContent([
                'destination'  => $payload['destination'],
                'buildfile'    => $payload['destination'] . 'build.xml',
                'buildFolder'  => $payload['buildFolder'],
                'packageInfo'  => $packageInfo,
                'buildId'      => $payload['buildId'],

            ]);
            $this->getQueue()->push($job);
        }

    }
}
