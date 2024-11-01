<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Data\Models\OwnerModel;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;


class OwnerModelTest extends TestCase {

    public function testOwnerModelFromArray(){
        $array = array(
            'text_app_uuid'           => 'text uuid',
            'sketch_app_uuid'         => 'sketch uuid',
            'photo_app_uuid'          => 'photo uuid',
            'owner_code'              => 'owner code',
            'catalog_code'            => 'catalog code',
            'catalog_market'          => 'market',
            'use_clothing'            => TRUE
        );
        $expected_model = new OwnerModel;
        $expected_model->owner_code = 'owner code';
        $empty_model = new OwnerModel;
        $empty_model->loadDataFromArray($array);
        $this->assertEquals(
            $expected_model,
            $empty_model
        );
    }

    public function testOwnerModelFromArrayFails(){
        $array = array();
        $this->expectException(NoDataException::class);
        $empty_model = new OwnerModel;
        $empty_model->loadDataFromArray($array);
    }

    public function testOwnerModelFromArrayFailsPartialData(){
        $array = array(
            'text_app_uuid'           => 'text uuid',
            'sketch_app_uuid'         => 'sketch uuid',
            'photo_app_uuid'          => 'photo uuid',
            'catalog_code'            => 'catalog code',
            'catalog_market'          => 'market',
            'use_clothing'            => TRUE
        );
        $this->expectException(NoDataException::class);
        $empty_model = new OwnerModel;
        $empty_model->loadDataFromArray($array);
    }

    public function testOwnerModelToArray(){
        $expected_array  = array(
            'owner_code' => 'owner code'
        );
        $model = new OwnerModel;
        $model->owner_code = 'owner code';
        $this->assertEquals(
            $expected_array,
            $model->toArray()
        );
    }
    
}