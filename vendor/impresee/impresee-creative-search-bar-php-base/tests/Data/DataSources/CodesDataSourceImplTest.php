<?php 
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Data\DataSources\CodesDataSourceImpl;
    use Impresee\CreativeSearchBar\Core\Utils\CodesGenerator;


final class CodesDataSourceImplTest extends TestCase {
    private $code_generator;
    private $datasource;

    protected function setUp(): void {
        $this->code_generator = $this->createMock(CodesGenerator::class);
        $this->datasource = new CodesDataSourceImpl($this->code_generator);
    }

    public function testGenerateNewCode(){
        $expected_code = 'this-is-a-code';
        $this->code_generator->expects($this->once())
            ->method('generateCode')
            ->will($this->returnValue($expected_code));
        $return_code = $this->datasource->generateNewCode();
        $this->assertEquals(
            $expected_code,
            $return_code
        );
    }

}