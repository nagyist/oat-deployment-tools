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

class SyncJob extends AbstractJob
{

    public function execute()
    {
        $payload = $this->getContent();
        /** @var DeployService $deployService */
        $deployService = $this->getServiceLocator()->get('DeployService');
        $deployService->setBuildFolder($payload['buildFolder']);

        $result = $deployService->runPhingTask(
            $payload['buildfile'],
            'sync_package',
            null,
            $payload['packageInfo']
        );

        if ($result['success']) {
            $job            = new InstallJob();
            $isTaoInstalled = $deployService->isTaoInstalled();
            $job->setContent([
                'task'         => $isTaoInstalled ? 'platform_update' : 'platform_install',
                'buildfile'    => $payload['destination'] . 'build.xml',
                'buildFolder'  => $payload['buildFolder'],
                'packageInfo'  => $payload['packageInfo'],
            ]);
            $this->getQueue()->push($job);
        }
    }

}