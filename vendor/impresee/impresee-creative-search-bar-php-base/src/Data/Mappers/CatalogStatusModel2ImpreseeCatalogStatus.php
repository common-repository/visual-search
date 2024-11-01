<?php
    namespace Impresee\CreativeSearchBar\Data\Mappers;
    use Impresee\CreativeSearchBar\Data\Models\CatalogStatusModel;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIsProcessingStatus;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogDoneStatus;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogStatusError;

class CatalogStatusModel2ImpreseeCatalogStatus {
    
    public function mapFrom(CatalogStatusModel $model){
        if ($model->processing){
            $catalog_status = new CatalogIsProcessingStatus($model->update_url);
            return $catalog_status;
        } else {
            return new CatalogDoneStatus;
        }
    } 

    public function mapFromException(\Exception $e){
        return new CatalogStatusError;
    }    
}