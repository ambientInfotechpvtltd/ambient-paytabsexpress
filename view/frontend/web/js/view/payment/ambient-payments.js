/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'ambient',
                component: 'Ambient_Paytabs/js/view/payment/method-renderer/ambient-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
