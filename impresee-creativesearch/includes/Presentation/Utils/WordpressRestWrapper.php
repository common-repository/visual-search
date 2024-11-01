<?php
    namespace SEE\WC\CreativeSearch\Presentation\Utils;
    use Impresee\CreativeSearchBar\Core\Utils\RestInterface;
    use Impresee\CreativeSearchBar\Core\Constants\Services;

class WordpressRestWrapper implements RestInterface {
    private $services;

    public function __construct(Services $services){
        $this->services = $services;
    }

    public function getRestUrlCatalog(String $catalog_code){
        return get_rest_url(null, $this->services->getPlatformCatalogPath()).$catalog_code.'?page=1';
    }
}