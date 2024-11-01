<?php
namespace SEE\WC\CreativeSearch\Presentation\Utils;
use Impresee\CreativeSearchBar\Core\Constants\Services;
use Impresee\CreativeSearchBar\Core\Constants\Project;

class WooServices implements Services {
    private $project;

    public function __construct(Project $project){
        $this->project = $project;
    }
    
    private function getBaseUrl(){
        return $this->project->getIsDebug() ? 'https://dev2.impresee.com' : 'https://api.impresee.com';
    }

    private function getBaseEmailUrl(){
        return $this->project->getIsDebug() ? 'https://dev2.impresee.com' : 'https://contact.impresee.com';
    }

    public function getConsoleUrl() {
        $base = $this->project->getIsDebug() ? 'https://dev2.impresee.com' : 'https://console.impresee.com';
        return $base."/Console";
    }

    public function getAdminEmailUrl() {
        return $this->getBaseEmailUrl()."/Contact/api/v1/send/admin_message";
    }
    public function getEventEmailUrl(){
        return $this->getBaseEmailUrl()."/Contact/api/v1/send/event_message";
    }
    public function getCreateOwnerUrl(){
        return $this->getBaseUrl()."/ImpreseeAdmin/api/v2/woocommerce/install";
    }
    public function getCreateCatalogUrl(){
        return $this->getBaseUrl()."/ImpreseeAdmin/api/v2/woocommerce/create_catalog/";
    }
    public function getUpdateCatalogUrl(){
        return $this->getBaseUrl()."/ImpreseeAdmin/api/v2/woocommerce/update_catalog/";   
    }
    public function getRemoveDataUrl(){
        return $this->getBaseUrl()."/ImpreseeAdmin/api/v2/woocommerce/uninstall/"; 
    }
    public function getCatalogStatusUrl(){
        return $this->getBaseUrl()."/ImpreseeAdmin/api/v2/woocommerce/catalog_status/";
    }
    public function getPlatformCatalogPath(){
        return '/impresee/v1/catalog/';
    }
    public function getSubscriptionDataUrl(){
        return $this->getBaseUrl()."/ImpreseeAdmin/api/v2/woocommerce/store_information";
    }
    public function getSubscriptionStatusUrl(){
        return $this->getBaseUrl()."/ImpreseeAdmin/api/v2/woocommerce/is_suspended";
    }
    public function getCreateAccountUrl(){
        return $this->getBaseUrl()."/ImpreseeAdmin/api/v2/woocommerce/store_signup_request";
    }
    public function getNotifyChangePluginStatusUrl(){
        return $this->getBaseUrl()."/ImpreseeAdmin/api/v2/woocommerce/";
    }
}