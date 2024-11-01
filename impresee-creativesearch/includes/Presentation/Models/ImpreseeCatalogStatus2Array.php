<?php

    namespace SEE\WC\CreativeSearch\Presentation\Models;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeCatalogStatus;

class ImpreseeCatalogStatus2Array {

    public static function toArray(ImpreseeCatalogStatus $status){
        return array(
            'processing' => $status->isProcessing(), 
            'has_error'  => $status->withError()
        );
    }
} 