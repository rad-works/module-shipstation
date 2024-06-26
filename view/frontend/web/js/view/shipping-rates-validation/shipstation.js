define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    '../../model/shipping-rates-validator/shipstation',
    '../../model/shipping-rates-validation-rules/shipstation'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    shipstationShippingRatesValidator,
    shipstationShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('shipstation', shipstationShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('shipstation', shipstationShippingRatesValidationRules);

    return Component;
});
