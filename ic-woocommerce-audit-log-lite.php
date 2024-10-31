<?php
/*
Plugin Name: Order Audit Log for Woocommerce
Description: Audit Log is a plugin which tracks the changes that users make in the orders.
Version: 2.0
Author: Infosoft Consultant 
Author URI: http://plugins.infosofttech.com
Plugin URI: https://wordpress.org/plugins/order-audit-log-for-woocommerce/

Tested Wordpress Version: 6.1.x
WC requires at least: 3.5.x
WC tested up to: 7.4.x
Requires at least: 5.7
Requires PHP: 5.6

Text Domain: icwcauditloglite
Domain Path: /languages/
*/

class ic_woocommerce_audit_log_lite{
	var $constants = array();
	
	public function __construct(){
		add_filter( 'plugin_action_links_order-audit-log-for-woocommerce/ic-woocommerce-audit-log-lite.php', array( $this, 'plugin_action_links' ), 9, 2 );
		add_action( 'init', array( $this, 'load_plugin_textdomain' ));
		add_action( 'plugins_loaded', array($this, 'plugins_loaded') );
	}
	
	function plugin_action_links($plugin_links, $file = ''){
		$plugin_links[] = '<a target="_blank" href="'.admin_url('admin.php?page=icwcauditloglite').'">' . esc_html__( 'Order Audit Log', 'icwcauditloglite' ) . '</a>';
		return $plugin_links;
	}

