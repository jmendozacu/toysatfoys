/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'Mageplaza_Osc/js/action/set-checkout-information',
    'Mageplaza_Osc/js/model/braintree-paypal',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/quote',
    'underscore'
], function ($, setCheckoutInformationAction, braintreePaypalModel, additionalValidators, quote, _) {
    'use strict';
    return function (BraintreePaypalComponent) {
        return BraintreePaypalComponent.extend({
            /**
             * Set list of observable attributes
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                var self = this;

                this._super();
                // for each component initialization need update property
                this.isReviewRequired = braintreePaypalModel.isReviewRequired;
                this.customerEmail = braintreePaypalModel.customerEmail;
                this.active = braintreePaypalModel.active;

                return this;
            },
            /**
             * Get shipping address
             * @returns {Object}
             */
            getShippingAddress: function () {
                var address = quote.shippingAddress();
                if (!address) {
                    address = {};
                }
                if (!address.street) {
                    address.street = ['', ''];
                }
                if (address.postcode === null) {
                    return {};
                }

                return {
                    recipientName: address.firstname + ' ' + address.lastname,
                    streetAddress: address.street[0],
                    locality: address.city,
                    countryCodeAlpha2: address.countryId,
                    postalCode: address.postcode,
                    region: address.regionCode,
                    phone: address.telephone,
                    editable: this.isAllowOverrideShippingAddress()
                };
            }
        })
    }
});