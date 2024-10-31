<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	require_once('ic_commerce_audit_log_lite_function.php');

if ( ! class_exists( 'ic_commerce_audit_log_lite_setttings' ) ) {
	class ic_commerce_audit_log_lite_setttings extends ic_commerce_audit_log_lite_function{
		
		public $constants 	=	array();
		
		public function __construct($constants) {
			
			$this->constants		= $constants;
			add_action( 'admin_notices', 	array( $this, 'admin_notices'));
			add_action( 'admin_init', 		array( &$this, 'save_settings'),100);
			add_action( 'admin_init', 		array( &$this, 'init_settings'),110);
			
			
		}
		
		public function init() {
			
			if ( !current_user_can( $this->constants['plugin_role'] ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ,'icwcauditloglite' ) );
			}
			
			if(!isset($_REQUEST['page'])) return false;
			
			echo get_option($this->constants['plugin_key'].'_activated_plugin_error');
			delete_option($this->constants['plugin_key'].'_activated_plugin_error');
			
			?>	
				<h2>WooAudit Log - Settings</h2>
                <form method="post" id="form_ic_commerce_settings" name="form_ic_commerce_settings" class="form_ic_commerce_settings force_submit" autocomplete="off">
                	<div class="ic_commerce_settings">
                    <?php
                    	settings_fields( $this->constants['plugin_key'] );
                        do_settings_sections( $this->constants['plugin_key'] );
					?>
                        <div class="submit_btn savebtn">
                            <?php                        
                                if (current_user_can( $this->constants['plugin_role'] ) )  {
                                    //submit_button('Submit','primary','submit',true);
                                    echo '<p class="submit"><input name="submit" id="submit" class="button onformprocess ic_save_setting" value="'. __( 'Save Changes', 'icwcauditloglite').'" type="submit"></p>';
                                }else{
                                    //submit_button('Save Changes','primary','submit',true, array( "disabled"=>"disabled"));
                                    echo '<p class="submit"><input name="submit" id="submit" class="button onformprocess ic_save_setting" value="'. __( 'Save Changes', 'icwcauditloglite').'" type="submit"  "disabled"="disabled"></p>';
                                }						
                            ?>
                        </div>
                    </div>
                </form>
                <div id="ic_please_wait" class="ic_please_wait_hide"><div class="ic_please_wait_msg"><?php _e('Please Wait','icwcauditloglite'); ?></div><div class="ic_close_button"><?php _e('Close','icwcauditloglite'); ?></div></div>
                <style type="text/css">
					.ic_please_wait_hide{ display:none;}
					.ic_close_button{ color:#0074a2; position:absolute; right:10px; top:10px; cursor:pointer; display:none; font-size:12px;}
                	.ic_please_wait{ display:block; position:fixed; left:35%; right:35%; top:40%;  background:#fff; border:2px solid #CCC; text-align:center; font-size:15px; color:#666; padding-top:10px; padding-bottom:10px;}
					/*.ic_commerce_settings .form-table th,
					.ic_commerce_settings .form-table td{padding: 10px 5px 5px 20px;}
					*/
                </style>
			<?php      
		}  
		
		//New Change ID 20140918
		var $admin_notice = "";  
		function admin_notices(){
			if(isset($_GET['page']) and ($_GET['page'] == $this->constants['plugin_key'].'_options_page')){
				
				
				$msg = get_option($this->constants['plugin_key'].'_admin_notice_error','');
				if($msg){
					update_option($this->constants['plugin_key'].'_admin_notice_error','');
				}
				
				echo $msg;
				
				$msg = get_option($this->constants['plugin_key'].'_admin_notice_message','');
				if($msg){
					update_option($this->constants['plugin_key'].'_admin_notice_message','');
				}
				
				echo $msg;
			}
		}
		
		function save_settings(){
			$option = $this->constants['plugin_key'];
			
			//echo file_get_contents('https://www.google.com/accounts/ClientLogin');
			
			//Save Option on save
			if(isset($_POST[$option]) and (isset($_POST['option_page']) and $_POST['option_page'] == $option) and  isset($_POST['option_page'])){
				
				$o 		= get_option($option,false);
				$post 	= $_POST[$option];
				
				
				//New Change ID 20140918
				$error_txt = '<div class="updated fade"><p>'.__("Settings saved",'icwcauditloglite')." </p></div>\n";
				
				$msg = get_option($this->constants['plugin_key'].'_admin_notice_message','');
				if($msg){
					update_option($this->constants['plugin_key'].'_admin_notice_message',$error_txt);
				}else{
					delete_option($this->constants['plugin_key'].'_admin_notice_message');
					add_option($this->constants['plugin_key'].'_admin_notice_message',$error_txt);
				}
					
					
					
				$settings = $this->get_audit_log_settings();
				foreach($settings as $key => $label){
					$post[$key] = empty($post[$key]) ? 0: $post[$key];
				}
				
				$post = apply_filters("ic_commerce_audit_log_lite_settting_values", $post, $this, $o);
				
				$new_post = array();
				foreach($post as $field_key => $field_value){
					if(!is_array($field_value)){
						$new_post[$field_key] = stripslashes($field_value);
					}else{
						$new_post[$field_key] = $field_value;
					}
				}
				
				if($o){
					update_option($option,$new_post);
				}else{delete_option($option);
					add_option($option,$new_post);
				}
				
				$this->constants['plugin_options'] 	= get_option($this->constants['plugin_key']);
			}
		}
		
		function get_audit_log_settings(){
			$transaction_types = array();			
			$transaction_types['order_item_edited'] 			= __('Order Item Edited',	'icwcauditloglite');
			$transaction_types['order_trash'] 					= __('Order Moved To Trash', 'icwcauditloglite');
			$transaction_types['order_untrash'] 				= __('Order Moved From Trash To Restore','icwcauditloglite');
			
			//$transaction_types['order_item_added'] 				= __('New Item Added',		'icwcauditloglite');
			return $transaction_types;
		}
		
		public function init_settings() {
			$option = $this->constants['plugin_key'];
		
			// Create option in wp_options.
			if ( false == get_option( $option ) ) {
				add_option( $option );
			}
			
			$settings = $this->get_audit_log_settings();
			
			add_settings_section('audit_log_sections',	__(	'Audit Events: (Following various Order level Events are presently logged for Audit)', 'icwcauditloglite'),	array( &$this, 'section_options_callback' ),$option);
			
			foreach($settings as $key => $label){
				add_settings_field($key ,$label,array( &$this,'checkbox_element_callback' ),$option, 'audit_log_sections', array('menu'=> $option,'value'=>'1','label_for'=>$key,'id'=>$key,'default'=>0));
			}
			
			
			do_action('ic_commerce_audit_log_lite_settting_bottom',$this, $option);
			
			// Register settings.
			register_setting( $option, $option, array( &$this, 'options_validate' ) );
	   }
	
			
		
	
		/**
		 * Text field callback.
		 *
		 * @param  array $args Field arguments.
		 *
		 * @return string      Text field.
		 */
		public function text_element_callback( $args ) {
			$menu 		= $args['menu'];
			$id 		= $args['id'];
			$size 		= isset( $args['size'] ) 		? $args['size'] : '25';
			$class 		= isset( $args['class'] ) 		? ' class="'.$args['class'] .'"': '';
			$maxlength	= isset( $args['maxlength'] ) 	? ' maxlength="'.$args['maxlength'] .'"': '';			
			$type		= isset( $args['type'] ) 		? $args['type']: 'text';
			$autocpt	= isset( $args['autocomplete'] ) 		? $args['autocomplete']: 'off';
			
			$options	= get_option( $menu );
					
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
			
			$default_value = isset( $args['default'] ) ? $args['default'] : '';
			
			/*
			if(!current_user_can('manage_options')){
				$ga_fields = array("ga_account","ga_password","ga_profile_id");
				if(in_array($id,$ga_fields)){
					$current = "";
				}
			}
			*/
			
			 
			
			$disabled = (isset($args['disabled'])) ? ' disabled' : '';
			$readonly = (isset($args['readonly'])) ? ' readonly="readonly"' : '';
			
			$html = sprintf( '<input id="%1$s" name="%2$s[%1$s]" value="%3$s" size="%4$s"%5$s%6$s%7$s%8$s type="%9$s" data-name="%1$s"  data-default_value="%10$s"  autocomplete="%11$s" />', $id, $menu, $current, $size, $disabled, $class, $maxlength, $readonly, $type, $default_value, $autocpt);
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}	
		
			echo $html."\n";
		}
		
		public function text_array_element_callback( $args ) {
			$menu 		= $args['menu'];
			$id 		= $args['id'];
			$size 		= isset( $args['size'] ) 		? $args['size'] : '25';
			$class 		= isset( $args['class'] ) 		? ' class="'.$args['class'] .'"': '';
			$maxlength	= isset( $args['maxlength'] ) 	? ' maxlength="'.$args['maxlength'] .'"': '';			
			$type		= isset( $args['type'] ) 		? $args['type']: 'text';
			$multi_name	= isset( $args['multi_name'] ) 	? $args['multi_name']: '';
			
			$multi_name	= '';
			
			$options	= get_option( $menu );
			
			//$this->print_array($options);
					
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];				
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
			
			/*
			if(!current_user_can('manage_options')){
				$ga_fields = array("ga_account","ga_password","ga_profile_id");
				if(in_array($id,$ga_fields)){
					$current = "";
				}
			}
			*/
			
			$disabled = (isset($args['disabled'])) ? ' disabled' : '';
			$readonly = (isset($args['readonly'])) ? ' readonly="readonly"' : '';
			
			$html = sprintf( '<input id="%1$s" name="%2$s[%1$s]" value="%3$s" size="%4$s"%5$s%6$s%7$s%8$s type="%9$s" />', $id, $menu, $current, $size, $disabled, $class, $maxlength, $readonly, $type, $multi_name);
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}	
		
			echo $html."\n";
		}
		
		
		
		/**
		 * Displays a selectbox for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function _select_element_callback( $args ) {//New Change ID 20140918
			$menu = $args['menu'];
			$id = $args['id'];
			
			$options = get_option( $menu );
			
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];				
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
	
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : ''; 
			
			$html = sprintf( '<select class="select_box select_%2$s" name="%1$s[%2$s]" id="%1$s[%2$s]"%3$s>', $menu, $id, $disabled ); 
			$html .= sprintf( '<option value="%s"%s>%s</option>', '0', selected( $current, '0', false ), '' );
			
			foreach ( $args['options'] as $key => $label ) { 
				$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label ); 
			}
			$html .= sprintf( '</select>' ); 
	
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
			
			echo $html;
		}
		
		//New Change ID 20140918
		public function select_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
			$options = get_option( $menu );
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
			
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			$multiple = (isset( $args['multiple'] )) ? ' multiple="multiple"' : '';
			$size = (isset( $args['size'] )) ? " size=\"{$args['size']}\"" : '';
			
			$width = (isset( $args['width'] )) ? $args['width'] : '';
			
			$first_option_label = (isset( $args['first_option_label'] )) ? trim($args['first_option_label']): 'Select One';
			$first_option_value = (isset( $args['first_option_value'] )) ? trim($args['first_option_value']): 0;
			
			$default 			= isset($args['default']) ? $args['default'] : 0;
			if(is_array($default)){
				if(count($default)>0){
					$default 			= implode(",",$default);	
				}else{
					$default 			= 0;	
				}				
			}else{
				$default 			= trim($default);
			}
						
			$default_attr		= " data-default_value=\"{$default}\"";
			
			
			$_multiple = "";
			$style = "";
			
			if($width){
				$style = " style=\"";
				$style .= "width:{$width};";
				$style .= '"';
			}
			
			if(strlen($multiple)>0)	$_multiple = "[]";
			
			$html = sprintf( '<select  class="select_box select_%2$s" name="%1$s[%2$s]%7$s" id="%2$s"%3$s%4$s%5$s%6$s%8$s>', $menu, $id, $disabled, $multiple, $size,$style,$_multiple, $default_attr );
			if(strlen($multiple)>0){ 
				if(!is_array($current)){
					$current = array();
				}
				foreach ( $args['options'] as $key => $label ) {
					if(in_array($key,$current)){
						$html .= sprintf( '<option value="%s"%s>%s</option>', $key, '  selected="selected"', $label ); 
					}else{
						$html .= sprintf( '<option value="%s"%s>%s</option>', $key, '', $label ); 
					}
				}
			}else{
				if(!empty($first_option_label)) $html .= sprintf( '<option value="%s"%s>%s</option>', '0', selected( $current, $first_option_value, false ), $first_option_label); 
				
				foreach ( $args['options'] as $key => $label ) {
					$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label ); 
				}
			}
			
			
			$html .= sprintf( '</select>' );
			
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
			echo $html;
		}
	
		/**
		 * Displays a multiple selectbox for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function multiple_select_element_callback( $args ) {
			$html = '';
			foreach ($args as $id => $boxes) {
				$menu = $boxes['menu'];
				
				$options = get_option( $menu );
				
				if ( isset( $options[$id] ) ) {
					$current = $options[$id];
				} else {
					$current = isset( $boxes['default'] ) ? $boxes['default'] : '';
				}
				
				$disabled = (isset( $boxes['disabled'] )) ? ' disabled' : '';
				
				$box = sprintf( '<select name="%1$s[%2$s]" id="%1$s[%2$s]"%3$s>', $menu, $id, $disabled);
				$box .= sprintf( '<option value="%s"%s>%s</option>', '0', selected( $current, '0', false ), '' );
				
				foreach ( $boxes['options'] as $key => $label ) {
					$box .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
				}
				$box .= '</select>';
		
				if ( isset( $boxes['description'] ) ) {
					$box .= sprintf( '<p class="description">%s</p>', $boxes['description'] );
				}
				
				$html .= $box.'<br />';
			}
			
			
			echo $html;
		}
	
		/**
		 * Checkbox field callback.
		 *
		 * @param  array $args Field arguments.
		 *
		 * @return string      Checkbox field.
		 */
		public function checkbox_element_callback( $args ) {
			$menu 	= $args['menu'];
			$id 	= $args['id'];
			
			$value 	= isset($args['value']) ? $args['value'] : 1;
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
		
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			$html = sprintf( '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="%5$s"%3$s %4$s/>', $id, $menu, checked( $value, $current, false ), $disabled,$value);
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
				
			echo $html;
		}
	
		/**
		 * Displays a multicheckbox a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function radio_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
	
			$html = '';
			foreach ( $args['options'] as $key => $label ) {
				$html .= sprintf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s />', $menu, $id, $key, checked( $current, $key, false ) );
				$html .= sprintf( '<label for="%1$s[%2$s][%3$s]"> %4$s</label><br>', $menu, $id, $key, $label);
			}
			
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
	
			echo $html;
		}
	
		/**
		 * Section null callback.
		 *
		 * @return void.
		 */
		public function section_options_callback(){
			//echo "<tr><td colspan='2'>uhfiu hfiu spiaf gh</td></tr>";
		}
		
		
		
	
		/**
		 * Validate/sanitize options input
		 */
		public function options_validate( $input ) {
			// Create our array for storing the validated options.
			$output = array();
	
			// Loop through each of the incoming options.
			foreach ( $input as $key => $value ) {
	
				// Check to see if the current option has a value. If so, process it.
				if ( isset( $input[$key] ) ) {
	
					// Strip all HTML and PHP tags and properly handle quoted strings.
					$output[$key] = strip_tags( stripslashes( $input[$key] ) );
				}
			}
	
			// Return the array processing any additional functions filtered by this action.
			return apply_filters( $this->constants['plugin_key'].'_validate_input', $output, $input );
		}
		
		public function _choose_image_callback( $args ) {
			$menu 		= $args['menu'];
			$id 		= $args['id'];
			$size 		= isset( $args['size'] ) 		? $args['size'] : '25';
			$class 		= isset( $args['class'] ) 		? ' class="'.$args['class'] .'"': '';
			$maxlength	= isset( $args['maxlength'] ) 	? ' maxlength="'.$args['maxlength'] .'"': '';
			
			
			
			$choose_id 		= isset( $args['choose_id'] ) 			? $args['choose_id'] 	: $id;
			$choose_class	= isset( $args['choose_class'] ) 		? ' '.$args['choose_class']: '';
			$choose_data	= isset( $args['choose_data'] ) 		? $args['choose_data'] 	: 'Choose a Image';
			$choose_update 	= isset( $args['choose_update'] ) 		? $args['choose_update'] 	: 'Set as Refresh image';
			$choose_label	= isset( $args['choose_label'] ) 		? $args['choose_label'] 	: 'Choose Image';
			
			$options	= get_option( $menu );
					
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
	
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			$html = "";
			$html .= "\n".sprintf( ' <input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" readonly="readonly"   size="%4$s"%5$s%6$s%7$s />', $id, $menu, $current, $size, $disabled, $class, $maxlength);			
			$html .= "\n".sprintf( ' <a id="%1$s" class="onformprocess button%2$s" data-choose="%3$s" data-update="%4$s">%5$s</a>',$choose_id,$choose_class,$choose_data, $choose_update, $choose_label);
			$html .= "\n".sprintf( ' <a id="clear_%1$s" class="onformprocess clear_textbox button%2$s" data-choose="%3$s" data-update="%4$s">%5$s</a>',$choose_id,$choose_class,$choose_data, $choose_update, 'Clear');

			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
			
			if ($current) {
				$company_name	=	$blog_title = get_bloginfo('name'); ;
				$html .= sprintf( '<div class="logo_image logo_image_%1$s"><img src="%2$s" alt="%3$s" /></div>', $choose_class, $current, $company_name);
			}	
		
			echo $html;
		}
		
		public function choose_image_callback( $args ) {
			$menu 		= $args['menu'];
			$id 		= $args['id'];
			$size 		= isset( $args['size'] ) 		? $args['size'] : '25';
			$class 		= isset( $args['class'] ) 		? ' class="upload_field '.$args['class'] .'"': 'upload_field';
			$maxlength	= isset( $args['maxlength'] ) 	? ' maxlength="'.$args['maxlength'] .'"': '';
			
			
			
			$choose_id 		= isset( $args['choose_id'] ) 			? $args['choose_id'] 	: $id;
			$choose_class	= isset( $args['choose_class'] ) 		? ' '.$args['choose_class']: '';
			$choose_data	= isset( $args['choose_data'] ) 		? $args['choose_data'] 	: 'Choose a Image';
			$choose_update 	= isset( $args['choose_update'] ) 		? $args['choose_update'] 	: 'Set as Refresh image';
			$choose_label	= isset( $args['choose_label'] ) 		? $args['choose_label'] 	: 'Choose Image';
			
			$options	= get_option( $menu );
					
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
	
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			$html = "";
			$html .= "\n".sprintf( ' <input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" readonly="readonly"   size="%4$s"%5$s%6$s%7$s />', $id, $menu, $current, $size, $disabled, $class, $maxlength);			
			$html .= "\n".sprintf( ' <a id="%1$s" class="ic_upload_button onformprocess button%2$s" data-choose="%3$s" data-update="%4$s">%5$s</a>',$choose_id,$choose_class,$choose_data, $choose_update, $choose_label);
			$html .= "\n".sprintf( ' <a id="clear_%1$s" class="onformprocess clear_textbox button%2$s" data-choose="%3$s" data-update="%4$s">%5$s</a>',$choose_id,$choose_class,$choose_data, $choose_update, 'Clear');

			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
			
			if ($current) {
				$company_name	=	$blog_title = get_bloginfo('name'); ;
				$html .= sprintf( '<div class="logo_image logo_image_%1$s"><img src="%2$s" alt="%3$s" /></div>', $choose_class, $current, $company_name);
			}	
		
			echo $html;
		}
		
		public function color_picker_callback( $args ) {
			$menu 		= $args['menu'];
			$id 		= $args['id'];
			$size 		= isset( $args['size'] ) 		? $args['size'] : '25';
			$class 		= isset( $args['class'] ) 		? ' class="'.$args['class'] .'"': '';
			$maxlength	= isset( $args['maxlength'] ) 	? ' maxlength="'.$args['maxlength'] .'"': '';
			$options	= get_option( $menu );
					
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
	
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			$html = sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s"  size="%4$s"%5$s%6$s%7$s />', $id, $menu, $current, $size, $disabled, $class, $maxlength);
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= ' <a href="#"   title="this is my title" class="help_tip">target element</a>';			}	
		
			echo $html;
			
			//wp_enqueue_script('wpb-tooltip-jquery', plugins_url('/wpb-tooltip.js', __FILE__ ), array('jquery-ui-tooltip'), '', true);
			
			
		}
		
		public function textarea_element_callback( $args ) {
			$menu 		= $args['menu'];
			$id 		= $args['id'];
			$cols 		= isset( $args['cols'] ) 		? $args['cols'] : '30';
			$rows 		= isset( $args['rows'] ) 		? $args['rows'] : '5';
			$class 		= isset( $args['class'] ) 		? ' class="'.$args['class'] .'"': '';
			$maxlength	= isset( $args['maxlength'] ) 	? ' maxlength="'.$args['maxlength'] .'"': '';
			$options	= get_option( $menu );
					
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
	
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			$html = sprintf( '<textarea id="%1$s" name="%2$s[%1$s]" cols="%3$s" rows="%4$s" class="%5$s">%6$s</textarea>', $id, $menu, $cols, $rows, $class, $current);
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}	
		
			echo $html."\n";
		}
		
		function create_button_element_callback( $args ) {
			$menu 		= $args['menu'];
			$id 		= $args['id'];
			$buttons	= isset( $args['buttons'] ) 	? $args['buttons'] : array();
			$options	= get_option( $menu );
					
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
	
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			$html = "";
			foreach($buttons as $btn => $bt){
				$id 	= $id ."_". $bt['id'];				
				$type 	= $bt['type'];
				$value 	= $bt['value'];
				$sub_action 	= isset($bt['sub_action']) ? $bt['sub_action'] : $id;
				$html .= sprintf( '<input type="%1$s" id="%2$s" name="%3$s[%2$s]" value="%4$s" class="%2$s onformprocess button test_email_schedule" data-sub_action="%5$s" />', $type, $id, $menu, $value, $sub_action);
			}
			
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}	
		
			echo $html."\n";
		}
		
		public function label_element_callback( $args ) {
			
			$html =  $default 		= isset( $args['default'] ) 		? $args['default'] : '';
			
			//$option_page = isset($_GET['page']) ? $_GET['page'] : '';
			//$currunt_page = admin_url("admin.php?page={$option_page}&ga_logout=yes");				
			//$html = 'You are presently logged in to GA account; click on Logout button to <a href="'.$currunt_page.'">Logout</a> of GA account';
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= ' <a href="#"   title="this is my title" class="help_tip">target element</a>';			}	
		
			echo $html;
			
			//wp_enqueue_script('wpb-tooltip-jquery', plugins_url('/wpb-tooltip.js', __FILE__ ), array('jquery-ui-tooltip'), '', true);
			
			
		}
		
		function check_email($check) {
			$expression = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$/";
			if (preg_match($expression, $check)) {
				return true;
			} else {
				return false;
			} 
		}
		
		function get_email_string($emails){
			$emails = str_replace("|",",",$emails);
			$emails = str_replace(";",",",$emails);
			$emails = explode(",", $emails);
			
			$newemail = array();
			foreach($emails as $key => $value):
				$e = trim($value);
				if($this->check_email($e)){
					$newemail[] = $e;
				}				
			endforeach;
			
			if(count($newemail)>0){
				$newemail = array_unique($newemail);
				return implode(",",$newemail);
			}else
				return false;
		}
		
		function get_default_cogs_key($post, $post_key = 'cogs_metakey_simple', $cog_defaulty_key = 'cogs_default_metakey_simple'){
			$default_value 			= isset($this->constants['cog'][$cog_defaulty_key]) ? $this->constants['cog'][$cog_defaulty_key] : '';
			$cogs_metakey			= isset($post[$post_key]) ? str_replace(" ","_",strtolower(trim($post['cogs_metakey_simple']))) : $default_value;
			return strlen($cogs_metakey)>0 ? $cogs_metakey : $default_value;
		}
		
		function get_number_array($start = 0,$end = 0,$multiply = 1){
			$tick_char_lengths = array();
			for($gh=$start;$gh<=$end;$gh++){
				$v = $gh * $multiply;
				$tick_char_lengths[$v] = $v;
			}
			//$this->print_array($tick_char_lengths);	
			return $tick_char_lengths;
		}
		
	}
}