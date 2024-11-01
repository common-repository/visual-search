<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;

interface ImpreseeCatalogStatus {
    
    public function isProcessing();
    public function withError();
}

