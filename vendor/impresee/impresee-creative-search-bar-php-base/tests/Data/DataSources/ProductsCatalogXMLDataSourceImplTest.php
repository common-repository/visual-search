<?php 
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Data\DataSources\ProductsCatalogXMLDataSourceImpl;
    use Impresee\CreativeSearchBar\Core\Errors\ErrorBuildingCatalog;
    use Impresee\CreativeSearchBar\Core\Constants\Project;
    use Impresee\CreativeSearchBar\Data\Models\ProductModel;

final class ProductsCatalogXMLDataSourceImplTest extends TestCase {
    private $datasource;
    private $catalog_version = '2';

    protected function setUp(): void{
        $project_stub = $this->createMock(Project::class);
        $project_stub->method('getCatalogFormat')
            ->willReturn($this->catalog_version);
        $this->datasource = new ProductsCatalogXMLDataSourceImpl($project_stub);
    }

    public function testGetCorrectImpreseeVersion(){
        $version = $this->datasource->getCatalogVersion();
        $this->assertEquals(
            $this->catalog_version,
            $version
        );
    }

    public function testBuildCorrectCatalog(){
        $product = new ProductModel;
        $product->id = 123;
        $product->sku = "AB123";
        $product->name = "product";
        $product->url = "https://example.com/p";
        $product->price = 20.99;
        $product->price_from = 51;
        $product->parent_id = 1234;
        $product->main_category = "Category";
        $product->thumbnail = "https://example.com/p.jpg";
        $product->main_image = "https://example.com/p.jpg";
        $product->secondary_categories = array('Category 2');
        $product->secondary_images = array("https://example.com/p-1.jpg");
        $product->extra_attributes = array('type' => 'variation');
        $expected_catalog_string = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'."\n".'<feed><product><id><![CDATA[123]]></id><sku><![CDATA[AB123]]></sku><name><![CDATA[product]]></name><url><![CDATA[https://example.com/p]]></url><price><![CDATA[20.99]]></price><price_from><![CDATA[51]]></price_from><parent_id><![CDATA[1234]]></parent_id><main_category><![CDATA[Category]]></main_category><main_image><![CDATA[https://example.com/p.jpg]]></main_image><thumbnail><![CDATA[https://example.com/p.jpg]]></thumbnail><secondary_category0><![CDATA[Category 2]]></secondary_category0><secondary_image0><![CDATA[https://example.com/p-1.jpg]]></secondary_image0><see_type><![CDATA[variation]]></see_type></product></feed>';
        $catalog_string = $this->datasource->generateXmlFromProducts(array($product));
        $this->assertEquals(
            $expected_catalog_string,
            $catalog_string
        );
    }
}