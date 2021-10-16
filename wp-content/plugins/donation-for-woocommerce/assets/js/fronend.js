$ = jQuery;

if ( jQuery("form.woocommerce-checkout").length > 0 && !( jQuery("form.woocommerce-checkout").hasClass('wc-roundoff')  )  ) {
	jQuery("form.woocommerce-checkout").addClass('wc-roundoff');
}

function add_popup_before_order () {
	jQuery.ajax({
		url: wcOrderScript.donationToOrder.ajaxUrl,
		type: "POST",
		dataType: "json",
		data: {
			action: 'add_popup_before_order',
			nonce: wcOrderScript.donationToOrder.nonce
		},
		beforeSend: function () {
			//if loader needs to add put here
		},
		success: function (response) {
			if (response['status']=='success') {
				jQuery(".wc-donation-popup").find("#wc-donation-price-" + response['campaign_id']).val(response['donation']);
				var text = jQuery(".wc-donation-popup").find(".donation_text").text();
				var res = text.replace("%amount%", response['donation']);
				jQuery(".wc-donation-popup").find(".donation_text").text(res);
				jQuery(".wc-donation-popup").addClass('wc-popup-show');
			} else {
				jQuery("form.woocommerce-checkout").submit();
			}
		}
	});
}

/* when click on place order*/
jQuery( document ).on(
	"click",
	"#place_order",
	function (e) {
		if ( wcOrderScript.donationToOrder.is_roundOff == 'yes' ) {
			e.preventDefault();
			//debugger;
			add_popup_before_order();
		}
	}
);

function NumbersOnly(myfield, e, dec) {
	/*if ( isNaN(removeCommas(myfield.value)) && myfield.value != "-") {
        return false;
	}*/
	// console.log(min);
	// console.log(max);
	
	// var priceEl = document.getElementById('wc-donation-price-' + id);
    var allowNegativeNumber = false;
    var key;
    var keychar;

    if (window.event)
        key = window.event.keyCode;
    else if (e)
        key = e.which;
    else
        return true;
    keychar = String.fromCharCode(key);
	var srcEl = e.srcElement ? e.srcElement : e.target;    
	
	// if (typeof(priceEl) != 'undefined' && priceEl != null) {
		
	// 	priceEl.value = isFinite(parseFloat(myfield.value)) ? parseFloat(myfield.value) : '';
	// }
    // control keys
    if ((key == null) || (key == 0) || (key == 9) || (key == 13) || (key == 27) ) {
		return true;
	} 
	
	// if ( min != '' && max != '' ) {
	// 	if ( ( parseFloat(myfield.value) >= min) && ( parseFloat(myfield.value) <= max) ) {
	// 		if (typeof(priceEl) != 'undefined' && priceEl != null) {
	// 			priceEl.value = isFinite(parseFloat(myfield.value)) ? parseFloat(myfield.value) : '';
	// 		}
	// 		return true;
	// 	} else {
	// 		myfield.value = '';
	// 		if (typeof(priceEl) != 'undefined' && priceEl != null) {
	// 			priceEl.value = isFinite(parseFloat(myfield.value)) ? parseFloat(myfield.value) : '';
	// 		}
	// 		return false;
	// 	}
	// }

    // numbers
    if ((("0123456789").indexOf(keychar) > -1) )
        return true;

    // decimal point jump
    else if (dec && (keychar == ".")) {
        //myfield.form.elements[dec].focus();
        return srcEl.value.indexOf(".") == -1;        
	}
	
	else if (dec && (keychar == ",")) {
		return srcEl.value.indexOf(",") == -1;
	}

    //allow negative numbers
    else if (allowNegativeNumber && (keychar == "-")) {    
        return (srcEl.value.length == 0 || srcEl.value == "0.00")
    }
    else
    return false;
}

jQuery(document).on('focusout', '.grab-donation', function() {
	var This = jQuery(this);
	var min = This.data('min');
	var max = This.data('max');
	var id = This.data('campaign_id');
	var rand_id = This.data('rand_id');
	var priceEl = document.getElementById('wc-donation-price-' + id + '_' + rand_id);
	var val = This.val();

	if ( val >= min && val <= max ) {		
		priceEl.value = val;
	} else {
		priceEl.value = '';
		This.val('');
	}
});

jQuery(document).on("click", ".wc-close", function(){
	jQuery(this).parents(".wc-donation-popup").removeClass("wc-popup-show");
});