	function load_plugin_textdomain(){
		load_plugin_textdomain( 'icwoocommercetax', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}
	
	function plugins_loaded(){
		global $wp_version, $wpdb;
		 
		include_once('include/ic_commerce_audit_log_lite_init.php'); 
		$constants	=	array();
		$constants['plugin_key'] 				= 'icwcauditloglite';
		$constants['plugin_slug'] 				= 'ic-woocommerce-audit-log-lite';
		$constants['plugin_file_id'] 			= "ic-woocommerce-audit-log-lite";
		$constants['plugin_folder'] 			= "ic-woocommerce-audit-log-lite";
		
		$constants['plugin_file'] 				= __FILE__;
		
		$constants['version'] 				   	= "1.9";
		$constants['sub_version']			   	= "1.9";
		$constants['last_updated'] 			  	= "2019-03-15";
		$constants['customized'] 				= "no";
		$constants['customized_date'] 		   	= "";
		$constants['wp_version'] 				= $wp_version;
		$constants['parent_plugin_version'] 	= get_option('woocommerce_version',0);
		$constants['parent_plugin_db_version']  = get_option('woocommerce_db_version',0);
		$constants['plugin_name'] 			   	= "ic-woocommerce-audit-log-lite";		
		
		$constants['plugin_api_url'] 			= "http://plugins.infosofttech.com/api-woo-audit-log-lite.php";
		$constants['product_id'] 			    = '14353';
		
		if($_SERVER['SERVER_NAME'] == "p43"){
			$constants['plugin_api_url'] 			= "http://p43/api/api-woo-audit-log-lite.php";
			$constants['product_id'] 			    = '19';
		}else{
			$constants['plugin_api_url'] 			= "http://plugins.infosofttech.com/api-woo-audit-log-lite.php";
			$constants['product_id'] 			    = '14353';
		}
		
		$constants['is_admin'] 				  	= is_admin();
		
		$constants['admin_page'] 				= isset($_GET['page']) ? $_GET['page'] : '';
		
		$constants['is_wc_ge_27'] 				= version_compare( WC_VERSION, '2.7', '<' );
		
		$constants['table_name'] 				= $this->get_table_name();
		
		$constants['plugin_role'] 				= 'read';
		
		$constants['plugin_options'] 			= get_option($constants['plugin_key']);
		
		$obj 		= new ic_commerce_audit_log_lite_init($constants,__FILE__); 
		
		$this->constants = $constants;
	}
	
	function get_table_name(){
		global $wp_version, $wpdb;
		$table_name	= $wpdb->prefix."ic_order_audit_log";
		return $table_name;
	}
	
	function print_array($ar = NULL,$display = true){
		if($ar){
			$output = "<pre>";
			$output .= print_r($ar,true);
			$output .= "</pre>";
			
			if($display){
				echo $output;
			}else{
				return $output;
			}
		}
	
	}
	
	public static function activation() {
		if (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			$ic = new ic_woocommerce_audit_log_lite();
			$ic->create_table();
		}else{
			 wp_die( __('Please activate WooCommerce.', 'icwcauditloglite' ), 'Plugin dependency check', array( 'back_link' => true ) );
		}
	}
	
	 public static function create_table(){
	 	global $wpdb;
		
		$table_name	= $wpdb->prefix."ic_order_audit_log";
		
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE `{$table_name}` (
			  `id` 					int(11) 		NOT NULL AUTO_INCREMENT,
			  `user_id` 			int(11) 		NOT NULL,
			  `date` 				datetime 		NOT NULL,
			  `order_item_name` 	varchar(250) 				DEFAULT NULL,
			  `order_id` 			int(11) 		NOT NULL 	DEFAULT 0,
			  `refund_id` 			int(11) 		NOT NULL 	DEFAULT 0,
			  `customer_id` 		int(11) 		NOT NULL,
			  `order_date` 			datetime 		NOT NULL,
			  `order_item_id` 		int(11) 					DEFAULT NULL,
			  `product_id` 			int(11) 					DEFAULT NULL,
			  `variation_id` 		int(11) 					DEFAULT NULL,
			  `action_log` 			varchar(20) 				DEFAULT NULL,
			  `old_order_status` 	varchar(20) 				DEFAULT NULL,
			  `new_order_status` 	varchar(20) 				DEFAULT NULL,
			  `old_order_item_qty` 	int(5) 						DEFAULT NULL,
			  `new_order_item_qty` 	int(5) 						DEFAULT NULL,	
			  `line_subtotal` 		double						DEFAULT NULL,
			  `line_total` 			double 						DEFAULT NULL,
			   PRIMARY KEY (`id`)
			);";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}else{
			
			$result = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'old_order_item_qty'");
			if($wpdb->num_rows<=0){
				$sql = "ALTER TABLE `{$table_name}` CHANGE `order_item_qty` `old_order_item_qty` INT(5) NULL DEFAULT NULL";
				$result 	= $wpdb->query($sql);
				$sql = "ALTER TABLE `{$table_name}` ADD `new_order_item_qty` INT(5) NOT NULL AFTER `old_order_item_qty`";
				$result 	= $wpdb->query($sql);
			}
			
			$result = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'action_log'");
			if($wpdb->num_rows<=0){
				$sql = "ALTER TABLE `{$table_name}` ADD `action_log` varchar(20) DEFAULT NULL AFTER `variation_id`";
				$result 	= $wpdb->query($sql);
			}
			
			$result = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'line_subtotal'");
			
			if($wpdb->num_rows<=0){
				$sql = "ALTER TABLE `{$table_name}` ADD `line_subtotal`  double DEFAULT NULL AFTER `new_order_item_qty`";
				$result 	= $wpdb->query($sql);
				
				$sql = "ALTER TABLE `{$table_name}` ADD `line_total`  double DEFAULT NULL AFTER `new_order_item_qty`";
				$result 	= $wpdb->query($sql);
			}
			
			$result = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'old_order_status'");
			
			if($wpdb->num_rows<=0){
				$sql = "ALTER TABLE `{$table_name}` ADD `old_order_status` varchar(20) DEFAULT NULL AFTER `action_log`";
				$result 	= $wpdb->query($sql);
				
				$sql = "ALTER TABLE `{$table_name}` ADD `new_order_status`  varchar(20) DEFAULT NULL AFTER `action_log`";
				$result 	= $wpdb->query($sql);
			}
			
			$result = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'customer_id'");
			
			if($wpdb->num_rows<=0){
				$sql = "ALTER TABLE `{$table_name}` ADD `customer_id` int(11) NOT NULL AFTER `order_id`";
				$result 	= $wpdb->query($sql);//print_r($wpdb);
				
				$sql = "ALTER TABLE `{$table_name}` ADD `order_date`  datetime NOT NULL AFTER `order_id`";
				$result 	= $wpdb->query($sql);//print_r($wpdb);
			}
			
			$result = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'refund_id'");
			
			if($wpdb->num_rows<=0){
				$sql = "ALTER TABLE `{$table_name}` ADD `refund_id` int(11) NOT NULL DEFAULT 0 AFTER `order_id`";
				$result 	= $wpdb->query($sql);
			}
			
			$sql = "ALTER TABLE `{$table_name}` CHANGE `action_log` `action_log` VARCHAR(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;";
			$result 	= $wpdb->query($sql);
						
			
		}
		
		
		$settings['order_trash'] 				= 1;
		$settings['order_untrash'] 				= 1;		
		$settings['order_item_edited'] 			= 1;		
		$settings['order_item_added'] 			= 0;
		
		update_option('icwcauditloglite', $settings);
	}
}

$obj = new  ic_woocommerce_audit_log_lite();
register_activation_hook(__FILE__, array('ic_woocommerce_audit_log_lite', 'activation'));