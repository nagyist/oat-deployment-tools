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

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Curl\Curl;
use PharData;

class DeployController extends AbstractActionController
{
    

    /**
     * Runs example phing file and returns
     *
     * @return ViewModel
     */
    public function runAction()
    {
        $dataDir = realpath('data') . '/build/';
        $parckageUrl = $this->params()->fromPost('package_url');
        $testParckageUrl = $this->params()->fromPost('test_package_url');
        $id = $this->params()->fromPost('build_id');
        $deployService = $this->getServiceLocator()->get('DeployService');
        
        if($id == null){
            return new JsonModel (
                array(
                    'success' => false,
                    'error' => 'no id provided'
                )
            );
        }
        $filename = null;
        if(@mkdir($dataDir . $id )){
           $deployService->setBuildFolder($dataDir . $id);
           $result = $deployService->downloadBuild($parckageUrl, $id);

        } else {
            return new JsonModel (
                    array(
                        'success' => false,
                        'error' => 'Unable to create build folder check privilege or build already exists'
                    )
                );
        }

        if($result['success']) {
            $destination = $dataDir.$id.'/tmp/';
            $result = $deployService->extractBuild($result['filename'], $destination);
        }
        else {
            return  new JsonModel ($result);
        }
      
        if($result['success']) {
            $result = $deployService->runPhingTask(
                $destination . 'build.xml', 
                'help' ,
                 $destination .'build.properties' 
            );
        }
        
        return new JsonModel ($result);
    }
    

}
