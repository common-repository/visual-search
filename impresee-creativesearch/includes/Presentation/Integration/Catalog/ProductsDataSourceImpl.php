<?php
    namespace SEE\WC\CreativeSearch\Presentation\Integration\Catalog;
    use Impresee\CreativeSearchBar\Data\Models\ProductModel;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;
    use Impresee\CreativeSearchBar\Data\DataSources\ProductsDataSource;
    use Impresee\CreativeSearchBar\Core\Utils\LogHandler;

if ( !defined('ABSPATH') ) {
    exit;
}
class ProductsDataSourceImpl implements ProductsDataSource{
  private $log_handler;

  public function __construct(LogHandler $log_handler){
        $this->log_handler = $log_handler;
    } 


  private function validate_get_param($param_name, $default_value) {
    $value = filter_input(INPUT_GET, $param_name, FILTER_VALIDATE_INT);
      if ( !$value || $value == null ){
        $value = $default_value;
      }
      return $value;
  }

  /**
  * Uses WooCommerce to obtain all relevant products, it then parses them
  * @return array of products
  */
  public function getFilteredStoreProducts(Store $store, CatalogIndexationConfiguration $config_data) {
      $hide_products_without_price = !$config_data->show_products_with_no_price;
      $stock_config_include_all = !$config_data->index_only_in_stock_products;
      $page = $this->validate_get_param('page', 1);
      $page_size = $this->validate_get_param('page_size', 500);
      $query = array(
          'status' => 'publish',
          'visibility' => 'catalog',
          'paginate' => true,
          'page' => $page,
          'limit' => $page_size
      );
      if ( !$stock_config_include_all ) {
          $query['stock_status'] = 'instock';
      }
      $products = wc_get_products( $query );
      $total_pages = $products->max_num_pages;
      if ($total_pages < $page) return array();
      $parsed_products = array();
      foreach ( $products->products as $product ) {
        $categories_tags = $this->getTagsAndCategories($product);
        $product_type = $product->get_type();
        $vendor_id =  get_post_field( 'post_author', $product->get_id());
        if(function_exists('dokan_get_store_info')){
          $vendor = dokan_get_store_info($vendor_id);
          $vendor_name = $vendor['store_name'];
        }
        else if (function_exists('wcfmmp_get_store_info') && $vendor_id){
          $vendor = wcfmmp_get_store_info($vendor_id);
          $vendor_name = $vendor['store_name'];
        }
        else {
          $vendor_name = "";
        }
        $product_attributes = $product->get_attributes();
        switch ($product_type) {
          case 'simple':
            $parsed_product = $this->parseSimpleProduct($product, $product_attributes);
            $parsed_products = $this->addProductToList($parsed_products, $parsed_product, $categories_tags, $hide_products_without_price, $vendor_name);
            break;
          case 'variable':
            $parsed_variations = $this->parseVariableProduct($product, $product_attributes);
            foreach ($parsed_variations as $variation) {
              $parsed_products = $this->addProductToList($parsed_products, $variation, $categories_tags, $hide_products_without_price, $vendor_name);
            }
            break;
          case 'external':
            $parsed_product = $this->parseExternalProduct($product, $product_attributes);
            $parsed_products = $this->addProductToList($parsed_products, $parsed_product, $categories_tags, $hide_products_without_price, $vendor_name);
            break;
          case 'grouped':
            $parsed_product = $this->parseGroupedProduct($product, $product_attributes);
            $parsed_products = $this->addProductToList($parsed_products, $parsed_product, $categories_tags, $hide_products_without_price, $vendor_name);
            break;
          default:
            $parsed_product = $this->parseSimpleProduct($product, $product_attributes);
            $parsed_products = $this->addProductToList($parsed_products, $parsed_product, $categories_tags, $hide_products_without_price, $vendor_name);
            $this->log_handler->writeToLog("New kind of product: {$product_type}", LogHandler::IMSEE_LOG_INFO);
            break;
        }
      }
      return $parsed_products;
  }

