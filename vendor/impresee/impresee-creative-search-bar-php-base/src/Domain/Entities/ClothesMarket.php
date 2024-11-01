<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;
    use Impresee\CreativeSearchBar\Core\Constants\CatalogMarketCodes;

class ClothesMarket implements CatalogMarket {
    public function toString(): string {
        return CatalogMarketCodes::APPAREL;
    }   
}

