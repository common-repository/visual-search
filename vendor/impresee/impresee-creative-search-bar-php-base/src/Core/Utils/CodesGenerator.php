<?php
    namespace Impresee\CreativeSearchBar\Core\Utils;
    use Ramsey\Uuid\Uuid;

class CodesGenerator {
    
    public function generateCode(){
        $uuid = Uuid::uuid4();
        $new_code = $uuid->toString();
        return $new_code;
    }
}
