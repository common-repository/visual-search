<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Core\Utils\KeyValueStorage;
    use Impresee\CreativeSearchBar\Core\Utils\RestInterface;
    use Impresee\CreativeSearchBar\Core\Constants\StorageCodes;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotStoreDataException;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotRemoveStoreCodeException;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;

class StoreLocalDataSourceImpl implements StoreLocalDataSource {
    private $rest_interface;
    private $key_value_storage;
    private $storage_codes;

    public function __construct(
        KeyValueStorage $key_value_storage,
        RestInterface $rest_interface,
        StorageCodes $storage_codes
    ){
        $this->rest_interface = $rest_interface;
        $this->key_value_storage = $key_value_storage;
        $this->storage_codes = $storage_codes;
    } 

    public function getStoreUrl(){
        $site_url =  $this->key_value_storage->getValue($this->storage_codes->getSiteHomeKey());
        if (!$site_url){
            throw new NoDataException;
        }
        return $site_url;
    }
    public function getStoreAdminData(){
        return $this->key_value_storage->getValue($this->storage_codes->getUserEmailKey());
    }
    public function getLanguage(){
        return $this->key_value_storage->getValue($this->storage_codes->getLocaleKey());
    }
    public function getSiteTitle(){
        return $this->key_value_storage->getValue($this->storage_codes->getSiteTitleKey());
    }
    public function getTimezone(){
        return  $this->key_value_storage->getValue($this->storage_codes->getTimezoneKey());
    }
    public function getCurrentCatalogGenerationCode(String $store_url){
        $impresee_data = $this->key_value_storage
            ->getValue('see_wccs_impresee_data');
        if (!$impresee_data || !isset($impresee_data['catalog_generation_code'])){
            $stored_code = $this->key_value_storage
                ->getValue($this->storage_codes->getStoreCatalogCodeKeyPrefix());
            if (!$stored_code) {
                $stored_code = $this->key_value_storage
                ->getValue($this->storage_codes->getStoreCatalogCodeKeyPrefix()."{$store_url}");
                
            }
            if (!$stored_code) {
                throw new NoDataException;
            }
            return $stored_code;
        }
        return $impresee_data['catalog_generation_code'];
    }
    public function storeCatalogGenerationCode(String $store_url, String $code){
        $success = $this->key_value_storage->saveValue(
            $this->storage_codes->getStoreCatalogCodeKeyPrefix(),
            $code
        );
        if (!$success){
            throw new CouldNotStoreDataException;
            
        }
    }

    public function removeStoreData(String $store_url){
        $successCode = $this->key_value_storage->removeKey(
            $this->storage_codes->getStoreCatalogCodeKeyPrefix()
        );
        $successCodeOld = $this->key_value_storage->removeKey(
            $this->storage_codes->getStoreCatalogCodeKeyPrefix()."{$store_url}"
        );
        $successOnboarding = $this->key_value_storage->removeKey(
            $this->storage_codes->getStoreFinishedOnboardingKeyPrefix()
        );
        $successOnboardingOld = $this->key_value_storage->removeKey(
            $this->storage_codes->getStoreFinishedOnboardingKeyPrefix()."{$store_url}"
        );
        if ((!$successCode && !$successCodeOld)|| (!$successOnboarding && !$successOnboardingOld)){
            throw new CouldNotRemoveStoreCodeException;
            
        }

    }

    public function setFinishedOnboarding(String $store_url){
        $this->key_value_storage->saveValue(
            $this->storage_codes->getStoreFinishedOnboardingKeyPrefix(),
            TRUE
        );
    }

    public function finishedOnboarding(String $store_url){
        $finished = $this->key_value_storage->getValue(
            $this->storage_codes->getStoreFinishedOnboardingKeyPrefix()
        );
        $finishedOld = $this->key_value_storage->getValue(
            $this->storage_codes->getStoreFinishedOnboardingKeyPrefix()."{$store_url}"
        );
        return $finished || $finishedOld;
    }

    public function getCreateCatalogUrl(String $catalog_code){
        return $this->rest_interface->getRestUrlCatalog($catalog_code);
    }
}