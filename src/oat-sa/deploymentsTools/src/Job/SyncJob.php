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
            $payload['propertyfile'],
            $payload['packageInfo']
        );

        if ($result['success']) {
            $job            = new InstallJob();
            $propertyFile   = $this->parseProperties(file_get_contents($payload['destination'] . 'build.properties'));
            $isTaoInstalled = is_file($propertyFile['tao.root'] . '/config/generis.conf.php');
            $job->setContent([
                'task'         => $isTaoInstalled ? 'platform_update' : 'platform_install',
                'buildfile'    => $payload['destination'] . 'build.xml',
                'propertyfile' => $payload['destination'] . 'build.properties',
                'buildFolder'  => $payload['buildFolder'],
                'packageInfo'  => $payload['packageInfo'],
            ]);
            $this->getQueue()->push($job);
        }
    }

    /**
     * @param string $txtProperties
     *
     * @return array
     */
    private function parseProperties($txtProperties)
    {
        $result             = array();
        $lines              = explode("\n", $txtProperties);
        $key                = '';
        $isWaitingOtherLine = false;
        $value              = '';

        foreach ($lines as $i => $line) {
            if (empty( $line ) || ( ! $isWaitingOtherLine && strpos($line, '#') === 0 )) {
                continue;
            }

            if ( ! $isWaitingOtherLine) {
                $key   = substr($line, 0, strpos($line, '='));
                $value = substr($line, strpos($line, '=') + 1, strlen($line));
            } else {
                $value .= $line;
            }
            /* Check if ends with single '\' */
            if (strrpos($value, "\\") === strlen($value) - strlen("\\")) {
                $value              = substr($value, 0, strlen($value) - 1) . "\n";
                $isWaitingOtherLine = true;
            } else {
                $isWaitingOtherLine = false;
            }

            $result[$key] = $value;
            unset( $lines[$i] );
        }

        return $result;
    }

}