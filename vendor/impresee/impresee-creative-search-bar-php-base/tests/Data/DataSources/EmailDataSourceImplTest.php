<?php 
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use Impresee\CreativeSearchBar\Data\DataSources\EmailDataSourceImpl;
    use Impresee\CreativeSearchBar\Data\Models\{ErrorEmailModel, InformationEmailModel};
    use Impresee\CreativeSearchBar\Core\Constants\{Services, DestinationGroups};
    use Impresee\CreativeSearchBar\Core\Utils\LogHandler;
    use ImpreseeGuzzleHttp\Client;


final class EmailDataSourceImplTest extends TestCase {
    private $client;
    private $log_handler;
    private $datasource;
    private $url_admin_email = 'url1';
    private $url_event_email = 'url2';

    protected function setUp(): void{
        $services_stub = $this->createMock(Services::class);
        $services_stub->method('getAdminEmailUrl')
            ->willreturn($this->url_admin_email);
        $services_stub->method('getEventEmailUrl')
            ->willreturn($this->url_event_email);
        $this->client = $this->createMock(Client::class);
        $this->log_handler = $this->createMock(LogHandler::class);
        $this->datasource = new EmailDataSourceImpl($this->client, $this->log_handler,
            $services_stub);
    }

    public function testSendErrorEmail(){
        $error_email = new ErrorEmailModel(
            'hostaname', 
            'example title', 
            'example body',
            'name',
            'example long stacktrace'
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with($this->equalTo('POST'), 
                $this->equalTo($this->url_admin_email),
                $this->equalTo([
                    'json' => $error_email->toJson()
                ]))
            ->will($this->returnValue(new FulfilledPromise(NULL)));
        $this->datasource->sendErrorEmail($error_email);
    }

    public function testSendInformationEmail(){
        $information_email = new InformationEmailModel(
            'host',
            DestinationGroups::SALES,
            'example',
            'example details',
            'name'
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with($this->equalTo('POST'), 
                $this->equalTo($this->url_event_email),
                $this->equalTo([
                    'json' => $information_email->toJson()
                ]))
            ->will($this->returnValue(new FulfilledPromise(NULL)));
        $this->datasource->sendInformationEmail($information_email);
    }
}