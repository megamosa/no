<?php
namespace MagoArab\WithoutEmail\Plugin\Checkout;

use Magento\Checkout\Block\Onepage\Success;
use Magento\Framework\App\ObjectManager;

class SuccessPlugin
{
    /**
     * Replace email address with phone tracking message
     *
     * @param Success $subject
     * @param string $result
     * @return string
     */
    public function afterGetAdditionalInfoHtml(Success $subject, $result)
    {
        // Load the order using ObjectManager
        $objectManager = ObjectManager::getInstance();
        $orderRepository = $objectManager->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $orderFactory = $objectManager->get(\Magento\Sales\Model\OrderFactory::class);
        $configHelper = $objectManager->get(\MagoArab\WithoutEmail\Helper\Config::class);
        
        // If module is not enabled, return original result
        if (!$configHelper->isEnabled()) {
            return $result;
        }
        
        try {
            $orderId = $subject->getOrderId();
            if ($orderId) {
                $order = $orderFactory->create()->loadByIncrementId($orderId);
                
                // Get the phone number from the order
                $shippingAddress = $order->getShippingAddress();
                $phoneNumber = $shippingAddress ? $shippingAddress->getTelephone() : '';
                
                if ($phoneNumber) {
                    // Create custom HTML with phone number
                    $phoneMessage = '<div class="phone-tracking-message">You can track your order using your phone number: <strong>' . $phoneNumber . '</strong></div>';
                    
                    // Replace the email address display with our custom message
                    $result = preg_replace('/<div[^>]*>.*?Email Address.*?<\/div>/s', $phoneMessage, $result);
                }
            }
        } catch (\Exception $e) {
            // If anything goes wrong, return the original content
        }
        
        return $result;
    }
}