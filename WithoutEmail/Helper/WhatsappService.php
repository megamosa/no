<?php
namespace MagoArab\WithoutEmail\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use MagoArab\WithoutEmail\Helper\Config as ConfigHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Encryption\EncryptorInterface;

class WhatsappService extends AbstractHelper
{
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var Json
     */
    protected $json;
    
    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Curl $curl
     * @param ConfigHelper $configHelper
     * @param Json $json
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        Curl $curl,
        ConfigHelper $configHelper,
        Json $json,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->curl = $curl;
        $this->configHelper = $configHelper;
        $this->json = $json;
        $this->encryptor = $encryptor;
    }

    /**
     * Generate OTP code
     *
     * @return string
     */
    public function generateOtp(): string
    {
        $otpLength = $this->configHelper->getOtpLength();
        $characters = '0123456789';
        $otp = '';
        
        for ($i = 0; $i < $otpLength; $i++) {
            $otp .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $otp;
    }

    /**
     * Send OTP via WhatsApp
     *
     * @param string $phoneNumber
     * @param string $otp
     * @param string $messageType
     * @return bool
     * @throws LocalizedException
     */
    public function sendOtp(string $phoneNumber, string $otp, string $messageType = 'registration'): bool
    {
       // Check the phone number
        if (empty($phoneNumber)) {
            $this->_logger->error('Phone number is empty');
            return false;
        }
        
        // Message formatting
        $message = $this->getOtpMessageByType($otp, $messageType);
        
        // Instead of using a complex Provider, we will use UltraMsg directly
        return $this->sendDirectViaUltraMsg($phoneNumber, $message);
    }

    /**
     *Send via UltraMsg directly (simplified version)
     *
     * @param string $phoneNumber
     * @param string $message
     * @return bool
     */
    protected function sendDirectViaUltraMsg(string $phoneNumber, string $message): bool
    {
        // Hard-coded values for testing (replace with your actual values)
        $token = 'u43o0swruzxbna6m';  // API key
        $instance = '117732';  // Instance ID
        
        // Clean the phone number (remove + and any non-number symbols)
        $cleanPhone = preg_replace('/\D/', '', $phoneNumber);
        
        // Add + if not present
        if (substr($cleanPhone, 0, 1) !== '+') {
            $cleanPhone = '+' . $cleanPhone;
        }
        
       //Build URL
        $url = "https://api.ultramsg.com/instance{$instance}/messages/chat";
        
        // Setting parameters
        $params = [
            'token' => $token,
            'to' => $cleanPhone,
            'body' => $message
        ];
        
        // Using cURL directly
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_HTTPHEADER => [
                "content-type: application/x-www-form-urlencoded"
            ]
        ]);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        
        // Record information for correction
        $this->_logger->debug('UltraMsg request params: ' . json_encode($params));
        
        if ($err) {
            $this->_logger->error('UltraMsg API error: ' . $err);
            return false;
        } else {
            $this->_logger->debug('UltraMsg API response: ' . $response);
            return true;
        }
    }
/**
 * Send notification for order status
 *
 * @param string $phoneNumber
 * @param array $params
 * @param string $status
 * @return bool
 */
public function sendOrderStatusNotification(string $phoneNumber, array $params, string $status): bool
{
    // تنسيق الرسالة
    $message = $this->getOrderStatusMessage($params, $status);
    
    // إرسال الرسالة باستخدام الطريقة الحالية
    return $this->sendDirectViaUltraMsg($phoneNumber, $message);
}

/**
 * Get order status message
 *
 * @param array $params
 * @param string $status
 * @return string
 */
protected function getOrderStatusMessage(array $params, string $status): string
{
    $message = '';
    
    switch ($status) {
        case 'processing':
            $message = __('Hello %1, your order #%2 is now being processed. Thank you for shopping with us!', 
                $params['customer_name'] ?? '',
                $params['order_id'] ?? ''
            );
            break;
        case 'shipped':
            $message = __('Hello %1, your order #%2 has been shipped. Tracking number: %3', 
                $params['customer_name'] ?? '',
                $params['order_id'] ?? '',
                $params['tracking_number'] ?? __('Not available')
            );
            break;
        case 'delivered':
            $message = __('Hello %1, your order #%2 has been delivered. Thank you for shopping with us!', 
                $params['customer_name'] ?? '',
                $params['order_id'] ?? ''
            );
            break;
        default:
            $message = __('Hello %1, there is an update to your order #%2.', 
                $params['customer_name'] ?? '',
                $params['order_id'] ?? ''
            );
            break;
    }
    
    return $message;
}
    /**
     * Get OTP message by type
     *
     * @param string $otp
     * @param string $messageType
     * @return string
     */
    protected function getOtpMessageByType(string $otp, string $messageType): string
    {
        switch ($messageType) {
            case 'registration':
                return __('Your registration OTP code is: %1. This code will expire in %2 minutes.', 
                    $otp, 
                    $this->configHelper->getOtpExpiry()
                );
            case 'forgot_password':
                return __('Your password reset OTP code is: %1. This code will expire in %2 minutes.', 
                    $otp, 
                    $this->configHelper->getOtpExpiry()
                );
            default:
                return __('Your OTP code is: %1. This code will expire in %2 minutes.', 
                    $otp, 
                    $this->configHelper->getOtpExpiry()
                );
        }
    }
}