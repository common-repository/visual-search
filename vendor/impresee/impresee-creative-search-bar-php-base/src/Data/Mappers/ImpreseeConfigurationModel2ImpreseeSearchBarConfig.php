<?php 
    namespace Impresee\CreativeSearchBar\Data\Mappers;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeConfigurationModel;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBarConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeApplication;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeCatalog;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchByPhoto;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBySketch;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchByText;
    use Impresee\CreativeSearchBar\Domain\Entities\HomeDecorMarket;
    use Impresee\CreativeSearchBar\Domain\Entities\ClothesMarket;
    use Impresee\CreativeSearchBar\Domain\Entities\OtherMarket;
    use Impresee\CreativeSearchBar\Core\Constants\CatalogMarketCodes;

class ImpreseeConfigurationModel2ImpreseeSearchBarConfig {

    public function mapFrom(ImpreseeConfigurationModel $from){ 
        $impresee_data = new ImpreseeSearchBarConfiguration;
        $impresee_data->owner_code = $from->owner_model->owner_code;
        $catalog = new ImpreseeCatalog;
        $apps = [];
        $apps[] = new ImpreseeApplication($from->sketch_app_uuid, new ImpreseeSearchBySketch);
        $apps[] = new ImpreseeApplication($from ->photo_app_uuid, new ImpreseeSearchByPhoto);
        $apps[] = new ImpreseeApplication($from->text_app_uuid, new ImpreseeSearchByText);
        $impresee_data->applications = $apps;
        $catalog->catalog_code = $from->catalog_code;
        $catalog->processed_once = $from->catalog_processed_once;
        switch ($from->catalog_market) {
            case CatalogMarketCodes::HOME_DECOR:
                $catalog->catalog_market = new HomeDecorMarket;
                break;
            case CatalogMarketCodes::APPAREL:
                $catalog->catalog_market = new ClothesMarket;
                break;
            case CatalogMarketCodes::OTHER:
                $catalog->catalog_market = new OtherMarket;
                break;
            default:
                $catalog->catalog_market = new HomeDecorMarket;
                break;
        }
        $catalog->catalog_code = $from->catalog_code;
        $impresee_data->catalog = $catalog;
        return $impresee_data;
    }
    
}