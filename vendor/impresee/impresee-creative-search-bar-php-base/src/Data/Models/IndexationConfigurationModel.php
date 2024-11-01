<?php
    namespace Impresee\CreativeSearchBar\Data\Models;
    use Impresee\CreativeSearchBar\Data\Models\Serializable;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;

class IndexationConfigurationModel implements Serializable {
    public $only_products_with_price;
    public $only_products_in_stock;

    public function loadDataFromArray(Array $data){
        if(!isset($data['only_products_with_price']) || !isset($data['only_products_in_stock'])){
            throw new NoDataException;
        }
        $this->only_products_with_price = $data['only_products_with_price'];
        $this->only_products_in_stock = $data['only_products_in_stock'];
    }


    public static function fromOldFormatArray(Array $data){
        if(!isset($data['show_products_no_price']) || !isset($data['catalog_stock'])){
            throw new NoDataException;
        }
        $model = new IndexationConfigurationModel;
        $model->only_products_with_price = $data['show_products_no_price'] == 'disabled';
        $model->only_products_in_stock = $data['catalog_stock'] == 'in_stock';
        return $model;
    }

    public function toArray(){
        return array(
            'only_products_with_price' => $this->only_products_with_price,
            'only_products_in_stock'   => $this->only_products_in_stock
        );
    }
}