  /** 
  * Adds a product to the final list
  * @param list of products
  * @param product to be included
  * @param category and tags of said product
  * @param whether a product without price should be included or not
  * @param vendor name
  */ 
  private function addProductToList($products_list, $product, $category_and_tags, $hide_products_without_price, $vendor_name){
    if($hide_products_without_price && (is_null($product['simple_attributes']['price']) || !$product['simple_attributes']['price'])){
      return $products_list;
    }
    $categories = $category_and_tags['categories'];
    $simple_attributes = $product['simple_attributes'];
    $main_category = "";
    $secondary_categories = array();
    $first = true;
    foreach ($categories as $attribute_value) {
        if ($first){
            $main_category = $attribute_value['name'];
            $first = false;
        } else {
            array_push($secondary_categories, $attribute_value['name']);
        }
    }
    $array_attributes = $product['array_attributes'];
    $secondary_images = $array_attributes['secondary_images'];
    $woo_attributes = $array_attributes['woo_attributes'];
    $extra_attributes = array();
    $extra_attributes['vendor'] = $vendor_name;
    $extra_attributes['type'] = $simple_attributes['type'];
    $extra_attributes['status'] = $simple_attributes['status'];
    $extra_attributes['description'] = $simple_attributes['description'];
    $extra_attributes['short_description'] = $simple_attributes['short_description'];
    $extra_attributes['sale_price'] = $simple_attributes['sale_price'];
    $extra_attributes['stock'] = $simple_attributes['stock'];
    $extra_attributes['stock_status'] = $simple_attributes['stock_status'];
    $extra_attributes['main_image_thumb'] = $simple_attributes['main_image_thumb'];
    $extra_attributes['created_at'] = $simple_attributes['created_at'];
    $extra_attributes['modified_at'] = $simple_attributes['modified_at'];
    $tags = $category_and_tags['tags'];
    foreach ($tags as $attribute_key => $attribute_value) {
        $tag_key = "tag_{$attribute_key}";
        $extra_attributes[$tag_key] = $attribute_value;
    }
    foreach ($woo_attributes as $name => $value) {
      $extra_attributes[$name] = $value;
    }
    $product_model = new ProductModel;
    $product_model->id = $simple_attributes['id'];
    $product_model->sku = $simple_attributes['sku'];
    $product_model->name = $simple_attributes['name'];
    $product_model->url = $simple_attributes['url'];
    $product_model->price = $simple_attributes['price'];
    $product_model->price_from = $simple_attributes['price_from'];
    $product_model->parent_id = $simple_attributes['parent_id'];
    $product_model->main_image = $simple_attributes['main_image'];
    $product_model->main_category = $main_category;
    $product_model->secondary_categories = $secondary_categories;
    $product_model->secondary_images = $secondary_images;
    $product_model->extra_attributes = $extra_attributes;
    

    array_push($products_list, $product_model);
    return $products_list; 
  }

  /**
  * Extract tax information for a product
  * @param WC_product product
  * @return array
  */
  private function getTaxInformation($product){
    if ($product->get_tax_status() == 'taxable'){
      $rates = \WC_Tax::get_rates_for_tax_class($product->get_tax_class());
      /*
  Array
(
    [1] => stdClass Object
        (
            [tax_rate_id] => 1
            [tax_rate_country] => 
            [tax_rate_state] => 
            [tax_rate] => 10.0000
            [tax_rate_name] => Tax
            [tax_rate_priority] => 1
            [tax_rate_compound] => 0
            [tax_rate_shipping] => 1
            [tax_rate_order] => 0
            [tax_rate_class] => 
            [postcode_count] => 0
            [city_count] => 0
        )

    [2] => stdClass Object
        (
            [tax_rate_id] => 2
            [tax_rate_country] => US
            [tax_rate_state] => 
            [tax_rate] => 5.0000
            [tax_rate_name] => Tax 2
            [tax_rate_priority] => 2
            [tax_rate_compound] => 0
            [tax_rate_shipping] => 1
            [tax_rate_order] => 1
            [tax_rate_class] => 
            [postcode_count] => 0
            [city_count] => 0
        )
)
      */
      return array('tax' => $rates);
    } 
    return array('tax' => array());
  }

