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

namespace oat\deploymentsTools\Controller;

use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class StatusController extends AbstractActionController
{
    public function queueAction()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $entries = $em->getRepository('oat\deploymentsTools\Entity\DeployQueue')->findBy([], ['created' => 'desc']);

        /** @var \SlmQueueDoctrine\Queue\DoctrineQueue $q */
        $q = $this->getServiceLocator()->get('SlmQueue\Queue\QueuePluginManager')->get('deploy');

        array_map(function ($e) use ($q) {
            $e->setQueueManager($q);
        }, $entries);

        /** @var array $config */
        $config = $this->getServiceLocator()->get('Config');
        $deletedLifetime  = $config['slm_queue']['queues']['deploy']['deleted_lifetime'];
        $buriedLifetime   = $config['slm_queue']['queues']['deploy']['buried_lifetime'];

        return new ViewModel(['entries' => $entries, 'deletedLifetime' => $deletedLifetime, 'buriedLifetime' => $buriedLifetime]);
    }

    public function showLogsAction()
    {
        $basePath = realpath(getcwd() . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'build');

        $finder = new Finder();
        /** @var SplFileInfo $file */
        $result = $finder->files()->in($basePath . '*/*/log')->name('*.log');


        return new ViewModel(['result' => $result]);

    }

    public function logAction()
    {
        $targetFile = realpath(rawurldecode($this->params('file')));
        $basePath   = realpath(getcwd() . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'build');

        if (0 !== strpos($targetFile, $basePath) || 'log' !== pathinfo($targetFile, PATHINFO_EXTENSION)) {
            $this->getResponse()->setStatusCode(404);

            return;
        } else {
            $response = $this->getResponse();
            $response->setStatusCode(Response::STATUS_CODE_200);
            $response->getHeaders()->addHeaders(array(
                'Content-Type' => 'text/plain',
            ));

            $response->setContent(file_get_contents($targetFile));

            return $response;
        }


    }
}