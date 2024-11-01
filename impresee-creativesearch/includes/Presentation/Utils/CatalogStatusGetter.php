<?php
    namespace SEE\WC\CreativeSearch\Presentation\Utils;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogStatusError;
    use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
    use Impresee\CreativeSearchBar\Domain\UseCases\UpdateImpreseeCatalog;
    use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeCatalogState;
    use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfiguration;
    use SEE\WC\CreativeSearch\Presentation\Models\ImpreseeCatalogStatus2Array;

class CatalogStatusGetter {
    private $utils;
    private $get_impresee_data;
    private $get_catalog_state;
    private $update_impresee_catalog;

    public function __construct(
        PluginUtils $utils,
        GetImpreseeConfiguration $get_impresee_config,
        GetImpreseeCatalogState $get_catalog_state,
        UpdateImpreseeCatalog $update_impresee_catalog
    ){
        $this->utils = $utils;
        $this->get_impresee_data = $get_impresee_config;
        $this->get_catalog_state = $get_catalog_state;
        $this->update_impresee_catalog = $update_impresee_catalog;
    }   

    private function getImpreseeData(){
        $store = $this->utils->getStore();
        if ($store == NULL){
            return NULL;
        }
        $impresee_data_promise = $this->get_impresee_data->execute($store);
        $impresee_either = $impresee_data_promise->wait();
        $impresee_data = $impresee_either->either(
            function ($failure) { return NULL; },
            function ($impresee_data) { return $impresee_data; }
        );
        return $impresee_data;
    }

    public function getCatalogState(String $owner_id){
        $impresee_data = $this->getImpreseeData();
        if ($impresee_data == NULL || $impresee_data->owner_code != $owner_id){
            return ImpreseeCatalogStatus2Array::toArray(new CatalogStatusError);
        }
        $store = $this->utils->getStore();
        $impresee_catalog_status_promise = $this->get_catalog_state->execute($impresee_data, $store);
        $impresee_catalog_status = $impresee_catalog_status_promise->wait();
        $catalog_status = $impresee_catalog_status->either(
            function($failure){ return NULL; },
            function($catalog_status) { return $catalog_status; }
        );
        return ImpreseeCatalogStatus2Array::toArray($catalog_status);
    }

    public function updateCatalog(String $owner_id, String $catalog_code){
        $impresee_data = $this->getImpreseeData();
        if ($impresee_data == NULL || $impresee_data->owner_code != $owner_id || $impresee_data->catalog == NULL
            || $impresee_data->catalog->catalog_code != $catalog_code
        ){
            return ImpreseeCatalogStatus2Array::toArray(new CatalogStatusError);
        }
        $store = $this->utils->getStore();
        $update_impresee_catalog_promise = $this->update_impresee_catalog->execute($impresee_data, $store);
        $update_impresee_catalog_status = $update_impresee_catalog_promise->wait();
        $update_status = $update_impresee_catalog_status->either(
            function($failure){ return new CatalogStatusError; },
            function($update_data) { return $update_data; }
        );
        return ImpreseeCatalogStatus2Array::toArray($update_status);
    }
}