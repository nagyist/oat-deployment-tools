<?php

namespace oat\deploymentsTools\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class DeployController extends AbstractActionController
{
    


    /**
     * Runs example phing file and returns
     *
     * @return ViewModel
     */
    public function runAction()
    {
        
        $dataDir = __DIR__ . '/../../../../data/' ;
        $parckageUrl = $this->params()->fromPost('package_url');
        $testParckageUrl = $this->params()->fromPost('test_package_url');
        $id = $this->params()->fromPost('build_id');
        



        $content = 'package_url : '  . $parckageUrl;
        $content .= 'test_package_url : '  . $testParckageUrl;
        $content .= 'build_id : ' . $id;
        
        file_put_contents($dataDir . 'results.txt',  $content);

        
        $buildResult = $this
            ->getServiceLocator()
            ->get('BsbPhingService')
            ->build('test', array(
                'buildFile' => $dataDir . 'build.xml',
            ));
            
        
        $result = new JsonModel(array(
            'cmd' => $buildResult->getCommandLine(),
            'returnStatus' => $buildResult->getExitCodeText(),
            'output' => $buildResult->getOutput(),
            'error'  => $buildResult->getErrorOutput()
        )); 

        
        return $result;
    }
    

}
