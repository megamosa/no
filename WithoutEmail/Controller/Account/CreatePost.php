<?php
/**
 * MagoArab_WithoutEmail extension
 *
 * @category  MagoArab
 * @package   MagoArab_WithoutEmail
 * @author    MagoArab
 */
declare(strict_types=1);

namespace MagoArab\WithoutEmail\Controller\Account;

use Magento\Customer\Controller\Account\CreatePost as MagentoCreatePost;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Helper\Address;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Framework\Escaper;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use MagoArab\WithoutEmail\Helper\Config;
use MagoArab\WithoutEmail\Model\PhoneNumber;

class CreatePost extends MagentoCreatePost
{
    /**
     * @var Config
     */
    protected $configHelper;
    
    /**
     * @var PhoneNumber
     */
    protected $phoneNumber;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $accountManagement
     * @param Address $addressHelper
     * @param UrlFactory $urlFactory
     * @param FormFactory $formFactory
     * @param SubscriberFactory $subscriberFactory
     * @param Escaper $escaper
     * @param CustomerUrl $customerUrl
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectFactory $dataObjectFactory
     * @param Validator $formKeyValidator
     * @param Config $configHelper
     * @param PhoneNumber $phoneNumber
     * @param RequestInterface|null $request
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManagement,
        Address $addressHelper,
        UrlFactory $urlFactory,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        Escaper $escaper,
        CustomerUrl $customerUrl,
        DataObjectHelper $dataObjectHelper,
        DataObjectFactory $dataObjectFactory,
        Validator $formKeyValidator,
        Config $configHelper,
        PhoneNumber $phoneNumber,
        RequestInterface $request = null
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $scopeConfig,
            $storeManager,
            $accountManagement,
            $addressHelper,
            $urlFactory,
            $formFactory,
            $subscriberFactory,
            $escaper,
            $customerUrl,
            $dataObjectHelper,
            $dataObjectFactory,
            $formKeyValidator,
            $request
        );
        
        $this->configHelper = $configHelper;
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Create customer account action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->configHelper->isEnabled()) {
            return parent::execute();
        }
        
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/create');
        }
        
        if (!$this->getRequest()->isPost()) {
            return $resultRedirect->setPath('*/*/create');
        }
        
        $this->session->regenerateId();
        
        try {
            // Validate phone number
            $phoneNumber = $this->getRequest()->getParam('phone_number');
            
            if (empty($phoneNumber)) {
                throw new LocalizedException(__('Phone number is required.'));
            }
            
            $this->phoneNumber->setPhoneNumber($phoneNumber);
            if (!$this->phoneNumber->validatePhoneNumber()) {
                throw new LocalizedException(__(
                    'Phone number must be between %1 and %2 digits.',
                    $this->configHelper->getMinPhoneLength(),
                    $this->configHelper->getMaxPhoneLength()
                ));
            }
            
            // Validate OTP
            $otpData = $this->session->getData('otp_' . $phoneNumber);
            if (!$otpData || !isset($otpData['verified']) || !$otpData['verified']) {
                throw new LocalizedException(__('Please verify your phone number with OTP before registration.'));
            }
            
            // Generate email from phone number
            $domain = $this->getDomainFromStore();
            $email = $this->phoneNumber->generateEmailFromPhone($phoneNumber, $domain);
            
            // Add email to request parameters
            $this->getRequest()->setParam('email', $email);
            
            // Call parent execute() to handle the rest of the registration process
            return parent::execute();
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->session->setCustomerFormData($this->getRequest()->getParams());
            return $resultRedirect->setPath('*/*/create');
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('An error occurred while creating your account.'));
            $this->session->setCustomerFormData($this->getRequest()->getParams());
            return $resultRedirect->setPath('*/*/create');
        }
    }

    /**
     * Get domain from current store
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getDomainFromStore(): string
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $parsedUrl = parse_url($baseUrl);
        
        return $parsedUrl['host'] ?? 'example.com';
    }
}