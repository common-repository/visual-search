<?php
    namespace Impresee\CreativeSearchBar\Data\Models;

interface Serializable {
    public function toArray();

    public function loadDataFromArray(Array $array);
}