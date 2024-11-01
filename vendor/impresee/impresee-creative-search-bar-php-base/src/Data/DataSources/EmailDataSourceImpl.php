<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Core\Utils\LogHandler;
    use Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource;
    use Impresee\CreativeSearchBar\Data\Models\EmailModel;
    use Impresee\CreativeSearchBar\Core\Constants\Services;
    use ImpreseeGuzzleHttp\Client;

class EmailDataSourceImpl implements EmailDataSource {
    private $client;
    private $log_handler;
    private $services;

    public function __construct(Client $client, LogHandler $log_handler, Services $services){
        $this->client = $client;
        $this->log_handler = $log_handler;
        $this->services = $services;
    }

    private function sendEmail(EmailModel $email_data, String $url){
        $this->log_handler->writeToLog(
            'Sending email to: '.$url.' with params: '.print_r($email_data, TRUE),
            LogHandler::IMSEE_LOG_DEBUG
        );
        try {
            $send_email = $this->client->requestAsync('POST', $url, [
                'json' => $email_data->toJson()
            ]);
            $send_email->wait();

        } catch (\Exception $e){
            // we ignore all errors
        }
    }

    public function sendErrorEmail(EmailModel $email_data){
        return $this->sendEmail($email_data, $this->services->getAdminEmailUrl());

    }
    public function sendInformationEmail(EmailModel $email_data){
        return $this->sendEmail($email_data, $this->services->getEventEmailUrl());
    }
}