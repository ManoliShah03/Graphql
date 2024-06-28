define([
    'jquery',
    'mage/utils/wrapper',
    'Sigma_OrderComment/js/order/order-comments-assigner'
], function ($, wrapper, orderCommentsAssigner) {
    'use strict';

    return function (placeOrderAction) {

        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            orderCommentsAssigner(paymentData);

            return originalAction(paymentData, messageContainer);
        });
    };
});
