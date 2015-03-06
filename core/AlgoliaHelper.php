<?php namespace Algolia\Core;

class AlgoliaHelper
{
    private $algolia_client;
    private $algolia_registry;
    private $validCredential;

    public function __construct($app_id, $search_key, $admin_key)
    {
        try
        {
            $this->algolia_client = new \AlgoliaSearch\Client($app_id, $admin_key);

            /* Check app_id && admin_key => Exception thrown if not working */
            $this->algolia_client->listIndexes();

            /* Check search_key_rights */
            $keys_values = $this->algolia_client->getUserKeyACL($search_key);

            if ( ! ($keys_values && isset($keys_values['acl']) && in_array('search', $keys_values['acl'])))
                throw new \Exception("Search key does not have search right");

            $this->validCredential = true;
        }
        catch(\Exception $e)
        {
            $this->validCredential = false;
        }

        $this->algolia_registry = \Algolia\Core\Registry::getInstance();
    }

    public function handleIndexCreation()
    {
        $indexable_tax      = $this->algolia_registry->indexable_tax;
        $created_indexes    = $this->algolia_client->listIndexes();

        $index_name = $this->algolia_registry->index_name;

        $indexes = array();

        if (isset($indexes["items"]))
        {
            $indexes = array_map(function ($obj) {
                return $obj["name"];
            }, $created_indexes["items"]);
        }

        foreach ($indexable_tax as $tax)
        {
            if (in_array($index_name."_".$tax, $indexes) == false)
            {
                $index = $this->algolia_client->initIndex($index_name."_".$tax);
                $index->setSettings(array("attributesToRetrieve" => array("objectID")));
            }
        }
    }

    public function validCredential()
    {
        return $this->validCredential;
    }

    public function pushObjects($index_name, $objects)
    {
        $index = $this->algolia_client->initIndex($index_name);

        $index->saveObjects($objects);
    }

    public function cleanIndex($index_name)
    {
        $index = $this->algolia_client->initIndex($index_name);

        $index->clearIndex();
    }

    public function pushObject($index_name, $object)
    {
        $index = $this->algolia_client->initIndex($index_name);

        $index->saveObject($object);
    }

    public function deleteObject($index_name, $object)
    {
        $index = $this->algolia_client->initIndex($index_name);

        $index->deleteObject($object);
    }


    public function deleteObjects($index_name, $objects)
    {
        $index = $this->algolia_client->initIndex($index_name);

        $index->deleteObjects($objects);
    }
}