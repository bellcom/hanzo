<?php

namespace Hanzo\Bundle\ServiceBundle\Services;

use Exception;
use Criteria;
use Hanzo\Model\OrdersStateLog;
use Propel;

use Hanzo\Core\Hanzo;
use Hanzo\Core\Tools;

use Hanzo\Model\Orders;
use Hanzo\Model\OrdersPeer;
use Hanzo\Model\OrdersQuery;
use Hanzo\Model\OrdersAttributes;
use Hanzo\Model\OrdersAttributesQuery;
use Hanzo\Model\OrdersSyncLogQuery;

use Hanzo\Bundle\CheckoutBundle\Event\FilterOrderEvent;
use Hanzo\Bundle\PaymentBundle\Methods\Dibs\DibsApi;
use Hanzo\Bundle\PaymentBundle\Methods\Dibs\DibsApiCallException;

class DeadOrderService
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var bool
     */
    protected $dryrun = false;
    protected $debug  = false;

    /**
     * @var array
     */
    protected $currency_map = [
        58  => 'DKK',
        161 => 'NOK',
        207 => 'SEK',
    ];

    /**
     * @var \Hanzo\Bundle\AxBundle\Actions\Out\AxServiceWrapper
     */
    private $ax;

    /**
     * @var \Hanzo\Bundle\PaymentBundle\Methods\Dibs\DibsApi
     */
    private $dibsApi;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \Hanzo\Bundle\AxBundle\Actions\Out\PheanstalkQueue
     */
    private $pheanstalkQueue;

    /**
     * @var \Hanzo\Bundle\CoreBundle\Service\Model\OrdersService
     */
    private $ordersService;

    /**
     * @param $parameters
     * @param $settings
     */
    public function __construct($parameters, $settings)
    {
        $this->dibsApi         = $parameters['dibsapi'];
        $this->eventDispatcher = $parameters['event_dispatcher'];
        $this->ax              = $parameters['service_wrapper'];
        $this->pheanstalkQueue = $parameters['pheanstalk_queue'];
        $this->ordersService   = $parameters['orders_service'];
        $this->settings        = $settings;

        if (!$this->dibsApi instanceof DibsApi) {
            throw new \InvalidArgumentException('DibsApi expected as first parameter.');
        }
    }

    /**
     * autoCleanup
     *
     * Finds and deletes all orders that are dead
     *
     * @param bool $dryrun Avoid changing orders
     * @param bool $debug Output debugging info
     * @return void
     */
    public function autoCleanup($dryrun, $debug)
    {
        $this->dryrun = $dryrun;
        $this->debug  = $debug;

        $this->debug("Starting DeadOrderService Auto Clean");
        if ($this->dryrun) {
            $this->debug("Dry run mode");
        }

        $this->getOrdersToBeDeleted( 0, true);
    }

    /**
     * getOrdersToBeDeleted
     *
     * @param int  $limit
     * @param bool $instanceDelete
     * @return array
     */
    public function getOrdersToBeDeleted($limit = 0, $instanceDelete = false)
    {
        $toBeDeleted = [];
        $orders      = $this->getOrders( $limit );

        $this->debug("Found ".count($orders)." that matches filter");

        $i = 1;
        foreach ($orders as $order) {
            $this->debug($i++ .' of '. count($orders));
            $status = $this->checkOrderForErrors($order);

            if (isset($status['is_error']) && ($status['is_error'] === true)) {
                if ($instanceDelete) {
                    if (!$this->dryrun) {
                        $this->debug("  Deleting order: ".$order->getId());

                        $order->setDBConnection(\Propel::getConnection());
                        $order->setIgnoreDeleteConstraints(true);
                        $this->ordersService->deleteOrder($order);
                    } else {
                        $this->debug("  (Dryrun) Deleting order: ".$order->getId());
                    }
                } else {
                    $this->debug("  Order queued to be deleted (".$order->getId()."): ");
                    $toBeDeleted[] = $order;
                }

                $this->debug(print_r($status,1));
            }
        }

        return $toBeDeleted;
    }

    /**
     * checkOrderForErrors
     *
     * @param  Orders $order
     * @return array
     */
    public function checkOrderForErrors($order)
    {
        $status = [
            'is_error'          => false,
            'error_msg'         => '',
            'id'                => $order->getId(),
            'order_last_update' => $order->getUpdatedAt()
        ];

        // fix broken currency code
        if (!$order->getCurrencyCode()) {
            $code = 'EUR';
            if (isset($this->currency_map[$order->getBillingCountriesId()])){
                $code = $this->currency_map[$order->getBillingCountriesId()];
            }

            $order->setCurrencyCode($code);
        }

        $pgId = $order->getPaymentGatewayId();
        $this->debug("Processing: order id: ".$order->getId()." (payment gateway id:".$pgId."), in state: ".$order->getState());

        $transId = $this->getTransactionId($order);

        if (empty($pgId) && !is_null($transId)) {
            // No payment gateway id, but an transId
            $this->debug("  Has no payment gateway id, but an transId: ". $transId);
            try {
                $callbackData = $this->dibsApi->call()->callback($order);
                if (isset($callbackData['orderid'])) {
                    mail('un@bellcom.dk', 'setPaymentGatewayId', 'for order id: '.$order->getId()."\n\n".__FILE__.' '.__LINE__."\n\n");
                    $order->setPaymentGatewayId($callbackData['orderid']);
                }
            } catch (DibsApiCallException $e) {
                $this->debug('  Dibs call failed: '. $e->getMessage());
                $status['is_error'] = false;
                $status['error_msg'] = $e->getMessage();

                return $status;
            }
        }

        if (is_null($transId)) {
            if ($order->getInEdit()) {
                $this->debug( '  No trans id found, and order is in edit' );
                $status['is_error'] = false;
                $status['error_msg'] = 'Går til tidligere version: Ingen transaktions id kunne findes for denne version';

                if (!$this->dryrun) {
                    $order->toPreviousVersion();
                    $this->ax->SalesOrderLockUnlock($order, false);

                    $log = new OrdersStateLog();
                    $log->info($order->getId(), Orders::INFO_STATE_EDIT_CANCLED_BY_CLEANUP);
                    $log->save();
                } else {
                    $this->debug('  Should role back to prew version of order... ' . implode(', ', $order->getVersionIds()));
                }
            } else {
                $this->debug('  No trans id found');
                $status['is_error']  = true;
                $status['error_msg'] = 'Slet: Ingen transaktions id kunne findes';
            }

            return $status;
        }

        $order->setAttribute('transact', 'payment', $transId);
        $this->debug("  Setting transId: ". $transId);

        if (!$this->dryrun) {
            $order->save();
        }

        try {
            $orderStatus = $this->getStatus($order);
            $this->debug( "  Order status by dibs, desc: ". $orderStatus->data['status_description'] .' status: '. $orderStatus->data['status'] );
        } catch (DibsApiCallException $e) {
            $this->debug('  Dibs call failed: '. $e->getMessage());
            $status['is_error']  = false;
            $status['error_msg'] = $e->getMessage();

            return $status;
        }

        if (isset($orderStatus->data['status'])) {
            switch ($orderStatus->data['status']) {
                case 2:
                    // Looks like payment is ok -> update order
                    $this->debug("  Payment looks ok, updating order, state ok");
                    $order->setState(Orders::STATE_PAYMENT_OK);
                    $order->setFinishedAt(time());

                    try {
                        $callbackData = $this->dibsApi->call()->callback($order);
                    } catch (DibsApiCallException $e) {
                        $this->debug('  Dibs call failed: '. $e->getMessage());
                        $status['is_error']  = false;
                        $status['error_msg'] = $e->getMessage();

                        return $status;
                    }

                    $fields = [
                        'paytype',
                        'cardnomask',
                        'cardprefix',
                        'acquirer',
                        'cardexpdate',
                        'currency',
                        'ip',
                        'approvalcode',
                        'transact',
                    ];

                    foreach ($fields as $field) {
                        if (isset($callbackData->data[$field])) {
                            $this->debug("  Setting field ".$field." to ".$callbackData->data[$field]);
                            $order->setAttribute($field , 'payment', $callbackData->data[$field]);
                        }
                    }

                    if (!$this->dryrun) {
                        if ($order->getInEdit()) {
                            $this->debug('  Order was in edit mode');
                            $currentVersion = $order->getVersionId();

                            // If the version number is less than 2 there is no previous version
                            if (!($currentVersion < 2)) {
                                $oldOrderVersion = ($currentVersion - 1);
                                $oldOrder        = $order->getOrderAtVersion($oldOrderVersion);

                                try {
                                    $this->debug('  Canceling old payment');
                                    $this->dibsApi->call()->cancel($oldOrder->getCustomers(), $oldOrder);
                                } catch (\Exception $e) {
                                    $this->debug('  Could not cancel payment for old order, id: '. $oldOrder->getId() .' error was: '. $e->getMessage());
                                    Tools::log('DOS: Could not cancel payment for old order, id: '. $oldOrder->getId() .' error was: '. $e->getMessage());
                                }
                            }

                            $log = new OrdersStateLog();
                            $log->setOrdersId($order->getId());
                            $log->setState(0);
                            $log->setMessage(Orders::INFO_STATE_EDIT_CANCLED_BY_CLEANUP);
                            $log->setCreatedAt(time());
                            $log->save();
                        }

                        try {
                            $this->debug('  Syncing to ax...');

                            // let the queue system handle this
                            $this->pheanstalkQueue->appendSendOrder($order);

                            $order->setState(Orders::STATE_PENDING);
                            $order->setInEdit(false);
                            $order->setSessionId($order->getId());
                            $order->save();
                        } catch (Exception $e) {
                            $this->debug('  Sync failed: '.$e->getMessage());
                        }
                    }

                    break;

                default:
                    $this->debug('  Order status er '. $orderStatus->data['status']);
                    $status['is_error']  = true;
                    $status['error_msg'] = 'Order status er '. $orderStatus->data['status'];

                    return $status;
            }
        }

        return $status;
    }

    /**
     * deleteOrders
     *
     * @param array $toBeDeleted array of order objects to delete.
     * @return void
     */
    protected function deleteOrders(array $toBeDeleted)
    {
        foreach ($toBeDeleted as $order) {
            if (!$this->dryrun) {
                $this->debug("Deleting order: ".$order->getId());

                $order->setDBConnection(\Propel::getConnection());
                $order->setIgnoreDeleteConstraints(true);
                $this->ordersService->deleteOrder($order);
            } else {
                $this->debug("(Dryrun) Deleting order: ".$order->getId());
            }
        }
    }

    /**
     * debug
     *
     * @param string $msg
     * @return void
     */
    public function debug($msg)
    {
        $this->debug
            ? error_log('[DEBUG]: '.$msg)
            : '';
    }

    /**
     * getStatus
     *
     * @param Orders $order
     * @return \Hanzo\Bundle\PaymentBundle\Methods\Dibs\DibsApiCallResponse
     */
    protected function getStatus(Orders $order)
    {
        return $this->dibsApi->call()->payinfo($order);
    }

    /**
     * getTransactionId
     *
     * @param Orders $order
     * @return string|null
     */
    protected function getTransactionId(Orders $order)
    {
        $atts = $order->getAttributes(Propel::getConnection(null, Propel::CONNECTION_WRITE));

        foreach ($atts as $att) {
            if (isset($att->transact)) {
                return $att->transact;
            }
        }

        try {
            $result = $this->dibsApi->call()->transinfo($order);
        } catch (DibsApiCallException $e) {
            return null;
        }

        return $result->data['transact'];
    }


    /**
     * getOrders
     *
     * Get all orders that are older than 2 hours, which have not been finished, payed via dibs, and have not reached a state higher than 0...
     *
     * NICETO: priority: low, support limit
     * @param  int $limit
     * @return array
     */
    public function getOrders($limit = 0)
    {
        $orders = OrdersQuery::create()
            ->filterByUpdatedAt(date('Y-m-d H:i:s', strtotime('2 hours ago')), Criteria::LESS_THAN)
            ->filterByCreatedAt(date('Y-m-d H:i:s', strtotime('6 month ago')), Criteria::GREATER_THAN)
            ->filterByBillingMethod('dibs')
            ->filterByState(array('max' => Orders::STATE_PAYMENT_OK))
            ->find(Propel::getConnection(null, Propel::CONNECTION_WRITE))
        ;

        return $orders;
    }
}
