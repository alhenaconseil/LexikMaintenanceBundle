<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers\Query\MongoDB;

/**
 * Class for handle MongoDB connection through a custom connection
 *
 * @package LexikMaintenanceBundle
 * @author  Benoît Wery <benoit@sociallymap.com>
 */
class ServerQuery extends BaseQuery
{
    /**
     * @return string
     */
    protected function buildServerConnectionString()
    {
        $host = empty($this->options['host']) ? null : $this->options['host'];
        $port = empty($this->options['port']) ? null : $this->options['port'];
        $username = empty($this->options['user']) ? null : $this->options['user'];
        $password = empty($this->options['password']) ? null : $this->options['password'];
        $database = empty($this->options['database']) ? null : $this->options['database'];

        if ($username && $password) {
            $credentials = sprintf('%s:%s@', $username, $password);
        } else {
            $credentials = '';
        }
        if ($port) {
            $port = ':' . $port;
        }

        $serverConnectionString = 'mongodb://' . $credentials . $host . $port . '/' . $database;

        return $serverConnectionString;
    }

    /**
     * {@inheritdoc}
     */
    protected function initDB()
    {
        if (is_null($this->collection)) {

            if (!class_exists('MongoClient')) {
                throw new \RuntimeException('You need to enable mongo extension.');
            }

            $serverConnectionString = $this->buildServerConnectionString();
            $databaseName = $this->options['database'];
            $client = new \MongoClient($serverConnectionString);
            $database = $client->selectDB($databaseName);
            $collection = $database->selectCollection($this->options['collection']);
            $this->collection = $collection;
        }

        return $this->collection;
    }
}
