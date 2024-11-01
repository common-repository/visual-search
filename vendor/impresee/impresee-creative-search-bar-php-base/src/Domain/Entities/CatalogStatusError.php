<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;

class CatalogStatusError implements ImpreseeCatalogStatus  {
    public function isProcessing(){
        return false;
    }
    public function withError(){
        return true;
    }
}
