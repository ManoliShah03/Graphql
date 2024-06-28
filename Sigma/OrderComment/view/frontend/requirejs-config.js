var config = {
config: {
    mixins: {
        'Magento_Checkout/js/action/place-order': {
            'Sigma_OrderComment/js/order/place-order-mixin': true
        },
        'Magento_Checkout/js/action/set-payment-information': {
            'Sigma_OrderComment/js/order/set-payment-information-mixin': true
        }
    }
}
};
