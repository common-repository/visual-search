<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Core\Constants\StorageCodes;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Core\Utils\KeyValueStorage;
    use Impresee\CreativeSearchBar\Data\DataSources\HolidayConfigurationLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\BaseLocalStorageDataSource;
    use Impresee\CreativeSearchBar\Data\Models\HolidayConfigurationModel;
    use Impresee\CreativeSearchBar\Core\Errors\{NoDataException, CouldNotRemoveDataException};

class HolidayConfigurationLocalDataSourceImpl extends BaseLocalStorageDataSource implements HolidayConfigurationLocalDataSource {
    private $storage_codes;
    
    public function __construct(KeyValueStorage $key_value_storage, StorageCodes $storage_codes){
        parent::__construct($key_value_storage);
        $this->storage_codes = $storage_codes;
    } 


    public function getLocalHolidayConfiguration(Store $store){
        $store_name = $store->getStoreName();
        $storage_key = $this->storage_codes->getLocalHolidayConfigKeyPrefix();
        $holiday_model = new HolidayConfigurationModel;
        return $this->loadDataFromStorage($storage_key, $store_name, $holiday_model);
    }

    public function updateLocalHolidayConfiguration(Store $store, HolidayConfigurationModel $configuration){
        $store_name = $store->getStoreName();
        $storage_key = $this->storage_codes->getLocalHolidayConfigKeyPrefix(); 
        try {
            // we compare the old data with the new one, if they're the same we don't store anything
            $old_data = $this->getLocalHolidayConfiguration($store);
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

    public function removeLocalHolidayConfiguration(Store $store){
        $store_name = $store->getStoreName();
        $storage_key_snippet = $this->storage_codes->getLocalHolidayConfigKeyPrefix(); 
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