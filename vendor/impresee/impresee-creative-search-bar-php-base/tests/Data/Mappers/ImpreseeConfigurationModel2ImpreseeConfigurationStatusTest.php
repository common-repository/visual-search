<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Core\Constants\CatalogMarketCodes;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeConfigurationStatus;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Mappers\ImpreseeConfigurationModel2ImpreseeConfigurationStatus;

class ImpreseeConfigurationModel2ImpreseeConfigurationStatusTest extends TestCase {
    private $mapper;

    protected function setUp(): void{
        $this->mapper = new ImpreseeConfigurationModel2ImpreseeConfigurationStatus;
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
        $impresee_stored_model->created_data = TRUE;
        $impresee_stored_model->send_catalog_to_update_first_time = TRUE;
        $impresee_stored_model->last_catalog_update_url = 'http://example.com';
        $expected_config = new ImpreseeConfigurationStatus;
        $expected_config->created_data = TRUE;
        $expected_config->sent_catalog_to_update = TRUE;
        $expected_config->last_catalog_update_url = 'http://example.com';
        $expected_config->catalog_processed_once = TRUE;
        $this->assertEquals(
            $expected_config,
            $this->mapper->mapFrom($impresee_stored_model)
        );
    }

}
