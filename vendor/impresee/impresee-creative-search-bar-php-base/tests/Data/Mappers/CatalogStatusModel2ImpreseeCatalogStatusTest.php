<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIsProcessingStatus;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogDoneStatus;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogStatusError;
    use Impresee\CreativeSearchBar\Data\Models\CatalogStatusModel;
    use Impresee\CreativeSearchBar\Data\Mappers\CatalogStatusModel2ImpreseeCatalogStatus;

class CatalogStatusModel2ImpreseeCatalogStatusTest extends TestCase {
    private $mapper;

    protected function setUp(): void {
        $this->mapper = new CatalogStatusModel2ImpreseeCatalogStatus;
    }

    public function testMapProcessingStatus(){
        $update_url = 'http://example.com';
        $status_model = new CatalogStatusModel;
        $status_model->processing = TRUE;
        $status_model->update_url = $update_url;
        $expected_status = new CatalogIsProcessingStatus($update_url);
        $this->assertEquals(
            $expected_status,
            $this->mapper->mapFrom($status_model)
        );
    }

    public function testMapDoneStatus(){
        $status_model = new CatalogStatusModel;
        $status_model->processing = FALSE;
        $status_model->update_url = '';
        $expected_status = new CatalogDoneStatus;
        $this->assertEquals(
            $expected_status,
            $this->mapper->mapFrom($status_model)
        );
    }

    public function testMapErrorStatus(){
        $exception = new \Exception;
        $expected_status = new CatalogStatusError;
        $this->assertEquals(
            $expected_status,
            $this->mapper->mapFromException($exception)
        );
    }
}