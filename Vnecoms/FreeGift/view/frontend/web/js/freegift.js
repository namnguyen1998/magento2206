/**
* Copyright © 2016 Magento. All rights reserved.
* See COPYING.txt for license details.
*/
define([
	'ko',
    'uiComponent',
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/translate',
    'mage/template',
    'priceUtils',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert',
    'domReady!'
], function (ko, Component, $, customerData, $t, mageTemplate, utils, uiModal, uiAlert) {
    'use strict';

    return Component.extend({
    	defaults: {
    		template: 'Vnecoms_FreeGift/product-list',
    		products: [],
    		priceFormat: null,
			basePriceFormat: null,
			priceTemplate: '<span class="price"><%- data.formatted %></span>',
			exchangeRate: 1,
			addProductUrl: '',
			current_page: '',
			freegift_limit: 1,
			added_freegift_count: 0,
			tracks: {
				products: true,
            },
    	},
    	
        initialize: function () {
        	var self = this;
        	this._super()
            .observe({
            	isLoading: true,
            	freeGiftLimit: this.freegift_limit,
            	addedFreeGiftCount: this.added_freegift_count
            });
        	
        	var freeGiftData = customerData.get('freegift');        	
        	freeGiftData.subscribe(function (updatedFreeGift) {
                this.update(updatedFreeGift);
            }, this);
        	
        	$(this.products).each(function(index,product){
        		self.products[index].isAddingToCart = ko.observable(false);
        	});
            return this;
        },
        /**
         * Update data
         */
        update: function(data){
        	var self = this;
        	var products = data.products;
        	$(products).each(function(index, product){
        		products[index].isAddingToCart = ko.observable(false);
        	});
        	this.products = products;
        	this.freeGiftLimit(data.freegift_limit);
        	this.addedFreeGiftCount(data.added_freegift_count);
        	
        },
        
        /**
         * Can show free gift block
         * 
         * @return bool
         */
        canShowBlock: function(){
        	return this.products.length > 0;
        },
        
        /**
         * Add free gift to shopping cart
         * 
         * @param object product
         */
        addFreeGiftToCart: function(product){
        	var self = this;
        	if(product.isAddingToCart()) return;
        	
        	product.isAddingToCart(true);
        	var url = this.addProductUrl;
        	$.ajax({
	      		  url: url,
	      		  method: "POST",
	      		  data: { 
	      			  product_id : product.entity_id,
	  			  },
	      		  dataType: "json"
	  		}).done(function( response ){
	  			product.isAddingToCart(true);
	  	  	  	if(response.ajaxExpired){
	  	  	  	  	window.location = response.ajaxRedirect;
	  	  	  	  	return;
	  	  	  	}
	  	  	  	if(response.redirect){
	  	  	  	  	window.location = response.redirect;
	  	  	  	  	return;
	  	  	  	}
	  	  	  	
	  	  	  	if(!response.success){
		  	  	  	uiAlert({
		        		title: $t('Error'),
		                content: response.message
		            })
	  	  	  	}else if(self.current_page == 'checkout_cart_index'){
	  	  	  		window.location.reload();
	  	  	  	}else if(self.current_page == 'onestepcheckout_index_index'){
					window.location.reload();
				}
		  	  	
	  		}).error(function (response, q, t) { 
	  			console.log(response);
	  			uiAlert({
	        		title: $t('Error'),
	                content: response.responseText
	            })
	  		});
        },
        
        /**
         * Format Price
         * 
         * @param number
         */
        formatPrice: function(number, isBaseCurrency){
        	number = parseFloat(number);
        	if(typeof(isBaseCurrency) == 'undefined' || !isBaseCurrency){
        		number = number * this.exchangeRate; /* Convert to Current currency */
        		var priceFormat 	= this.priceFormat;
        		var priceTemplate 	= mageTemplate(this.priceTemplate);
        	}else{
        		var priceFormat 	= this.basePriceFormat;
        		var priceTemplate 	= mageTemplate(this.priceTemplate);
        	}
        	
        	var priceData = {formatted:utils.formatPrice(number, priceFormat)};
            return priceTemplate({data: priceData});
        },

        /**
         * Format number with zero
         */
        formatNumber: function(number){
        	return (number <= 9)?'0' + number:number;
        }
    });
});
