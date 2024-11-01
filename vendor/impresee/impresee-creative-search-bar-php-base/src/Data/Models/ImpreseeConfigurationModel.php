<?php
    namespace Impresee\CreativeSearchBar\Data\Models;
    use Impresee\CreativeSearchBar\Data\Models\Serializable;
    use Impresee\CreativeSearchBar\Data\Models\OwnerModel;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;
    use Impresee\CreativeSearchBar\Core\Constants\CatalogMarketCodes;

class ImpreseeConfigurationModel implements Serializable{
    public $text_app_uuid;
    public $sketch_app_uuid;
    public $photo_app_uuid;
    public $owner_model;
    public $use_clothing;
    public $catalog_processed_once;
    public $catalog_code;
    public $catalog_market;
    public $created_data;
    public $send_catalog_to_update_first_time;
    public $last_catalog_update_url;


    public function toArray(){
        $impresee_data = array(
            'text_app_uuid'                => $this->text_app_uuid,
            'sketch_app_uuid'              => $this->sketch_app_uuid,
            'photo_app_uuid'               => $this->photo_app_uuid,
            'catalog_code'                 => $this->catalog_code,
            'catalog_market'               => $this->catalog_market,
            'use_clothing'                 => $this->use_clothing,
            'catalog_processed_once'       => $this->catalog_processed_once,
            'created_data'                 => $this->created_data,
            'send_catalog_to_update_first_time'       => $this->send_catalog_to_update_first_time,
            'last_catalog_update_url'      => $this->last_catalog_update_url   
        );
        return array_merge($impresee_data, $this->owner_model->toArray());
    }

    public function loadDataFromArray(Array $impresee_data){
        if (
            !isset($impresee_data['text_app_uuid']) ||
            !isset($impresee_data['photo_app_uuid']) ||
            !isset($impresee_data['sketch_app_uuid']) ||
            !isset($impresee_data['owner_code']) ||
            !isset($impresee_data['catalog_code']) ||
            !isset($impresee_data['catalog_market']) ||
            !isset($impresee_data['use_clothing']) ||
            !isset($impresee_data['catalog_processed_once']) ||
            !isset($impresee_data['created_data']) ||
            !isset($impresee_data['send_catalog_to_update_first_time']) ||
            !isset($impresee_data['last_catalog_update_url'])
        ){
            throw new NoDataException;
        }
        $this->text_app_uuid = $impresee_data['text_app_uuid'];
        $this->photo_app_uuid = $impresee_data['photo_app_uuid'];
        $this->sketch_app_uuid = $impresee_data['sketch_app_uuid'];
        $owner_model = new OwnerModel;
        $owner_model->owner_code = $impresee_data['owner_code'];
        $this->owner_model = $owner_model;
        $this->catalog_code = $impresee_data['catalog_code'];
        $this->catalog_market = $impresee_data['catalog_market'];
        $this->use_clothing = $impresee_data['use_clothing'];
        $this->catalog_processed_once = $impresee_data['catalog_processed_once'];
        $this->created_data = $impresee_data['created_data'];
        $this->send_catalog_to_update_first_time = $impresee_data['send_catalog_to_update_first_time'];
        $this->last_catalog_update_url = $impresee_data['last_catalog_update_url'];
    }

    public static function fromArrayOldStorage(Array $old_data_array, String $product_type){
            $use_clothing = $product_type == 'apparel';
            $catalog_market = $product_type == 'apparel' ? CatalogMarketCodes::APPAREL : CatalogMarketCodes::HOME_DECOR;
        $owner_model = new OwnerModel;
        $owner_model->owner_code = $old_data_array['owner_code'];
        $config_model = new ImpreseeConfigurationModel;
        $config_model->text_app_uuid = $use_clothing ? $old_data_array['photo_clothing_app_uuid'] : $old_data_array['photo_app_uuid'];
        $config_model->sketch_app_uuid = $old_data_array['sketch_app_uuid'];
        $config_model->photo_app_uuid = $use_clothing ? $old_data_array['photo_clothing_app_uuid'] : $old_data_array['photo_app_uuid'];
        $config_model->owner_model = $owner_model;
        $config_model->use_clothing = $use_clothing;
        $config_model->catalog_processed_once = TRUE;
        $config_model->catalog_code = $old_data_array['impresee_catalog_code'];
        $config_model->catalog_market = $catalog_market;
        $config_model->created_data = TRUE;
        $config_model->send_catalog_to_update_first_time = TRUE;
        $config_model->last_catalog_update_url = '';
        return $config_model;
    }
}