<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;
    use Impresee\CreativeSearchBar\Core\Constants\CatalogMarketCodes;

class InvalidMarket implements CatalogMarket {
    private $code;
    
    public function __construct(String $code){
        $this->code = $code;
    }
    public function toString(): string {
        return $code;;
    }
} 
