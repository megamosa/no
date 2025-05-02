/**
 * MagoArab_WithoutEmail extension
 *
 * @category  MagoArab
 * @package   MagoArab_WithoutEmail
 * @author    MagoArab
 */
define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';
    
    return function (config) {
        $(document).ready(function () {
            var minLength = config.minLength || 9;
            var maxLength = config.maxLength || 15;
            
            // Add custom validation for phone number
            $.validator.addMethod(
                'validate-phone-number',
                function (value) {
                    if (value.length < minLength || value.length > maxLength) {
                        return false;
                    }
                    
                    // Check if it contains only digits
                    return /^\d+$/.test(value);
                },
                $t('Phone number must be between ' + minLength + ' and ' + maxLength + ' digits and contain only numbers.')
            );
            
            // Highlight phone field
            $('.phone-field-highlight input').focus(function () {
                $(this).closest('.field').addClass('focused');
            }).blur(function () {
                $(this).closest('.field').removeClass('focused');
            });
        });
    };
});