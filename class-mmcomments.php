<?php 

/*
code by VisualData
*/

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

include_once plugin_dir_path(__FILE__). "class-group-pods.php";

if (class_exists('VippMMComments')) {return; }

class VippMMComments extends VippCustomFieldGroupsForPodsFree {

	
	public $header_prefix = 'cio_section_';
	
	public $footer_prefix = 'cio_end_section_';
	
	
	function cio_enable_mm_comments() {
	
		add_filter('comment_text', array($this, 'cio_mmc_display_comment'), 10, 3 );
	
	
		//echo apply_filters( 'comment_text', $comment_text, $comment, $args );
	
	}

	
	function cio_mmc_display_comment ( $comment_text, $comment, $args) {
	
		$id = $comment->comment_ID;
		//cio_showv($comment);
		
		$fields_array = $this-> cio_pods_find_headers_with_fields('comment');

		
		
		if ($fields_array) {
		
			foreach ($fields_array as $section=>$v) {
				
				 $comment_text .= $this-> cio_mmc_display_section ('comment', $section, $v, $id);
		
			}
		
		}
		
		
		return $comment_text;
	
	} 
	
	
		
	//display a group of fields from a given pod type and id. section could be 0 if group is not used.
	function cio_mmc_display_section ($post_type, $section, $v, $id) {
			
		$pod = pods($post_type, $id);
		
		
		
		if (!$pod->exists()) return;
		
		if (!isset($v['fields']) or empty($v['fields'])) return;
		
		/*
		$op = $pod->fields($section);
		
		
		if (false==$this->cio_pods_is_show_section ($section, null, 'pick', $op, $pod, $id ) ) return;
		*/
		
		$html = '<div class="cio-display cio-display-'. str_replace('_', '-', $section) .'">';
	
		foreach($v['fields'] as $field=>$fv) {
			
			//$options = $pod->fields($field);
			$html .= '<div class="cio-display-start-new-row"> </div>';
			
			
			if (!$pod->display($field) ) continue;
			
			if ( 'file'==$fv['type'][0] ) {
			
				if ( 'single'==$fv['file_format_type'][0] ) {
				
				
					if ('images'==$fv['file_type'][0]  ) {
					
						$html .= '<div class="cio-label cio-label-' . str_replace('_', '-', $field) .'">' . $fv['post_title']  . '</div>';
						$html .= '<div class="cio-field cio-field-' . str_replace('_', '-', $field) .'"><a href="' . $pod->display($field) .' "><img class="cio-mmc-image" src="' . pods_image_url($pod->field($field)) . '"> </a></div>';
						
						//$html .= '<div class="cio-desc cio-desc-' . str_replace('_', '-', $field).'">' . $fv['description']    . '</div>';
					
					
					} 
				
				} 
				else { 
					//free version supports displaying single image only. premium version supports multiple files.
					continue;
				}
			
			} 
			
			else {
    		
				$html .= '<div class="cio-label cio-label-' . str_replace('_', '-', $field) .'">' . $fv['post_title']  . '</div>';
				$html .= '<div class="cio-field cio-field-' . str_replace('_', '-', $field) .'">' . $pod->display($field) . '</div>';
						
				//$html .= '<div class="cio-desc cio-desc-' . str_replace('_', '-', $field).'">' . $fv['description']    . '</div>';
						
			}
		
		}
		
		
	
		$html .=  '</div>';
	
		return $html; 
	}
	
		
	// it returns all headers with associated fields
	function cio_pods_find_headers_with_fields ($type, $include_header=false, $include_footer=false) {
			
	
		$user_pod_id = $this->find_post_id_by_slug($type);

		$fields_array = $this->find_children_by_parent_post_id($user_pod_id);

		$section_array = array();
		
			
		if ($fields_array) {
			
			$temp_array = array();
			
			$empty_header = true;
			
			$header = 0;
			
			$section_array[0] = array();
			
			
			//$v is multi dimensional array with get_post_meta
			foreach ($fields_array as $field=>$v) {
				
				
		
				if (stristr($field,$this->header_prefix)) {
					
					//there may be some fields before the first header. store these fields with key 0 for future use.
					
					if ($temp_array) {
					
						$section_array[0] = array();
						$section_array[0]['fields'] = $temp_array;
					}
					
					$empty_header=false;
					
					$header = $field;
					
					$section_array[$header] = array();
					$section_array[$header]['fields'] = array();
					
					if ($include_header) {
						$section_array[$header]['fields'][$field] = $v;
						//the field defining the header is included to show header label and description.
					}
					$section_array[$header]['name'] = $v['post_title'];
					$section_array[$header]['description'] = $v['description'];
					
				 
				
				} else if (stristr($field,$this->footer_prefix)) {
				
					if ($include_footer) {
						$section_array[$header]['fields'][$field] = $v;
						//the field defining the footer is included to show footer label and description.
					}
				
					
				
				} else {
					//this is a normal field
					
					//there may be some fields before the first header. collect these fields with key=0
				
					
				
					$section_array[$header]['fields'][$field] = $v;
				
				}
				
			
			}
	
		}
	
		return $section_array;
	}
	
	
	

}


?>
