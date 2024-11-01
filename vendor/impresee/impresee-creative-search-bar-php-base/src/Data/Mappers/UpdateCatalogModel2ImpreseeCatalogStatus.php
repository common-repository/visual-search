<?php
    namespace Impresee\CreativeSearchBar\Data\Mappers;
    use Impresee\CreativeSearchBar\Data\Models\UpdateCatalogModel;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIsProcessingStatus;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogStatusError;

class UpdateCatalogModel2ImpreseeCatalogStatus {
    
    public function mapFrom(UpdateCatalogModel $model){
        $catalog_status = new CatalogIsProcessingStatus($model->update_url);
        return $catalog_status;
    } 
    public function mapFromException(\Exception $e){
        return new CatalogStatusError;
    }   
}