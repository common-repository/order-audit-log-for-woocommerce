var submitClicked = false;
var print_page_active = false;
function back_to_detail(){
	if(!print_page_active) return false;
	print_page_active = false;
	$ = jQuery;
	$('.hide_for_print').show();
	$('.hide_for_print').show();
	$('.hide_for_print').show();
	$('.search_for_print_block').hide();
}

function print_report(){
	window.print();
}



jQuery(function($){
	jQuery("a.opening_stock_quantity_updated").click(function (){
		popup_open 	= true;
		popup_id 	= "#confirm_set_all_opening_quantity_popup";
		showPopup();
		return false;
	});
	
	jQuery("input#btnDeleteConfirmation").click(function (){
		popup_open 	= true;
		popup_id 	= "#confirm_alert_popup";
		showPopup();
		return false;
	});
	
	jQuery("input#btnConfirmDelteNo").click(function (){		
		hidePopup();
		return false;
	});
	
	jQuery("input#btnConfirmDelteYes").click(function (){	
	
		if(submitClicked){ return false;}
		
		submitClicked 		= true;	
		
		var data = {};
		var delete_id = $(this).attr('data-delete_id');
		
		data['action']		= 'ic_inventory_ajax_request';			
		data['delete_id'] 	= $(this).attr('data-delete_id');
		data['sub_action'] 	= $(this).attr('data-sub_action');
		data['call'] 		= $(this).attr('data-action_call');
		
		//alert(JSON.stringify(data));
		//alert(JSON.stringify(ajax_object));
		
		//alert(ajax_object.ic_ajax_action)
		
		$('.buttons').hide();
		$('.alert_msg').find('p').html('Please Wait!');
		
		$.ajax({
			type: "POST",
			url: ajax_object.ic_ajax_url,
			data: data,
			success:function(data) {
				$('.alert_msg').find('p').html('Deleted successfully. Please Wait!');
				window.location = data;
				submitClicked = false;
			},
			error: function(jqxhr, textStatus, error ){	
				//alert(data)				
				//window.location = window.location;
				submitClicked = false;
			}
		});
		return false;
		
		//hidePopup();
		return false;
	});
});

