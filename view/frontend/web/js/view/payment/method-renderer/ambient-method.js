/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'mage/url'
    ],
    function (Component, url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Ambient_Paytabs/payment/ambient-form'
            },
            redirectAfterPlaceOrder: false,
            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                //window.location.replace(url.build('citrus/checkout/start'));
				jQuery(function($) {
				//alert(url.build('citrus/checkout/start'));
				//$("#paytabsloader",parent.document).html('<b>OK</b>');
				$.ajax({
        			url: url.build('ambient/checkout/start'),
		        	type: 'get',
        			dataType: 'json',
					cache: false,
        			processData: false, // Don't process the files
        			contentType: false, // Set content type to false as jQuery will tell the server its a query string request
              beforeSand: function (obj) {
                jQuery('body').trigger('processStart');
              },
		        	success: function (data) {
                jQuery('body').trigger('processStop');
                    	$("#paytabsexpressloader",parent.document).html(data['html']);
						//alert(data['html']);
                	},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
    			});
				});
            }
        });
    }
);
