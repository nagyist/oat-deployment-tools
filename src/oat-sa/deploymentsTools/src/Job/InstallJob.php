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
use SlmQueue\Job\AbstractJob;
use SlmQueue\Queue\QueueAwareInterface;
use SlmQueue\Queue\QueueAwareTrait;
use SlmQueue\Worker\WorkerEvent;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class InstallJob extends AbstractJob implements ServiceLocatorAwareInterface, QueueAwareInterface
{

    use QueueAwareTrait;
    use ServiceLocatorAwareTrait;

    public function execute()
    {
        $payload = $this->getContent();
        /** @var DeployService $deployService */
        $sl = $this->getServiceLocator()->getServiceLocator();
        $deployService = $sl->get('DeployService');
        $deployService->setBuildFolder($payload['buildFolder']);


        $result = $deployService->runPhingTask(
            $payload['buildfile'],
            $payload['task'],
            null,
            $payload['packageInfo']
        );

        $notificator = $sl->has('Slack') ? $sl->get('Slack') : $sl->get('BuildLogService');
        $ref   = $deployService->getPackageInfo($deployService->getSrcFolder())['ref'];

        if (!$result['success']) {
            $notificator->addError(sprintf('Delivery of %s failed to  %s', $ref, $deployService->getTaoUri()));

            return WorkerEvent::JOB_STATUS_FAILURE_RECOVERABLE;
        } else {
            $notificator->addInfo(sprintf('%s successfully delivered to  %s', $ref, $deployService->getTaoUri()));
        }

    }
}