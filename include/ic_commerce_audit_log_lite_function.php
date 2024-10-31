<?php
if (!class_exists('ic_commerce_audit_log_lite_function')) {
	class ic_commerce_audit_log_lite_function{
		var $constants = array();
		
		public function __construct($constants = array()){			
			$this->constants= $constants;
		}
		
	
		public function get_request($name,$default = NULL,$set = false){
			if(isset($_REQUEST[$name])){
				$newRequest = $_REQUEST[$name];
				
				if(is_array($newRequest)){
					$newRequest = implode(",", $newRequest);
				}else{
					$newRequest = trim($newRequest);
				}
				
				if($set) $_REQUEST[$name] = $newRequest;
				
				return $newRequest;
			}else{
				if($set) 	$_REQUEST[$name] = $default;
				return $default;
			}
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
		
		function print_sql($string){			
			
			$string = str_replace("\t", "",$string);
			$string = str_replace("\r\n", "",$string);
			$string = str_replace("\n", "",$string);
			
			$string = str_replace("SELECT ", "\n\tSELECT \n\t",$string);
			//$string = str_replace(",", "\n\t,",$string);
			
			$string = str_replace("FROM", "\n\nFROM",$string);
			$string = str_replace("LEFT", "\n\tLEFT",$string);
			
			$string = str_replace("AND", "\r\n\tAND",$string);			
			$string = str_replace("WHERE", "\n\nWHERE",$string);
			
			$string = str_replace("LIMIT", "\nLIMIT",$string);
			$string = str_replace("ORDER", "\nORDER",$string);
			$string = str_replace("GROUP", "\nGROUP",$string);
			$string = str_replace("AS", " AS ",$string);
			
			$string = str_replace(",", "\n ,",$string);
			
			$new_str = "<pre>";
				$new_str .= $string;
			$new_str .= "</pre>";
			
			echo $new_str;
		}
		
		
		function create_dropdown($data = NULL, $name = "",$id='', $show_option_none="Select One", $class='', $default ="-1", $type = "array", $multiple = false, $size = 0, $d = "-1", $display = true){
			$count 				= count($data);
			$dropdown_multiple 	= '';
			$dropdown_size 		= '';
			
			$selected =  explode(",",$default);
			
			if($count<=0) return '';
			
			if($multiple == true and $size >= 0){
				//$this->print_array($data);
				
				if($count < $size) $size = $count + 1;
				$dropdown_multiple 	= ' multiple="multiple"';
				//echo $count;
				$dropdown_size 		= ' size="'.$size.'"  data-size="'.$size.'"';
			}
			$output = "";
			$output .= '<select name="'.$name.'" id="'.$id.'" class="'.$class.'"'.$dropdown_multiple.$dropdown_size.'>';
			
			//if(!$dropdown_multiple)
			
			//$output .= '<option value="-1">'.$show_option_none.'</option>';
			
			if($show_option_none){
				if($default == "all"){
					$output .= '<option value="'.$d.'" selected="selected">'.$show_option_none.'</option>';
				}else{
					$output .= '<option value="'.$d.'">'.$show_option_none.'</option>';
				}
			}
			
			if($type == "object"){
				foreach($data as $key => $value):
					$s = '';
					
					if(in_array($value->id,$selected)) $s = ' selected="selected"';					
					//if($value->id == $default ) $s = ' selected="selected"';
					
					$c = (isset($value->counts) and $value->counts > 0) ? " (".$value->counts.")" : '';
					
					$output .= "\n<option value=\"".$value->id."\"{$s}>".$value->label.$c."</option>";
				endforeach;
			}else if($type == "array"){
				foreach($data as $key => $value):
					$s = '';
					if(in_array($key,$selected)) $s = ' selected="selected"';
					//if($key== $default ) $s = ' selected="selected"';
					
					$output .= "\n".'<option value="'.$key.'"'.$s.'>'.$value.'</option>';
				endforeach;
			}else{
				foreach($data as $key => $value):
					$s = '';
					if(in_array($key,$selected)) $s = ' selected="selected"';
					//if($key== $default ) $s = ' selected="selected"';
					$output .= "\n".'<option value="'.$key.'"'.$s.'>'.$value.'</option>';
				endforeach;
			}
						
			$output .= '</select>';
			if($display){
				echo $output;
			}else{
				return  $output;
			}
		
		}
		
		function ExportToCsv($filename = 'export.csv',$rows,$columns,$format="csv"){				
			global $wpdb;
			$csv_terminated = "\n";
			$csv_separator = ",";
			$csv_enclosed = '"';
			$csv_escaped = "\\";
			$fields_cnt = count($columns); 
			$schema_insert = '';
			
			if($format=="xls"){
				$csv_terminated = "\r\n";
				$csv_separator = "\t";
			}
				
			foreach($columns as $key => $value):
				$l = $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $value) . $csv_enclosed;
				$schema_insert .= $l;
				$schema_insert .= $csv_separator;
			endforeach;// end for
		 
		   $out = trim(substr($schema_insert, 0, -1));
		   $out .= $csv_terminated;
			
			//printArray($rows);
			
			for($i =0;$i<count($rows);$i++){
				
				//printArray($rows[$i]);
				$j = 0;
				$schema_insert = '';
				foreach($columns as $key => $value){
						
						
						 if ($rows[$i][$key] == '0' || $rows[$i][$key] != ''){
							if ($csv_enclosed == '')
							{
								$schema_insert .= $rows[$i][$key];
							} else
							{
								$schema_insert .= $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $rows[$i][$key]) . $csv_enclosed;
							}
						 }else{
							$schema_insert .= '';
						 }
						
						
						
						if ($j < $fields_cnt - 1)
						{
							$schema_insert .= $csv_separator;
						}
						$j++;
				}
				$out .= $schema_insert;
				$out .= $csv_terminated;
			}
			
			if($format=="csv"){
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Length: " . strlen($out));	
				header("Content-type: text/x-csv");
				header("Content-type: text/csv");
				header("Content-type: application/csv");
				header("Content-Disposition: attachment; filename=$filename");
			}elseif($format=="xls"){
				
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Length: " . strlen($out));
				header("Content-type: application/octet-stream");
				header("Content-Disposition: attachment; filename=$filename");
				header("Pragma: no-cache");
				header("Expires: 0");
			}
			
			echo $out;
			exit;
		 
		}	
		
		function get_pagination($total_pages = 50,$limit = 10,$adjacents = 3,$targetpage = "admin.php?page=RegisterDetail",$request = array()){		
				
				if(count($request)>0){
					unset($request['p']);
					//$new_request = array_map(create_function('$key, $value', 'return $key."=".$value;'), array_keys($request), array_values($request));
					//$new_request = implode("&",$new_request);
					//$targetpage = $targetpage."&".$new_request;
				}
				
				
				/* Setup vars for query. */
				//$targetpage = "admin.php?page=RegisterDetail"; 	//your file name  (the name of this file)										
				/* Setup page vars for display. */
				if(isset($_REQUEST['p'])){
					$page = $_REQUEST['p'];
					$_GET['p'] = $page;
					$start = ($page - 1) * $limit; 			//first item to display on this page
				}else{
					$page = false;
					$start = 0;	
					$page = 1;
				}
				
				if ($page == 0) $page = 1;					//if no page var is given, default to 1.
				$prev = $page - 1;							//previous page is page - 1
				$next = $page + 1;							//next page is page + 1
				$lastpage = ceil($total_pages/$limit);		//lastpage is = total pages / items per page, rounded up.
				$lpm1 = $lastpage - 1;						//last page minus 1
				
				
				
				$label_previous = __('previous', 'icwcauditloglite');
				$label_next = __('next', 'icwcauditloglite');
				
				/* 
					Now we apply our rules and draw the pagination object. 
					We're actually saving the code to a variable in case we want to draw it more than once.
				*/
				$pagination = "";
				if($lastpage > 1)
				{	
					$pagination .= "<div class=\"pagination\">";
					//previous button
					if ($page > 1) 
						$pagination.= "<a href=\"$targetpage&p=$prev\" data-p=\"$prev\">{$label_previous}</a>\n";
					else
						$pagination.= "<span class=\"disabled\">{$label_previous}</span>\n";	
					
					//pages	
					if ($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
					{	
						for ($counter = 1; $counter <= $lastpage; $counter++)
						{
							if ($counter == $page)
								$pagination.= "<span class=\"current\">$counter</span>\n";
							else
								$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
						}
					}
					elseif($lastpage > 5 + ($adjacents * 2))	//enough pages to hide some
					{
						//close to beginning; only hide later pages
						if($page < 1 + ($adjacents * 2))		
						{
							for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
							{
								if ($counter == $page)
									$pagination.= "<span class=\"current\">$counter</span>\n";
								else
									$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
							}
							$pagination.= "...";
							$pagination.= "<a href=\"$targetpage&p=$lpm1\" data-p=\"$lpm1\">$lpm1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=$lastpage\" data-p=\"$lastpage\">$lastpage</a>\n";		
						}
						//in middle; hide some front and some back
						elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
						{
							$pagination.= "<a href=\"$targetpage&p=1\" data-p=\"1\">1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=2\" data-p=\"2\">2</a>\n";
							$pagination.= "...";
							for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
							{
								if ($counter == $page)
									$pagination.= "<span class=\"current\">$counter</span>\n";
								else
									$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
							}
							$pagination.= "...";
							$pagination.= "<a href=\"$targetpage&p=$lpm1\" data-p=\"$lpm1\">$lpm1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=$lastpage\" data-p=\"$lastpage\">$lastpage</a>\n";		
						}
						//close to end; only hide early pages
						else
						{
							$pagination.= "<a href=\"$targetpage&p=1\" data-p=\"1\">1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=2\" data-p=\"2\">2</a>\n";
							$pagination.= "...";
							for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
							{
								if ($counter == $page)
									$pagination.= "<span class=\"current\">$counter</span>\n";
								else
									$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
							}
						}
					}
					
					//next button
					if ($page < $counter - 1) 
						$pagination.= "<a href=\"$targetpage&p=$next\" data-p=\"$next\">{$label_next}</a>\n";
					else
						$pagination.= "<span class=\"disabled\">{$label_next}</span>\n";
					$pagination.= "</div>\n";		
				}
				return $pagination;
			
		}//End Get Pagination
		
		function GetDataGrid($rows=array(),$columns=array(),$summary=array()){
			global $wpdb;
			$csv_terminated = "\n";
			$csv_separator = ",";
			$csv_enclosed = '"';
			$csv_escaped = "\\";
			$fields_cnt = count($columns); 
			$schema_insert = '';
			
			$th_open = "\n<th class=\"#class#\">";
			$th_close = "</th>";
			
			$td_open = "\n<td class=\"#class#\">";
			$td_close = "</td>";
			
			$tr_open = "\n<tr>";
			$tr_close = "\n</tr>";			
			
			foreach($columns as $key => $value):
				$l = str_replace("#class#",$key,$th_open) . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $value) . $th_close;
				$schema_insert .= $l;				
			endforeach;// end for
			
			//New Change ID 20140918
			$company_name		= $this->get_request('company_name','');
			$report_title		= $this->get_request('report_title','');
			$display_logo		= $this->get_request('display_logo','');
			$display_date		= $this->get_request('display_date','');
			$display_center		= $this->get_request('display_center','');
			$report_name		= $this->get_request('report_name',"details_view");
			$zero				= wc_price(0);
			
			$keywords			= $this->get_request('pdf_keywords','keywords');
			$description		= $this->get_request('pdf_description','description');
			$detail_view 		= $this->get_request('detail_view',"no");
			$total_columns 			= array();			
			$columns2			= array_merge($columns,$total_columns);
			$column_align_style = $this->get_pdf_style_align($columns2,'right','','', $report_name);
			$date_format 		= get_option( 'date_format' );
			
			//New Change ID 20140918
			$out ='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<title>'.$report_title.'</title>
						<meta name="description" content="'.$description.'" />
						<meta name="keywords" content="'.$keywords.'" />
						<meta name="author" content="'.$company_name.'" />
						<style type="text/css"><!--
					.header {position: fixed; top: -40px; text-align:center;}
						  .footer { position: fixed; bottom: 0px; text-align:center;}
						  .pagenum:before { content: counter(page); }
					/*.Container{width:750px; margin:0 auto; border:1px solid black;}*/
					body{font-family: "Source Sans Pro", sans-serif; font-size:10px;}
					span{font-weight:bold;}
					.Clear{clear:both; margin-bottom:10px;}
					/*label{width:100px; float:left; }*/
					table.grid_table{width:100%}
					table {border-collapse: collapse;}
					.sTable3{border:1px solid #DFDFDF; }
					.sTable3 th{
						padding:10px 10px 7px 10px;
						background:#eee url(../images/thead.png) repeat-x top left;
						/*border-bottom:1px solid #DFDFDF;*/
						text-align:left;
						}
					.Form{padding:1% 1% 11% 1%; margin:5px 5px 5px 5px;}
					.myclass{border:1px solid black;}
						
					.sTable3 tbody tr td{padding:8px 10px; background:#fff; border-top:1px solid #DFDFDF; border-right:1px solid #DFDFDF;}
					.sTable3 tbody tr.AltRow td{background:#FBFBFB;}
					.print_header_logo.center_header, .header.center_header{margin:auto;  text-align:center;}					
					'.$column_align_style.'--></style>
					</head>
					<body>';
			
			
			
			$logo_html		=	"";
			
			if(strlen($display_logo) > 0){
				$company_logo	=	$logo_image 			= $this->get_setting('logo_image',$this->constants['plugin_options'], '');
				$upload_dir 	= wp_upload_dir(); // Array of key => value pairs
				$company_logo	= str_replace($upload_dir['baseurl'],$upload_dir['basedir'],$company_logo);
				//$logo_html 		= "<div class='Clear'><img src='".$company_logo."' alt='' /><span>".$company_name."</span></div>";
				$logo_html 		= "<div class='Clear  print_header_logo ".$display_center."'><img src='".$company_logo."' alt='' /></div>";
			}else{
				//$logo_html 		= "<div class='Clear'><span>".$company_name."</span></div>";
			}
			if(strlen($company_name) > 0)	$out .="<div class='header ".$display_center."'><h2>".stripslashes($company_name)."</h2></div>";			
			$out .="<div class='footer'>Page: <span class='pagenum'></span></div>";
			$out .= "<div class='Container1'>";
			$out .= "<div class='Form1'>";
			$out .= $logo_html;
			
			if(strlen($company_name) > 0 || strlen($display_logo) > 0)
			$out .= "<hr class='myclass1'>";
			
			
			
			
			if(strlen($report_title) > 0)
				$out .= "<div class='Clear'><label>Report Title: </label><label>".stripslashes($report_title)."</label></div>";
			
			$out .= "<div class='Clear'></div>";
			if($display_date) $out .= "<div class='Clear'><label>".__( 'Date:', 'icwcauditloglite' )." </label><label>".date($date_format)."</label></div>";
			
			$out .= "<div class='Clear'></div>";
			$out .= "<div class='Clear'>";			
			$out .= "<table class='sTable3 grid_table'>";
			$out .= "<thead>";
			$out .= $tr_open;			
			//$out .= trim(substr($schema_insert, 0, -1));
			$out .= $schema_insert;
			$out .= $tr_close;
			$out .= "</thead>";			
			$out .= "<tbody>";			
			$out .= $csv_terminated;
			
				
			
			$last_order_id = 0;
			$alt_order_id = 0; 
			for($i =0;$i<count($rows);$i++){			
				$j = 0;
				$schema_insert = '';
				foreach($columns as $key => $value){
						 if ($rows[$i][$key] == '0' || $rows[$i][$key] != ''){
							if ($csv_enclosed == '')
							{
								$schema_insert .= $rows[$i][$key];
							} else
							{
								//$schema_insert .= $td_open . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $rows[$i][$key]) . $td_close;
								$schema_insert .= str_replace("#class#",$key,$td_open) . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $rows[$i][$key]) . $td_close;
								
							}
							
						 }else{
							$schema_insert .= $td_open.''.$td_close;;
						 }
						$j++;
				}				
				$out .= $tr_open;
				$out .= $schema_insert;
				$out .= $tr_close;			
			}

			$out .= "</tbody>";
			$out .= "</table>";	
			$out .= "</div>";
			
			if(count($summary)){
				$out .= "<div class=\"print_summary_bottom\">";			
				$out .= "Summary Total:";
				$out .= "</div>";
				$out .= "<div class=\"print_summary_bottom2\">";
				$out .= "<br />";
				
				$detail_view	= $this->get_request('detail_view',"no");
				$zero = wc_price(0);
				//$out .= $this->result_grid($detail_view,$summary,$zero);
				$out .= "</div>";
			}
			$out .= "</div></div></body>";			
			$out .="</html>";	
			//echo $out;exit;
			return  $out;
		 
		}
		
		function export_to_pdf($export_rows = array(),$output){
			if(count($export_rows)>0){
				
				$export_file_name 		= $this->get_request('export_file_name',"no");
				
				$today 					= date_i18n("Y-m-d-H-i-s");
				
				$export_file_format 	= 'pdf';
				
				$report_name 			= $this->get_request('report_name','');	
							
				if(strlen($report_name)> 0){
					$report_name 			= str_replace("_page","_list",$report_name);
					$report_name 			= $report_name."-";
				}
				
				//$file_name 				= $export_file_name."-".$report_name.$today.".".$export_file_format;
				$file_name 				= $export_file_name."-".$report_name.$today;
				
				$file_name 				= str_replace("_","-",$file_name);
				
				$orientation_pdf 		= $this->get_request('orientation_pdf',"portrait");
				
				$paper_size 			= $this->get_request('paper_size',"letter");
				
				
				
				include_once("dompdf/dompdfinit.php");				
				$dompdf->set_paper($paper_size,$orientation_pdf);
				$dompdf->load_html($output);
				$dompdf->render();
				$dompdf->stream($file_name);			
			}
		}
		
		function get_pdf_style_align($columns=array(),$alight='right',$output = '',$prefix = "", $report_name = NULL){
				$output_array 	= array();
				$report_name	= $report_name == NULL ? $this->get_request('report_name','') : $report_name;
				$custom_columns = apply_filters("ic_commerce_pdf_custom_column_right_alignment",array(), $columns,$report_name);
				
				foreach($columns as $key => $value):
					switch ($key) {
						case "order_id":
							$output_array['th'.$key] = "{$prefix} th.{$key}";
							$output_array['td'.$key] = "{$prefix} td.{$key}";
							break;
						default:
							if(isset($custom_columns[$key])){
								$output_array['th'.$key] = "{$prefix} th.{$key}";
								$output_array['td'.$key] = "{$prefix} td.{$key}";
							}
							break;
					}
				endforeach;
				
				if(count($custom_columns)>0){
					foreach($custom_columns as $key => $value):
						$output_array['th'.$key] = "{$prefix} th.{$key}";
						$output_array['td'.$key] = "{$prefix} td.{$key}";
					endforeach;
				}
				
				if(count($output_array)>0){
					$output .= implode(",",$output_array);
					$output .= "{text-align:{$alight};}";					
				}
				
				return $output;
			}
			
			function get_items_id_list($order_items = array(),$field_key = 'order_id', $return_default = '-1' , $return_formate = 'string'){
				$list 	= array();
				$string = $return_default;
				if(count($order_items) > 0){
					foreach ($order_items as $key => $order_item) {
						if(isset($order_item->$field_key)){
							if(!empty($order_item->$field_key))
								$list[] = $order_item->$field_key;
						}
					}
					
					$list = array_unique($list);
					
					if($return_formate == "string"){
						$string = implode(",",$list);
					}else{
						$string = $list;
					}
				}
				return $string;
		}//End Function
		
		function create_log($content = '', $clear = false){
			//$content = "some text here 1 -" .$order_id ;
			
			
			$error_folder  = ABSPATH.'wp-logerror/';			
			$new_line	   = "\n";
			
			if(!isset($this->constants['log_created'])){
				
				if (!file_exists($error_folder)) {
					@mkdir($error_folder, 0777, true);
				}
				
				$this->constants['log_created'] 	= date_i18n("Y-m-d H:i:s");
				$today_date 						= date_i18n("Y-m-d");
				$this->constants['log_file_name'] 	= $error_folder . "/inventory_log-{$today_date}.log";				
				//$new_line	   						= "";
			}
			
			$clear 			= $clear == true ? "w" : "a";			
			$date 			= $this->constants['log_created'];			
			$fp 			= fopen($this->constants['log_file_name'],$clear);
			
			fwrite($fp,"{$new_line}{$date}:\t $content");
			
			fclose($fp);
		}
		
		
		public static function get_postmeta($order_ids = '0', $columns = array(), $extra_meta_keys = array(), $type = 'all'){
			
			global $wpdb;
			
			$post_meta_keys = array();
			
			if(count($columns)>0)
			foreach($columns as $key => $label){
				$post_meta_keys[] = $key;
			}
			
			foreach($extra_meta_keys as $key => $label){
				$post_meta_keys[] = $label;
			}
			
			foreach($post_meta_keys as $key => $label){
				$post_meta_keys[] = "_".$label;
			}
			
			$post_meta_key_string = implode("', '",$post_meta_keys);
			
			$sql = " SELECT * FROM {$wpdb->postmeta} AS postmeta";
			
			$sql .= " WHERE 1*1";
			
			if(strlen($order_ids) >0){
				$sql .= " AND postmeta.post_id IN ($order_ids)";
			}
			
			if(strlen($post_meta_key_string) >0){
				$sql .= " AND postmeta.meta_key IN ('{$post_meta_key_string}')";
			}
			
			if($type == 'total'){
				$sql .= " AND (LENGTH(postmeta.meta_value) > 0 AND postmeta.meta_value > 0)";
			}
			
			$sql .= " ORDER BY postmeta.post_id ASC, postmeta.meta_key ASC";
			
			//echo $sql;return '';
			
			$order_meta_data = $wpdb->get_results($sql);			
			
			if($wpdb->last_error){
				echo $wpdb->last_error;
			}else{
				$order_meta_new = array();	
					
				foreach($order_meta_data as $key => $order_meta){
					
					$meta_value	= $order_meta->meta_value;
					
					$meta_key	= $order_meta->meta_key;
					
					$post_id	= $order_meta->post_id;
					
					$meta_key 	= ltrim($meta_key, "_");
					
					$order_meta_new[$post_id][$meta_key] = $meta_value;
					
				}
			}
			
			return $order_meta_new;
			
		}
		
		function get_product_variations($order_id_string = array()){			
			global $wpdb;
			
			if(is_array($order_id_string)){
				$order_id_string = implode(",",$order_id_string);
			}
				
			$sql = "SELECT meta_key, REPLACE(REPLACE(meta_key, 'attribute_', ''),'pa_','') AS attributes, meta_value, post_id as variation_id
					FROM  {$wpdb->prefix}postmeta as postmeta WHERE 
					meta_key LIKE '%attribute_%'";
			
			if(strlen($order_id_string) > 0){
				$sql .= " AND post_id IN ({$order_id_string})";
				//$sql .= " AND post_id IN (23)";
			}
			
			$order_items 		= $wpdb->get_results($sql);
			
			$product_variation  = array(); 
			if(count($order_items)>0){
				foreach ( $order_items as $key => $order_item ) {
					$variation_label	=	ucfirst($order_item->meta_value);
					$variation_key		=	$order_item->attributes;
					$variation_id		=	$order_item->variation_id;
					$product_variation[$variation_id][$variation_key] =  $variation_label;
				}
			}
			return $product_variation;
		}
		
		function get_products_variation($items = array(), $variation_field = 'variation_id', $product_field = "product_name"){
			
			$variation_ids			= $this->get_items_id_list($items,$variation_field);		
			$product_variations 	= $this->get_product_variations($variation_ids);
			
			if($product_variations != '-1' and count($product_variations) >0){
				foreach ($items as $key => $value){
					
					$variation_id = $items[$key]->{$variation_field};
					
					if($variation_id > 0){
						$product_variation = isset($product_variations[$variation_id]) ? $product_variations[$variation_id] : array();
						
						$product_variation = implode(", ",$product_variation);
						
						$items[$key]->{$product_field}  = $value->{$product_field} ." - ". $product_variation;
						$items[$key]->product_variation  = $product_variation;
					}
					
				}
			}
			
			return $items;
		}
		
		
		function get_page_tabs($page_tab = '', $plugin_options = array()){
			$page 		 		  	= $this->get_request("page");
			$page_tabs 				= array();	
			
			switch($page){
				case "ic-purchase-report":
				case "ic-adjustment-report":
					$page_tabs = array(
							'purchase_report'			=> __('Purchase Report',				'icwcauditloglite')
							,'purchase_report_details'	=> __('Purchase Report Details',		'icwcauditloglite')
							//,'stock_adjustment_report'	=> __('Stock Adjustment Report',	'icwcauditloglite')
					);
					break;
				case "ic-stock-list":
					$page_tabs 	= array(
						'simple_product'					=> __('Simple Product',				'icwcauditloglite')
						,'variable_product'					=> __('Variable Product',			'icwcauditloglite')
						,'stock_as_on_report'				=> __('AS ON Report',				'icwcauditloglite')
					);
					break;
				case "icwcauditloglite":
				case "icwcauditloglite_list_page":
					$page_tabs 	= array(
						'order_audit_log'					=> __('Order Audit Log',					'icwcauditloglite')
					);
					break;
			}
			
			
			return $page_tabs;
		}
		
		function get_current_user_id(){
			$user_id = isset($this->constants['current_user_id']) ? $this->constants['current_user_id'] :  get_current_user_id();
			return $user_id;
		}
			
		function create_hidden_fields($request = array(), $type = "hidden"){
			$output_fields = "";
			//$this->print_array($request);
			foreach($request as $key => $value):
				if(is_array($value)){
					foreach($value as $akey => $avalue):
						if(is_array($avalue)){
							$output_fields .=  "\n<input type=\"{$type}\" name=\"{$key}[{$akey}]\" value=\"".implode(",",$avalue)."\" />";
						}else{
							$output_fields .=  "<input type=\"{$type}\" name=\"{$key}[{$akey}]\" value=\"{$avalue}\" />";
						}
					endforeach;
				}else{
					$output_fields .=  "\n<input type=\"{$type}\" name=\"{$key}\" value=\"{$value}\" />";
				}
			endforeach;
			return $output_fields;
		}
		
		function create_search_form_hidden_fields($request = array(), $type = "hidden"){
			$output_fields = "";
			foreach($request as $key => $value):
				$output_fields .=  "\n<input type=\"{$type}\" name=\"{$key}\" id=\"{$key}\" value=\"{$value}\" />";
			endforeach;
			return $output_fields;
		}
		
		function get_per_page($default = 10){
			
			$screen 	= get_current_screen();
			$user 		= get_current_user_id();
			$option 	= $screen->get_option('per_page', 'option');
			$per_page 	= get_user_meta($user, $option, true);
			$per_page	= empty($per_page) ? $default : $per_page;
			
			return $per_page;
		}
		
		function add_screen_option(){
			
			$option_key = 'per_page_'.$this->constants['admin_page'];						
			$args = array(
				'label' => __('List Per Page'),
				'default' => 10,
				'option' => $option_key
			);
			add_screen_option('per_page', $args );
		}
		
		function set_screen_option($status, $option, $value) {			
			$option_key = 'per_page_'.$this->constants['admin_page'];
			if ($option_key == $option ) return $value;		 
			return $status;
		}
		
		function get_products_dropdown_data(){
			global $wpdb;
			
			$query = "SELECT";
			$query .= " posts.post_parent";	
			$query .= " FROM $wpdb->posts AS posts";
			$query .= " WHERE 1*1";
			$query .= " AND posts.post_type IN ('product_variation')";
			$query .= " AND posts.post_parent NOT IN (0)";	
			$query .= " GROUP BY posts.post_parent";
			$items = $wpdb->get_results($query);
			$post_parent_ids = $this->get_items_id_list($items,'post_parent');
			
			
			$query = "SELECT";
			
			//$query .= " posts.post_title";			
			$query .= " posts.ID AS id";			
			//$query .= ", posts.post_type";			
			//$query .= ", posts.post_parent";
			
			$query .= ", CASE  
						WHEN posts.post_type ='product_variation' THEN parent_products.post_title
						WHEN posts.post_type ='product' THEN posts.post_title 
						ELSE posts.post_title 
					END 									AS 'label'";
			
			$query .= ", CASE  
						WHEN posts.post_type ='product_variation' THEN posts.ID
						WHEN posts.post_type ='product' THEN 0
						ELSE 0
					END 									AS variation_id";
					
		
			
			$query .= " FROM $wpdb->posts AS posts";
			$query .= " LEFT JOIN $wpdb->posts AS parent_products ON parent_products.ID = posts.post_parent ";
			$query .= " WHERE 1*1";
			$query .= " AND posts.post_type IN ('product','product_variation')";
			
			$query .= " AND posts.post_title NOT IN ('Auto Draft')";
			
			$query .= " AND (LENGTH(posts.post_title) > 0 || LENGTH(parent_products.post_title) > 0)";
			
			//$query .= " AND (LENGTH(posts.post_title) > 0 || LENGTH(label) > 0)";
			
			$query .= " AND posts.ID NOT IN ($post_parent_ids)";
			
			//$query .= " AND posts.post_parent NOT IN ($pvpp_not_found)";
			
			$query .= " ORDER BY label ASC";
			$items = $wpdb->get_results($query);
			
			$items = $this->get_products_variation($items,'variation_id','label');
			
			//$this->print_array($items);
			
			return $items;
		}
		
		function get_setting($id, $data, $defalut = NULL){
			if(isset($data[$id]))
				return $data[$id];
			else
				return $defalut;
		}
		
		function ic_get_order_statuses(){
			if(!isset($this->constants['wc_order_statuses'])){
				if(function_exists('wc_get_order_statuses')){
					$order_statuses = wc_get_order_statuses();						
				}else{
					$order_statuses = array();
				}
				
				$order_statuses['trash']	=	__("Trash");
									
				$this->constants['wc_order_statuses'] = $order_statuses;
			}else{
				$order_statuses = $this->constants['wc_order_statuses'];
			}
			return $order_statuses;
		}
		
	}//End Class
}