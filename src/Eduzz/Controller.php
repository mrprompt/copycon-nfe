<?php
namespace CopyCon\Eduzz;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

/**
 * Eduzz Controller
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
final class Controller
{
    /**
     * Validate transaction from Eduzz Request and Process It.
     *
     * @param  \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function post(Application $app)
    {
        $app['response'] = $app['request']->request->all();

        $post = $app['request']->request->all();

        if ($app['request']->get('trans_status') == 3) {
            $fields = [
                'name'              => $post['aff_name'],
                'email'             => $post['aff_email'],
                'cityServiceCode'   => '5762',
                'description'       => $post['product_name'],
                'servicesAmount'    => $post['trans_paid'],
                'borrower'          => [
                    'name'              => $post['cus_name'],
                     'email'            => $post['cus_address'],
                    'federalTaxNumber'  => $post['cus_taxnumber'],
                    'address'           => [
                        'country'               => 'BRA',
                        'street'                => $post['cus_address'],
                        'number'                => $post['cus_address_number'],
                        'additionalInformation' => $post['cus_address_comp'],
                        'district'              => $post['cus_address_district'],
                        'postalCode'            => $post['cus_address_zip_code'],
                        'state'                 => $post['cus_address_state'],
                        'city'                  => [
                            'code' => $post['cus_address_city'],
                            'name' => $post['cus_address_state']
                        ],
                    ]
                ]
            ];

            $app['nfe.params'] = $fields;
        }

        $result  = [
            'status' => $app['request']->get('trans_status'),
            'verified' => ((int) $app['request']->get('trans_status') === 3)
        ];

        return $app->json($result, ($result['verified'] ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR));
    }
}