jQuery(document).ready(function($) {
	
	jQuery( "#start_date" ).datepicker({
		dateFormat : 'yy-mm-dd',
		changeMonth: true,
		changeYear: true,
		onClose: function( selectedDate ) {
			$( "#end_date" ).datepicker( "option", "minDate", selectedDate );
		}
	});							
	
	jQuery( "#end_date" ).datepicker({
		dateFormat : 'yy-mm-dd',
		changeMonth: true,
		changeYear: true,
		onClose: function( selectedDate ) {
			$( "#start_date" ).datepicker( "option", "maxDate", selectedDate );
		}
	}); 
    
	$(document).on('click','.pagination a',  function(){
		var p = $(this).attr('data-p');
		$('form#search_order_pagination').find('input[name=p]').val(p);			
		$('form#search_order_pagination').submit();
		return false;
	});
	
	$(document).on('submit','form#search_order_pagination',  function(){
		
		if(submitClicked) return false;
		
		$('.ajax_progress').html("Please wait").fadeIn();
		$(".form_process").fadeIn();
		$(".onformprocess").attr('disabled',true).addClass('disabled');
		submitClicked = true;
		$.ajax({
			type	:	"POST",
			url		:	ajax_object.ic_ajax_url,
			data	:	$( "form#search_order_pagination" ).serialize(),			
			
			success:function(data) {
				
				submitClicked = false;
				$('div.search_report_content').html(data);
				$('.ajax_progress').html("Please wait").fadeOut();
				$(".form_process").hide();
				$(".onformprocess").attr('disabled',false).removeClass('disabled');
			},
			error: function(jqxhr, textStatus, error ){
				submitClicked = false;								
				//window.location = window.location;
				$(".form_process").hide();
				$(".onformprocess").attr('disabled',false).removeClass('disabled');
			}
		});
		return false;
	});
	
	
	
	$('form#search_order_report, form#frm_purchase_report').submit(function(){
		
		
		
			if(submitClicked) return false;
			
			var that = this;
			
			var errorString = "";					
			var submitButton = $(that).find('input[type="submit"]');											
			submitButton.attr('disabled',true).addClass('disabled');
			
			if(errorString.length > 1 ){			
				submitClicked = false;
				submitButton.attr('disabled',false).removeClass('disabled');
				return false;
			}
			
			$(that).find('input[type="submit"]').attr('disabled',true);
			$('.ajax_progress').html("Please wait----").fadeIn();
			$(".form_process").fadeIn();
			$(".onformprocess").attr('disabled',true).addClass('disabled');
			
			$('input[type="text"]').each(function(index, element) {
				var v = $.trim($(element).val());
				$(element).val(v);
			});
			
			submitClicked = true;
			
			$('.ajax_progress').html("Please wait").fadeIn();
			
			$.ajax({
				type: "POST",
				url: ajax_object.ic_ajax_url,
				data:  $(that).serialize(),
				success:function(data) {
					
					submitClicked = false;
					$('div.search_report_content').html(data);								
					
					submitButton.attr('disabled',false).removeClass('disabled');
					
					$('.ajax_progress').html("Please wait").fadeOut();
					$(".form_process").hide();
					$(".onformprocess").attr('disabled',false).removeClass('disabled');
					$(".form_process").hide();
					
				},
				error: function(jqxhr, textStatus, error ){
					submitClicked = false;
					$(".form_process").hide();
					//window.location = window.location;
					submitButton.attr('disabled',false).removeClass('disabled');
					$(".form_process").hide();
					$(".onformprocess").attr('disabled',false).removeClass('disabled');
				}
			});
			return false;
	});
	
	var search_content = $.trim($('div.search_report_content').html());
	if(search_content.length == 0){
		$('div.search_report_content').html("Please wait!");
		$('#SearchOrder, #ic_button').trigger('click');	
	}
	
$(document).on('click','input.button_search_for_print',  function(){

	if(submitClicked){ return false;}
	
	submitClicked 		= true;
	
	$('.hide_for_print').hide();
	$('.hide_for_print').hide();
	$('.hide_for_print').hide();
	$('.search_for_print_block').show().html("Please wait");
	var do_action_type = $(this).attr('data-do_action_type');
	var form = $(this).attr('data-form');
	
	var data = {};
	
	$('.form_search_for_print').find('input[type="hidden"]').each(function(index, element) {
		var _name = $(element).attr('name');
		data[_name] = $(element).val();
		
	});
	
	print_page_active = true;
	
	$.ajax({
		type: "POST",
		url: ajax_object.ic_ajax_url,
		data: data,
		success:function(data) {
			
			if(print_page_active){
				$('div.search_for_print_block').html(data);
			}
			
			submitClicked = false;
		},
		error: function(jqxhr, textStatus, error ){					
			window.location = window.location;
			submitClicked = false;
		}
	});
	return false;
});
	
	
	$(document).on('click','input.search_for_print',  function(){
		
			if(submitClicked){ return false;}
			
			submitClicked 		= true;
			
			$('.hide_for_print').hide();
			$('.hide_for_print').hide();
			$('.hide_for_print').hide();
			$('.search_for_print_block').show().html("Please wait");
			var do_action_type = $(this).attr('data-do_action_type');
			var form = $(this).attr('data-form');
			
			var data = {};
			
			if(form == "popup"){
				$(this).parent().parent().parent().parent().parent().find('input[type="hidden"]').each(function(index, element) {
					var _name = $(element).attr('name');
					if(_name != "export_file_format");
					data[_name] = $(element).val();
					
				});
				
				$(this).parent().parent().parent().parent().parent().find('input[type="text"]').each(function(index, element) {
					var _name = $(element).attr('name');
					data[_name] = $(element).val();
					
				});
				
				$(this).parent().parent().parent().parent().parent().find('input[type="checkbox"]').each(function(index, element) {
					var _name = $(element).attr('name');
					if(jQuery(element).is(':checked')){
						data[_name] = $(element).val();
					}
					
				});
			}else{
				$(this).parent().find('input[type="hidden"]').each(function(index, element) {
					var _name = $(element).attr('name');
					if(_name != "export_file_format");
					data[_name] = $(element).val();
					
				});
			};
			
			data['action'] = ajax_object.ic_ajax_action;
			
			data['do_action_type'] = do_action_type;
						
			print_page_active = true;
			
			$.ajax({
				type: "POST",
				url: ajax_object.ic_ajax_url,
				data: data,
				success:function(data) {
					
					if(print_page_active){
						$('div.search_for_print_block').html(data);
					}
					
					submitClicked = false;
				},
				error: function(jqxhr, textStatus, error ){					
					window.location = window.location;
					submitClicked = false;
				}
			});
			return false;
	});
	
	$(document).on('click','a.delete_item',  function(){		
		var delete_id = $(this).attr('data-delete_id');
		$('form#search_order_pagination').find('input[name=delete_id]').val(delete_id);			
		$('form#search_order_pagination').submit();
		return false;		
	});
	
	$(document).on('click','input#btnDelete',  function(){		
		var delete_id = $(this).attr('data-delete_id');
		$('form#search_order_pagination').find('input[name=delete_id]').val(delete_id);			
		$('form#search_order_pagination').submit();
		return false;		
	});
	
	
	
	
});