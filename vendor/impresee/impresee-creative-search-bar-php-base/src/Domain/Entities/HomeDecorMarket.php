<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;
    use Impresee\CreativeSearchBar\Core\Constants\CatalogMarketCodes;

class HomeDecorMarket implements CatalogMarket {
    public function toString(): string {
        return CatalogMarketCodes::HOME_DECOR;
    }   
}
