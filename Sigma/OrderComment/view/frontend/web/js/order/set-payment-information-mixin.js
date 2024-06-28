define([
    'jquery',
    'mage/utils/wrapper',
    'Sigma_OrderComment/js/order/order-comments-assigner'
], function ($, wrapper, orderCommentsAssigner) {
    'use strict';

    return function (placeOrderAction) {

        return wrapper.wrap(placeOrderAction, function (originalAction, messageContainer, paymentData) {
            orderCommentsAssigner(paymentData);

            return originalAction(messageContainer, paymentData);
        });
    };
});
