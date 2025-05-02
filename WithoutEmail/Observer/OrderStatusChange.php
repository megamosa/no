<?php
/**
 * MagoArab_WithoutEmail extension
 *
 * @category  MagoArab
 * @package   MagoArab_WithoutEmail
 * @author    MagoArab
 */
declare(strict_types=1);

namespace MagoArab\WithoutEmail\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use MagoArab\WithoutEmail\Helper\Config;
use MagoArab\WithoutEmail\Helper\WhatsappService;

class OrderStatusChange implements ObserverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    
    /**
     * @var Config
     */
    protected $configHelper;
    
    /**
     * @var WhatsappService
     */
    protected $whatsappService;

    /**
     * Constructor
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param Config $configHelper
     * @param WhatsappService $whatsappService
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Config $configHelper,
        WhatsappService $whatsappService
    ) {
        $this->customerRepository = $customerRepository;
        $this->configHelper = $configHelper;
        $this->whatsappService = $whatsappService;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->configHelper->isEnabled() || !$this->configHelper->isOrderNotificationsEnabled()) {
            return;
        }

        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if (!$order || !$order->getId()) {
            return;
        }
        
        // Get customer phone number
        $customerId = $order->getCustomerId();
        if (!$customerId) {
            return;
        }
        
        try {
            $customer = $this->customerRepository->getById($customerId);
            $phoneAttribute = $customer->getCustomAttribute('phone_number');
            
            if (!$phoneAttribute) {
                return;
            }
            
            $phoneNumber = $phoneAttribute->getValue();
            
            // Get order status
            $status = $order->getStatus();
            $notificationStatus = $this->mapOrderStatus($status);
            
            if (!$notificationStatus) {
                return;
            }
            
            // Prepare message parameters
            $params = [
                'order_id' => $order->getIncrementId(),
                'customer_name' => $customer->getFirstname() . ' ' . $customer->getLastname()
            ];
            
            // Add tracking number if available
            if ($notificationStatus === 'shipped') {
                $tracksCollection = $order->getTracksCollection();
                if ($tracksCollection->getSize() > 0) {
                    $track = $tracksCollection->getFirstItem();
                    $params['tracking_number'] = $track->getTrackNumber();
                }
            }
            
            // Send notification
            $this->whatsappService->sendOrderStatusNotification($phoneNumber, $params, $notificationStatus);
        } catch (NoSuchEntityException $e) {
            // Customer not found, ignore
        } catch (LocalizedException $e) {
            // Failed to send notification, log error
        }
    }
    
    /**
     * Map Magento order status to notification status
     *
     * @param string $status
     * @return string|null
     */
    protected function mapOrderStatus(string $status): ?string
    {
        switch ($status) {
            case Order::STATE_PROCESSING:
                return 'processing';
            case Order::STATE_COMPLETE:
                return 'delivered';
            case 'shipped':
            case 'shipping':
                return 'shipped';
            default:
                return null;
        }
    }
}