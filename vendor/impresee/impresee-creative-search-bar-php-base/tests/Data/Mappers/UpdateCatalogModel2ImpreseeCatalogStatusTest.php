<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Core\Constants\ExceptionCodes;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIsProcessingStatus;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogStatusError;
    use Impresee\CreativeSearchBar\Data\Models\UpdateCatalogModel;
    use Impresee\CreativeSearchBar\Data\Mappers\UpdateCatalogModel2ImpreseeCatalogStatus;

class UpdateCatalogModel2ImpreseeCatalogStatusTest extends TestCase {
    private $mapper;

    protected function setUp(): void {
        $this->mapper = new UpdateCatalogModel2ImpreseeCatalogStatus;
    }

    public function testMapUpdateCatalogToProcessingStatus(){
        $update_url = 'http://example.com';
        $update_model = new UpdateCatalogModel;
        $update_model->update_url = $update_url;
        $expected_status = new CatalogIsProcessingStatus($update_url);
        $this->assertEquals(
            $expected_status,
            $this->mapper->mapFrom($update_model)
        );
    }

    public function testMapFailedUpdateCatalogToErrorStatus(){
        $exception = new \Exception(ExceptionCodes::ERROR_SENDING_CATALOG_TO_UPDATE);
        $expected_status = new CatalogStatusError;
        $this->assertEquals(
            $expected_status,
            $this->mapper->mapFromException($exception)
        );
    }
}