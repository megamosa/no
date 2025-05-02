<?php
namespace MagoArab\WithoutEmail\Plugin\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor as MagentoLayoutProcessor;
use Magento\Framework\App\ObjectManager;
use MagoArab\WithoutEmail\Helper\Config;

class LayoutProcessor
{
    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * Constructor
     *
     * @param Config $configHelper
     */
    public function __construct(
        Config $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     * Process checkout layout
     *
     * @param MagentoLayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        MagentoLayoutProcessor $subject,
        array $jsLayout
    ) {
        if (!$this->configHelper->isEnabled()) {
            return $jsLayout;
        }
        
        // الحصول على الخدمات مباشرة من ObjectManager
        $objectManager = ObjectManager::getInstance();
        $customerSession = $objectManager->get(\Magento\Customer\Model\Session::class);
        
        // Path to shipping address fields
        $shippingAddressFieldset = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];
        
        // 1. تعديل حقل البريد الإلكتروني
        if (isset($shippingAddressFieldset['email'])) {
            // جعل حقل البريد الإلكتروني مخفياً بواسطة CSS
            $shippingAddressFieldset['email']['config']['additionalClasses'] = 'hidden-field';
            // تعطيل القواعد البصرية
            $shippingAddressFieldset['email']['config']['visible'] = false;
            // ولكن مع الاحتفاظ به في النموذج
            $shippingAddressFieldset['email']['visible'] = false;
        }
        
        // 2. تحريك حقل الهاتف ليكون في البداية وتحديثه
        if (isset($shippingAddressFieldset['telephone'])) {
            // تحديث خصائص حقل الهاتف
            $shippingAddressFieldset['telephone']['sortOrder'] = 10;
            $shippingAddressFieldset['telephone']['label'] = __('Phone Number');
            $shippingAddressFieldset['telephone']['additionalClasses'] = 'phone-field-highlight';
            $shippingAddressFieldset['telephone']['validation'] = array_merge(
                $shippingAddressFieldset['telephone']['validation'] ?? [],
                [
                    'required-entry' => true,
                    'validate-number' => true,
                    'min_text_length' => $this->configHelper->getMinPhoneLength(),
                    'max_text_length' => $this->configHelper->getMaxPhoneLength()
                ]
            );
            
            // 3. إضافة أحداث JavaScript لتعبئة البريد الإلكتروني تلقائياً
            $onChangeScript = "
                var phoneNumber = event.target.value;
                if (phoneNumber) {
                    var domain = window.location.hostname;
                    var email = phoneNumber + '@' + domain;
                    
                    var emailField = document.querySelector('input[name$=\".email\"]');
                    if (emailField) {
                        emailField.value = email;
                        var changeEvent = document.createEvent('HTMLEvents');
                        changeEvent.initEvent('change', true, false);
                        emailField.dispatchEvent(changeEvent);
                    }
                }
                return true;
            ";
            
            // إضافة الأحداث إلى حقل الهاتف
            if (!isset($shippingAddressFieldset['telephone']['config'])) {
                $shippingAddressFieldset['telephone']['config'] = [];
            }
            
            // إضافة عنصر elementTmpl مخصص لإضافة أحداث JavaScript
            $shippingAddressFieldset['telephone']['config']['customScope'] = 'shippingAddress';
            $shippingAddressFieldset['telephone']['config']['template'] = 'ui/form/field';
            $shippingAddressFieldset['telephone']['config']['elementTmpl'] = 'MagoArab_WithoutEmail/form/element/input-phone';
            
            // إعادة ترتيب الحقول
            $telephoneField = $shippingAddressFieldset['telephone'];
            unset($shippingAddressFieldset['telephone']);
            
            $newFieldset = [
                'telephone' => $telephoneField
            ];
            
            foreach ($shippingAddressFieldset as $fieldName => $fieldConfig) {
                $newFieldset[$fieldName] = $fieldConfig;
            }
            
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] = $newFieldset;
        }
        
        // 4. إضافة CSS الإضافي لإخفاء حقل البريد
        if (!isset($jsLayout['components']['checkout']['children']['additionalStyles'])) {
            $jsLayout['components']['checkout']['children']['additionalStyles'] = [
                'component' => 'Magento_Ui/js/form/components/html',
                'config' => [
                    'content' => '<style>.hidden-field { display: none !important; }</style>'
                ]
            ];
        }
        
        return $jsLayout;
    }
}