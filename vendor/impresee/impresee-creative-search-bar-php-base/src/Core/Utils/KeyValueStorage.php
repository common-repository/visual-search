<?php
    namespace Impresee\CreativeSearchBar\Core\Utils;

interface KeyValueStorage {
    public function saveValue(String $key, $value): bool;
    public function getValue(String $key);
    public function removeKey(String $key);
}