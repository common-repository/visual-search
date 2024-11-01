<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Core\Constants\{StorageCodes, Project};
    use Impresee\CreativeSearchBar\Core\Utils\KeyValueStorage;
    use Impresee\CreativeSearchBar\Data\DataSources\ImpreseeLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\BaseLocalStorageDataSource;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Models\IndexationConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Models\OwnerModel;
    use Impresee\CreativeSearchBar\Data\Models\PluginVersionModel;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeSubscriptionStatusModel;
    use Impresee\CreativeSearchBar\Core\Errors\{NoDataException,CouldNotStoreDataException, CouldNotRemoveDataException};

class ImpreseeLocalDataSourceImpl extends BaseLocalStorageDataSource implements ImpreseeLocalDataSource {
    private $storage_codes;
    private $project;

    public function __construct(KeyValueStorage $key_value_storage, StorageCodes $storage_codes, Project $project){
        parent::__construct($key_value_storage);
        $this->storage_codes = $storage_codes;
        $this->project = $project;
    } 

    public function registerImpreseeLocalData(Store $store, ImpreseeConfigurationModel $impresee_data){
        $store_name = $store->getStoreName();
        $key = $this->storage_codes->getImpreseeLocalDataKeyPrefix();
        $this->saveDataToStorage($key, $impresee_data);
    }
    public function getRegisteredImpreseeData(Store $store){
        try {
            $store_name = $store->getStoreName();
            $storage_key = $this->storage_codes->getImpreseeLocalDataKeyPrefix();
            $empty_model = new ImpreseeConfigurationModel;
            return $this->loadDataFromStorage($storage_key, $store_name, $empty_model);
        } catch(NoDataException $e){
            // if old storage format
            $old_data_array = $this->key_value_storage->getValue(
                $this->storage_codes->getOldImpreseeLocalDataKey()
            );
            if (!$old_data_array){
                throw new NoDataException;
            }
            $general_settings = $this->key_value_storage->getValue(
                $this->storage_codes->getOldLocalGeneralSettingsKey()
            );
            $product_type = $general_settings && isset($general_settings['product_type']) ? $general_settings['product_type'] : 'home&decor' ;
            if($product_type == null){
                $product_type = 'home&decor';
            }
            return ImpreseeConfigurationModel::fromArrayOldStorage($old_data_array, $product_type);
        }
        
    }
    public function registerLocalOwner(Store $store, OwnerModel $owner_model){
        $store_name = $store->getStoreName();
        $this->saveDataToStorage($this->storage_codes->getImpreseeLocalDataKeyPrefix(), $owner_model);
    }
    public function getRegisteredOwner(Store $store){
        $store_name = $store->getStoreName();
        $impresee_data_array = $this->key_value_storage->getValue(
            $this->storage_codes->getImpreseeLocalDataKeyPrefix()
        );
        if (!$impresee_data_array){
            $impresee_data_array = $this->key_value_storage->getValue(
                $this->storage_codes->getImpreseeLocalDataKeyPrefix()."{$store_name}"
            );
        }
        if (!$impresee_data_array){
            throw new NoDataException;
        }
        $owner_model = new OwnerModel;
        $owner_model->loadDataFromArray($impresee_data_array);
        return $owner_model;
    }

    public function removeAllLocalData(Store $store){
        $store_name = $store->getStoreName();
        $success = $this->key_value_storage->removeKey(
            $this->storage_codes->getImpreseeLocalDataKeyPrefix()
        );
         $success_old = $this->key_value_storage->removeKey(
            $this->storage_codes->getImpreseeLocalDataKeyPrefix()."{$store_name}"
        );
        $success_indexation_data = $this->key_value_storage->removeKey(
            $this->storage_codes->getLocalIndexationConfigKeyPrefix()
        );
        $success_indexation_data_old = $this->key_value_storage->removeKey(
            $this->storage_codes->getLocalIndexationConfigKeyPrefix()."{$store_name}"
        );
        if ((!$success && !$success_old) || (!$success_indexation_data && !$success_indexation_data_old))
        {
            throw new CouldNotRemoveDataException;
        }
    }

