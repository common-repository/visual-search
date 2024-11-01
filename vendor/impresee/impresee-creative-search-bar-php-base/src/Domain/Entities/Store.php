<?php 
    namespace Impresee\CreativeSearchBar\Domain\Entities;
class Store {
    public $url;
    public $shop_email;
    public $shop_title;
    public $language;
    public $timezone;
    public $catalog_generation_code;

    public function getStoreName(){
        if (is_null($this->url)){
            return 'store';
        }
        $site_name = str_replace('https://', '', $this->url);
        $site_name = str_replace('http://', '', $site_name);
        $site_name = str_replace('.', '_', $site_name);
        return $site_name;
    }

    public function hasValidUrl(){
        if (is_null($this->url) || strlen($this->url) === 0){
            return false;
        }
        if (strpos($this->url, 'localhost') !== FALSE ||
            strpos($this->url, '127.0.') !== FALSE || 
            strpos($this->url, '192.168.') !== FALSE 
        ){
            return false;
        }
        return true;

    }
}
