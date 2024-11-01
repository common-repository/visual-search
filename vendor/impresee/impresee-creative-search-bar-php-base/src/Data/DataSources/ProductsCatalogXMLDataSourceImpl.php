<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Core\Errors\ErrorBuildingCatalog;
    use Impresee\CreativeSearchBar\Core\Constants\Project;

class ProductsCatalogXMLDataSourceImpl implements ProductsCatalogXMLDataSource {
    private $project;

    public function __construct(Project $project){
        $this->project = $project;
    }

    public function getCatalogVersion(){
        return $this->project->getCatalogFormat();
    }

    /**
    * Creates an XML representing a products catalog, which follows Impresee's schema
    * @return XML string representing the catalog
    */
    public function generateXmlFromProducts(Array $products){
        $categories = array();
        $images = array();
        $base_xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?><feed></feed>';
        $root = new \SimpleXMLElement($base_xml);
        foreach ( $products as $product ) {
            $product_element = $root->addChild( 'product' );
            // id;
            $element = $product_element->addChild( 'id' );
            $this->addCData($element, $product->id);
            // sku;
            $element = $product_element->addChild( 'sku' );
            $this->addCData($element, $product->sku);
            // name;
            $element = $product_element->addChild( 'name' );
            $this->addCData($element, $product->name);
            // url;
            $element = $product_element->addChild( 'url' );
            $this->addCData($element, $product->url);
            // price;
            $element = $product_element->addChild( 'price' );
            $this->addCData($element, $product->price);
            // price_from;
            $element = $product_element->addChild( 'price_from' );
            $this->addCData($element, $product->price_from);
            // parent_id;
            if ($product->parent_id){

                $element = $product_element->addChild( 'parent_id' );
                $this->addCData($element, $product->parent_id);
            }
            // main_category;
            $element = $product_element->addChild( 'main_category' );
            $this->addCData($element, $product->main_category);
            // main_image;
            $element = $product_element->addChild( 'main_image' );
            $this->addCData($element, $product->main_image);
            // thumbnail;
            $element = $product_element->addChild( 'thumbnail' );
            $this->addCData($element, $product->thumbnail);
            // secondary_categories;
            foreach ($product->secondary_categories as $attribute_key => $attribute_value) {
                $element = $product_element->addChild( "secondary_category{$attribute_key}" );
                $this->addCData($element, $attribute_value);
            }
            // secondary_images;
            foreach ($product->secondary_images as $attribute_key => $attribute_value) {
                $element = $product_element->addChild( "secondary_image{$attribute_key}" );
                $this->addCData($element, $attribute_value);
            }
            // extra_attributes;
            foreach ($product->extra_attributes as $attribute_key => $attribute_value) {
                $element = $product_element->addChild( 'see_'.preg_replace("/[^a-zA-Z0-9]+/", "", $attribute_key) );
                if($element == NULL) continue;
                $this->addCData($element, $attribute_value);
            }
        }
        
        return trim($root->asXML());
    }

    /**
    * Adds cdata text to a node
    */
    private function addCData( $node_element, $cdata_text ) {
        $node = dom_import_simplexml( $node_element );
        if (!$node){
            throw new ErrorBuildingCatalog;   
        } 
        $no   = $node->ownerDocument; 
        $text = $cdata_text;
        if(is_null($cdata_text)){
            $text = "null";
        } else if(!is_null($cdata_text) && is_bool($cdata_text) && !$cdata_text){
            $text = "false";
        }
        $clean_text = preg_replace( "/\r|\n/", " ", $text);
        $node->appendChild( $no->createCDATASection( $clean_text ) ); 
    } 
}