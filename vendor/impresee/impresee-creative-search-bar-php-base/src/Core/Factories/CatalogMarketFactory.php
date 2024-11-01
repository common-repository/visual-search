<?php
    namespace Impresee\CreativeSearchBar\Core\Factories;
    use Impresee\CreativeSearchBar\Core\Constants\CatalogMarketCodes;
    use Impresee\CreativeSearchBar\Domain\Entities\HomeDecorMarket;
    use Impresee\CreativeSearchBar\Domain\Entities\OtherMarket;
    use Impresee\CreativeSearchBar\Domain\Entities\ClothesMarket;
    use Impresee\CreativeSearchBar\Domain\Entities\InvalidMarket;

class CatalogMarketFactory {
    public static function createMarket(String $market_code){
        switch($market_code){
            case CatalogMarketCodes::HOME_DECOR:
                return new HomeDecorMarket;
            case CatalogMarketCodes::OTHER:
                return new OtherMarket;
            case CatalogMarketCodes::APPAREL:
                return new ClothesMarket;
            default:
                return new InvalidMarket($market_code);
        }
    }
}