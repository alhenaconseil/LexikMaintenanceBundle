<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers\Query\MongoDB;

use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Default Class for handle MongoDB connection through doctrine-odm-bundle
 *
 * @package LexikMaintenanceBundle
 * @author  Benoît Wery <benoit@sociallymap.com>
 */
class DefaultQuery extends BaseQuery
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @param array         $options   Options driver
     * @param EntityManager $dm Entity Manager
     */
    public function __construct(array $options, DocumentManager $dm)
    {
        parent::__construct($options);
        $this->dm = $dm;
    }

    /**
     * {@inheritdoc}
     */
    protected function initDB()
    {
        if (is_null($this->collection)) {
            $databaseName = $this->dm->getConfiguration()->getDefaultDB();
            $collection = $this->dm->getConnection()->selectCollection($databaseName, $this->options['collection']);
            $this->collection = $collection;
        }

        return $this->collection;
    }
}
