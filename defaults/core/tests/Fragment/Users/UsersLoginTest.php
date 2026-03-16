<?php
namespace Defaults\Core\Tests\Fragment\Users;
use YoudsFramework\Request\Web;
use YoudsFramework\Testing\ChainTestCase;

class UsersLoginTest extends ChainTestCase
{
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->contentName = 'Users';
        $this->chainName = 'Login';
    }

    /**
     * @dataProvider getRequestMethods
     */
    public function testFunctionality($input)
    {
        $this->setRequestMethod('write');
        $this->setRequestData(new Web());
        $rd = $this->createRequestDataHolder(array(Web::SOURCE_PARAMETERS => $input));
        $this->setArguments($rd);
        $this->runAction();

        foreach ($input as $key => $value):
            $this->assertValidatedArgument($key);
        endforeach;

        $ot = new \YoudsFramework\OutputType();
        $ot->initialize($this->getContext(), $input, 'web', ['plain' => ['plain']], array(), array(), 'web');
        //dump($ot->);
        $this->setOutputType($ot);
        $this->runLayout();
        $this->assertNoValidationErrors();
        $this->assertLayoutNameEquals('Success');
        $this->assertLayoutResultEquals('Successfully completed the action.');
    }

    public function getRequestMethods() : array
    {
        return [
            [
                [
                    'email' => 'abc@def.com',
                    'password' => '123456abc!'
                ]
            ]
        ];
    }
}