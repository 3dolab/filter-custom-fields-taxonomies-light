<?php
	if( !function_exists( 'is_plugin_active' ) )
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
	if( is_plugin_active( 'types/wpcf.php' ) ):
		add_filter( 'sf_postmeta_serialize', 'sf_types_is_checkbox' );
		add_filter( 'sf_get_postmeta_values', 'sf_types_postmeta_values_of_checkboxes', 10, 2 );
		add_filter( 'sf-filter-args', 'sf_types_check_args_for_checkboxes' );
	endif;
	
	function sf_types_is_checkbox( $return ){
		if( $return['add_this'] )
			return $return;
			
		$meta_key = $return['meta_key'];
		if( !preg_match( '^wpcf^', $meta_key ) )
			return $return;
		
		$types = get_option( 'wpcf-fields' );
		foreach( $types as $type )
			if( isset( $type['type'] ) && isset( $type['meta_key'] ) && $type['type'] == 'checkboxes' && $type['meta_key'] == $meta_key )
				return array( 'add_this' => true, 'meta_key' => $meta_key );
		
		return $return;
	}
	
	function sf_types_postmeta_values_of_checkboxes( $value ){
		global $wpdb;
		$meta_key = $value[0]->meta_key;
		if( !preg_match( '^wpcf^', $meta_key ) )
			return $value;
		
		$types = get_option( 'wpcf-fields' );
		$choices = array();
		foreach( $types as $type ):
			if( isset( $type['data']['options'] ) && isset( $type['type'] ) && isset( $type['meta_key'] ) && $type['type'] == 'checkboxes' && $type['meta_key'] == $meta_key ):
				foreach( $type['data']['options'] as $option ):
					$choices[] = array( 'meta_value' => $option['set_value'], 'meta_key' => $option['title'] );
				endforeach;
			endif;
		endforeach;
		
		$choices = json_encode( $choices );
		$choices = json_decode( $choices );
		if( count( $choices ) > 0 )
			return $choices;
		return $value;
	}
	
	function sf_types_check_args_for_checkboxes( $args ){
		if( !isset( $args['meta_query'] ) )
			return $args;
		
		$acf_fields = array();
		foreach( $args['meta_query'] as $key => $val ):
			$is_checkbox = sf_types_is_checkbox( array( 'add_this' => false, 'meta_key' => $val['key'] ) );
			if( $is_checkbox['add_this'] ):
				$acf_fields[] = $val;
				unset( $args['meta_query'][ $key ] );
			endif;
		endforeach;
		
		$where_meta = array();
		foreach( $acf_fields as $field ):
			if( !is_array( $field['value'] ) ):
				$where_meta[ $field['key'] ][] = 's:' . strlen( $field['value'] ) . ':"' . $field['value'] . '";';
			else:
				foreach( $field['value'] as $fv ):
					$where_meta[ $field['key'] ][] = 's:' . strlen( $fv ) . ':"' . esc_sql( like_escape( $fv ) ) . '";';
				endforeach;
			endif;
		endforeach;
		if( count( $where_meta ) > 0 ):
			add_filter( 'posts_join_paged', 'sf_types_checkbox_filter_join', 10, 2 );
			add_filter( 'posts_where', 'sf_types_checkbox_filter_where', 10, 2 );
			add_filter( 'posts_groupby', 'sf_groupby' );
			$args['sf-typescheckbox-meta'] = $where_meta;
		endif;
		
		return $args;
	}
	
	
	function sf_types_checkbox_filter_join( $join_paged_statement, &$wp_query ){
		global $wpdb;
		$acf = $wp_query->get( 'sf-typescheckbox-meta' );
		if( isset( $acf ) && is_array( $acf ) && count( $acf ) > 0 ):
			foreach( $wp_query->get( 'sf-typescheckbox-meta' ) as $meta => $val ):
				$join_paged_statement .= " LEFT JOIN " . $wpdb->prefix . "postmeta as " . md5( $meta ) . " ON ( " . md5( $meta ) . ".post_id = " . $wpdb->prefix . "posts.ID ) ";
			endforeach;
		endif;
		remove_filter( 'posts_join_paged', 'sf_types_checkbox_filter_join', 10 );
		return $join_paged_statement;
	}
	
	function sf_types_checkbox_filter_where( $sf_where, &$wp_query ){
		global $wpdb;
		$acf = $wp_query->get( 'sf-typescheckbox-meta' );
		$sf_add_where = '';
		if( isset( $acf ) && is_array( $acf ) && count( $acf ) > 0 ):
			$sf_add_where = ' AND (';
			$sf_add_meta_arr = array();
			foreach( $acf as $meta => $search_term_array ):
				foreach( $search_term_array as $search_term ):
					$sf_add_meta_arr[ $meta ][] = ' (' .md5( $meta ) . '.meta_value LIKE \'%' . $search_term . '%\' ) ';
				endforeach;
			endforeach;
			
			foreach( $sf_add_meta_arr as $meta => $val ):
				if( $sf_add_where != ' AND (' )
					$sf_add_where .= ' ) AND ( ';
				$sf_add_meta_single = '';
				foreach( $val as $sql ):
					if( !empty( $sf_add_meta_single ) )
						$sf_add_meta_single .= ' OR ';
					$sf_add_meta_single .= $sql;
				endforeach;
				
				$sf_add_where .= $sf_add_meta_single . ' && ' .md5( $meta ) . '.meta_key = \'' . $meta . '\'';
			endforeach;
			$sf_add_where .= ' ) ';
		endif;
		$sf_where .= $sf_add_where;
		remove_filter( 'posts_where', 'sf_types_checkbox_filter_where', 10 );
		return $sf_where;
	}
?>