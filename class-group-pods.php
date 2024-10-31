<?php 

/*
code by VisualData 
*/

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if (class_exists('VippCustomFieldGroupsForPodsFree')) {return; }



class VippCustomFieldGroupsForPodsFree {

	

	function run() {
	
				
		//hook into admin ui. cio_pods_admin_ui_header_background changes the background of field headers.
		add_action( 'pods_admin_ui_setup_edit_fields', array($this, 'cio_pods_admin_ui_header_background'), 10, 2);
	
		//set header and footer field style in the frontend form
		
		add_action( 'pods_form_pre_fields', array($this, 'cio_pods_form_header_footer_style') );
		
		
		//disable input cells for section headers and footers.
		add_filter('pods_view_output',array($this,'cio_pods_disable_section_input'));
		
		//disable pods_meta_ input cells for section headers and footers.
		add_filter('pods_view_output',array($this,'cio_pods_disable_section_meta_input'));
		
		
	}
	
/**
 * disable input cells of fields used as section headers and footers
 *
 * @param string $output, the html input code generated by pods
 * @return string|void
 * @since 1.0
 */


	function cio_pods_disable_section_input ($output) {
	
		$section_array = array_merge(self::cio_pro_find_all_headers(),self::cio_pro_find_all_footers());
		
		if ($section_array) {
		
			foreach ($section_array as $k=>$v) {
			
				if (stristr($output,'<select name="pods_field_'.$k)) {
				
				
					$output='';
				
				}
			
			}
		
		}
		
		return $output;
		
	
	}
	
	/**
 * disable input cells of fields (pods_meta_) used as section headers and footers in pods meta
 *
 * @param string $output, the html input code generated by pods
 * @return string|void
 * @since 1.0
 */


	function cio_pods_disable_section_meta_input ($output) {
	
		$section_array = array_merge(self::cio_pro_find_all_headers(),self::cio_pro_find_all_footers());
		
		if ($section_array) {
		
			foreach ($section_array as $k=>$v) {
			
				if (stristr($output,'<select name="pods_meta_'.$k)) {
				
				
					$output='';
				
				}
			
			}
		
		}
		
		return $output;
		
	
	}
	
	
	
	
	
/**
 * add css class definition for fields used as section headers and footers.
 * Users may define their own css rules to override this style definition.
 * to do: enqueue the style code to proper registered handle.
 * 
 * @return null. the string css class definition is echoed. 
 * @since 1.0
 */
	function cio_pods_form_header_footer_style () {
	
	
		$style_code = '<style>';	
	
		$header_array = self::cio_pro_find_all_headers();

		
		if ($header_array) {
		
			
			//header name is stored as array key
			foreach ($header_array as $k=>$v) {
				$k=str_replace('_','-',$k);
				$style_code .= ' .pods-form-ui-row-name-' . esc_attr($k) . ' {color:SkyBlue; font-size:120%;} ';
			
			}
		
		}
		
	
		$style_code .= '</style>';
			
		echo $style_code; 
		
		
		//wp_add_inline_style( 'pods-form', $style_code );
	
	
	}
	
	
	/**
	 * Set the background color of header rows in admin panel.  colors are used as visual aid for users to quickly identify header fields.
	 * to do: enqueue the style code to proper registered handle.
	 *
	 * @param array $pod, array containing information about pods field
 	* @param object $obj, pods object. it is not used in this function, reserved for future use.
 	* @return null. the string css class definition enclosed in <style> </style> is echoed.
 	* @since 1.0
 	*/
	
	function cio_pods_admin_ui_header_background ($pod, $obj) {
	
		$section_array = self::cio_pro_find_all_headers($pod['name']);
		
		if ($section_array) {
			$style_code = '<style>';
			
			//field footer name is stored as array key
			foreach ($section_array as $k=>$v) {
				
				$style_code .= ' .pods-field-' . esc_attr($k) . ' {background:SkyBlue !important;} ';
			
			}
			$style_code .= '</style>';
			
			echo $style_code; 
		
		}
	}
	

	
	/**
	 * Find all field names used as field group headers. 
	 * @param string|null $type, name of the custom post name created or extended by pods,  such as user or product. if $type is not specified, this function returns all field names used as group headers.
 	* @return array. the array key contains the field name. the array value contains other information of the field (label, description and menu_order)
 	* @since 1.0
 	*/

