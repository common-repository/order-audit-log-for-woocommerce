<?php
include_once("ic_commerce_audit_log_lite_function.php");
if(!class_exists('ic_commerce_audit_log_lite_list')){
	class ic_commerce_audit_log_lite_list extends ic_commerce_audit_log_lite_function{
		
		var $constants 	= array();
		
		public function __construct($constants = array()){
			
			$this->constants = $constants;
		}
		
		function init(){
			$this->get_admin_page();
		}
		
		function get_list(){
			$this->get_admin_page();
		}
		
		function get_admin_page(){
			
			$onload_search 	= 'yes';
			$output 		= "";			
			$output .= $this->get_search_form();
			
			$output .= ' <div class="table table_shop_content search_report_content hide_for_print">';
				if($onload_search == "no"){
					$output .= "<div class=\"order_not_found\">".__("In order to view the results please hit \"<strong>Search</strong>\" button.",'icwcauditloglite')."</div>";
				}else{
					//$output .= $this->get_grid('limit_row');	
				}
			$output .= ' </div>';
			$output .= '<div id="search_for_print_block" class="search_for_print_block"></div>';
			
			$output .= '<style type="text/css">';
			$output .= $this->get_number_columns_css_style();
			$output .= '</style>';			
			echo $output;
			
		}
		
		function get_search_form(){
			$onload_search 	= 'no';
			$start_date 	= date_i18n('Y-01-01');
			$end_date 		= date_i18n('Y-m-d');
			$page			= $this->get_request('page');
			$start_date		= $this->get_request('start_date',$start_date,true);
			$end_date		= $this->get_request('end_date',$end_date,true);
			$onload_search	= $this->get_request('onload_search','no',true);
			$page_title		= '';
			$admin_url		= admin_url('admin.php');
			$add_url		= $admin_url."?page=ic-location&action=add";
			
			$page_tab	 				= $this->get_request("page_tab",'order_audit_log',true);
			$page_tab	 				= $this->get_request("page_tab",'order_audit_log',true);
			$page_tabs					= $this->get_page_tabs($page_tab);
			$page_tab_title 			= isset($page_tabs[$page_tab]) ? $page_tabs[$page_tab] : ucfirst(str_replace("page","",str_replace("_"," ",$page_tab)));
			$_REQUEST['page_tab_title'] = $page_tab_title;
			
			$per_page					= $this->get_per_page();
			?>
			<h2 class="hide_for_print"><?php echo $page_tab_title;?></h2>
			<div class="PluginMenu">
				<h2 class="nav-tab-wrapper woo-nav-tab-wrapper hide_for_print">
				<div class="responsive-menu"><a href="#" id="menu-icon"></a></div>
				<?php
					if(count($page_tabs)>1){
					   foreach ( $page_tabs as $key => $value ) {
							echo '<a href="'.admin_url( 'admin.php?page='.$page.'&page_tab=' . urlencode( $key ) ).'" class="nav-tab ';
							if ( $page_tab == $key ) echo 'nav-tab-active';
							echo '">' . esc_html( $value ) . '</a>';
					   }
				    }
				?></h2>
			</div>
			
            	 <div id="navigation" class="hide_for_print ic_navigation ic_formwrap">
                        <div class="collapsible ic_section-header" id="section1"><h3><?php _e('Custom Search','icwcauditloglite');?><span></span></h3></div>
                        <div class="container ic_search_report_form">
                            <div class="content">
                                <div class="search_report_form">
                                    <div class="form_process"></div>
                                    <form action="" name="Report" id="search_order_report" method="post"  id="frm_purchase_report" name="frm_purchase_report">
                                        <div class="form-table">
                                            <div class="form-group ic_form-group">
                                                <div class="ic_FormRow ic_firstrow">
                                                    <div class="ic_label-text"><label for="start_date"><?php _e('From Date:','icwcauditloglite');?></label></div>
                                                    <div class="ic_input-text"><input type="text" value="<?php echo $start_date;?>" id="start_date" name="start_date" readonly maxlength="10" /></div>
                                                </div>
                                                <div class="ic_FormRow">
                                                    <div class="ic_label-text"><label for="end_date"><?php _e('To Date:','icwcauditloglite');?></label></div>
                                                    <div class="ic_input-text"><input type="text" value="<?php echo $end_date;?>" id="end_date" name="end_date" readonly maxlength="10" /></div>
                                                </div>
                                            </div>
                                            
                                            <div class="ic_form-group">
                                                <?php if($page_tab == 'order_audit_log' || $page_tab == 'product_edit_log'){?>
                                                <div class="ic_FormRow">
                                                    <div class="ic_label-text"><label for="product_name"><?php _e('Product Name','icwcauditloglite')?>:</label></div>
                                                    <div class="ic_input-text">
                                                        <?php 
                                                        	$product_list 	= $this->get_products_dropdown_data();
															$product_id		= '-1';
                                                           	$this->create_dropdown($product_list,"product_id[]","product_id","","ic_product",$product_id, 'object', true, 5);
                                                        ?>
                                                    </div>
                                                </div>
                                                <?php }?>
                                                
                                                <?php if($page_tab == 'order_audit_log' || $page_tab == 'order_status_log'){?>
                                                <div class="ic_FormRow ic_firstrow">
                                                    <div class="ic_label-text"><label for="order_id"><?php _e('Audit Types:','icwcauditloglite')?></label></div>
                                                    <div class="ic_input-text">
                                                    	<?php
															$transaction_types 	= array();
															$transaction_types2 = array();
															
															$transaction_types['order_item_added'] 				= __('New Item Added',		'icwcauditloglite');
															$transaction_types['order_item_edited'] 			= __('Order Item Edited',	'icwcauditloglite');
															$transaction_types['order_trash']					= __('Order Trash',	'icwcauditloglite');
															$transaction_types['order_untrash']					= __('Restore Order from Trash',	'icwcauditloglite');
															
															/*$plugin_options = $this->constants['plugin_options'];
															foreach($plugin_options as $key => $value){
																
																if($value == 1){
																	$transaction_types2[] = 
																}
															}*/
																																													
															$this->create_dropdown($transaction_types,"transaction_type[]","transaction_type","","transaction_type",'', 'array', true, 5);
                                                        ?>
                                                    </div>
                                                </div>
                                                <?php }?>
                                            </div>
                                            
                                            <div class="ic_form-group">
                                                <?php if($page_tab == 'order_audit_log' || $page_tab == 'product_edit_log'){?>
                                                <div class="ic_FormRow">
                                                    <div class="ic_label-text"><label for="user_id">Username:</label></div>
                                                    <div class="ic_input-text">
                                                        <?php 
                                                        	$log_users = $this->get_log_users();
                                                           	$this->create_dropdown($log_users,"user_id[]","user_id","Select One","user_id",'', 'object', false, 5);
                                                        ?>
                                                    </div>
                                                </div>
                                                <?php }?>
                                                
                                                <?php if($page_tab == 'order_audit_log' || $page_tab == 'order_status_log'){?>
                                                <div class="ic_FormRow ic_firstrow">
                                                    <div class="ic_label-text"><label for="order_id"><?php _e('Order ID:')?></label></div>
                                                    <div class="ic_input-text">
                                                    	<input type="text" name="order_id" id="order_id" value="<?php echo $this->get_request('order_id','',true);?>" />
                                                    </div>
                                                </div>
                                                <?php }?>
                                            </div>
                                            
                                            
                                            
                                            
                                            <div class="ic_form-group">
                                                <div class="ic_FormRow ic_firstrow" style="width:100%">
                                                	<?php
                                                    	$hidden_fields = array();
														$hidden_fields['action'] 				=  'icwcauditloglite_ajax';
														$hidden_fields['sub_action'] 			=  $this->get_request('sub_action','icwcauditloglite_list_page',true);
														$hidden_fields['call'] 					=  $this->get_request('call','list',true);
														$hidden_fields['limit'] 				=  $this->get_request('limit',$per_page,true);
														$hidden_fields['p'] 					=  $this->get_request('p',1,true);
														$hidden_fields['admin_page'] 		 	=  $this->get_request('admin_page',$page,true);
														$hidden_fields['ic_admin_page'] 		=  $this->get_request('ic_admin_page',$page,true);
														$hidden_fields['adjacents'] 			=  $this->get_request('adjacents',3,true);
														$hidden_fields['page_title'] 		 	=  $this->get_request('page_title',$page_title,true);
														$hidden_fields['page_tab'] 		 		=  $this->get_request('page_tab',$page_tab,true);
														$hidden_fields['sort_by'] 		 		=  $this->get_request('sort_by','date',true);
														$hidden_fields['order_by'] 		 		=  $this->get_request('order_by','DESC',true);
														$hidden_fields['total_pages'] 		 	=  $this->get_request('total_pages',0,true);
														$hidden_fields['date_format'] 	 		=  $this->get_request('date_format',get_option('date_format'),true);
														$hidden_fields['page_name'] 			=  $this->get_request('page_name','all_detail',true);
														$hidden_fields['onload_search'] 		=  $this->get_request('onload_search',$onload_search,true);														
														$hidden_fields = apply_filters('ic_commerce_detail_page_search_form_hidden_fields', $hidden_fields, $page);
														echo $this->create_search_form_hidden_fields($hidden_fields);//$this->print_array($hidden_fields);
													?>
                                                    <span class="submit_buttons">
                                                    	<input name="SearchOrder" id="SearchOrder" class="onformprocess ic_searchbtn btn_margin ic_button" value="<?php _e("Search",'icwcauditloglite');?>" type="submit">
                                                        <input name="reset" id="reset" class="onformprocess open_popup csvicon ic_button" value="<?php _e("Reset",'icwcauditloglite');?>" type="reset">
                                                    	&nbsp; &nbsp; &nbsp; <span class="ajax_progress"></span>
													</span>
                                                </div>
                                            </div>                                                
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                
            <?php
		}	 
		
		function get_items($type = 'limit_row'){
			global $wpdb;
			
			$request			= $this->get_all_request();extract($request);			
			
			$table_name 		= $wpdb->prefix."ic_product_log";
			
			$sort_by 			= isset($_REQUEST['sort_by']) 			?	$_REQUEST['sort_by'] 	: 'created_date';
			
			$total_row_count 	= isset($_REQUEST['total_row_count']) 	?	$_REQUEST['total_row_count'] 	: 0;
			
			$page_tab 			= isset($_REQUEST['page_tab']) 			?	$_REQUEST['page_tab'] 	: '';
			
			
			
			if($page_tab == 'order_audit_log'){
				$table_name 		= $this->constants['table_name'];;
			}else if($page_tab == 'order_status_log'){
				$table_name 		= $wpdb->prefix."ic_order_status_log";
			}
			
			if($type == 'total_row'){
				if($total_row_count > 0){
					$summary = array();
					$summary['total_row_count'] = $total_row_count;
					return $summary;
				}
			}
			
			$query = "SELECT ";
			
			if($type == 'total_row'){
				$query .= " COUNT(*) ";
			}else{
				$query .= " *";
				
				if($page_tab == 'order_audit_log'){
					$query .= " , order_item_name AS product_name ";
					$query .= " , old_order_item_qty AS old_stock ";
					$query .= " , new_order_item_qty AS new_stock ";
				}
			}
			
			
			
			$query .= " FROM {$table_name}";
			
			$query .= " WHERE 1*1 ";
			
			if ($start_date != NULL &&  $end_date !=NULL){
				$query .= " AND DATE(date) BETWEEN '".$start_date."' AND '". $end_date ."'";
			}
			
			if($order_id != NULL && $order_id != ""){
				$query .= " AND order_id IN ({$order_id})";
			}
			
			if($product_id != NULL && $product_id != "" && $product_id != "-1"){
				$query .= " AND product_id IN ({$product_id})";
			}
			
			if($user_id != NULL && $user_id != "" && $user_id != "-1"){
				$query .= " AND user_id IN ({$user_id})";
			}
			
			if($transaction_type != NULL && $transaction_type != "" && $transaction_type != "-1"){
				
				$transaction_type = str_replace(",","','",$transaction_type);
				$query .= " AND action_log IN ('{$transaction_type}')";
			}
			
			if($old_order_status != NULL && $old_order_status != "" && $old_order_status != "-1"){
				$old_order_status = str_replace(",","','",$old_order_status);
				$query .= " AND old_order_status IN ('{$old_order_status}')";
			}
			
			if($new_order_status != NULL && $new_order_status != "" && $new_order_status != "-1"){
				$new_order_status = str_replace(",","','",$new_order_status);
				$query .= " AND new_order_status IN ('{$new_order_status}')";
			}
			
			$query.= " ORDER BY {$sort_by} {$order_by}";
			
			if($type == 'total_row'){
				if($total_row_count >0){
					$items['total_row_count'] = $total_row_count;
				}else{
					$counts = $wpdb->get_var($query);
					$items['total_row_count'] = $counts;
				}
			}else if($type == 'all_row'){
				$items = $wpdb->get_results($query);
			}else{
				$query.=' LIMIT '.(int)$start.','.(int)$limit;
				$items = $wpdb->get_results($query);
			}
			
			if($wpdb->last_error){
				echo $wpdb->last_error;
			}
			
			if(($type == 'limit_row' || $type == 'all_row') and count($items) > 0){	
			
				//$this->print_array($items);
				
				if($page_tab == 'order_audit_log' || $page_tab == 'product_edit_log'){
					
					$variation_ids			= $this->get_items_id_list($items,'variation_id');
					
					$variation_field 		= 'variation_id';			
					$variation_ids			= $this->get_items_id_list($items,$variation_field);
					$product_ids			= $this->get_items_id_list($items,'product_id');
					$user_ids				= $this->get_items_id_list($items,'user_id');
					
					$product_variations 	= $this->get_product_variations($variation_ids);
					$variation_postmeta 	= $this->get_postmeta($variation_ids,array(),array('_sku'));
					$product_postmeta 		= $this->get_postmeta($product_ids,array(),array('_sku'));				
					$usernames				= $this->get_usernames($user_ids);
					foreach ($items as $key => $value){
						
						$variation_id 				= isset($items[$key]->{$variation_field}) 	? $items[$key]->{$variation_field} 	: 0;
						$product_id 				= isset($items[$key]->product_id) 			? $items[$key]->product_id 			: 0;
						$user_id 					= isset($items[$key]->user_id) 				? $items[$key]->user_id 			: 0;
						
						$old_stock 					= isset($items[$key]->old_stock) 			? $items[$key]->old_stock 	: 0;
						$new_stock 					= isset($items[$key]->new_stock) 			? $items[$key]->new_stock 	: 0;
						
						$items[$key]->old_stock  	= ($old_stock == '0') ? '' : $old_stock;
						$items[$key]->new_stock  	= ($new_stock == 0) ? '' : $new_stock;
						
						if($variation_id > 0){
							$product_variation = isset($product_variations[$variation_id]) ? $product_variations[$variation_id] : array();
							$product_sku  = isset($variation_postmeta[$variation_id]['sku']) ? $variation_postmeta[$variation_id]['sku'] : '';
							$product_variation = implode(", ",$product_variation);
							
							$items[$key]->product_name  		= $value->product_name ." - ". $product_variation;
							$items[$key]->order_item_name  		= $value->order_item_name ." - ". $product_variation;
							$items[$key]->product_variation  	= $product_variation;
							$items[$key]->product_sku  			= empty($product_sku) ? (isset($product_postmeta[$product_id]['sku']) ? $product_postmeta[$product_id]['sku'] : '') : $product_sku;
						}else{
							$items[$key]->product_sku  			= isset($product_postmeta[$product_id]['sku']) ? $product_postmeta[$product_id]['sku'] : '';
						}
						
						$items[$key]->user_name  				= isset($usernames[$user_id]) ? $usernames[$user_id] : '';
						
					}
					
					
					$order_statuses					= $this->ic_get_order_statuses();					
					$order_statuses['deleted']		= __('Deleted Permanently');
					$order_statuses['trash'] 		= __("Trash",'wordprss');
					
					
					$transaction_types = array();
					
					$transaction_types['order_trash'] 					= __('Order Trash',			'icwcauditloglite');
					$transaction_types['order_untrash'] 				= __('Restore',				'icwcauditloglite');
					$transaction_types['order_item_edited'] 			= __('Order Item Edited',	'icwcauditloglite');
					$transaction_types['order_item_added'] 				= __('New Item Added',		'icwcauditloglite');					
					
					
					
					foreach ($items as $key => $value){
						
						$action_log 				= isset($items[$key]->action_log) 		? $items[$key]->action_log 			: '';
						$items[$key]->action_log  	= isset($transaction_types[$action_log])? $transaction_types[$action_log] 	: $action_log;
						
						
						$old_status 				= isset($items[$key]->old_order_status) 		? $items[$key]->old_order_status 		: '';
						$new_status 				= isset($items[$key]->new_order_status) 		? $items[$key]->new_order_status 		: '';
						$items[$key]->old_order_status  	= isset($order_statuses[$old_status]) 	? $order_statuses[$old_status] 	: $old_status;
						$items[$key]->new_order_status  	= isset($order_statuses[$new_status]) 	? $order_statuses[$new_status] 	: $new_status;
					}
					
					
				}else{
					
				}
			}
			
			//$this->print_array($items);
			
			return $items;
		}
		
		
		
		function get_usernames($user_ids = ''){
			
			global $wpdb;
			
			$sq = " SELECT user_login, ID AS user_id FROM $wpdb->users AS users";
			
			$sq .= " WHERE 1*1";
			
			$sq .= " AND users.ID IN ($user_ids)";
			
			$users = $wpdb->get_results($sq);
			
			$u = array();
			
			foreach($users as $key => $user){
				$u[$user->user_id] = $user->user_login;
			}
			
			return $u;
			
		}
		
		function create_search_form_hidden_fields($request = array(), $type = "hidden"){
			$output_fields = "";
			foreach($request as $key => $value):
				$output_fields .=  "\n<input type=\"{$type}\" name=\"{$key}\" id=\"{$key}\" value=\"{$value}\" />";
			endforeach;
			return $output_fields;
		}
		
		function get_columns(){
			
			$transaction_type = $this->get_request('transaction_type','default');
			
			if($transaction_type == '-1'){
				$transaction_types = array('default');
			}else{
				$transaction_types = explode(",",$transaction_type);
			}
						
			$columns = array();
			
			if(in_array('order_item_edited',$transaction_types)){
				$new_columns = array(
					'date'  				=> __('Audit Date/Time'),
					'user_name'  			=> __('Username'),
					'action_log'  			=> __('Audit Type'),
					'order_date'  			=> __('Order Date'),
					'order_id'  			=> __('Order ID'),
					'product_sku' 			=> __('SKU'),
					'order_item_name' 		=> __('Product Name'),				
					'line_subtotal' 		=> __('Item Sub Total'),
					'line_total' 			=> __('Item Total')
				);
				
				$columns = array_merge($columns, $new_columns);
			}			
			
			if(in_array('default',$transaction_types)){
				$new_columns = array(
					'date'  				=> __('Audit Date/Time'),
					'user_name'  			=> __('Username'),
					'action_log'  			=> __('Audit Type'),
					'order_date'  			=> __('Order Date'),
					'order_id'  			=> __('Order ID'),
					'product_sku' 			=> __('SKU'),
					'order_item_name' 		=> __('Product Name'),
					
					'old_order_status' 		=> __('Old Order Status'),
					'new_order_status' 		=> __('New Order Status'),			
					'line_subtotal' 		=> __('Item Sub Total'),
					'line_total' 			=> __('Item Total')
					
				);
				$columns = array_merge($columns, $new_columns);
			}
			
			
			
			if(count($columns)<=0){
				$columns = array(
					'date'  				=> __('Audit Date/Time'),
					'user_name'  			=> __('Username'),
					'action_log'  			=> __('Audit Type'),
					'order_date'  			=> __('Order Date'),
					'order_id'  			=> __('Order ID'),
					'product_sku' 			=> __('SKU'),
					'order_item_name' 		=> __('Product Name'),				
					'line_subtotal' 		=> __('Item Sub Total'),
					'line_total' 			=> __('Item Total'),
					'old_order_status' 		=> __('Old Order Status'),
					'new_order_status' 		=> __('New Order Status')
				);
			}
			
			
			return $columns;
		}
		
		function get_grid($type = 'limit_row'){
			
			$items 		= $this->get_items($type);
			$summary 	= $this->get_items('total_row');
			$columns 	= $this->get_columns($type, $type);
			$row_count	= 0;
			$admin_url	= admin_url('admin.php').'?page=ic-purchase&action=edit&ID=%s';
			$edit_label = "<a href=\"$admin_url\" target=\"_blank\">".__('Edit')."</a>";
			$total_pages= isset($summary['total_row_count']) ? $summary['total_row_count'] : 0;
			$total_count= 0;
			$total_amount= 0;
			
			$output = "";
			
			//$this->print_array($items);
			
			if ($items){
				$output = "";
				if($type != 'all_row'){
					$output .= '<div class="top_buttons">';
					$output .= $this->export_to_csv_button('top', $total_pages, $summary);
					$output .= '<div class="clearfix"></div></div>';
				}else{
					unset($columns['edit']);
					$output .= $this->back_print_botton('top');
				}			
				$output .= '<div class="ic_table-responsive">';
				$output .= '<table style="width:100%" class="widefat">';
				$output .= '    <thead>';
				$output .= '        <tr class="first">';					
									foreach($columns as $column_key => $value):
										$td_class		= $column_key;
										$td_value 		= $value;
										switch($column_key):
											case "order_total":
											case "order_subtotal":
											case "order_shipping":
											case "order_tax":
											case "line_total":
											case "line_subtotal":
												$td_class .= ' right_align';
												break;
											case "purchase_header_id":
											case "vendor_id":
											case "purchase_number":
											case "warehouse_id":
											case "purchase_no":
											case "price":
											case "quantity" :
												$td_class .= ' right_align';
												break;
											case "edit":
												$td_class .= ' right_align';
												break;	
										endswitch;/*End Columns Switch*/
										$output .= "\n<th class=\"{$td_class}\">{$td_value}</th>\n";
									endforeach;/*End Columns Foreach*/					
				$output .= '      </tr>';
				$output .= ' </thead>';
				$output .= ' <tbody>';
					foreach ( $items as $key => $item ) {
						if($row_count%2 == 0){$alternate = "alternate ";}else{$alternate = "";};
							$output .= '<tr class="'.$alternate."row_".$key.'">';
							foreach($columns as $column_key => $value):
								$td_class		= $column_key;
								$td_value 		= isset($item->$column_key) ? $item->$column_key : '';
								switch($column_key):
									case "order_total":
									case "order_subtotal":
									case "order_shipping":
									case "order_tax":
									case "line_total":
									case "line_subtotal":
										$td_class .= ' right_align';
										$td_value = wc_price($td_value );
										break;
									case "price":	
										$td_class .= ' right_align';
										$td_value = wc_price($td_value );
										break;	
									case "purchase_header_id":
									case "vendor_id":
									case "purchase_number":
									case "warehouse_id":
									case "purchase_no":
									case "quantity" :
										$td_class .= ' right_align';							
										break;
									case "____action_log" :
										$td_value 	= ucfirst($td_value);
										break;	
									case "date":
										break;
									case "edit":
										$td_class .= ' right_align';	
										$id 		= isset($item->purchase_header_id) ? $item->purchase_header_id : '';									
										$td_value 	= sprintf($edit_label, $id);
										break;
								endswitch;/*End Columns Switch*/
								$output .= "\n<td class=\"{$td_class}\">{$td_value}</td>\n";
							endforeach;/*End Columns Foreach*/
					}/*End Items Foreach*/
				$output .= ' </tbody>';
				$output .= '</table>';
				$output .= '</div>';
				if($type != 'all_row'){
					$output .= $this->total_count($total_count, $total_amount, $total_pages,$summary);
				}else{
					$output .= $this->back_print_botton('bottom');
				}
				echo $output;	
			}
			else{
			echo "No record found.";
			}
            
		}
		
		function total_count($TotalOrderCount = 0, $TotalAmount = 0, $total_pages = 0, $summary = array()){
			global $request;
			
			$admin_page 		= $this->get_request('page');
			$limit	 			= $this->get_request('limit',15, true);
			$adjacents			= $this->get_request('adjacents',3);
			$detail_view		= $this->get_request('detail_view',"no");
			$targetpage 		= "admin.php?page=".$admin_page;
			
			$create_pagination 	= $this->get_pagination($total_pages,$limit,$adjacents,$targetpage,$request);
			$output 			= "";
			$output .= '<table style="width:100%" class="detail_summary">';
			$output .= '	<tr>';
			$output .= '		<td>';	
			$output .= '       	  	<div class="clearfix"></div>';
			$output .= '          	<div>';			
			$output .= 					$create_pagination;
			$output .= '     	  	</div>';
			$output .= '       	  	<div class="clearfix"></div>';
			$output .= '          	<div>';			
			$output .= 			  		$this->export_to_csv_button('bottom',$total_pages, $summary);			
			$output .= '     	  	</div>';
			$output .= '    	  	<div class="clearfix"></div>';
			$output .= ' 		</td>';
			$output .= '</tr>';
			$output .= '</table>';
			$output .= '<script type="text/javascript">';
			$output .= " jQuery(document).ready(function() { ";
			$output .= " jQuery('.pagination a').removeAttr('href');";
			$output .= " }); ";
			$output .= '</script>';
			
			return $output;
		}
		
		function export_to_csv_button($position = 'bottom', $total_pages = 0, $summary = array()){
			global $request;
			
			$admin_page 					= $this->get_request('admin_page');
			$admin_page_url 				= admin_url('admin.php');
			$mngpg 							= $admin_page_url.'?page='.$admin_page ;
			$request						= $this->get_all_request();			
			$request['total_pages'] 		= $total_pages;				
			$request['count_generated']		=	1;
			
			foreach($summary as $key => $value):
				$request[$key]		=	$value;
			endforeach;
					
			$request_			=	$request;
			$action				= $request['action'];
			unset($request['action']);			
			unset($request['p']);
			
			/*$output = '<div id="'.$admin_page.'Export" class="RegisterDetailExport">';*/
			$output = '<div id="'.$admin_page.'Export" class="ic_RegisterDetailExport">';
				
				$output .= '<form id='. $admin_page.'_'.$position.'_form" class='. $admin_page.'_form ic_export_'. $position.'_form" action="'. $mngpg.'" method="post">';
					
					$output .= $this->create_hidden_fields($request);
					$page_tab 	= $this->get_request('page_tab',$admin_page);
					$output .= '<input type="hidden" name="export_file_name" value="'. $page_tab.'" />';
					$output .= '<input type="hidden" name="export_file_format" value="csv" />';
					$output .= '<input type="hidden" name="sub_action" value="'.$this->constants['plugin_key'].'_export" />';
					
					$output .= '<input type="submit" name="'. $admin_page.'_export_csv" class="onformprocess open_popup csvicon ic_button" value="'. __("Export to CSV",'icwcauditloglite').'" data-format="csv" data-popupid="export_csv_popup" data-hiddenbox="popup_csv_hidden_fields" data-popupbutton="'. __("Export to CSV",'icwcauditloglite').'" data-title="'. __("Export to CSV - Additional Information",'icwcauditloglite').'" />';
					$output .= '<input type="submit" name="'. $admin_page.'_export_xls" class="onformprocess open_popup excelicon ic_button" value="'. __("Export to Excel",'icwcauditloglite').'" data-format="xls" data-popupid="export_csv_popup" data-hiddenbox="popup_csv_hidden_fields" data-popupbutton="'. __("Export to Excel",'icwcauditloglite').'" data-title="'. __("Export to Excel - Additional Information",'icwcauditloglite').'" />';
					//$output .= '<input type="submit" name="'. $admin_page.'_export_pdf" class="onformprocess open_popup pdficon ic_button" value="'. __("Export to PDF",'icwcauditloglite').'" data-format="pdf" data-popupid="export_pdf_popup" data-hiddenbox="popup_pdf_hidden_fields" data-popupbutton="'. __("Export to PDF",'icwcauditloglite').'" data-title="'. __("Export to PDF",'icwcauditloglite').'" />';
					//$output .= '<input type="button" name="'. $admin_page.'_export_print" class="onformprocess open_popup printicon button_search_for_print ic_button" value="'. __("Print",'icwcauditloglite').'"  data-format="print" data-popupid="export_print_popup" data-hiddenbox="popup_print_hidden_fields" data-popupbutton="'. __("Print",'icwcauditloglite').'" data-title="'. __("Print",'icwcauditloglite').'" data-form="form" />';
					
					//$output .= '<input type="submit" name="'. $admin_page.'_export_csv" class="onformprocess open_popup ic_csvicon ic_button" value="'. __("Export to CSV",'icwcauditloglite').'" data-format="csv" data-popupid="export_csv_popup" data-hiddenbox="popup_csv_hidden_fields" data-popupbutton="'. __("Export to CSV",'icwcauditloglite').'" data-title="'. __("Export to CSV - Additional Information",'icwcauditloglite').'" />';
					//$output .= '<input type="submit" name="'. $admin_page.'_export_xls" class="onformprocess open_popup ic_excelicon ic_button" value="'. __("Export to Excel",'icwcauditloglite').'" data-format="xls" data-popupid="export_csv_popup" data-hiddenbox="popup_csv_hidden_fields" data-popupbutton="'. __("Export to Excel",'icwcauditloglite').'" data-title="'. __("Export to Excel - Additional Information",'icwcauditloglite').'" />';
					//$output .= '<input type="submit" name="'. $admin_page.'_export_pdf" class="onformprocess open_popup ic_pdficon ic_button" value="'. __("Export to PDF",'icwcauditloglite').'" data-format="pdf" data-popupid="export_pdf_popup" data-hiddenbox="popup_pdf_hidden_fields" data-popupbutton="'. __("Export to PDF",'icwcauditloglite').'" data-title="'. __("Export to PDF",'icwcauditloglite').'" />';
					//$output .= '<input type="button" name="'. $admin_page.'_export_print" class="onformprocess open_popup ic_printicon button_search_for_print ic_button" value="'. __("Print",'icwcauditloglite').'"  data-format="print" data-popupid="export_print_popup" data-hiddenbox="popup_print_hidden_fields" data-popupbutton="'. __("Print",'icwcauditloglite').'" data-title="'. __("Print",'icwcauditloglite').'" data-form="form" />';
				
				$output .= '</form>';
				
				if($position == "bottom"):
					
					$output .= '<form id="search_order_pagination" class="search_order_pagination" action='. $mngpg.'" method="post">';
					   $output .= $this->create_hidden_fields($request_);
					$output .= '</form>';
				
					$request_['call'] 		= 'print';
					$output .= '<form id="search_order_pagination" class="form_search_for_print" action='. $mngpg.'" method="post">';
					   $output .= $this->create_hidden_fields($request_);				   
					   $output .= '<input type="hidden" name="export_file_format" value="print" />';
					$output .= '</form>';
					
				endif;
			   $output .= '</div>';
			   
			   return $output;
		}
		
		function back_print_botton($position  = "bottom"){
			 $output = '';			 
			 $output = '<div class="back_print_botton noPrint">';
					 $output .= '<input type="button" name="backtoprevious" value="'. __("Back to Previous",'icwcauditloglite').'"  class="onformprocess ic_button" onClick="back_to_detail();" />';
					$output .=  '<input type="button" name="backtoprevious" value="'. __("Print",'icwcauditloglite').'"  class="onformprocess ic_button" onClick="print_report();" />';
				 $output .= '</div> ';
			return $output;
		}
		
			
		function get_number_columns(){
			$number_columns		= array('new_stock','old_stock','order_item_qty','order_id','user_id','product_id','variation_id');
			$number_columns		= apply_filters("ic_commerce_number_columns",$number_columns);			
			return $number_columns;
		}
		
		function get_price_columns(){
			$number_columns		= array('line_subtotal','line_total');
			$number_columns		= apply_filters("ic_commerce_price_columns",$number_columns);			
			return $number_columns;
		}
		
		function get_number_columns_css_style(){
			$number_columns = $this->get_number_columns();
			$tds = implode(', td.',$number_columns);
			$ths = implode(', th.',$number_columns);					
			$style = 'th.'.$ths.', td.'.$tds.'{ text-align:right}';
			return $style;
		}
		
		
		
		
		
		
		var $request = false;
		
		function get_all_request(){
			global $request;
			if(!$this->request){
				
				do_action("ic_commerce_detail_page_before_default_request");				
				$request 									= array();		
				$default_request							= array();
				$default_request['start_date'] 				=  NULL;
				$default_request['end_date'] 				=  NULL;
				$default_request['sort_by'] 				=  '';
				$default_request['order_by'] 				=  '';
				$default_request['limit'] 					=  '5';
				$default_request['p'] 						=  '1';
				$default_request['action'] 					=  '';
				$default_request['admin_page'] 				=  '10';
				$default_request['ic_admin_page'] 			=  '';
				$default_request['adjacents'] 				=  '3';
				$default_request['do_action_type'] 			=  '';
				$default_request['page_title'] 				=  '';
				$default_request['total_pages'] 			=  '0';
				$default_request['date_format'] 			=  'F j, Y';
				$default_request['page_name'] 				=  'all_detail';
				$default_request['onload_search'] 			=  'yes';
				$default_request['count_generated'] 		=  0;
				$default_request['total_row_count'] 		=  0;
				$default_request['warehouse_id'] 			=  '-1';
				
				$default_request['order_id'] 				=  '-1';
				$default_request['user_id'] 				=  '-1';
				$default_request['transaction_type'] 		=  '-1';
				$default_request['product_id'] 				=  '-1';
				 
				 $default_request['old_order_status'] 		=  '-1';
				 $default_request['new_order_status'] 		=  '-1';
				 
				 
				
				$_REQUEST 									= array_merge((array)$default_request, (array)$_REQUEST);
				
				$limit										= $_REQUEST['limit'];
				$p											= $_REQUEST['p'];
				
				$_REQUEST['start']							= ($p > 1) ? (($p - 1) * $limit) 	: 0;				
				
				if(isset($_REQUEST)){
					$REQUEST = $_REQUEST;
					$REQUEST = apply_filters("ic_commerce_before_request_creation", $REQUEST);
					foreach($REQUEST as $key => $value ):						
						$request[$key] =  $this->get_request($key,NULL);
					endforeach;
					$request = apply_filters("ic_commerce_after_request_creation", $request);
				}
				
				$this->request = $request;				
			}
			return $this->request;
		}
		
		function export_csv($export_file_format = 'csv'){
			
			$items 				= $this->get_items('all_row');			
			$columns 			= $this->get_columns();			
			$date_format		= $this->get_request('date_format',"Y-m-d");
			$price_columns		= $this->get_price_columns();
			
			unset($columns['edit']);
			
			$num_decimals   	= get_option( 'woocommerce_price_num_decimals'	,	0		);
			$decimal_sep    	= get_option( 'woocommerce_price_decimal_sep'	,	'.'		);
			$thousand_sep  		= get_option( 'woocommerce_price_thousand_sep'	,	','		);			
			$zero				= number_format(0, $num_decimals,$decimal_sep,$thousand_sep);
			$i					= 0;
			
			foreach ( $items as $rkey => $rvalue ):
				$order_item = $rvalue;
				$td_value 	= '';
				foreach($columns as $key => $value):
					switch ($key) {
						case "order_total":
						case "order_subtotal":
						case "order_shipping":
						case "order_tax":
						case "order_discount":
						case "other_charges_total":
							$td_value 	=  isset($rvalue->$key) ? $rvalue->$key : 0;
							$td_value 	=  strlen($td_value)>0 ? $td_value : 0;
							$td_value	=  $td_value == 0 ? $zero : number_format($td_value, $num_decimals,$decimal_sep,$thousand_sep);
							break;
						case "purchase_header_id":
						case "vendor_id":
						case "purchase_number":
						case "warehouse_id":
						case "purchase_no":
							$td_value = isset($order_item->$key) ? trim($order_item->$key) : '';
							break;
						case "delivery_date":
							$td_value = isset($order_item->$key) ? trim($order_item->$key) : '';
							$td_value = empty($td_value) ? '' : date($date_format,strtotime($td_value));
							//$td_value	= isset($order_item->$key) ? date($date_format,strtotime($order_item->$key)) : '';
							break;
						default:
							if(isset($price_columns[$key])){
								$td_value 	=  isset($rvalue->$key) ? $rvalue->$key : '';
								$td_value 	=  strlen($td_value)>0 ? $td_value : 0;
								$td_value	=  $td_value == 0 ? $zero : number_format($td_value, $num_decimals,$decimal_sep,$thousand_sep);
							}else{
								$td_value 	=  isset($rvalue->$key) ? $rvalue->$key : '';
							}
							break;					
					}
					$export_rows[$i][$key]	=  $td_value;
				endforeach;
				$i++;
			endforeach;
			
			$export_file_name 	= $this->get_request('export_file_name',"ic_inventory");
			$report_name 		= $this->get_request('report_name',"");
			$today_date 		= date_i18n("Y-m-d-H-i-s");				
			$export_filename 	= $export_file_name."-".$today_date.".".$export_file_format;
			$export_filename 	= apply_filters('ic_commerce_export_csv_excel_format_file_name',$export_filename,$report_name,$today_date,$export_file_name,$export_file_format);
			do_action("ic_commerce_export_csv_excel_format",$export_filename,$export_rows,$columns,$export_file_format,$report_name);
			$out = $this->ExportToCsv($export_filename,$export_rows,$columns,$export_file_format,$report_name);
		}
		
		function export_pdf($export_file_format = 'pdf'){
			
			$items 				= $this->get_items('all_row');			
			$columns 			= $this->get_columns();			
			$date_format		= $this->get_request('date_format',"Y-m-d");
			$price_columns		= $this->get_price_columns();
			
			unset($columns['edit']);
			
			$num_decimals   	= get_option( 'woocommerce_price_num_decimals'	,	0		);
			$decimal_sep    	= get_option( 'woocommerce_price_decimal_sep'	,	'.'		);
			$thousand_sep  		= get_option( 'woocommerce_price_thousand_sep'	,	','		);			
			$zero				= wc_price(0);
			$i					= 0;
			
			foreach ( $items as $rkey => $rvalue ):
				$order_item = $rvalue;
				$td_value 	= '';
				foreach($columns as $key => $value):
					switch ($key) {
						case "order_total":
						case "order_subtotal":
						case "order_shipping":
						case "order_tax":
						case "order_discount":
						case "other_charges_total":
							$td_value 	=  isset($rvalue->$key) ? $rvalue->$key : 0;
							$td_value 	=  strlen($td_value)>0 ? $td_value : 0;
							$td_value	=  $td_value == 0 ? $zero : wc_price($td_value);
							break;
						case "purchase_header_id":
						case "vendor_id":
						case "purchase_number":
						case "warehouse_id":
						case "purchase_no":
							$td_value = isset($order_item->$key) ? trim($order_item->$key) : '';
							break;
						case "delivery_date":
							$td_value = isset($order_item->$key) ? trim($order_item->$key) : '';
							$td_value = empty($td_value) ? '' : date($date_format,strtotime($td_value));							
							break;
						default:
							if(isset($price_columns[$key])){
								$td_value 	=  isset($rvalue->$key) ? $rvalue->$key : '';
								$td_value 	=  strlen($td_value)>0 ? $td_value : 0;
								$td_value	=  $td_value == 0 ? $zero : wc_price($td_value);
							}else{
								$td_value 	=  isset($rvalue->$key) ? $rvalue->$key : '';
							}
							break;					
					}
					$export_rows[$i][$key]	=  $td_value;
				endforeach;
				$i++;
			endforeach;
			
			$summary 	= array();		
			$output 	= $this->GetDataGrid($export_rows,$columns,$summary);			
			$this->export_to_pdf($export_rows,$output);
			
		}
		
		function get_products(){
			global $wpdb;			
			$sql = "SELECT ID as id, post_title AS label FROM $wpdb->posts AS posts WHERE 1*1 AND posts.post_type = 'product' ORDER BY label ASC";			
			$items = $wpdb->get_results($sql);
			return $items;
		}
		
		function ajax($type = 'limit_row'){
			
			$call	= $this->get_request('call');
			
			switch ($call) {
				case "list":
					echo $this->get_grid($type);
					break;
				case "print":
					echo $this->get_grid('all_row');
					break;
				default:
					echo "Sub Action {$sub_action} is not found. ic_stock_adjustment_list class";
			}
			
			die;
		}
		
		function export(){
			
			$export_file_format = '';
			$admin_page 		= $this->get_request('admin_page');
			
			if(isset($_REQUEST[$admin_page.'_export_csv'])){
				$export_file_format 			= 'csv';
				$_REQUEST['export_file_format'] = 'csv';
				$this->export_csv($export_file_format);
				die;
			}
			
			if(isset($_REQUEST[$admin_page.'_export_xls'])){
				$export_file_format 			= 'xls';
				$_REQUEST['export_file_format'] = 'xls';
				$this->export_csv($export_file_format);
				die;
			}
			
			if(isset($_REQUEST[$admin_page.'_export_pdf'])){
				$export_file_format 			= 'pdf';
				$_REQUEST['export_file_format'] = 'pdf';
				$this->export_pdf($export_file_format);
				die;
			}
		}
		
		function get_log_users(){
			global $wpdb;
			$table_name 		= $this->constants['table_name'];;
			$sql = "SELECT user_id AS id, display_name AS label FROM {$table_name} AS log LEFT JOIN $wpdb->users AS users ON users.ID = log.user_id GROUP BY user_id";
			$results = $wpdb->get_results($sql);
			return $results;
		}
	}//End Class
}
