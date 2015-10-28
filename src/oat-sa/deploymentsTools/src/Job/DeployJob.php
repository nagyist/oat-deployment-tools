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

use SlmQueue\Worker\WorkerEvent;

class DeployJob extends AbstractJob
{

    public function execute()
    {
        $payload       = $this->getContent();
        $deployService = $this->getServiceLocator()->get('DeployService');


        $result = $deployService->runPhingTask(
            $payload['buildfile'],
            $payload['task'],
            $payload['propertyfile']
        );

        if (!$result['success']){
            return WorkerEvent::JOB_STATUS_FAILURE_RECOVERABLE;
        }

    }
}