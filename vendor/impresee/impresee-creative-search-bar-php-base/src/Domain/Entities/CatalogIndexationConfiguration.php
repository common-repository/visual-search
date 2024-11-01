<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;
    use Impresee\CreativeSearchBar\Domain\Entities\SearchBarConfiguration;

class CatalogIndexationConfiguration implements SearchBarConfiguration {
    public $show_products_with_no_price;
    public $index_only_in_stock_products;
}