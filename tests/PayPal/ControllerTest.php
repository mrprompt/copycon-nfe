<?php
namespace CopyCon\Tests\PayPal;

use Silex\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * PayPal Controller Test Case
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
        $client->request('GET', '/paypal/');

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
        $client->request('PUT', '/paypal/');

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
        $client->request('DELETE', '/paypal/');

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
        $client->request('POST', '/paypal/');

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $client->getResponse();

        /* @var $content string */
        $content  = $response->getContent();

        /* @var $object \stdClass */
        $object   = json_decode($content);

        $this->assertJson($content);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertNotEmpty($content);
    }

    /**
     * @test
     * @dataProvider validRequestProvider
     */
    public function postWithValidParamsMustBeReturnCreatedStatus($params)
    {
        $post = [];

        parse_str($params, $post);

        /* @var $client \Symfony\Component\HttpKernel\Client */
        $client = $this->createClient();
        $client->request('POST', '/paypal/', $post);

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
        $ipn = 'mc_gross=19.95&protection_eligibility=Eligible&address_status=confirmed&payer_id=LPLWNMTBWMFAY'
            . '&tax=0.00&address_street=1+Main+St&payment_date=20%3A12%3A59+Jan+13%2C+2009+PST'
            . '&payment_status=Completed&charset=windows-1252&address_zip=95131&first_name=Test&mc_fee=0.88'
            . '&address_country_code=US&address_name=Test+User&notify_version=2.6&custom=&payer_status=verified'
            . '&address_country=United+States&address_city=San+Jose&quantity=1'
            . '&verify_sign=AtkOfCXbDm2hu0ZELryHFjY-Vb7PAUvS6nMXgysbElEn9v-1XcmSoGtf'
            . '&payer_email=gpmac_1231902590_per%40paypal.com&txn_id=61E67681CH3238416&payment_type=instant'
            . '&last_name=User&address_state=CA&receiver_email=gpmac_1231902686_biz%40paypal.com&payment_fee=0.88'
            . '&receiver_id=S8XGHLYDW9T3S&txn_type=express_checkout&item_name=&mc_currency=USD&item_number='
            . '&residence_country=US&test_ipn=1&handling_amount=0.00&transaction_subject=&payment_gross=19.95'
            . '&shipping=0.00';

        return [
            [
                $ipn
            ],
        ];
    }
}
