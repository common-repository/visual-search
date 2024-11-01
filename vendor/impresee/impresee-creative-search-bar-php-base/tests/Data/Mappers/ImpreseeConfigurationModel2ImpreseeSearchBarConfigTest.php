<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Core\Constants\CatalogMarketCodes;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBarConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeCatalog;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeApplication;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchByPhoto;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBySketch;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchByText;
    use Impresee\CreativeSearchBar\Domain\Entities\ClothesMarket;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Models\OwnerModel;
    use Impresee\CreativeSearchBar\Data\Mappers\ImpreseeConfigurationModel2ImpreseeSearchBarConfig;

class ImpreseeConfigurationModel2ImpreseeSearchBarConfigTest extends TestCase {
    private $mapper;

    protected function setUp(): void{
        $this->mapper = new ImpreseeConfigurationModel2ImpreseeSearchBarConfig;
    }

    public function testMapConfigModelIntoSearchBarConfig(){
        $impresee_stored_model = new ImpreseeConfigurationModel;
        $impresee_stored_model->text_app_uuid = '12345';
        $impresee_stored_model->sketch_app_uuid = '6789';
        $impresee_stored_model->photo_app_uuid = 'abcdre';
        $impresee_stored_model->use_clothing = TRUE;
        $impresee_stored_model->catalog_processed_once = TRUE;
        $impresee_stored_model->catalog_code = 'CATALOG';
        $impresee_stored_model->catalog_market = CatalogMarketCodes::APPAREL;
        $owner_model = new OwnerModel;
        $owner_model->owner_code = 'owner code';
        $catalog_market = new ClothesMarket;
        $impresee_stored_model->owner_model = $owner_model;
        $expected_config = new ImpreseeSearchBarConfiguration;
        $expected_config->owner_code = 'owner code';
        $catalog = new ImpreseeCatalog;
        $catalog->catalog_code = 'CATALOG';
        $catalog->processed_once = TRUE;
        $catalog->catalog_market = new ClothesMarket;
        $expected_config->catalog = $catalog;
        $apps = [];
        $apps[] = new ImpreseeApplication('6789', new ImpreseeSearchBySketch);
        $apps[] = new ImpreseeApplication('abcdre', new ImpreseeSearchByPhoto);
        $apps[] = new ImpreseeApplication('12345', new ImpreseeSearchByText);
        $expected_config->applications = $apps;
        $this->assertEquals(
            $expected_config,
            $this->mapper->mapFrom($impresee_stored_model)
        );
    }

}
