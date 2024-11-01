<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;

class CatalogIsProcessingStatus implements ImpreseeCatalogStatus {
    public $processing_url;

    public function __construct($url) {
        $this->processing_url = $url;
    }

    public function isProcessing(){
        return true;
    }

    public function withError(){
        return false;
    }
}