  /**
  * Takes a product and extracts its categories and tags
  * @param WC_Product product
  * @return array
  */
  private function  getTagsAndCategories($product){
    $clean_atag_pattern = '/<a .*href="([^"]+)" .*>(.*)<\/a>/'; 
    $product_id = $product->get_id();
    $tags = wc_get_product_tag_list($product_id, ',');
    $tags_array = empty($tags) ?  array() : explode(',', $tags);
    $clean_tags_array = array();
    foreach ($tags_array as $tag) { 
      preg_match_all($clean_atag_pattern, $tag, $matches);
      if(count($matches) < 3 || count($matches[2]) < 1){
        $this->log_handler->writeToLog("Invalid number of matches when extracting tags: ".count($matches)." ".$tag,
          LogHandler::IMSEE_LOG_WARNING);
        continue;
      }
      $tag_name = $matches[2][0];
      array_push($clean_tags_array, $tag_name);
    }
    // Extracts url and name
    $product_categories = array();
    $categories = wc_get_product_category_list($product_id, ',');
    $categories_array = explode(',', $categories);
    foreach ($categories_array as $category_url) {
      preg_match_all($clean_atag_pattern, $category_url, $matches);
      if(count($matches) < 3 || count($matches[1]) < 1 || count($matches[2]) < 1){
        $this->log_handler->writeToLog("Invalid number of matches when extracting category: ".count($matches)." ".$category_url,
          LogHandler::IMSEE_LOG_WARNING);
        continue;
      }
      $url = $matches[1][0];
      $category_name = $matches[2][0];
      $product_category_label = "/product-category/";
      $product_category_pos = strpos($url, $product_category_label);
      $current_category = array();
      if ($product_category_pos === false){
        $current_category['name'] = $category_name;
        $current_category['code'] = $url;
      }
      else {
        $split_category = explode($product_category_label, $category_url);
        if(count($split_category) < 2){
          continue;
        }
        $category_tree_string = trim($split_category[1], '/');
        $category_tree_array  = explode("/", $category_tree_string);
        $next_category = array();
        foreach ($category_tree_array as $category_element) {
          $current_category = $next_category;
          $next_category = array();
          $current_category['code'] = $category_element;
          $next_category['parent'] = $current_category;
          
        }
        $current_category['name'] = $category_name;
      }
      array_push($product_categories, $current_category);
    } 
    return array(
      'tags' => $clean_tags_array,
      'categories' => $product_categories
    );
  }

  /**
  * Takes a simple product and extracts all data
  * @param WC_Product_Grouped product
  * @return array
  */
  private function parseGroupedProduct($product, $product_attributes){
    $parsed_product = $this->parseSimpleProduct($product, $product_attributes);
    $children = $product->get_children();
    $price = 0;
    foreach ($children as $key => $value) {
      $_product = wc_get_product( $value );
      if (!$_product) continue;
      $price += $_product->get_price();
    }
    $parsed_product['price'] = $price;
    $parsed_product['price_from'] = $price;
    $parsed_product['sale_price'] = $price;
    return $parsed_product;
  }

  /**
  * Takes a simple product and extracts all data
  * @param WC_Product_Variable product
  * @return array
  */
  private function parseVariableProduct($product, $product_attributes){
    $parsed_products = array();
    $variations = $product->get_available_variations('objects');
    foreach ($variations as $variation) {
      $parsed_product = $this->parseVariation($product, $variation, $product_attributes);
      array_push($parsed_products, $parsed_product);
    }
    return $parsed_products; 
  }

