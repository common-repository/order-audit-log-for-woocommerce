jQuery(document).ready(function($) {
	
	$("input.numberonly").each(function(index, element) {
		var that 		= this;		
        var maxlength 	= parseInt($(that).attr("maxlength"));
		var str = "";
		for(i = 0; i < maxlength; i++){
			str = str + "9";
		}
		$(that).attr("data-max",str);
    });
	
	$("input.numberonly").keydown(function(event) {
		//return ;
		
		//$(".wrap").prepend(event.keyCode + ", <br>");
		
		// Allow: backspace, delete, tab, escape, enter and .
		if ( $.inArray(event.keyCode,[46,8,9,27,13,190]) !== -1 ||
			// Allow: Ctrl+A
			(event.keyCode == 65 && event.ctrlKey === true) || 
			
			// Allow: Ctrl+C
			(event.keyCode == 67 && event.ctrlKey === true) || 
			
			// Allow: Ctrl+V
			(event.keyCode == 86 && event.ctrlKey === true) || 
			
			 // Allow: home, end, left, right
			(event.keyCode >= 35 && event.keyCode <= 39)) {
				 // let it happen, don't do anything
				 return;
		}
		else {
			// Ensure that it is a number and stop the keypress
			if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
				event.preventDefault(); 
			}   
		}
	});
	
	
	/* IC Popup */
	$('.ic_open_popup').click(function(){
		popup_id = $(this).attr('data-popup_id');
		popup_open 	= true;		
		showPopup();
		return false;		
	});
	
	$('.ic_close_popup').click(function(){
		hidePopup();
	});
	
	$(window).resize(function(){
		center();
	});
	
	$(".save_order").attr('data-save_action','no');
	
	$("#btnStockAlertOK").click(function(){
		hidePopup();
		$(".save_order").trigger('click');
	});
	
	//$(".save_order").click(function(){
		
		/*var tr_order_refunds = $("#order_refunds").find("tr.refund").size();
		
		if(tr_order_refunds > 0){
			popup_id = '#popup_refunded_order_found';
			popup_open 	= true;		
			showPopup();									
			return false;
		}
		return true;*/
	//});
	
	/*$(".save_order").click(function(){
		
			var save_action = $(this).attr('data-save_action');
			
			if(save_action == 'no'){
				
				if(check_old_ic_item_quantity() == false){
					popup_id = '#stock_alert_edited';
					popup_open 	= true;		
					showPopup();
					$(this).attr('data-save_action','yes');
					return false;
				}
			}
			
		
		return true;
	});*/
	
	$(document).on('click', '#btnStockAlert', function(){
		$(".save-action").trigger('click');
		hidePopup();
	});
	
	
	$(document).on('click', '#btnPopupDeleteOrderItemOK', function(){
		hidePopup();
		var delete_item_number = $(this).attr('data-delete_item_number');							
		$('.'+delete_item_number).attr('data-delete_action','yes');
		$('.'+delete_item_number).trigger('click');
		return false;
	});
	
	$(document).on('click', '#btnBulkDeleteIItemsOK', function(){
		hidePopup();
		$('.bulk-delete-items').attr('data-delete_action','yes');
		$('.bulk-delete-items').trigger('click');
		hidePopup();
	});
	
	
	/*$('#order_line_items').on('click', '.delete-order-item', function(){							
		var delete_action = $(this).attr('data-delete_action');
		if(delete_action == 'no'){
			var delete_item_number = $(this).attr('data-delete_item_number');
			popup_id = '#popup_delete_order_item';
			popup_open 	= true;		
			showPopup();
			$('#btnPopupDeleteOrderItemOK').attr('data-delete_item_number',delete_item_number);
			return false;
		}
	});*/
	
	$(document).on('click', '#btnPopupQuantityRefundedOK', function(){
		$(".do-manual-refund").attr('data-save_action','yes');
		$(".do-manual-refund").trigger('click');
		return false;	
	});
	
	$(document).on('click', '#btnPopupDeleteRefundOK', function(){
		hidePopup();
		var delete_refund_number = $(this).attr('data-delete_refund_number');							
		$('.'+delete_refund_number).attr('data-delete_action','yes');
		$('.'+delete_refund_number).trigger('click');
		return false;
	});
	
	
	
	//button save_order button-primary tips
	//$( 'button.cancel-action' ).attr( 'data-reload', true );
	
	/*<input type="button" value="Open Popup" name="" id="" class="ic_button ic_open_popup" data-popup_id="confirm_alert_popup"  />*/
	
	/*
		<div class="ic_popup_mask"></div>
        <div id="confirm_alert_popup" class="ic_popup_box confirm_alert">
            <a class="popup_close" title="Close popup"></a>
            <h4><?php _e('Alert')?></h4>
            
            <div class="popup_content">
                <div class="alert_msg">
                    <p><?php _e('Do you want to delete.');?></p>
                </div>
                <div style=" text-align:right;" class="buttons">
                	<input type="button" value="Yes" name="btnConfirmDelte" id="btnConfirmDelteYes" class="ic_button"  data-delete_id="<?php echo $ID;?>" data-sub_action="ic_purchase_list" data-action_call="ic_purchase_delete"  />
                    <input type="button" value="No" name="btnConfirmDelte" id="btnConfirmDelteNo" class="ic_button ic_close_popup"  />
                </div>
                <div class="clear"></div>
            </div>
         </div>
	*/
});

