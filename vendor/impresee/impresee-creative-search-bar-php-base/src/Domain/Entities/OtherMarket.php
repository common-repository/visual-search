<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;
    use Impresee\CreativeSearchBar\Core\Constants\CatalogMarketCodes;

class OtherMarket implements CatalogMarket {
    public function toString(): string {
        return CatalogMarketCodes::OTHER;
    }   
}
