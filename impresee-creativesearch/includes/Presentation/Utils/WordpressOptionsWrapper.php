<?php
    namespace SEE\WC\CreativeSearch\Presentation\Utils;
    use Impresee\CreativeSearchBar\Core\Utils\KeyValueStorage;
    use Impresee\CreativeSearchBar\Core\Constants\StorageCodes;

class WordpressOptionsWrapper implements KeyValueStorage {
    private $storage_codes;

    public function __construct(StorageCodes $storage_codes){
        $this->storage_codes = $storage_codes;
    }

    public function saveValue(String $key, $value): bool{
        return update_option(
            $key, 
            $value
        );
    }

    public function getValue(String $key){
        switch ($key){
            case $this->storage_codes->getLocaleKey():
                return get_locale(); 
            case $this->storage_codes->getSiteTitleKey():
                return get_bloginfo( 'name' );
            case $this->storage_codes->getTimezoneKey():
                $value = get_option($key);
                if (!$value){
                    $offset = get_option($this->storage_codes->getGMTOffset());
                    if ($offset == 0){
                        return 'UTC';
                    }
                    return 'UTC'.$offset;
                }
            default:
                return get_option($key);
        }
    }   

    public function removeKey(String $key){
        return delete_option($key);
    }
}