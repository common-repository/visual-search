<?php 
    namespace Impresee\CreativeSearchBar\Data\Repositories;
    use Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource;
    use Impresee\CreativeSearchBar\Data\Models\ErrorEmailModel;
    use Impresee\CreativeSearchBar\Core\Constants\Project;

abstract class BaseRepository {
    protected $email_datasource;
    protected $project;

    public function __construct(
        EmailDataSource $email_data_source,
        Project $project
    ){  
        $this->email_datasource = $email_data_source;
        $this->project = $project;
    }

    protected function sendErrorEmail(\Throwable $t, String $message, String $store_name){
        $error_code = $t->getMessage();
        $email_data = new ErrorEmailModel(
            $store_name,
            $message.$store_name, 
            $error_code,
            $this->project->getProjectName(),
            $t->getTraceAsString()
        );
        $this->email_datasource->sendErrorEmail($email_data); 
    }
}