<?php
    namespace Impresee\CreativeSearchBar\Data\Models;

class ProductModel {
    public $id;
    public $sku;
    public $name;
    public $url;
    public $price;
    public $price_from;
    public $parent_id;
    public $main_category;
    public $main_image;
    public $thumbnail;
    // Array String
    public $secondary_categories;
    // Array String
    public $secondary_images;
    // Array String => String
    public $extra_attributes;
}