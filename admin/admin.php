<?php
	add_action( 'admin_enqueue_scripts', 'sf_adminscripts' );
	function sf_adminscripts( $hook ){
		if( !in_array( $hook, array( 'toplevel_page_search-filter', 'admin_page_search-filter-edit','search-filter_page_search-filter-new' ) ) )
			return;
			
		wp_register_style('sf-admin-css', SF_URL . '/res/admin-style.css' ); 
        wp_enqueue_style('sf-admin-css');
		wp_enqueue_style( 'font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.1/css/font-awesome.css', null, '4.0.1' );
		wp_enqueue_media();
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('jquery-ui-droppable');
		wp_enqueue_script('jquery-ui-accordion');
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker');
		wp_register_script('sf-admin-js', SF_URL . '/res/admin.js' ); 
        wp_enqueue_script('sf-admin-js');
		wp_localize_script( 'sf-admin-js', 'objectL10n', array(
			'datasource' => __( 'Data Source', 'sf' ),
			'value' => __( 'Value', 'sf' ),
			'fieldname' => __( 'Fieldname', 'sf' ),
			'save' => __( 'Save', 'sf' ),
			'cancel' => __( 'Cancel', 'sf' ),
			'fulltext_search' => __( 'Fulltext Search', 'sf' ),
			'delete_element' => __( 'Delete', 'sf' ),
			'selectbox' => __( 'Selectbox', 'sf' ),
			'radiobox' => __( 'Radiobox', 'sf' ),
			'inputfield' => __( 'Inputfield', 'sf' ),
			'checkbox' => __( 'Checkbox', 'sf' ),
			'range' => __( 'Range', 'sf' ),
			'start_range' => __( 'Start Range', 'sf' ),
			'end_range' => __( 'End Range', 'sf' ),
			'unit' => __( 'Unit', 'sf' ),
			'unit_in_front' => __( 'Show unit in front', 'sf' ),
			'operator' => __( 'Operator', 'sf' ),
			'equal' => __( 'Equal', 'sf' ),
			'like' => __( 'Like', 'sf' ),
			'bigger_than' => __( 'Bigger Than', 'sf' ),
			'smaller_than' => __( 'Smaller Than', 'sf' ),
			'bigger_than_or_equal' => __( 'Equal or Bigger Than', 'sf' ),
			'smaller_than_or_equal' => __( 'Equal or Smaller Than', 'sf' ),
			'options' => __( 'Options', 'sf' ),
			'automatic' => __( 'Automatic', 'sf' ),
			'individual' => __( 'Individual', 'sf' ),
			'enter_option' => __( 'Enter Option', 'sf' ),
			'enter_option_value' => __( 'Enter Option Value', 'sf' ),
			'enter_option_key' => __( 'Enter Option Key', 'sf' ),
			'add_option' => __( 'Add Option', 'sf' ),
			'please_enter_an_option_key' => __( 'Please enter an Option Key', 'sf' ),
			'please_enter_an_option_value' => __( 'Please enter an Option Value', 'sf' ),
			'delete_option' => __( 'Remove Option', 'sf' ),
			'search_contents' => __( 'Search Content', 'sf' ),
			'the_title' => __( 'The Title', 'sf' ),
			'the_content' => __( 'The Content', 'sf' ),
			'the_excerpt' => __( 'The Excerpt', 'sf' ),
			'orderby' => __( 'Order By', 'sf' ),
			'map' => __( 'Map', 'sf' ),
			'latitude' => __( 'Latitude', 'sf' ),
			'longitude' => __( 'Longitude', 'sf' ),
			'apikey' => __( 'API Key', 'sf' ),
			'map_admin_text' => __( 'Please select the Postmeta(s), in which the Latitude and Longitude are saved.', 'sf' ),
			'map_center_lat' => __( 'Center at Latitude', 'sf' ),
			'map_center_lon' => __( 'Center at Longitude', 'sf' ),
			'map_zoom' => __( 'Zoom', 'sf' ),
			'map_zoom_placeholder' => __( 'Choose between 1 and 18', 'sf' ),
			'map_style' => __( 'Map style', 'sf' ),
			'ROADMAP' => __( 'Roadmap', 'sf' ),
			'SATELLITE' => __( 'Satellite', 'sf' ),
			'HYBRID' => __( 'Hybrid', 'sf' ),
			'TERRAIN' => __( 'Terrain', 'sf' ),
			'map_postmeta_options' => __( 'Postmeta Options', 'sf' ),
			'map_map_options' => __( 'Map Options', 'sf' ),
			'show_field_when'	=>	__( 'Show field when', 'sf' ),
			'show_always'	=>	__( 'Show always', 'sf' ),
			'really_delete'	=>	__( 'Do you want to delete this form?', 'sf' ),
			'item_delete'	=>	__( 'Yes, delete form', 'sf' ),
			'cancel'	=>	__( 'Cancel', 'sf' ),
			'update_option'	=>	__( 'Update Option', 'sf' ),
			'hidden_field'	=>	__( 'Hidden Field', 'sf' ),
			'hierarchical'	=>	__( 'Hierarchical order', 'sf' ),
			'symbol_to_indent'	=>	__( 'Symbol to indent', 'sf' ),
			'step'	=>	__( 'Step', 'sf' ),
			'others'	=>	__( 'Others', 'sf' ),
			'author'	=>	__( 'Author', 'sf' ),
			'datebox'	=>	__( 'Date published', 'sf' ),
			'type'	=>	__( 'Type', 'sf' ),
			'from'	=>	__( 'From', 'sf' ),
			'till'	=>	__( 'Till', 'sf' ),
			'between'	=>	__( 'Between', 'sf' ),
			'source'	=>	__( 'Source', 'sf' ),
			'published'	=>	__( 'Date published', 'sf' ),
			'modified'	=>	__( 'Date Modified', 'sf' ),
			'style'	=>	__( 'Style', 'sf' ),
			'dateformat'	=>	__( 'Dateformat', 'sf' ),
			'include_children'	=>	__( 'Include Children', 'sf' ),
			'operator'	=>	__('Operator', 'sf' ),
			'yes'	=>	__('Yes', 'sf' ),
			'no'	=>	__('No', 'sf' ),
			'txt_in'	=>	__('IN', 'sf' ),
			'not_in'	=>	__('NOT IN', 'sf' ),
			'and'	=>	__('AND', 'sf' ),
			'term_operations'	=>	__('Term operations', 'sf' )
		) );
		
        wp_dequeue_script('sf-script');
	}
	add_action( 'admin_menu', 'wp_sf_adminpage' );
	function wp_sf_adminpage() {
		add_menu_page( __( 'Search Filter', 'sf' ), 'Search Filter', 'edit_posts', 'search-filter', 'sf_admin_output_index', SF_URL . 'res/admin/search-filter-icon.png' );		
		add_submenu_page( 'search-filter', __( 'New Filter', 'sf' ), __( 'New Filter', 'sf' ), 'edit_posts', 'search-filter-new', 'sf_admin_output_new' );
		add_submenu_page( null, __( 'Edit Filter', 'sf' ), __( 'Edit Filter', 'sf' ), 'edit_posts', 'search-filter-edit', 'sf_admin_output_edit' );
	}
	
	function sf_admin_output_index(){
		require_once( dirname( __FILE__ )  . "/index.php");
	}
	
	function sf_admin_output_new(){
		require_once( dirname( __FILE__ )  . "/new.php");	
	}
	
	function sf_admin_output_edit(){
		require_once( dirname( __FILE__ )  . "/edit.php");	
	}
	
	
	
	

	
?>