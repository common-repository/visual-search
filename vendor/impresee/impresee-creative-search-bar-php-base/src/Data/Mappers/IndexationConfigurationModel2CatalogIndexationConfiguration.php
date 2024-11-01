<?php 
    namespace Impresee\CreativeSearchBar\Data\Mappers;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;
    use Impresee\CreativeSearchBar\Data\Models\IndexationConfigurationModel;

class IndexationConfigurationModel2CatalogIndexationConfiguration {

    public function mapFrom(IndexationConfigurationModel $from){ 
        $configuration = new CatalogIndexationConfiguration;
        $configuration->show_products_with_no_price = !$from->only_products_with_price;
        $configuration->index_only_in_stock_products = $from->only_products_in_stock;
        return $configuration;
    }

    public function mapTo(CatalogIndexationConfiguration $to){
        $model = new IndexationConfigurationModel;
        $model->only_products_with_price = !$to->show_products_with_no_price;
        $model->only_products_in_stock = $to->index_only_in_stock_products;
        return $model;
    }
    
}