define([
    'jquery'
], function ($) {
    'use strict';

    return function (paymentData) {

        if (paymentData['extension_attributes'] === undefined) {
            paymentData['extension_attributes'] = {};
        }
        var id = jQuery(".payment-method._active").find("input.radio").val();
        paymentData['extension_attributes']['comments'] = jQuery('textarea#'+id).val();
    };
});
