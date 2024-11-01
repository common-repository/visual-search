<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Core\Utils\KeyValueStorage;
    use Impresee\CreativeSearchBar\Core\Errors\{CouldNotStoreDataException, NoDataException};
    use Impresee\CreativeSearchBar\Data\Models\Serializable;

abstract class BaseLocalStorageDataSource {
    protected $key_value_storage;

    public function __construct(KeyValueStorage $key_value_storage){
        $this->key_value_storage = $key_value_storage;

    }     

    protected function saveDataToStorage(String $key, Serializable $value){
        $array_value = $value->toArray();
        $this->saveArrayDataToStorage($key, $array_value);
        return $value;
    }

    protected function saveArrayDataToStorage(String $key, Array $value){
        $success = $this->key_value_storage->saveValue(
                $key,
                $value
        );
        if (!$success){
            throw new CouldNotStoreDataException;
        }
        return $value;
    }

    protected function loadDataFromStorage(String $key, String $store_name, Serializable $model){
        $impresee_data_array = $this->key_value_storage->getValue($key);
        if(!$impresee_data_array) {
            $impresee_data_array = $this->key_value_storage->getValue($key."{$store_name}");
        }
        if (!$impresee_data_array){
            throw new NoDataException;
        }
        $model->loadDataFromArray($impresee_data_array);
        return $model;
    }
}