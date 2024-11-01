<?php
    namespace SEE\WC\CreativeSearch\Presentation\Utils;
    use Impresee\CreativeSearchBar\Core\Utils\LogHandler;
    use Impresee\CreativeSearchBar\Core\Constants\Project;

class WordpressLogHandler implements LogHandler{
    private $logs_file;
    private $project;

    public function __construct(Project $project){
        $this->project = $project;
        $uploads  = wp_upload_dir( null, false );
        $logs_dir = $uploads['basedir'] . '/impresee-creativesearch-logs';

        if ( ! is_dir( $logs_dir ) ) {
            mkdir( $logs_dir, 0755, true );
        }

        $this->logs_file =  $logs_dir . '/' . 'impresee-'.date("Y-m-d").'.log';
    }

    public function writeToLog(String $line, String $type){
        try {
            if($type == LogHandler::IMSEE_LOG_DEBUG && !$this->project->getIsDebug()){
                return;
            }
            $logline = "[ ".$type." ] [ ".date("Y-m-d h:i:sa")." ] ".$line."\n";
            $file = fopen($this->logs_file, 'a');
            fwrite($file, $logline);
            fclose($file);    
        } catch(\Throwable $e){
            error_log('Error when writing impresee log: '.$e->getMessage());
        }

    }
}