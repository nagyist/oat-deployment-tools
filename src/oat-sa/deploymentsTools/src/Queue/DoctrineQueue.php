<?php

namespace oat\deploymentsTools\Queue;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;
use SlmQueueDoctrine\Queue\DoctrineQueue as BaseDoctrineQueue;
use SlmQueueDoctrine\Queue\DoctrineQueueInterface;
use Symfony\Component\Filesystem\Filesystem;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class DoctrineQueue extends BaseDoctrineQueue implements DoctrineQueueInterface, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    protected function purge()
    {

        if ($this->options->getBuriedLifetime() > static::LIFETIME_UNLIMITED) {

            $options = array('delay' => -($this->options->getBuriedLifetime() * 60));
            $buriedLifetime = $this->parseOptionsToDateTime($options);

            $this->cleanLogs($buriedLifetime, static::STATUS_BURIED);

            $delete = 'DELETE FROM ' . $this->options->getTableName() . ' ' .
                'WHERE finished < ? AND status = ? AND queue = ? AND finished IS NOT NULL';

            $this->connection->executeUpdate(
                $delete,
                array($buriedLifetime, static::STATUS_BURIED, $this->getName()),
                array(Type::DATETIME, Type::INTEGER, Type::STRING)
            );
        }

        if ($this->options->getDeletedLifetime() > static::LIFETIME_UNLIMITED) {
            $options = array('delay' => -($this->options->getDeletedLifetime() * 60));
            $deletedLifetime = $this->parseOptionsToDateTime($options);

            $this->cleanLogs($deletedLifetime, static::STATUS_DELETED);

            $delete = 'DELETE FROM ' . $this->options->getTableName() . ' ' .
                'WHERE finished < ? AND status = ? AND queue = ? AND finished IS NOT NULL';

            $this->connection->executeUpdate(
                $delete,
                array($deletedLifetime, static::STATUS_DELETED, $this->getName()),
                array(Type::DATETIME, Type::INTEGER, Type::STRING)
            );
        }
    }

    /**
     * Here we also have to remove working directories for outdated tasks
     * @param $time
     * @param $status
     */
    protected function cleanLogs(\DateTime $time, $status)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getServiceLocator()->getServiceLocator()->get('doctrine.entitymanager.orm_default')->createQueryBuilder();
        $q = $qb->select(array('i'))
            ->from('oat\deploymentsTools\Entity\DeployQueue', 'i')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->lt('i.finished', ':time'),
                    $qb->expr()->eq('i.status', $status),
                    $qb->expr()->eq('i.queue', ':queue'),
                    $qb->expr()->isNotNull('i.finished')
                )
            )
            ->setParameter('time', $time, Type::DATETIME)
            ->setParameter('queue', $this->getName(), Type::STRING)
            ->getQuery();

        $fs = new Filesystem();

        foreach ($q->getResult() as $entry) {
            /** @var \oat\deploymentsTools\Entity\DeployQueue $entry */
            $jobData = $this->unserializeJob($entry->getData());
            $jobName = substr(strrchr(get_class($jobData), "\\"), 1);

            //@TODO move to config
            if ('InstallJob' === $jobName) {
                $buildFolder = isset($jobData->getContent()['buildFolder']) ? $jobData->getContent()['buildFolder'] : null;
                if ($buildFolder) {
                    $fs->remove($buildFolder);
                }
            }
        }
    }
}
