<?php 
    namespace Impresee\CreativeSearchBar\Domain\Repositories;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;

interface StoreRepository  {
    public function getStoreInformation();
}