<?php
if(!class_exists('ic_commerce_audit_log_lite_class')){
	class ic_commerce_audit_log_lite_class extends ic_commerce_audit_log_lite_function{
		
		var $constants 	= array();
		
		public function __construct($constants = array()){			
			
			$this->constants = $constants;
			
			add_action('woocommerce_ajax_add_order_item_meta', 				array($this, 'ic_commerce_ajax_add_order_item_meta'),		101,2);
			add_action('woocommerce_before_save_order_items',				array($this, 'ic_commerce_before_save_order_items'),		101,2);			
			add_action('woocommerce_before_delete_order_item',				array($this, 'ic_commerce_before_delete_order_item'),		101,1);
			add_action('before_delete_post', 								array( $this,'ic_commerce_delete_order_items'),				7,1);
			
			add_action('wp_trash_post',										array($this, 'ic_commerce_order_status_changed_trash'),		101,1);
			add_action('untrash_post',										array($this, 'ic_commerce_order_status_changed_untrash'),	101,1);
			
			add_action('woocommerce_refund_deleted',						array($this, 'ic_commerce_refund_deleted'),					101,2);
			
		}
		
		function query_query($query = ''){
			
			return $query;
		}
		
		
		function add_order_item_log($item = array(), $order_id = 0,$item_id = '', $action_log = 'order_item_added'){
			global $wpdb;
			
			$datetime				= date_i18n("Y-m-d H:i:s");	
			$table_name 			= $this->constants['table_name'];;			
			$user_id				= get_current_user_id();
			
			$qty 					= isset($item['qty']) 			? $item['qty'] : 0;
			$product_id 			= isset($item['product_id']) 	? $item['product_id'] : 0;
			$variation_id 			= isset($item['variation_id']) 	? $item['variation_id'] : 0;
			$name 					= isset($item['name']) 			? $item['name'] : 0;			
			$line_total 			= isset($item['line_total']) 	? $item['line_total'] : 0;
			$line_subtotal 			= isset($item['line_subtotal']) ? $item['line_subtotal'] : 0;
			
			$new_qty 				= 1;
			$old_qty 				= 0;			
			$order_data				= $this->get_order_data($order_id);
			$order_date 			= isset($order_data->post_date) ? $order_data->post_date : '';
			$order_status 			= isset($order_data->post_status) ? $order_data->post_status : '';
			$customer_id			= get_post_meta($order_id,'_customer_user',true);
			
			switch($action_log){
				case "order_item_added":
					$new_qty 		= $qty;
					$old_qty 		= $qty;
					break;
			}
			
			$data = array();
			$data['user_id'] 			= $user_id;
			$data['date'] 				= $datetime;
			$data['order_item_name']	= $name;
			$data['order_id'] 			= $order_id;
			$data['order_item_id'] 		= $item_id;
			$data['product_id'] 		= $product_id;
			$data['variation_id'] 		= $variation_id;
			$data['old_order_item_qty'] = $old_qty;
			$data['new_order_item_qty'] = $new_qty;
			$data['action_log'] 		= $action_log;			
			$data['line_total'] 		= $line_total;
			$data['line_subtotal'] 		= $line_subtotal;
			$data['order_date'] 		= $order_date;
			$data['customer_id'] 		= $customer_id;
			$data['old_order_status'] 	= $order_status;
			$data['new_order_status'] 	= $order_status;
			$result 					= $wpdb->insert($table_name, $data);
		}
		
		function ic_commerce_before_save_order_items_save($order_id = '', $new_items = array(), $type = 'order_item_edited'){			
			global $wpdb;
			
			$datetime	= date_i18n("Y-m-d H:i:s");	
			$table_name = $this->constants['table_name'];;			
			$user_id	= get_current_user_id();
			
			/*Old*/			
			$order 		= wc_get_order( $order_id );
			$items 		= $order->get_items();
			
			$order_data				= $this->get_order_data($order_id);
			$order_date 			= isset($order_data->post_date) ? $order_data->post_date : '';
			$order_status 			= isset($order_data->post_status) ? $order_data->post_status : '';
			$customer_id			= get_post_meta($order_id,'_customer_user',true);
			
			
			
			if(count($items) >0){
				foreach($items as $item_id => $item ) {
					
					
					$qty 			= isset($item['qty']) ? $item['qty'] : 0;
					$product_id 	= isset($item['product_id']) ? $item['product_id'] : 0;
					$variation_id 	= isset($item['variation_id']) ? $item['variation_id'] : 0;
					$name 			= isset($item['name']) ? $item['name'] : 0;
					
					$line_total 	= isset($new_items['line_total'][$item_id]) ? $new_items['line_total'][$item_id] : 0;
					$line_subtotal 	= isset($new_items['line_subtotal'][$item_id]) ? $new_items['line_subtotal'][$item_id] : 0;
					
					$old_line_total 	= isset($item['line_total']) ? $item['line_total'] : 0;
					$old_line_subtotal 	= isset($item['line_subtotal']) ? $item['line_subtotal'] : 0;
					
					$new_qty 		= 0;
					$old_qty 		= wc_stock_amount($qty);
					if(isset($new_items['order_item_qty'][$item_id]) and $type == 'order_item_edited'){
						$new_qty = wc_stock_amount( $new_items['order_item_qty'][ $item_id ] );
					}elseif($type == 'deleted'){
						$new_qty 		= 0;
					}else{
						$new_qty 		= $old_qty;
					}
					
					if((
					   ($new_qty  != $old_qty and $type == 'order_item_edited') 
						|| ($line_total  != $old_line_total and $type == 'order_item_edited')
						|| ($line_subtotal  != $old_line_subtotal and $type == 'order_item_edited')
					)){
						$data = array();
						$data['user_id'] 			= $user_id;
						$data['date'] 				= $datetime;
						$data['order_item_name']	= $name;
						$data['order_id'] 			= $order_id;
						$data['order_item_id'] 		= $item_id;
						$data['product_id'] 		= $product_id;
						$data['variation_id'] 		= $variation_id;
						$data['old_order_item_qty'] = $old_qty;
						$data['new_order_item_qty'] = $new_qty;
						$data['action_log'] 		= $type;						
						$data['line_total'] 		= $line_total;
						$data['line_subtotal'] 		= $line_subtotal;
						$data['order_date'] 		= $order_date;
						$data['customer_id'] 		= $customer_id;
						$data['old_order_status'] 	= $order_status;
						$data['new_order_status'] 	= $order_status;
						$result 					= $wpdb->insert($table_name, $data);
						
						if($wpdb->last_error){
							$this->create_shop_oder_log();
						}//End
					}
				}
			}
			
			remove_action('woocommerce_before_save_order_items', array($this, 'ic_commerce_before_save_order_items'),101,2);
		}
		
		function ic_commerce_before_save_order_items($order_id = '', $new_items = array()){
			$order_item_edited 	= $this->get_setting('order_item_edited',$this->constants['plugin_options'], 0);
			if($order_item_edited == 1){
				$this->ic_commerce_before_save_order_items_save($order_id,$new_items,'order_item_edited');
			}
		}
		
		function ic_commerce_ajax_add_order_item_meta($item_id = 0, $item = array()){
			$order_item_added 	= $this->get_setting('order_item_added',$this->constants['plugin_options'], 0);
				$order_id    = absint(isset($_POST['order_id']) ? $_POST['order_id'] : 0);
				$this->add_order_item_log($item, $order_id ,$item_id , 'order_item_added');
		}
		
		function ic_commerce_order_status_changed_save($order_id = 0, $old_status = '', $new_status = '', $type = 'status_change'){
			global $wpdb;
			$datetime				= date_i18n("Y-m-d H:i:s");	
			$table_name 			= $this->constants['table_name'];
			$user_id				= get_current_user_id();
			
			$order_data				= $this->get_order_data($order_id);
			$order_date 			= isset($order_data->post_date) ? $order_data->post_date : '';
			$order_status 			= isset($order_data->post_status) ? $order_data->post_status : '';
			$customer_id			= get_post_meta($order_id,'_customer_user',true);
			
			$data = array();
			$data['user_id'] 			= $user_id;
			$data['date'] 				= $datetime;
			$data['order_item_name']	= '';
			$data['order_id'] 			= $order_id;
			$data['order_item_id'] 		= '';
			$data['product_id'] 		= '';
			$data['variation_id'] 		= '';
			$data['old_order_item_qty'] = '';
			$data['new_order_item_qty'] = '';
			$data['action_log'] 		= $type;			
			$data['line_total'] 		= '';
			$data['line_subtotal'] 		= '';
			$data['line_subtotal'] 		= '';
			$data['old_order_status'] 	= ($type == 'status_change') ? 'wc-'.$old_status : $old_status;
			$data['new_order_status'] 	= ($type == 'status_change') ? 'wc-'.$new_status : $new_status;
			$data['order_date'] 		= $order_date;
			$data['customer_id'] 		= $customer_id;
			$result 					= $wpdb->insert($table_name, $data);
			
		}
		
		function ic_commerce_order_status_changed_trash($order_id = 0){
			$order_trash = $this->get_setting('order_trash',$this->constants['plugin_options'], 0);
			if($order_trash == 1){
				$post 		= get_post($order_id, ARRAY_A);
				$post_type 	= isset($post['post_type']) ? $post['post_type'] : '';	
				
				if($post_type == 'shop_order'){
									
					$old_status = isset($post['post_status']) ? $post['post_status'] : '';
					$new_status	= 'trash';
									
					$this->ic_commerce_order_status_changed_save($order_id, $old_status, $new_status,'order_trash');
				}
			}
			
		}
		
		function ic_commerce_order_status_changed_untrash($order_id = 0){
			$order_untrash = $this->get_setting('order_untrash',$this->constants['plugin_options'], 0);
			if($order_untrash == 1){
				$post 		= get_post($order_id, ARRAY_A);
				$post_type 	= isset($post['post_type']) ? $post['post_type'] : '';	
				
				if($post_type == 'shop_order'){
					$old_status	= 'trash';
					$new_status	= get_post_meta($order_id, '_wp_trash_meta_status', true);
					$this->ic_commerce_order_status_changed_save($order_id, $old_status, $new_status,'order_untrash');
				}
			}
		}
		
		function get_order_id($order_item_id = ''){
			global $wpdb;
			$order_id	= 0;
			if($order_item_id > 0){
				$order_id = $wpdb->get_var($wpdb->prepare( "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_id = %d", $order_item_id ));
			}
			return $order_id;
		}
		
		function get_order_data($order_id = ''){
			global $wpdb;
			$order_date = '';
			if($order_id > 0){
				$order = $wpdb->get_row($wpdb->prepare( "SELECT post_date, post_status FROM {$wpdb->posts} WHERE ID = %d", $order_id ));
			}
			return $order;
		}
		
		function get_order_item($order_item_id = 0, $order_id = 0){
			
			$order_item	= array();
			if($order_item_id > 0){				
				if($order_id > 0){
					
					$order 		= wc_get_order( $order_id );			
					$items 		= $order->get_items();					
					if(count($items) >0){
						foreach($items as $item_id => $item) {
							if($order_item_id == $item_id){
								$order_item	= $item;
							}
						}
					}//End
				}
			}
			
			return $order_item;	
		}
		
		function create_shop_oder_log(){
			$obj = new  ic_woocommerce_audit_log_lite();
			$obj->create_table();
		}//End Method
		
	}//End Class
}
