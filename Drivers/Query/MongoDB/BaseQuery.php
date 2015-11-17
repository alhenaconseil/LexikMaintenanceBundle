<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers\Query\MongoDB;

/**
 * Abstract class to handle MongoDB connection
 *
 * @package LexikMaintenanceBundle
 * @author  Benoît Wery <benoit@sociallymap.com>
 */
abstract class BaseQuery
{
    const DEFAULT_COLLECTION = 'lexik_maintenance';

    /**
     * @var \MongoCollection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * @param array $options Options driver
     */
    public function __construct(array $options)
    {
        if (empty($options['collection'])) {
            $options['collection'] = self::DEFAULT_COLLECTION;
        }

        $this->options = $options;
    }

    /**
     * Get collection (connect if necessary)
     * @return \MongoCollection
     */
    protected function getCollection()
    {
        if (is_null($this->collection)) {
            $this->collection = $this->initDB();
        }

        return $this->collection;
    }

    /**
     * Customization for lock document
     *
     * @return array
     */
    protected function getDocumentCriteria()
    {
        if (empty($this->options['customize_lock_document'])) {
            $customization = [];
        } else {
            $customization = $this->options['customize_lock_document'];
        }

        return $customization;
    }

    /**
     * Result of delete query
     *
     * @return boolean
     */
    public function deleteQuery() {
        return $this->getCollection()->remove($this->getDocumentCriteria(), array('w' => 0));
    }

    /**
     * Result of select query
     *
     * @return array
     */
    public function selectQuery() {
        return $this->getCollection()->findOne($this->getDocumentCriteria());
    }

    /**
     * Result of insert query
     *
     * @param integer $ttl ttl value
     *
     * @return boolean
     */
    public function insertQuery($ttl) {
        $document = $this->getDocumentCriteria();
        $document['ttl'] = $ttl;

        return $this->getCollection()->save($document, array('w' => 0));
    }

    /**
     * Initialize MongoCollection connection
     *
     * @return \MongoCollection
     */
    abstract protected function initDB();

}

