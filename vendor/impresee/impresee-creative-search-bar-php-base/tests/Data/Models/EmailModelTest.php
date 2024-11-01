<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Data\Models\ErrorEmailModel;
    use Impresee\CreativeSearchBar\Data\Models\InformationEmailModel;
    use Impresee\CreativeSearchBar\Core\Constants\DestinationGroups;

final class EmailModelTest extends TestCase {

    public function testErrorEmailToJson(){
        $error_email = new ErrorEmailModel(
            'host',
            'example',
            'example body',
            'name',
            'incredibly long stacktrace'
        );
        $expected_json = array(
            'source_hostname'      => 'host',
            'source_project'       => 'name',
            'title'                => 'example',
            'body'                 => 'example body php: '.phpversion(),
            'is_error'             => TRUE,
            'throwable_stacktrace' => 'incredibly long stacktrace'
        );

        $this->assertEquals(
            $expected_json,
            $error_email->toJson()
        );
    }

    public function testInformationEmailToJson(){
        $information_email = new InformationEmailModel(
            'host',
            DestinationGroups::SALES,
            'example',
            'example details',
            'name'
        );
        $expected_json = array(
            'source_hostname'      => 'host',
            'source_project'       => 'name',
            'destination_group'    => DestinationGroups::SALES,
            'event_code'           => 'example',
            'event_details'        => 'example details php: '.phpversion()
        );

        $this->assertEquals(
            $expected_json,
            $information_email->toJson()
        );
    }
    
}