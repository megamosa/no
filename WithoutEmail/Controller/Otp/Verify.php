<?php
/**
 * MagoArab_WithoutEmail extension
 *
 * @category  MagoArab
 * @package   MagoArab_WithoutEmail
 * @author    MagoArab
 */
declare(strict_types=1);

namespace MagoArab\WithoutEmail\Controller\Otp;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionManagerInterface;
use MagoArab\WithoutEmail\Helper\Config;

class Verify implements HttpPostActionInterface , \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param JsonFactory $resultJsonFactory
     * @param SessionManagerInterface $session
     * @param Config $configHelper
     */
    public function __construct(
        RequestInterface $request,
        JsonFactory $resultJsonFactory,
        SessionManagerInterface $session,
        Config $configHelper
    ) {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session = $session;
        $this->configHelper = $configHelper;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        
        try {
            if (!$this->configHelper->isEnabled()) {
                throw new LocalizedException(__('This feature is not enabled.'));
            }
            
            $phoneNumber = $this->request->getParam('phone_number');
            $otpCode = $this->request->getParam('otp_code');
            
            if (empty($phoneNumber) || empty($otpCode)) {
                throw new LocalizedException(__('Phone number and OTP code are required.'));
            }
            
            // Get OTP data from session
            $otpData = $this->session->getData('otp_' . $phoneNumber);
            
            if (!$otpData || !isset($otpData['code']) || !isset($otpData['expiry'])) {
                throw new LocalizedException(__('OTP has not been sent. Please request a new OTP.'));
            }
            
            // Check if OTP has expired
            $currentTime = new \DateTime();
            $expiry = new \DateTime($otpData['expiry']);
            
            if ($currentTime > $expiry) {
                throw new LocalizedException(__('OTP has expired. Please request a new OTP.'));
            }
            
            // Verify OTP
            if ($otpData['code'] !== $otpCode) {
                throw new LocalizedException(__('Invalid OTP code. Please try again.'));
            }
            
            // Mark OTP as verified
            $otpData['verified'] = true;
            $this->session->setData('otp_' . $phoneNumber, $otpData);
            
            return $resultJson->setData([
                'success' => true,
                'message' => __('OTP verified successfully.')
            ]);
        } catch (LocalizedException $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => __('An error occurred while verifying OTP. Please try again.')
            ]);
        }
    }
}