<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Data\Models\IndexationConfigurationModel;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;

class IndexationConfigurationModelTest extends TestCase {

    public function testCreateDataFromArrayCorrectly(){
        $data_array = array(
            'only_products_with_price' => FALSE,
            'only_products_in_stock'   => FALSE
        );
        $expected_model = new IndexationConfigurationModel;
        $expected_model->only_products_with_price = FALSE;
        $expected_model->only_products_in_stock = FALSE;
        $empty_model = new IndexationConfigurationModel;
        $empty_model->loadDataFromArray($data_array);
        $this->assertEquals(
            $empty_model,
            $expected_model
        );
    }

    public function testCreateDataFromArrayFailsBecauseIncompleteData(){
        $data_array = array(
        );
        $this->expectException(NoDataException::class);
        $empty_model = new IndexationConfigurationModel;
        $empty_model->loadDataFromArray($data_array);
    }

    public function testCreatedataFromArrayOldFormat(){
        $data_array = array(
            'enable_search' => 'enabled',
            'show_products_no_price' => 'enabled',
            'catalog_stock' => 'all'
        );
        $expected_model = new IndexationConfigurationModel;
        $expected_model->only_products_with_price = FALSE;
        $expected_model->only_products_in_stock = FALSE;
        $result = IndexationConfigurationModel::fromOldFormatArray($data_array);
        $this->assertEquals(
            $result,
            $expected_model
        );
    }

    public function testCreatedataFromArrayOldFormatFailsBecauseDataIsIncomplete(){
        $data_array = array(
        );
        $this->expectException(NoDataException::class);
        $result = IndexationConfigurationModel::fromOldFormatArray($data_array);
    }

    public function testToArray(){
        $expected_array = array(
            'only_products_with_price' => FALSE,
            'only_products_in_stock'   => FALSE
        );
        $model = new IndexationConfigurationModel;
        $model->only_products_with_price = FALSE;
        $model->only_products_in_stock = FALSE;
        $result = $model->toArray();
        $this->assertEquals(
            $result,
            $expected_array
        );
    }
}