/* Vars */
var popup_open 			= false;
var popup_id 			= null;
var ic_item_quantity 	= {};

/* functions */
function center() {
	if (popup_open == true) {
		if(popup_id == null) return;
		
		obj = popup_id;
		var $ = jQuery;
		var windowWidth = document.documentElement.clientWidth;
		var windowHeight = document.documentElement.clientHeight;
		var popupHeight = $(obj).height();
		var popupWidth = $(obj).width();
		$(obj).css({
			"position": "fixed",
			"top": windowHeight / 2 - popupHeight / 2,
			"left": windowWidth / 2 - popupWidth / 2
		}).fadeIn();
	}
}

function hidePopup() {
	var $ = jQuery;
	if (popup_open == true) {
		$(".ic_popup_mask").fadeOut("slow");
		$(".ic_popup_box").fadeOut("slow");
		popup_open = false;
		popup_id = null;
	}
}

function showPopup() {//alert(1)
	var $ = jQuery;
	if (popup_open == true) {
		$(".ic_popup_mask").fadeIn("slow");
		$(popup_id).fadeIn("slow");
		center();
	}
}

function create_old_ic_item_quantity(){
	var $ = jQuery;
	ic_item_quantity 	= {};
	jQuery('input.quantity').each(function(index, element) {
		var quantity = jQuery(this).val();
		var name	= jQuery(this).attr('name');
		//name = name.replace( /^\D+/g, '');
		name = name.replace(/[^0-9]/g,'');
		ic_item_quantity[name] = quantity;
	});
	
	//alert(get_data_size(ic_item_quantity))
	//alert(JSON.stringify(ic_item_quantity));
}


function check_tottal_order_item_qty(quantity_field_class){
	var $ = jQuery;
	var total_qty = 0;	
	jQuery('input.'+quantity_field_class).each(function(index, element) {
		var quantity = jQuery(this).val();
		
		if(quantity == ""){
			quantity = 0;
		}
		
		quantity = parseInt(quantity);
		
		total_qty = total_qty + quantity;
	});
	
	return total_qty;
}

function check_old_ic_item_quantity(quantity_field_class){
	var $ = jQuery;
	var ic_new_item_quantity 	= {};
	var r = true;
	var qtymatched = true;
	jQuery('input.'+quantity_field_class).each(function(index, element) {
		var quantity = jQuery(this).val();
		var name	= jQuery(this).attr('name');
		name = name.replace(/[^0-9]/g,'');
		ic_new_item_quantity[name] = quantity;
		
		var id_found = false;
		
		jQuery.each(ic_item_quantity,function (id, old_quantity){
			if(name == id){
				if(quantity != old_quantity){
					//return false;
					qtymatched = false;
				}
				id_found = true;
			}
		});
		
		if(id_found == false){
			return false;
		}
	});
	
	//alert(JSON.stringify(ic_item_quantity));
	//alert(JSON.stringify(ic_new_item_quantity));
	
	//alert(get_data_size(ic_item_quantity) + " == "+ get_data_size(ic_new_item_quantity))
	
	if (qtymatched ==false){
		return qtymatched;
	}
	if(get_data_size(ic_item_quantity) != get_data_size(ic_new_item_quantity)){
		return false;
	}
	
	return r;
}



function get_data_size(data){
	var i = 0;
	jQuery.each(data,function (id, quantity){
		i++;
	});
	return i;	
}

function block_content(){
	jQuery('div.block_content').block({ 
		//message: '<h2>Processing, please wait!</h2>', 
		//message: '<img src="http://www.accufinance.com/images/busy.gif" id="loader"/>',
		message: null,
		css: {
			backgroundColor			: '#fff',
			'-webkit-border-radius'	: '10px',
			'-moz-border-radius'	: '10px',
			border		:'1px solid #5AB6DF',
			//border		:'none',
			padding		:'15px',
			paddingTop	:'19px',
			opacity		:.9,
			color		:'#fff'
		},
		overlayCSS: {
			background	: '#fff',
			//background	: 'none',
			opacity		: 0.6
		}
	});
}

function unblock_content(){
	jQuery('div.block_content').unblock();
}
