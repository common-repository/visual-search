<?php 
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;
    use Impresee\CreativeSearchBar\Data\Models\IndexationConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Mappers\IndexationConfigurationModel2CatalogIndexationConfiguration;

class IndexationConfigurationModel2CatalogIndexationConfigurationTest extends TestCase {
    private $mapper;

    protected function setUp(): void{
        $this->mapper = new IndexationConfigurationModel2CatalogIndexationConfiguration;
    }

    public function testMapToEntity(){
        $model = new IndexationConfigurationModel;
        $model->only_products_with_price = TRUE;
        $model->only_products_in_stock = FALSE;
        $expected_indexation_config = new CatalogIndexationConfiguration;
        $expected_indexation_config->show_products_with_no_price = FALSE;
        $expected_indexation_config->index_only_in_stock_products = FALSE; 
        $result = $this->mapper->mapFrom($model);
        $this->assertEquals(
            $result,
            $expected_indexation_config
        );
    }

    public function testMapFromEntity(){
        $entity = new CatalogIndexationConfiguration;
        $entity->show_products_with_no_price = FALSE;
        $entity->index_only_in_stock_products = FALSE;
        $expected_model = new IndexationConfigurationModel;
        $expected_model->only_products_with_price = TRUE;
        $expected_model->only_products_in_stock = FALSE; 
        $result = $this->mapper->mapTo($entity);
        $this->assertEquals(
            $result,
            $expected_model
        );
    }

}