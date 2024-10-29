jQuery('body').on('click','.leadbox-submit', function(e) {
	e.preventDefault();
	var saveBtn = jQuery(this);
	var leadboxForm = saveBtn.closest('form');
	var recaptchaBox = leadboxForm.find('.g-recaptcha-response');
	var oldBtnTxt = saveBtn.html();
	
	saveBtn.prop('disabled', true);
	saveBtn.html('<div class="leadbox-loader"></div>');

	var postedValues = {};
	leadboxForm.find("input, select").each(function(i, v) {
		postedValues[jQuery(v).attr('name')] = jQuery(v).val();
	});
	postedValues['g-recaptcha-response'] = recaptchaBox.val();

	var postdata = {
		'action': 'APWTLeadBox',
		'apwtleadbox': 'submit',
		'apwtvalues':  postedValues
	};

  jQuery.ajax({
		type : "post",
		dataType : "json",
		url : APWTajaxurl.ajaxurl,
		data : postdata,
		success: function(response) {
		  if(response.status == "error") {
		    jQuery(leadboxForm.children('.leadbox-error')).html(response.message);
			if (typeof(grecaptcha) != 'undefined') {
				var widgetId = parseInt(leadboxForm.find('.recaptcha-widget').val());
				try {
					grecaptcha.reset(widgetId); //reset recaptcha
				} catch (e) {
					console.log(e);
				}
			}
		  } else {
				if (response.status != 'paige') {
					if (response.status == 'url') {
						window.location.href = response.message;
					} else {
						leadboxForm.html(thanksMessage);
					}
				}
		  }
		  saveBtn.prop('disabled', false);
		  saveBtn.html(oldBtnTxt);
		}
  });
});