    public function setCatalogProcessedOnce(Store $store){
        $config_data = $this->getRegisteredImpreseeData($store);
        if($config_data->catalog_processed_once){
            return;
        }
        $config_data->catalog_processed_once = TRUE;
        $this->registerImpreseeLocalData($store, $config_data);
    }

    public function setCreatedImpreseeData(Store $store){
        $config_data = $this->getRegisteredImpreseeData($store);
        if($config_data->created_data){
            return;
        }
        $config_data->created_data = TRUE;
        $this->registerImpreseeLocalData($store, $config_data);
    }
    public function setSentCatalogToUpdate(Store $store){
        $config_data = $this->getRegisteredImpreseeData($store);
        if($config_data->send_catalog_to_update_first_time){
            return;
        }
        $config_data->send_catalog_to_update_first_time = TRUE;
        $this->registerImpreseeLocalData($store, $config_data);
    }
    public function setLastCatalogUpdateProcessUrl(Store $store, String $url){
        $config_data = $this->getRegisteredImpreseeData($store);
        $config_data->last_catalog_update_url = $url;
        $this->registerImpreseeLocalData($store, $config_data);
    }

    public function getIndexationConfiguration(Store $store){
        try {
            $store_name = $store->getStoreName();
            $storage_key = $this->storage_codes->getLocalIndexationConfigKeyPrefix();
            $empty_model = new IndexationConfigurationModel;
            return $this->loadDataFromStorage($storage_key, $store_name, $empty_model);
        } catch(NoDataException $e){
            $old_config_data = $this->key_value_storage->getValue(
                $this->storage_codes->getOldLocalGeneralSettingsKey()
            );
            if (!$old_config_data){
                throw new NoDataException;
            }
            return IndexationConfigurationModel::fromOldFormatArray($old_config_data);
        }
    }

    public function updateIndexationConfiguration(Store $store, IndexationConfigurationModel $configuration){
        $store_name = $store->getStoreName();
        $storage_key = $this->storage_codes->getLocalIndexationConfigKeyPrefix();
        try {
            // we compare the old data with the new one, if they're the same we don't store anything
            $old_data = $this->getIndexationConfiguration($store);
            if ($old_data == $configuration){
                return $old_data;
            }
            return $this->saveDataToStorage($storage_key, $configuration);
        }
        catch(NoDataException $e){
            // This means we just need to store de data
            return $this->saveDataToStorage($storage_key, $configuration);
        }
    }

    public function getLocalSubscriptionStatusData(Store $store){
        $store_name = $store->getStoreName();
        $impresee_data_array = $this->key_value_storage->getValue(
            $this->storage_codes->getImpreseeLocalDataKeyPrefix()
        );
        if (!$impresee_data_array){
            $impresee_data_array = $this->key_value_storage->getValue(
                $this->storage_codes->getImpreseeLocalDataKeyPrefix()."{$store_name}"
            );
        }
        if (!$impresee_data_array){
            throw new NoDataException;
        }
        $subscription_status_model = new ImpreseeSubscriptionStatusModel;
        $subscription_status_model->loadDataFromArray($impresee_data_array);
        return $subscription_status_model;
    }

    public function updateLocalSubscriptionStatusData(Store $store, ImpreseeSubscriptionStatusModel $subscription_status){
        $store_name = $store->getStoreName();
        $impresee_data_array = $this->key_value_storage->getValue(
            $this->storage_codes->getImpreseeLocalDataKeyPrefix()
        );
        if(!$impresee_data_array){
            $impresee_data_array = array();
        }
        $final_impresee_data = array_merge($impresee_data_array, $subscription_status->toArray());
        return $this->saveArrayDataToStorage($this->storage_codes->getImpreseeLocalDataKeyPrefix(), $final_impresee_data);
    }

    public function updateStoredPluginVersion(Store $store){
        $store_name = $store->getStoreName();
        $storage_key = $this->storage_codes->getPluginVersionStorageKey();
        $model = new PluginVersionModel;
        $model->version = $this->project->getVersion();
        return $this->saveDataToStorage($storage_key, $model);
    }

    public function getStoredPluginVersion(Store $store){
        $store_name = $store->getStoreName();
        $storage_key = $this->storage_codes->getPluginVersionStorageKey();
        $empty_model = new PluginVersionModel;
        return $this->loadDataFromStorage($storage_key, $store_name, $empty_model);
    }
}