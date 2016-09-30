<?php
namespace CopyCon\PagSeguro;

use Exception;
use PHPSC\PagSeguro\Credentials;
use PHPSC\PagSeguro\Environments\Production;
use PHPSC\PagSeguro\Purchases\Subscriptions\Locator as SubscriptionLocator;
use PHPSC\PagSeguro\Purchases\Transactions\Locator as TransactionLocator;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

/**
 * PagSeguro Controller
 *
 * Receive Notification POST from PagSeguro Service and obtain information about transaction.
 *
 * Sent email to transaction customer and seller with transaction details and coupon.
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
final class Controller
{
    /**
     * Validate transaction from PagSeguro Request and Process It.
     *
     * @param  \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function post(Application $app)
    {
        $credentials = new Credentials(
            $app['config']['pagseguro']['email'],
            $app['config']['pagseguro']['token'],
            new Production()
        );

        try {
            $service = $app['request']->get('notificationType', 'transaction') == 'preApproval'
                ? new SubscriptionLocator($credentials)
                : new TransactionLocator($credentials);

            $code       = $app['request']->get('notificationCode');

            /* @var $purchase \PHPSC\PagSeguro\Purchases\Transactions\Transaction */
            $purchase   = $service->getByNotification($code);

            /* @var $shipping \PHPSC\PagSeguro\Shipping\Shipping */
            $shipping   = $purchase->getShipping();

            /* @var $details \PHPSC\PagSeguro\Purchases\Details */
            $details    = $purchase->getDetails();

            /* @var $payment \PHPSC\PagSeguro\Purchases\Transactions\Payment */
            $payment    = $purchase->getPayment();

            /* @var $customer \PHPSC\PagSeguro\Customer\Customer */
            $customer = $details->getCustomer();

            /* @var $verified boolean */
            $verified   = $purchase->isPaid();

            /* @var $status string */
            $status     = $purchase->isPaid() ? 'paga' : 'pendente';

            if ($verified) {
                $items = null;

                /* @var $item \PHPSC\PagSeguro\Items\Item */
                foreach ($purchase->getItems() as $item) {
                    $items .= "{$item->getDescription()}\n";
                }

                $fields = [
                    'name'              => $details->getCode(),
                    "email"             => $app['config']['mail']['from'],
                    'cityServiceCode'   => '5762',
                    'description'       => $items,
                    'servicesAmount'    => $payment->getGrossAmount(),
                    'borrower'          => [
                        'name'      => $customer->getName(),
                        'email'     => $details->getCustomer()->getEmail(),
                        "address"   => [
                            "country"               => "BRA",
                            "street"                => $shipping->getAddress()->getStreet(),
                            "number"                => $shipping->getAddress()->getNumber(),
                            "additionalInformation" => $shipping->getAddress()->getComplement(),
                            "district"              => $shipping->getAddress()->getDistrict(),
                            "postalCode"            => $shipping->getAddress()->getPostalCode(),
                            "city"                  => [
                                "code" => $shipping->getAddress()->getPostalCode(),
                                "name" => $shipping->getAddress()->getState()
                            ],
                            "state" => $shipping->getAddress()->getState(),
                        ]
                    ]
                ];

                $app['nfe.params'] = $fields;
            }
        } catch (Exception $error) {
            $verified   = false;
            $status     = 'pendente';

            $app['logger']->addDebug($error->getMessage());
        }

        $result = ['status' => $status, 'verified' => $verified];

        $app['response'] = [
            $app['request']->get('notificationType'),
            $app['request']->get('notificationCode')
        ];

        return $app->json($result, ($verified ? Response::HTTP_CREATED : Response::HTTP_INTERNAL_SERVER_ERROR));
    }
}
