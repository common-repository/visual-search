<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Core\Constants\StorageCodes;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Core\Utils\KeyValueStorage;
    use Impresee\CreativeSearchBar\Data\DataSources\SearchBarDisplayLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\BaseLocalStorageDataSource;
    use Impresee\CreativeSearchBar\Data\Models\CustomCodeModel;
    use Impresee\CreativeSearchBar\Data\Models\SearchBarDisplayConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeSnippetConfigurationModel;
    use Impresee\CreativeSearchBar\Core\Errors\{NoDataException, CouldNotRemoveDataException};

class SearchBarDisplayLocalDataSourceImpl extends BaseLocalStorageDataSource implements SearchBarDisplayLocalDataSource {
    private $storage_codes;

    public function __construct(KeyValueStorage $key_value_storage, StorageCodes $storage_codes){
        parent::__construct($key_value_storage);
        $this->storage_codes = $storage_codes;
    } 


    public function getLocalCustomCodeConfiguration(Store $store){
        $store_name = $store->getStoreName();
        $storage_key = $this->storage_codes->getLocalCustomCodeSettingsKeyPrefix();
        $code_model = new CustomCodeModel;
        try {
            return $this->loadDataFromStorage($storage_key, $store_name, $code_model);
        } catch (NoDataException $e){
            $old_format_array = $this->key_value_storage->getValue(
                $this->storage_codes->getOldLocalAdvancedSettingsKey()
            );
            if (!$old_format_array){
                throw new NoDataException;
            }
            return CustomCodeModel::fromOldArray($old_format_array);
        }
    }

    public function updateLocalCustomCodeConfiguration(Store $store, CustomCodeModel $configuration){
        $store_name = $store->getStoreName();
        $storage_key = $this->storage_codes->getLocalCustomCodeSettingsKeyPrefix(); 
        try {
            // we compare the old data with the new one, if they're the same we don't store anything
            $old_data = $this->getLocalCustomCodeConfiguration($store);
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

    public function getLocalImpreseeSnippetConfiguration(Store $store){
        $snippet_model = new ImpreseeSnippetConfigurationModel;
        try {
            $store_name = $store->getStoreName();
            $storage_key = $this->storage_codes->getLocalSnippetSettingsKeyPrefix();
            return $this->loadDataFromStorage($storage_key, $store_name, $snippet_model);
        } catch (NoDataException $e){
            $old_format_array = $this->key_value_storage->getValue(
                $this->storage_codes->getOldSnippetConfigKey()
            );
            if (!$old_format_array){
                throw new NoDataException;
            }
            $snippet_model->loadFromOldStorageArray($old_format_array);
            return $snippet_model;
        }
    }

    public function updateLocalImpreseeSnippetConfiguration(Store $store, ImpreseeSnippetConfigurationModel $configuration){
        $store_name = $store->getStoreName();
        $storage_key = $this->storage_codes->getLocalSnippetSettingsKeyPrefix(); 
        try {
            // we compare the old data with the new one, if they're the same we don't store anything
            $old_data = $this->getLocalImpreseeSnippetConfiguration($store);
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

    public function removeCustomCodeLocalData(Store $store){
        $store_name = $store->getStoreName();
        $storage_key_custom_code = $this->storage_codes->getLocalCustomCodeSettingsKeyPrefix();
        $success_remove_code = $this->key_value_storage->removeKey(
            $storage_key_custom_code
        );
        $success_remove_code_old = $this->key_value_storage->removeKey(
            $storage_key_custom_code."{$store_name}"
        );
        
        
        if (!$success_remove_code && !$success_remove_code_old){
            throw new CouldNotRemoveDataException;
        }
    }

    public function removeSnippetLocalData(Store $store){
        $store_name = $store->getStoreName();
        $storage_key_snippet = $this->storage_codes->getLocalSnippetSettingsKeyPrefix(); 
        
        $success_remove_snippet = $this->key_value_storage->removeKey(
            $storage_key_snippet
        );
        $success_remove_snippet_old_format = $this->key_value_storage->removeKey(
            $storage_key_snippet.$store_name
        );
        if (!$success_remove_snippet && !$success_remove_snippet_old_format){
            throw new CouldNotRemoveDataException;
        }
    }

}