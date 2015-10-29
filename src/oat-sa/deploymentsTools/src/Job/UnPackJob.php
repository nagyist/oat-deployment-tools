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
use SlmQueue\Worker\WorkerEvent;

class UnPackJob extends AbstractJob
{

    public function execute()
    {
        $payload = $this->getContent();
        /** @var DeployService $deployService */
        $deployService = $this->getServiceLocator()->get('DeployService');

        $result = $deployService->extractBuild($payload['filename'], $payload['destination']);

        if ( ! $result['success']) {
            return WorkerEvent::JOB_STATUS_FAILURE_RECOVERABLE;
        }

        $result['success'] = $result['success'] && file_exists($payload['destination'] . 'continuousphp.package');
        $versionFile       = file_get_contents($payload['destination'] . 'continuousphp.package');
        $packageInfo       = json_decode($versionFile, true);
        $result['success'] = $result['success'] && isset( $packageInfo['build_id'] ) && isset( $packageInfo['ref'] ) && isset( $packageInfo['commit'] );

        if ( ! $result['success']){
            $this->getServiceLocator()->get('BuildLogService')->addDebug('incorrect package info provided',
                ['packageInfo' => $packageInfo]);
        }


        if ($result['success']) {
            $job = new BackupJob();
            $job->setContent([
                'destination'  => $payload['destination'],
                'buildfile'    => $payload['destination'] . 'build.xml',
                'propertyfile' => $payload['destination'] . 'build.properties',
                'buildFolder'  => $payload['buildFolder'],
                'packageInfo'  => $packageInfo,
            ]);
            $this->getQueue()->push($job);
        }

    }
}