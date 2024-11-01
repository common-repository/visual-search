<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;


class ImpreseeApplication {
    public $code;
    public $search_type;

    public function __construct(String $code, ImpreseeApplicationType $search_type){
        $this->code = $code;
        $this->search_type = $search_type;
    }
}