	function cio_pro_find_all_headers ($type="") {
	
		//always sanitize variable just in case.
		$type = sanitize_title_for_query($type);
	
	
		if (!empty($type)) {
			//find headers of given type
	
			$user_pod_id = $this->find_post_id_by_slug($type);

			$fields_array = $this->find_children_by_parent_post_id($user_pod_id);

			$section_array = array();
			
			if ($fields_array) {
			
				//$v is multi dimensional array with get_post_meta
				foreach ($fields_array as $field=>$v) {
				
					//header name starts with cio_section_ 
					if (stristr($field,'cio_section_')) {
				
						$section_array[$field] = array(
						'post_title' => $v['post_title'],
						'description' => $v['description'],
						'menu_order' => $v['menu_order'],
			
						);
	
					} 
				
			
				}
	
			}
		}
		else {
		//if the type is not specified, search and return all section headers starting with name cio_section_
	
			global $wpdb;
	
			$table_prefix = $wpdb->get_blog_prefix();
	
			$section_array=array();

			$posts_array = $wpdb->get_results( "
				SELECT  ID, post_name, post_title, post_content, menu_order 
				FROM ". $table_prefix ."posts 
				WHERE post_name LIKE 'cio_section_%' 
				AND post_status='publish' 
				AND post_type='_pods_field'
				
				" );
			 
			if ($posts_array) {
				foreach ($posts_array as $v) {
					
					$section_array[$v->post_name] = array(
						'post_title' => $v->post_title,
						'description' => $v->post_content,
						'menu_order' => $v->menu_order,
			
					);
		
				}
	
			}
	
		
		} 
		
		
		return $section_array;
	}
	
	
	
	/**
	 * Find all field names used as field group footers. 
	 * @param string|null $type, name of the custom post name created or extended by pods.  if $type is not specified, this function returns all field names used as group footers.
 	* @return array. the array key contains the field name. the array value contains other information of the field (label, description and menu_order)
 	* @since 1.0
 	*/
	function cio_pro_find_all_footers ($type="") {
	
		//always sanitize variable just in case.
		$type = sanitize_title_for_query($type);
	
	
		if (!empty($type)) {
			
			$user_pod_id = $this->find_post_id_by_slug($type);

			$fields_array = $this->find_children_by_parent_post_id($user_pod_id);

			$section_array = array();
		
			
			if ($fields_array) {
			
			
			
				//$v is multi dimensional array with get_post_meta
				foreach ($fields_array as $field=>$v) {
				
				
				
					//footer is a field type. 
					if (stristr($field,'cio_end_section_')) {
				
						$section_array[$field] = array(
						'post_title' => $v['post_title'],
						'description' => $v['description'],
						'menu_order' => $v['menu_order'],
			
						);
					} 
				
			
				}
	
			}
		} 
		else {
		//if the type is not specified, search and return all section headers starting with name cio_section_
	
			global $wpdb;
	
			$table_prefix = $wpdb->get_blog_prefix();
	
			$section_array=array();

			$posts_array = $wpdb->get_results( "
				SELECT  ID, post_name, post_title, post_content, menu_order 
				FROM ". $table_prefix ."posts 
				WHERE post_name LIKE 'cio_end_section_%' 
				AND post_status='publish' 
				AND post_type='_pods_field'
				
				" );
			 
			if ($posts_array) {
				foreach ($posts_array as $v) {
					
					$section_array[$v->post_name] = array(
						'post_title' => $v->post_title,
						'description' => $v->post_content,
						'menu_order' => $v->menu_order,
			
					);
		
				}
	
			}
		
		}
	
		return $section_array;
	}
	
	
	
	/**
	 * Find the post ID by given post_name. the slug is sanitized the same way as WP_Query. 
	 * see this link for more info. https://developer.wordpress.org/reference/functions/sanitize_title_for_query/
	 * This function should be slightly faster than get_posts, get_pages or WP_Query as those general purpose functions fetch whole bunch of information not really needed.
	 * It does not require joining meta table either.
	 * @param string $slug, post_name of the post. Note this is not the post_title. It is the name (slug) normally appearing in the url if permalink is enabled.
 	* @return number. the post id, of the post_type _pods_pod. 
 	* @since 1.0
 	*/
	static function find_post_id_by_slug($slug) {
		
				$slug = sanitize_title_for_query($slug);
			
				global $wpdb;
			
				$table_prefix = $wpdb->get_blog_prefix();
		
				$post_id = $wpdb->get_var( "
					SELECT  ID 
					FROM ". $table_prefix ."posts
					WHERE post_name='". $slug . "' 
					AND post_type='_pods_pod'
				
					" );
		
				if ($post_id) {
			
					return $post_id; 
				} else {
			
					return false;
			
				}
			
			}

	/**
	 * Find the children posts by given parent post id. the id is validated as integer
	 * This function should be slightly faster than get_posts, get_pages or WP_Query as those general purpose functions fetch whole bunch of information not really needed.
	 * It does not require joining meta table either.
	 * @param int $id, parent post id. 
 	* @return array.  if children posts are found, post_name (pods fields) are stored in the array key.
 	* @since 1.0
 	*/
	static function find_children_by_parent_post_id($id) {

		$id = intval($id);
	
		global $wpdb;
	
		$table_prefix = $wpdb->get_blog_prefix();
	
		$fields_array=array();

		$posts_array = $wpdb->get_results( "
			SELECT  ID, post_name, post_title, post_content, menu_order 
			FROM ". $table_prefix ."posts 
			WHERE post_parent=" . $id . " 
			AND post_status='publish' 
			AND post_type='_pods_field'
			ORDER BY menu_order ASC

			" );
		//be careful, get_post_meta returns multi dimensional array. 
		if ($posts_array) {
			foreach ($posts_array as $v) {
				$field_post_meta = get_post_meta($v->ID);
				$fields_array[$v->post_name] = array_merge($field_post_meta, array(
					'post_title' => $v->post_title,
					'description' => $v->post_content,
					'menu_order' => $v->menu_order,
			
				));
		
			}
	
		}
	
	
	
		return $fields_array;
	
	}
	

}

?>