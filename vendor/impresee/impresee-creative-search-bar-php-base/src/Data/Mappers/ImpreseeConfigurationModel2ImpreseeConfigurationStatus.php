<?php 
    namespace Impresee\CreativeSearchBar\Data\Mappers;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeConfigurationStatus;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeConfigurationModel;

class ImpreseeConfigurationModel2ImpreseeConfigurationStatus {

    public function mapFrom(ImpreseeConfigurationModel $from){ 
        $impresee_data = new ImpreseeConfigurationStatus;
        $impresee_data->created_data = $from->created_data;
        $impresee_data->sent_catalog_to_update = $from->send_catalog_to_update_first_time;
        $impresee_data->last_catalog_update_url = $from->last_catalog_update_url;
        $impresee_data->catalog_processed_once = $from->catalog_processed_once;
        return $impresee_data;
    }
    
}