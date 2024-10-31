<?php
require_once('ic_commerce_audit_log_lite_function.php');
if (!class_exists('ic_commerce_audit_log_lite_init')) {
	class ic_commerce_audit_log_lite_init extends ic_commerce_audit_log_lite_function{
		
		var $path 		= "";
		var $constants 	= array();
		
		public function __construct($constants = array(), $path = ""){
			
			$this->constants = $constants;
			
			$this->path = $path;
			add_action('admin_menu', 							   				array($this,	'admin_menu'));
			add_action('admin_init', 							   				array($this,	'admin_init'));
			add_action('init', 								  	 				array($this,	'init'));
			add_action('wp_ajax_icwcauditloglite_ajax',  	 					array($this,	'call_ajax_action'));
			add_action('admin_enqueue_scripts',  				 				array($this,	'admin_enqueue_scripts'));
			add_action('wp_loaded', 											array($this, 	'wp_loaded'));	
			add_filter('set-screen-option', 									array($this, 	'set_screen_option'), 10, 3);		
		}
		
		function init(){
			include_once("ic_commerce_audit_log_lite_class.php");	
			$obj = new ic_commerce_audit_log_lite_class($this->constants);
		}
		
		function wp_loaded(){
			global $activate_audit_log_lite_intence;
			$c	= $this->constants;	
			$this->setting_page();		
		}
		
		function setting_page(){
				global $ic_commerce_audit_log_lite_setttings_intence;
				$current_page	= $this->get_request('page',NULL,false);
				$option_page	= $this->get_request('option_page',NULL,false);
				
				if($current_page == $this->constants['plugin_key'].'_options_page' || $option_page == $this->constants['plugin_key']){				
					$c				= $this->constants;
					include_once('ic_commerce_audit_log_lite_setting.php');	
					$ic_commerce_audit_log_lite_setttings_intence = new ic_commerce_audit_log_lite_setttings($c);				
				}
				return $ic_commerce_audit_log_lite_setttings_intence;
			}
		
		function plugin_submenu_list($admin_pages = array(),$parent_menu = 'wooaudit-lite'){
			global $submenu;
			
			$submenu_list = isset($submenu[$parent_menu]) ? $submenu[$parent_menu] : array();
			
			foreach($submenu_list as $key => $menu_list){
				$admin_pages[] = isset($menu_list[2]) ? $menu_list[2] : '';
			}
						
			$this->constants['plugin_submenu'] = $admin_pages;
			
			return $admin_pages;
		}
		
		
		
		function admin_menu(){
			
			$this->constants['plugin_role'] = $option["role"] = "read";
			
			$parent_slug = 'icwcauditloglite';
			
			add_menu_page(__('WooAudit Lite','icwcauditloglite'), __('WooAudit Lite','icwcauditloglite'), $option["role"],$parent_slug,  array(&$this,'add_page'),  plugins_url( '../images/icon-inventory.png', __FILE__ ),'57.98' );
			
			
							
				$list_hook = add_submenu_page($parent_slug, __('Audit Log','icwcauditloglite'), __('Audit Log','icwcauditloglite'),$this->constants['plugin_role'],$parent_slug, 	array(&$this,'add_page'));
				
				do_action('ic_commerce_audit_log_lite_admin_menu',$parent_slug,$this->constants);
				
		
			
			add_submenu_page($parent_slug, __('Settings','icwcauditloglite'), __('Settings','icwcauditloglite'),$option["role"],$this->constants['plugin_key'].'_options_page',array(&$this,'add_page'));
			
			add_submenu_page($parent_slug, __('Other Plug-ins','icwcauditloglite'), __('Other Plug-ins','icwcauditloglite'),$option["role"],$this->constants['plugin_key'].'_add_ons_page',array(&$this,'add_page'));
			
			
			
			$this->plugin_submenu_list(array(),$parent_slug);
			
			add_action("load-$list_hook",array($this, 'add_screen_option'));
			
		}
		
		
		function admin_init(){
			
			$sub_action = $this->get_request('sub_action');
			if($sub_action == $this->constants['plugin_key'].'_export'){				
				$admin_page = $this->get_request('admin_page');				
				
				if ($admin_page == 'icwcauditloglite_list_page' || $admin_page == 'icwcauditloglite'){
					include_once("ic_commerce_audit_log_lite_list.php");
					$obj = new ic_commerce_audit_log_lite_list($this->constants);
					$obj->export();die;
				}				
			}
		}
		
		
		function admin_enqueue_scripts(){
			
			
			$page = isset($_REQUEST["page"]) ? $_REQUEST["page"] : '';
			
			$admin_pages = $this->constants['plugin_submenu'];
			
			if(!in_array($page,$admin_pages)){
				return false;
			}
			
			wp_enqueue_script( 'ajax-script-inv-pro', plugins_url( '../js/script.js', __FILE__ ), array('jquery') );
			wp_enqueue_style(  'admin_styles', plugins_url( '../css/ic-admin.css', __FILE__) );
			wp_enqueue_style(  'normalize_styles', plugins_url( '../css/ic-normalize.css', __FILE__) );
			wp_localize_script('ajax-script-inv-pro','ajax_object',array('ic_ajax_url'=>admin_url('admin-ajax.php'),'admin_url'=>admin_url("admin.php"),'admin_page'=>$page));
			
			switch ($page) {				
				case "icwcauditloglite":
				case "icwcauditloglite_list_page":
					wp_enqueue_script('jquery-ui-datepicker');
					wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
					wp_enqueue_script( 'ic-purchase-js-2',plugins_url('../js/ic-purchase-2.js',__FILE__),array('jquery'));
					break;
			}
		}
		
		function call_ajax_action(){						
			$sub_action	= $this->get_request('sub_action');
			$c	= $this->constants;			
			switch ($sub_action) {
				case "icwcauditloglite":
				case "icwcauditloglite_list_page":
					include_once("ic_commerce_audit_log_lite_list.php");
					$obj = new ic_commerce_audit_log_lite_list($this->constants);
					$obj->ajax();
					break;
				default:
					echo "Sub Action {$sub_action} is not found. ic_init class";
			}
			die;
		}
		
		function add_page(){
			?>	
            	<div class="wrap ic_inventory_wrap iccommercepluginwrap">
                <div class="container-liquid">
				<?php
					$page 	= isset($_REQUEST["page"]) ? $_REQUEST["page"] : '';
					$c 		= $this->constants;
					switch ($page) {
						case "icwcauditloglite":
						case "icwcauditloglite_list_page":
							include_once("ic_commerce_audit_log_lite_list.php");
							$obj = new ic_commerce_audit_log_lite_list($this->constants);
							$obj->init();
							break;
						case "icwcauditloglite_activate_page":
							global $activate_audit_log_lite_intence;
							$title = __('Activate','icwcauditloglite');
							$obj = $activate_audit_log_lite_intence;
							$obj->init();							
							break;
						case "icwcauditloglite_options_page":
							global $ic_commerce_audit_log_lite_setttings_intence;
							$title = __('Activate','icwcauditloglite');
							$obj = $ic_commerce_audit_log_lite_setttings_intence;
							$obj->init();
							break;
						case "icwcauditloglite_add_ons_page":
							include_once("ic_commerce_audit_log_lite_add_ons.php");
							$obj = new ic_commerce_audit_log_lite_addons($this->constants);
							$obj->init();
							break;
						default:
							echo "No Page {$page} is found.";
					}                
                ?>
            	</div>
                </div>
            <?php  
		}
	}
}