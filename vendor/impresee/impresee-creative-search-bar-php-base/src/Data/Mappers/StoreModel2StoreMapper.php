<?php
    namespace Impresee\CreativeSearchBar\Data\Mappers;
    use Impresee\CreativeSearchBar\Data\Models\StoreModel;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;

class StoreModel2StoreMapper {

    public function mapFrom(StoreModel $from){
        $store = new Store;
        $store->url = $from->url;
        $store->shop_email = $from->admin_email;
        $store->shop_title = $from->site_title;
        $store->timezone = $from->timezone;
        $store->language = $from->language;
        $store->catalog_generation_code = $from->catalog_generation_code;
        return $store;
    }
    public function mapTo(Store $to){
        $store_model = new StoreModel;
        $store_model->url = $to->url;
        $store_model->site_title = $to->shop_title;
        $store_model->admin_email = $to->shop_email;
        $store_model->timezone = $to->timezone;
        $store_model->language = $to->language;
        $store_model->catalog_generation_code = $to->catalog_generation_code;
        return $store_model;
    }
}