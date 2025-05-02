var config = {
    map: {
        '*': {
            'magoarab_checkout_email_filler': 'MagoArab_WithoutEmail/js/checkout-email-filler'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'MagoArab_WithoutEmail/js/view/shipping-mixin': true
            }
        }
    }
};