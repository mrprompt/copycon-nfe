<?php
namespace CopyCon\Tests\PagSeguro;

use Silex\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * PagSeguro Controller Test Case
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
final class ControllerTest extends WebTestCase
{
    /**
     * Test bootstrap
     *
     * @return \Silex\Application
     */
    public function createApplication()
    {
        $app            = require __DIR__ . '/../../bootstrap.php';
        $app['debug']   = true;
        $app['testing'] = true;

        return $this->app = $app;
    }

    /**
     * @test
     */
    public function getMustBeReturnNotAllowedStatus()
    {
        /* @var $client \Symfony\Component\HttpKernel\Client */
        $client = $this->createClient();
        $client->request('GET', '/pagseguro/');

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $client->getResponse();

        $this->assertJson($response->getContent());
        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function putMustBeReturnNotAllowedStatus()
    {
        /* @var $client \Symfony\Component\HttpKernel\Client */
        $client = $this->createClient();
        $client->request('PUT', '/pagseguro/');

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $client->getResponse();

        $this->assertJson($response->getContent());
        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function deleteMustBeReturnNotAllowedStatus()
    {
        /* @var $client \Symfony\Component\HttpKernel\Client */
        $client = $this->createClient();
        $client->request('DELETE', '/pagseguro/');

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $client->getResponse();

        $this->assertJson($response->getContent());
        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function postWithoutParamsMustBeReturnExceptionStatus()
    {
        /* @var $client \Symfony\Component\HttpKernel\Client */
        $client = $this->createClient();
        $client->request('POST', '/pagseguro/');

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $client->getResponse();

        /* @var $content string */
        $content  = $response->getContent();

        /* @var $object \stdClass */
        $object   = json_decode($content);

        $this->assertJson($content);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertEquals($object->verified, false);
    }

    /**
     * @test
     * @dataProvider validRequestProvider
     */
    public function postWithValidParamsMustBeReturnCreatedStatus($params)
    {
        /* @var $client \Symfony\Component\HttpKernel\Client */
        $client = $this->createClient();
        $client->request('POST', '/pagseguro/', $params);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $client->getResponse();

        /* @var $content string */
        $content  = $response->getContent();
        var_dump($content);

        /* @var $object \stdClass */
        $object   = json_decode($content);

        $this->assertJson($content);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertEquals($object->verified, true);
    }

    /**
     * @test
     * @dataProvider deniedRequestProvider
     */
    public function postWithValidDeniedTransactionMustBeReturnExceptionStatus($params)
    {
        /* @var $client \Symfony\Component\HttpKernel\Client */
        $client = $this->createClient();
        $client->request('POST', '/pagseguro/', $params);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $client->getResponse();

        /* @var $content string */
        $content  = $response->getContent();

        /* @var $object \stdClass */
        $object   = json_decode($content);

        $this->assertJson($content);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertEquals($object->verified, false);
    }

    /**
     * @return array
     */
    public function validRequestProvider()
    {
        return [
            [
                [
                    'notificationCode' => 'C15C37-F4E685E68538-3334118FA980-11BE04',
                    'notificationType' => 'preApprove'
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function deniedRequestProvider()
    {
        return [
            [
                [
                    'notificationCode' => '878CF3-7C48154815A9-1664D8FFA3DF-5F6F88',
                    'notificationType' => 'preApprove'
                ]
            ],
        ];
    }
}
