define([
    'jquery',
    'ko'
], function ($, ko) {
    'use strict';

    return function (target) {
        return target.extend({
            validateShippingInformation: function () {
                // احصل على النتيجة الأصلية
                var originalResult = this._super();
                
                // تحديث حقل البريد الإلكتروني تلقائيًا من رقم الهاتف
                var $phoneField = $('input[name="telephone"]');
                if ($phoneField.length && $phoneField.val()) {
                    var phoneValue = $phoneField.val();
                    var domain = window.location.hostname;
                    var email = phoneValue + '@' + domain;
                    
                    // تحديث حقل البريد الإلكتروني
                    var $emailField = $('#customer-email');
                    if ($emailField.length && (!$emailField.val() || $emailField.val() === '')) {
                        $emailField.val(email).trigger('change').trigger('blur');
                        
                        // تحديث كائن knockout إذا كان موجودًا
                        try {
                            var viewModel = ko.dataFor($emailField[0]);
                            if (viewModel && viewModel.email && typeof viewModel.email === 'function') {
                                viewModel.email(email);
                            }
                        } catch (e) {
                            console.log('KO update error', e);
                        }
                    }
                }
                
                return originalResult;
            }
        });
    };
});