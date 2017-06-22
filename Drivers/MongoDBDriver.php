<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Lexik\Bundle\MaintenanceBundle\Drivers\Query\MongoDB\DefaultQuery;
use Lexik\Bundle\MaintenanceBundle\Drivers\Query\MongoDB\ServerQuery;

/**
 * Class driver for handle MongoDB
 *
 * @package LexikMaintenanceBundle
 * @author  Benoît Wery <benoit@sociallymap.com>
 */
class MongoDBDriver extends AbstractDriver implements DriverTtlInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $mongodb;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var BaseQuery
     */
    protected $queryDriver;

    /**
     * Constructor
     *
     * @param ManagerRegistry $mongodb The registry
     */
    public function __construct(ManagerRegistry $mongodb = null)
    {
        $this->mongodb = $mongodb;
    }

    /**
     * Set options from configuration
     *
     * @param array $options Options
     */
    public function setOptions($options)
    {
        $this->options = $options;

        if (isset($this->options['host'])) {
            $this->queryDriver = new ServerQuery($this->options);
        } else {
            if (isset($this->options['connection'])) {
                $this->queryDriver = new DefaultQuery($this->options, $this->mongodb->getManager($this->options['connection']));
            } else {
                $this->queryDriver = new DefaultQuery($this->options, $this->mongodb->getManager());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createLock()
    {
        try {
            $ttl = null;
            if (isset($this->options['ttl']) && $this->options['ttl'] !== 0) {
                $now = new \Datetime('now');
                $ttl = $this->options['ttl'];
                $ttl = $now->modify(sprintf('+%s seconds', $ttl))->format('Y-m-d H:i:s');
            }
            $status = $this->queryDriver->insertQuery($ttl);
        } catch (\Exception $e) {
            $status = false;
        }

        return $status;
    }

    /**
     * {@inheritdoc}
     */
    protected function createUnlock()
    {
        try {
            $status = $this->queryDriver->deleteQuery();
        } catch (\Exception $e) {
            $status = false;
        }

        return $status;
    }

    /**
     * {@inheritdoc}
     */
    public function isExists()
    {
        $data = $this->queryDriver->selectQuery();

        if (!$data) {
            return null;
        }

        if (null !== $data['ttl']) {
            $now = new \DateTime('now');
            $ttl = new \DateTime($data['ttl']);

            if ($ttl < $now) {
                return $this->createUnlock();
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageLock($resultTest)
    {
        $key = $resultTest ? 'lexik_maintenance.success_lock_mongodb' : 'lexik_maintenance.not_success_lock';

        return $this->translator->trans($key, array(), 'maintenance');
    }

    /**
     * {@inheritDoc}
     */
    public function getMessageUnlock($resultTest)
    {
        $key = $resultTest ? 'lexik_maintenance.success_unlock' : 'lexik_maintenance.not_success_unlock';

        return $this->translator->trans($key, array(), 'maintenance');
    }

    /**
     * {@inheritdoc}
     */
    public function setTtl($value)
    {
        $this->options['ttl'] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getTtl()
    {
        return $this->options['ttl'];
    }

    /**
     * {@inheritdoc}
     */
    public function hasTtl()
    {
        return isset($this->options['ttl']);
    }
}