  /**
  * Takes a simple product and extracts all data
  * @param WC_Product_External product
  * @return array
  */
  private function parseExternalProduct($product, $product_attributes){
    $parsed_product = $this->parseSimpleProduct($product, $product_attributes);
    return $parsed_product;
  }

  private function parseVariation($product, $variation, $product_attributes){
    if(is_array($variation)){
      $variation_obj = new \WC_Product_variation( $variation['variation_id'] );
      return $this->parseSimpleProduct($variation_obj, $product_attributes);
    }
    return $this->parseSimpleProduct($variation, $product_attributes);
  }

  private function getRealImageUrl($image_url){
    $split_res = preg_split('/(https?):\\/\\//', $image_url, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
    if(count($split_res) == 4){
      return $split_res[2].'://'.$split_res[3];
    }
    return $image_url;
  }

  private function parseProductUrl($product_url){
    $starts_with_protocol = preg_match("#^https?:\/\/(.*)$#i", trim($product_url));
    if ($starts_with_protocol) return $product_url;
    return 'https:'.$product_url;
  }

  /**
  * Takes a simple product and extracts all data
  * @param WC_Product_Simple product
  * @return array
  */
  private function parseSimpleProduct($product, $product_attributes){
    $main_image = $image_id = $product->get_image_id();
    $main_image_url = NULL;
    $main_image_thumb = NULL;
    if ( $main_image != 0 ) {
      $main_image_url = $this->getRealImageUrl(wp_get_attachment_image_url( $main_image,'large' ));
      $main_image_thumb = $this->getRealImageUrl(wp_get_attachment_image_url( $main_image,'medium' ));
    }
    $simple_attributes = array(
      'url'=> $this->parseProductUrl(get_permalink( $product->get_id() )),
      'type' => $product->get_type(),
      'id' => $product->get_id(),
      'name' => $product->get_name(),
      'status' => $product->get_status(),
      'featured' => $product->get_featured(),
      'description' => $product->get_description(),
      'short_description' => $product->get_short_description(),
      'sku' => $product->get_sku(),
      'price' => $product->get_price(),
      'price_from' => $product->get_regular_price(),
      'sale_price' => $product->get_sale_price(),
      'total_sales' => $product->get_total_sales(),
      'manage_stock' => $product->get_manage_stock(),
      'stock' => $product->get_stock_quantity(),
      'stock_status' => $product->get_stock_status(),
      'parent_id' => $product->get_parent_id(),
      'main_image' => $main_image_url,
      'main_image_thumb' => $main_image_thumb,
      'is_virtual' => $product->get_virtual(),
      'is_downloadable' => $product->get_downloadable(),
      'average_rating' => $product->get_average_rating(),
      'number_reviews' => $product->get_review_count(),
      'created_at' => $product->get_date_created(),
      'modified_at' => $product->get_date_modified(),
    );
    $woo_attributes = array();
    foreach ($product_attributes as $attribute) {
      $attribute_name = $attribute->get_name();
      $attribute_value = $product->get_attribute($attribute_name);
      if(!$attribute_value) $attribute_value = '';
      $woo_attributes[str_replace('pa_', '', $attribute_name)] = $attribute_value;
    }
    $images = array();
    $images_thumb = array();
    $gallery_images = $product->get_gallery_image_ids();
    foreach ( $gallery_images as $image_id ) {
      if ( $image_id == $main_image ) {
        continue;
      }
      array_push(
        $images,  
        $this->getRealImageUrl(wp_get_attachment_image_url( $image_id,'large' ))
      );
       array_push(
        $images_thumb,  
        $this->getRealImageUrl(wp_get_attachment_image_url( $image_id,'medium' ))
      );
    }
    $array_attributes = array(
      'secondary_images' => $images,
      'secondary_images_thumb' => $images_thumb,
      'woo_attributes' => $woo_attributes
    );

    return array(
      'simple_attributes' => $simple_attributes,
      'array_attributes' => $array_attributes
    );
  }
}
