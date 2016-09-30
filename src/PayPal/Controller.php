<?php
namespace CopyCon\PayPal;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Toolani\Payment\Paypal\IpnVerifier;
use Exception;

/**
 * PayPal Controller
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
final class Controller
{
    /**
     * Validate transaction from PayPal IPN Request and Process It.
     *
     * @param  \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function post(Application $app)
    {
        $post = $app['request']->request->all();

        $verifier = new IpnVerifier($useSandbox = true);
        $verified = null;

        try {
            $verified = $verifier->verify($post);
        } catch (Exception $e) {
            $verified = false;
        }

        // Verification status is available as a string, too. See table below
        $status = $verifier->getVerificationStatusString();

        /* @var $result array */
        $result = ['status' => $status, 'verified' => $verified];

        $app['response'] = $post;

        if ($verified) {
            $fields = [
                'name'              => "{$post['first_name']} {$post['last_name']}",
                'email'             => $post['payer_email'],
                'cityServiceCode'   => '5762',
                'description'       => $post['invoice'],
                'servicesAmount'    => $post['mc_gross'],
                'borrower'          => [
                    'name'              => "{$post['first_name']} {$post['last_name']}",
                     'email'            => $post['payer_email'],
                    'federalTaxNumber'  => $post['payer_id'],
                    'address'           => [
                        'country'               => 'BRA',
                        'street'                => $post['address_street'],
                        'number'                => $post['address_street'],
                        'additionalInformation' => $post['custom'],
                        'district'              => $post['address_city'],
                        'postalCode'            => $post['address_zip'],
                        'state'                 => $post['address_state'],
                        'city' => [
                            'code' => $post['address_country_code'],
                            'name' => $post['address_state']
                        ],
                    ]
                ]
            ];

            $app['nfe.params'] = $fields;
        }

        return $app->json($result, ($verified ? Response::HTTP_CREATED : Response::HTTP_INTERNAL_SERVER_ERROR));
    }
}
