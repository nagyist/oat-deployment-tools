<?php

namespace oat\deploymentsTools\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ConsoleModel;
use Zend\View\Model\ViewModel;

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
            
        
        
        $view = new ViewModel();
        $view->setVariable('process', $buildResult);

        return $view;
    }
    
    public function helpAction()
    {
        $model = new ConsoleModel();
        
        $model->setResult('No application found' . PHP_EOL);
        
        return $model;
    }
}
