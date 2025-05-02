<?php
/**
 * MagoArab_WithoutEmail extension
 *
 * @category  MagoArab
 * @package   MagoArab_WithoutEmail
 * @author    MagoArab
 */
declare(strict_types=1);

namespace MagoArab\WithoutEmail\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config extends AbstractHelper
{
    /**
     * Config paths
     */
    const XML_PATH_ENABLED = 'magoarab_withoutemail/general/enabled';
    const XML_PATH_MIN_PHONE_LENGTH = 'magoarab_withoutemail/general/min_phone_length';
    const XML_PATH_MAX_PHONE_LENGTH = 'magoarab_withoutemail/general/max_phone_length';
    const XML_PATH_WHATSAPP_PROVIDER = 'magoarab_withoutemail/whatsapp_settings/provider';
    const XML_PATH_ULTRAMSG_API_KEY = 'magoarab_withoutemail/whatsapp_settings/ultramsg_api_key';
    const XML_PATH_ULTRAMSG_INSTANCE_ID = 'magoarab_withoutemail/whatsapp_settings/ultramsg_instance_id';
    const XML_PATH_DIALOG360_API_KEY = 'magoarab_withoutemail/whatsapp_settings/dialog360_api_key';
    const XML_PATH_DIALOG360_PHONE_NUMBER_ID = 'magoarab_withoutemail/whatsapp_settings/dialog360_phone_number_id';
    const XML_PATH_OTP_LENGTH = 'magoarab_withoutemail/whatsapp_settings/otp_length';
    const XML_PATH_OTP_EXPIRY = 'magoarab_withoutemail/whatsapp_settings/otp_expiry';
    const XML_PATH_ENABLE_ORDER_NOTIFICATIONS = 'magoarab_withoutemail/notifications/enable_order_notifications';
    const XML_PATH_ORDER_PROCESSING_TEMPLATE = 'magoarab_withoutemail/notifications/order_processing_template';
    const XML_PATH_ORDER_SHIPPED_TEMPLATE = 'magoarab_withoutemail/notifications/order_shipped_template';
    const XML_PATH_ORDER_DELIVERED_TEMPLATE = 'magoarab_withoutemail/notifications/order_delivered_template';
	const XML_PATH_ENABLE_OTP = 'magoarab_withoutemail/whatsapp_settings/enable_otp';
	const XML_PATH_PHONE_FORMAT = 'magoarab_withoutemail/general/phone_format';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * Check if module is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get minimum phone length
     *
     * @param int|null $storeId
     * @return int
     */
    public function getMinPhoneLength(int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_MIN_PHONE_LENGTH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: 9; // Default minimum length if not set
    }

    /**
     * Get maximum phone length
     *
     * @param int|null $storeId
     * @return int
     */
    public function getMaxPhoneLength(int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_MAX_PHONE_LENGTH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: 15; // Default maximum length if not set
    }

    /**
     * Get WhatsApp provider
     *
     * @param int|null $storeId
     * @return string
     */
    public function getWhatsAppProvider(int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_WHATSAPP_PROVIDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get UltraMsg API key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getUltraMsgApiKey(int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ULTRAMSG_API_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get UltraMsg instance ID
     *
     * @param int|null $storeId
     * @return string
     */
    public function getUltraMsgInstanceId(int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ULTRAMSG_INSTANCE_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get 360Dialog API key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getDialog360ApiKey(int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_DIALOG360_API_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get 360Dialog phone number ID
     *
     * @param int|null $storeId
     * @return string
     */
    public function getDialog360PhoneNumberId(int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_DIALOG360_PHONE_NUMBER_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get OTP length
     *
     * @param int|null $storeId
     * @return int
     */
    public function getOtpLength(int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_OTP_LENGTH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: 6; // Default OTP length if not set
    }

    /**
     * Get OTP expiry time in minutes
     *
     * @param int|null $storeId
     * @return int
     */
    public function getOtpExpiry(int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_OTP_EXPIRY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: 10; // Default expiry time in minutes if not set
    }

    /**
     * Check if order notifications are enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isOrderNotificationsEnabled(int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_ORDER_NOTIFICATIONS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get order processing template
     *
     * @param int|null $storeId
     * @return string
     */
    public function getOrderProcessingTemplate(int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ORDER_PROCESSING_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
	/**
 * Get phone format
 *
 * @param int|null $storeId
 * @return string
 */
public function getPhoneFormat(int $storeId = null): string
{
    return (string)$this->scopeConfig->getValue(
        self::XML_PATH_PHONE_FORMAT,
        ScopeInterface::SCOPE_STORE,
        $storeId
    ) ?: 'local'; // Default format if not set
}

    /**
     * Get order shipped template
     *
     * @param int|null $storeId
     * @return string
     */
    public function getOrderShippedTemplate(int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ORDER_SHIPPED_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get order delivered template
     *
     * @param int|null $storeId
     * @return string
     */
    public function getOrderDeliveredTemplate(int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ORDER_DELIVERED_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    /**
 * Check if OTP is enabled
 *
 * @param int|null $storeId
 * @return bool
 */
public function isOtpEnabled(int $storeId = null): bool
{
    return $this->isEnabled() && (bool)$this->scopeConfig->getValue(
        self::XML_PATH_ENABLE_OTP,
        ScopeInterface::SCOPE_STORE,
        $storeId
    );
}
    /**
     * Get store domain
     *
     * @param int|null $storeId
     * @return string
     */
    public function getStoreDomain(int $storeId = null): string
    {
        try {
            $baseUrl = $this->storeManager->getStore($storeId)->getBaseUrl();
            $parsedUrl = parse_url($baseUrl);
            return $parsedUrl['host'] ?? 'example.com';
        } catch (\Exception $e) {
            return 'example.com';
        }
    }
}