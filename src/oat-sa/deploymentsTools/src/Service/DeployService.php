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

use BsbPhingService\Service\PhingService;
use Curl\Curl;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use UnexpectedValueException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class DeployService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /** @var  Logger */
    protected $logger;
    private $buildFolder;

    public function __construct($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function downloadBuild($url, $id)
    {
        if (null === $url) {
            return [
                'success' => false,
                'error'   => 'Url has not been set'
            ];
        }

        /** @var  Logger $logger */
        $logger = $this->getServiceLocator()->get('BuildLogService');
        $logger->addInfo('Download initiated', ['package_url' => $url, 'build_id' => $id]);

        if ( ! is_dir($this->getBuildFolder() . '/download/')) {
            mkdir($this->getBuildFolder() . '/download/');
        }
        $filename = $this->getBuildFolder() . '/download/' . $id . '.tar.gz';
        if (is_file($filename)) {
            unlink($filename);
        }
        $curl = new Curl();
        $curl->download($url, $filename);

        return $curl->response && is_file($filename) ? [
            'success'  => true,
            'filename' => $filename,
        ] : [
            'success' => false,
            'error'   => $curl->rawResponse
        ];
    }


    public function extractBuild($filename, $destination)
    {
        try {
            if ( ! is_dir($destination)) {
                mkdir($destination);
            }
            $tar = new \Archive_Tar($filename, 'gz');
            $tar->extract($destination);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage()
            ];
        }

        return [
            'success'     => true,
            'destination' => $destination
        ];
    }


    public function runPhingTask($buildFile, $task, $propertyFile = null, array $payload = [])
    {

        $buildParams = array(
            'buildFile' => $buildFile,
        );

        if ( ! is_null($propertyFile)) {
            $buildParams['propertyfile'] = $propertyFile;
        }

        /** @var PhingService $BsbPhingService */
        $BsbPhingService = $this->getServiceLocator()->get('BsbPhingService');
        $logger          = $this->getPackageLogger();

        $this->getServiceLocator()->get('BuildLogService')->addInfo(sprintf('Task %s has been started', $task),
            ['package' => $payload]);

        $buildResult = $BsbPhingService->build($task, $buildParams, false);
        $buildResult->run(function ($type, $buffer) use ($logger) {
            $logger->addDebug($buffer);
        });

        if (isset( $buildResult ) && $buildResult->isSuccessful()) {

            return [
                'success'       => true,
                'phingExitCode' => $buildResult->getExitCodeText()
            ];
        } else {
            $this->getServiceLocator()->get('BuildLogService')->addInfo(sprintf('Task %s failed with %s %s', $task,
                $buildResult->getExitCode(), $buildResult->getExitCodeText()));
            return [
                'success' => false,
            ];
        }
    }

    public function setBuildFolder($buildFolder)
    {
        $this->buildFolder = $buildFolder;
    }

    public function getBuildFolder()
    {
        if (null === $this->buildFolder) {
            throw new UnexpectedValueException('folder has not been set');
        }

        return $this->buildFolder;
    }

    /**
     * Set up extra channel per package
     * @return Logger
     */
    protected function getPackageLogger()
    {
        if ( ! $this->logger) {
            /** @var  Logger $logger */
            $logger  = new Logger('Phing');
            $handler = (new RotatingFileHandler($this->getBuildFolder() . '/log/phing.log'))
                ->setFormatter(new LineFormatter());
            $logger->pushHandler($handler);
            $this->logger = $logger;
        }


        return $this->logger;
    }

}