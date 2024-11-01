<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;

class CatalogDoneStatus implements ImpreseeCatalogStatus {
    public function isProcessing(){
        return false;
    }
    public function withError(){
        return false;
    }
}
