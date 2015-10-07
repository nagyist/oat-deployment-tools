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


namespace oat\deploymentsTools\Service;

use Curl\Curl;
use PharData;

class DeployService 
{
    private $buildFolder = null;

    private $serviceLocator;
    
    public function __construct($serviceLocator){
        $this->serviceLocator = $serviceLocator;
    }
    

    public function downloadBuild($url, $id )
    {
        mkdir($this->getBuildFolder() . '/download/' );
        $filename = $this->getBuildFolder() . '/download/'. $id. '.tar.gz';
        
        if($url != null) {
            if(is_file($filename)){
                unlink($filename);
            }
            $curl = new Curl();
            $curl->download($url,$filename);
        }
        $response =  is_file($filename) && $curl->response ? 'OK' : 'FAIL';
        
        if(is_file($filename) && $curl->response) {
            return array(
                'success' => true,
                'filename' => $filename,
                
            );
        }
        else {
            return array(
                'success' => false,
                'error' =>  $curl->rawResponse
            );
        }
    }
    
    
    public function extractBuild($filename, $destination)
    {
        try {
            if(!is_dir($destination))
            {
                mkdir($destination);
            }
            $tar = new \Archive_Tar($filename, "gz");
            $tar->extract($destination);
        }
        catch(\Exception $e) {
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
        return array(
            'success' => true,
            'destination' => $destination
        );
    }
    
    
    public function runPhingTask($buildFile, $task, $propertyFile = null)
    {
        
        $buildParams = array(
            'buildFile' =>  $buildFile,
        );
        if(!is_null($propertyFile)){
            $buildParams['propertyfile'] = $propertyFile;
        }
        $buildResult = $this->getServiceLocator()
                            ->get('BsbPhingService')
                            ->build($task, $buildParams);
                            
        
        if(isset($buildResult)){
            mkdir($this->getBuildFolder() . '/log/');
            file_put_contents(
                $this->getBuildFolder() . '/log/phing.log', 
                $buildResult->getOutput()
            );
            return array(
                'success' => true,
                'phingExitCode' => $buildResult->getExitCodeText()   
            );
        }
        else {
            return array(
                'success' => false,
                
                    
            );
        }
    }
    
    public function setBuildFolder($buildFolder)
    {
        $this->buildFolder = $buildFolder;    
    }
    
    public function getBuildFolder()
    {
        if($this->buildFolder ==null) {
            throw new \Exception('folder has not been set');
        }
        return $this->buildFolder;
    }
    
    private function getServiceLocator(){
        return $this->serviceLocator;
    }
    
}