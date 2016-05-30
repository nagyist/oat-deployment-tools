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

namespace oat\deploymentsTools\Controller;

use oat\deploymentsTools\Job\RestoreJob;
use oat\deploymentsTools\Job\UnPackJob;
use oat\deploymentsTools\Service\DeployService;
use SlmQueue\Queue\QueueAwareInterface;
use SlmQueue\Queue\QueueAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class DeployController extends AbstractActionController implements QueueAwareInterface
{
    use QueueAwareTrait;

    /**
     * @return DeployService
     */
    protected function getDeployService()
    {
        return $this->getServiceLocator()->get('DeployService');
    }

    /**
     * Restores state from backup
     *
     * @return JsonModel
     */
    public function restoreAction()
    {
        $buildFolder = realpath(rawurldecode($this->params()->fromRoute('dir')));
        $this->getDeployService()->setBuildFolder($buildFolder);

        $job = new RestoreJob();
        $job->setContent([
            'buildFolder' => $this->getDeployService()->getSrcFolder(),
            'buildfile'   => $this->getDeployService()->getSrcFolder() . 'build.xml',
            'buildId'     => $this->getDeployService()->getPackageInfo($this->getDeployService()->getSrcFolder())['build_id'],
        ]);
        $this->queue->push($job);

        return new JsonModel ([]);
    }

    /**
     * Runs example phing file and returns
     *
     * @return JsonModel
     */
    public function runAction()
    {
        $packageUrl = $this->params()->fromPost('package_url');
        $id         = $this->params()->fromPost('build_id');

        if (null === $id) {
            return new JsonModel ([
                'success' => false,
                'error'   => 'no id provided'
            ]);
        }

        $deployService = $this->getDeployService();

        $dataDir = realpath('data') . '/build/';

        if (is_writable($dataDir) && !is_dir($dataDir . $id) && mkdir($dataDir . $id)) {
            $deployService->setBuildFolder($dataDir . $id);
            $result = $deployService->downloadBuild($packageUrl, $id);
        } else {
            return new JsonModel ([
                'success' => false,
                'error' => 'Unable to create build folder check privilege or build already exists'
            ]);
        }
        if ($result['success']) {

            $job = new UnPackJob();
            $job->setContent([
                'filename' => $result['filename'],
                'destination' => $deployService->getSrcFolder(),
                'buildFolder' => $deployService->getBuildFolder(),
                'buildId'     => $id,
            ]);
            $this->queue->push($job);
        } else {
            return new JsonModel ($result);
        }

        return new JsonModel ([
            'success' => true,
            'text' => 'Deployment job has been scheduled'
        ]);
    }

}
