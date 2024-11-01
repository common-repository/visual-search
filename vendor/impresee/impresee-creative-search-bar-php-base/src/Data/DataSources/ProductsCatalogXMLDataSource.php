<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;

interface ProductsCatalogXMLDataSource {

    public function getCatalogVersion();
    public function generateXmlFromProducts(Array $products);

}