function addDonationToOrder(type, amount, min_amount, max_amount, campaign_id, is_recurring, new_period, new_length, new_interval, tracker, donation_type) {
	
	let skip = false;

	if ( type === 'roundoff-skip' ) {
		jQuery("form.woocommerce-checkout").submit();
		return true;	
	} 

	if ( (amount != 0 && amount != null && amount > 0) ) {
		jQuery.ajax(
			{
				url: wcOrderScript.donationToOrder.ajaxUrl,
				type: "POST",
				dataType: "json",
				data: {
					action: wcOrderScript.donationToOrder.action,
					nonce: wcOrderScript.donationToOrder.nonce,
					campaign_id: campaign_id,
					amount: amount,
					type: type,
					is_recurring: is_recurring,
					new_period: new_period,
					new_length: new_length,
					new_interval: new_interval
				},
				beforeSend: function() {

				},
				success: function (response) {
					if (response['response'] == 'success') {
						jQuery( "[name='update_cart']" ).prop( "disabled", false );
						jQuery( "[name='update_cart']" ).trigger( "click" );
						if (type==='roundoff') {
							jQuery(".wc-donation-popup").removeClass("wc-popup-show");
						}
						
						jQuery('body').trigger('update_checkout');

						if ( response['checkoutUrl'] != '' ) {
							window.location.href = response['checkoutUrl'];
						}

					}
				}
			}
		);
	} else {
		var text = wcOrderScript.donationToOrder.other_amount_placeholder;
		text = text.replace("%min%", min_amount);
		text = text.replace("%max%", max_amount);
		if ( donation_type == 'both' || donation_type == 'predefined' ) {
			if ( tracker == 1 ) {				
				alert(`${text}`);
			} else {
				alert(`Please select amount`);
			}
		} else {
			alert(`${text}`);
		}
		return true;
	}

}

jQuery( document ).on("click", ".wc-donation-f-submit-donation, #wc-donation-round-f-submit-donation, #wc-donation-round-f-submit-skip-donation", function (e) {
		e.preventDefault();
		var parents = jQuery(this).parents('.wc-donation-in-action');
		var type = jQuery(this).data('type');
		var campaign_id = jQuery(this).siblings('.wc_donation_camp').val();
		var rand_id = jQuery(this).siblings('.wp_rand').val();
		var is_recurring = false;
		var new_period = 'day';
		var new_length = '1';
		var new_interval = '1';
		var min_amount = 0;
		var max_amount = 0;
		var donation_type = jQuery(parents).data('donation-type');
		var tracker = jQuery(parents).find('.wc-opened').length;

		var tracker2 = jQuery(parents).find('.wc-donation-f-donation-other-value').length;

		if ( jQuery(this).data('min-value') ) {
			min_amount = jQuery(this).data('min-value');
		}

		if ( jQuery(this).data('max-value') ) {
			max_amount = jQuery(this).data('max-value');
		}

		if ( jQuery(parents).find('.wc_is_recurring').length > 0 && jQuery(parents).find('.wc_is_recurring').is(':checked') ) {
			is_recurring = jQuery(parents).find('.donation-is-recurring').val();
			new_period = jQuery(parents).find('._subscription_period').val();
			new_length = jQuery(parents).find('#_subscription_length').val();
			new_interval = jQuery(parents).find('#_subscription_period_interval').val();
		} else {
			is_recurring = 'no';
		}

		//alert(campaign_id);
		if ( type == 'roundoff' || type ==  'roundoff-skip' ) {
			var amount = jQuery('.roundoff-donation-price').val();
		} else {
			var id = '.donate_' + campaign_id + '_' + rand_id;
			//alert(id);
			var amount = jQuery(id).val();
		}
		
		addDonationToOrder(type, amount, min_amount, max_amount, campaign_id, is_recurring, new_period, new_length, new_interval, tracker, donation_type);
	}
);

jQuery(document).on('click', 'label.wc-label-button input[type="radio"][name="wc_label_price"]', function() {
	
	if ( jQuery(this).is(':checked') ) {
		//alert('I clicked');
		jQuery(this).parent('.wc-label-button').siblings().removeClass('wc-active');
		jQuery(this).parent('.wc-label-button').addClass('wc-active');
	}
});

jQuery(document).on('change copy paste click', '.wc-label-button input, .wc-label-radio input, .wc-label-select', function(){

	var id = jQuery(this).data('id');
	jQuery('#wc-donation-price-' + id).val(jQuery(this).val());

	if ( jQuery(this).val() == 'wc-donation-other-amount' ) {
		jQuery("#wc-donation-f-donation-other-value-" + id).show();
		jQuery("#wc-donation-f-donation-other-value-" + id).addClass('wc-opened');
	} else {
		jQuery("#wc-donation-f-donation-other-value-" + id).hide();
		jQuery("#wc-donation-f-donation-other-value-" + id).removeClass('wc-opened');
	}
});

jQuery(document).on('change', '._subscription_period', function(){

	var $this = jQuery(this);
	var period = $this.val();
	var len = $this.next('._subscription_length').val();

	jQuery.ajax({
		url: wcOrderScript.donationToOrder.ajaxUrl,
		type: "POST",
		dataType: "json",
		data: {
			action: 'wc_donation_get_sub_length_by_sub_period',
			period: period,
			length: len
		},		
		success: function (response) {
			if ( response.range != '' ) {
				$this.parents('.wc-donation-in-action').find('.range').html(response.range);
			}
		}
	});

});