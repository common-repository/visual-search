<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Core\Utils\CodesGenerator;

class CodesDataSourceImpl implements CodesDataSource {
    private $code_generator;

    public function __construct(CodesGenerator $code_generator){
        $this->code_generator = $code_generator;
    }

    public function generateNewCode(){
        $new_code = $this->code_generator->generateCode();
        return $new_code;
    }
}