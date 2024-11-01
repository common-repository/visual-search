<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;
    use Impresee\CreativeSearchBar\Domain\Entities\SearchBarConfiguration;

class ImpreseeSnippetConfiguration implements SearchBarConfiguration {
    public $general_configuration;
    public $labels_configuration;
    public $search_by_text_configuration;
}