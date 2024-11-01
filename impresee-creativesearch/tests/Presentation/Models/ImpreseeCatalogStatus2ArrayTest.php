<?php
    use PHPUnit\Framework\TestCase;
    use SEE\WC\CreativeSearch\Presentation\Models\ImpreseeCatalogStatus2Array;
    use Impresee\CreativeSearchBar\Domain\Entities\{CatalogIsProcessingStatus, CatalogDoneStatus, CatalogStatusError};

class ImpreseeCatalogStatus2ArrayTest extends TestCase {

    public function testCatalogDoneStatus2Array(){
        $expected_array = array(
            'processing' => FALSE, 
            'has_error'  => FALSE
        );
        $result = ImpreseeCatalogStatus2Array::toArray(new CatalogDoneStatus);
        $this->assertEquals(
            $result,
            $expected_array
        );
    }
    public function testCatalogProcessingStatus2Array(){
        $expected_array = array(
            'processing' => TRUE, 
            'has_error'  => FALSE
        );
        $result = ImpreseeCatalogStatus2Array::toArray(new CatalogIsProcessingStatus('https://example.com'));
        $this->assertEquals(
            $result,
            $expected_array
        );
    }
    public function testCatalogErrorStatus2Array(){
        $expected_array = array(
            'processing' => FALSE, 
            'has_error'  => TRUE
        );
        $result = ImpreseeCatalogStatus2Array::toArray(new CatalogStatusError);
        $this->assertEquals(
            $result,
            $expected_array
        );
    }

   
} 