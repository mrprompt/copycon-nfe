<?php
namespace CopyCon\Tests\Eduzz;

use Silex\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use DateTime;

/**
 * Eduzz Controller Test Case
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
        $client->request('GET', '/eduzz/');

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
        $client->request('PUT', '/eduzz/');

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
        $client->request('DELETE', '/eduzz/');

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
        $client->request('POST', '/eduzz/');

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
        $this->assertEquals($object->status, '');
    }

    /**
     * @test
     * @dataProvider validRequestWithPayedProvider
     */
    public function postWithValidParamsAndPayedTransactionMustBeReturnCreatedStatus($params)
    {
        /* @var $client \Symfony\Component\HttpKernel\Client */
        $client = $this->createClient();
        $client->request('POST', '/eduzz/', $params);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $client->getResponse();

        /* @var $content string */
        $content  = $response->getContent();

        /* @var $object \stdClass */
        $object   = json_decode($content);

        $this->assertJson($content);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNotEmpty($content);
        $this->assertEquals($object->verified, true);
        $this->assertEquals($object->status, 3);
    }

    /**
     * @test
     * @dataProvider validRequestWithNonPayedProvider
     */
    public function postWithValidParamsAndNonPayedTransactionMustBeReturnExceptionStatus($params)
    {
        /* @var $client \Symfony\Component\HttpKernel\Client */
        $client = $this->createClient();
        $client->request('POST', '/eduzz/', $params);

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
        $this->assertEquals($object->status, '');
    }

    /**
     * @return array
     */
    public function validRequestWithPayedProvider()
    {
        return [
            [
                [
                    'trans_cod' => '766B9C-AD4B044B04DA-77742F5FA653-E1AB24',
                    'trans_value' => '0.01',
                    'trans_paid' => '0.01',
                    'trans_status' => 3, // pago
                    'trans_paymentmethod' => 13, // Visa
                    'trans_createdate' => new DateTime(),
                    'trans_paiddate' => new DateTime(),
                    'product_cod' => 0,
                    'product_name' => 'Test',
                    'cus_cod' => 0,
                    'cus_taxnumber' => 0,
                    'cus_name' => 'User Test',
                    'cus_email' => 'foo@foobar.net',
                    'cus_address' => 'Foo',
                    'cus_address_number' => 1,
                    'cus_address_country' => 'BRA',
                    'cus_address_district' => 'FLN',
                    'cus_address_comp' => '',
                    'cus_address_city' => 'Foo',
                    'cus_address_state' => 'Bar',
                    'cus_address_zip_code' => 55,
                    'aff_cod' => 0,
                    'aff_name' => 'Foo',
                    'aff_email' => 'foo@bar.bar',
                    'aff_value' => 0.01,
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function validRequestWithNonPayedProvider()
    {
        return [
            [
                [
                    [
                        'trans_cod' => '766B9C-AD4B044B04DA-77742F5FA653-E1AB24',
                        'trans_value' => '0.01',
                        'trans_paid' => '0.01',
                        'trans_status' => 4, // cancelada
                        'trans_paymentmethod' => 1, // Boleto
                        'trans_createdate' => new DateTime(),
                        'trans_paiddate' => new DateTime(),
                        'product_cod' => 0,
                        'product_name' => 'Test',
                        'cus_cod' => 0,
                        'cus_taxnumber' => 0,
                        'cus_name' => 'User Test',
                        'cus_email' => 'foo@foobar.net',
                        'cus_address' => 'Foo',
                        'cus_address_number' => 1,
                        'cus_address_country' => 'BRA',
                        'cus_address_district' => 'FLN',
                        'cus_address_comp' => '',
                        'cus_address_city' => 'Foo',
                        'cus_address_state' => 'Bar',
                        'cus_address_zip_code' => 55,
                        'aff_cod' => 0,
                        'aff_name' => 'Foo',
                        'aff_email' => 'foo@bar.bar',
                        'aff_value' => 0.01,
                    ]
                ],
                [
                    [
                        'trans_cod' => '766B9C-AD4B044B04DA-77742F5FA653-E1AB24',
                        'trans_value' => '0.01',
                        'trans_paid' => '0.01',
                        'trans_status' => 8, // em análise
                        'trans_paymentmethod' => 9, // PayPal Int.
                        'trans_createdate' => new DateTime(),
                        'trans_paiddate' => new DateTime(),
                        'product_cod' => 0,
                        'product_name' => 'Test',
                        'cus_cod' => 0,
                        'cus_taxnumber' => 0,
                        'cus_name' => 'User Test',
                        'cus_email' => 'foo@foobar.net',
                        'cus_address' => 'Foo',
                        'cus_address_number' => 1,
                        'cus_address_country' => 'BRA',
                        'cus_address_district' => 'FLN',
                        'cus_address_comp' => '',
                        'cus_address_city' => 'Foo',
                        'cus_address_state' => 'Bar',
                        'cus_address_zip_code' => 55,
                        'aff_cod' => 0,
                        'aff_name' => 'Foo',
                        'aff_email' => 'foo@bar.bar',
                        'aff_value' => 0.01,
                    ]
                ],
            ],
        ];
    }
}
