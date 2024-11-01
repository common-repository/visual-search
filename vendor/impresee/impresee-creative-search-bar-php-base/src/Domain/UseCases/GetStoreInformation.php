<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Repositories\StoreRepository;

class GetStoreInformation{
    private $store_repository;

    public function __construct(StoreRepository $store_repository){
        $this->store_repository = $store_repository;
    }

    public function execute(){
        return $this->store_repository->getStoreInformation();
    }
}