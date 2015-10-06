<?php

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
        
        $dataDir = '/var/www/html/deployment-tools/data/';
        $parckageUrl = $this->params()->fromPost('package_url');
        $testParckageUrl = $this->params()->fromPost('test_package_url');
        $id = $this->params()->fromPost('build_id');
        
        if($id == null){
             return new JsonModel (array('error' => 'no id provided'));
        }

        $filename = $dataDir. 'download/'. $id. '.tar.gz';
        
        
        if($parckageUrl != null) {
            if(is_file($filename)){
                unlink($filename);
            }
            $curl = new Curl();
            $curl->download($parckageUrl,$filename);
        }
        $response =  is_file($filename) && $curl->response ? 'OK' : 'FAIL';
        
        if(is_file($filename)) {
            $tar = new \Archive_Tar($filename, "gz");
            $tar->setErrorHandling(PEAR_ERROR_EXCEPTION);
            $tar->extract($dataDir. 'extract');
            
            
            $buildResult = $this
                ->getServiceLocator()
                ->get('BsbPhingService')
                ->build('help', array(
                    'buildFile' => $dataDir. 'extract/' . $id . '/build.xml',
                    'propertyfile' =>  $dataDir . 'extract/' . $id . '/build.properties'
                ));
        }
        //var_dump($buildResult);
/*         echo 'cmd'. PHP_EOL . $buildResult->getCommandLine();
         echo 'out' . PHP_EOL . $buildResult->getOutput();
         echo 'errorout' . PHP_EOL . $buildResult->getErrorOutput();*/

        
        return new JsonModel (array(
            'package' => $parckageUrl,
            'test' => $testParckageUrl,
            'id' => $id,
            'download' => $response,
            'phingExitCode' => isset($buildResult) ? $buildResult->getExitCodeText() : 'FAIL' ,

        ));
    }
    

}
