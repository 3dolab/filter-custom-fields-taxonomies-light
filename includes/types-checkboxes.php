<?php
	if( !function_exists( 'is_plugin_active' ) )
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
	if( is_plugin_active( 'types/wpcf.php' ) ):
		add_filter( 'sf_postmeta_serialize', 'sf_types_is_checkbox' );
		add_filter( 'sf_get_postmeta_values', 'sf_types_postmeta_values_of_checkboxes', 10, 2 );
		add_filter( 'sf-filter-args', 'sf_types_check_args_for_checkboxes' );
		add_filter( 'sf_post_meta_key_values', 'sf_types_checkboxes_get_post_meta_value', 10, 4 );
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
					$choices[] = array( 'meta_value' => $option['title'], 'meta_key' => $option['set_value'] );
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
	
	/*
	Filtering meta values for the Types plugin wpcf checkboxes
	*/
	function sf_types_checkboxes_get_post_meta_value( $return, $object_id, $meta_key, $single=true ){
		
		if( !preg_match( '^wpcf^', $meta_key ) )
			return null;
		//if ( !_wpcf_is_checkboxes_field( $meta_key ) )
		$keycheckbox = array( 'add_this' => false, 'meta_key' => $m );
		$checkbox = sf_types_is_checkbox( $keycheckbox );
		if( $checkbox['add_this'] )
			return null;
		
		$meta_cache = wp_cache_get($object_id, 'post_meta');

		if ( !$meta_cache ) {
			$meta_cache = update_meta_cache( $meta_type, array( $object_id ) );
			$meta_cache = $meta_cache[$object_id];
		}
		if ( ! $meta_key ) {
			$return = $meta_cache;
		}elseif( isset($meta_cache[$meta_key]) ) {
			if ( $single )
				$return = maybe_unserialize( $meta_cache[$meta_key][0] );
			else
				$return = array_map('maybe_unserialize', $meta_cache[$meta_key]);
		}elseif ($single)
			$return = '';
		else
			$return = array();

		if(is_array($return) && !empty($return)) {
			$types = get_option( 'wpcf-fields' );
			$choices = array();
			//echo '<pre>'; print_r($types); echo '</pre>';
			foreach( $types as $type ){
				if( isset( $type['data']['options'] ) && isset( $type['meta_key'] ) && $type['meta_key'] == $meta_key ){
					foreach( $type['data']['options'] as $index => $option ){
						if(isset($return[$index]) && !empty($return[$index]))
							if( isset( $type['type'] )&& $type['type'] == 'checkboxes' )
								if(isset($option['set_value']) && in_array($option['set_value'], $return[$index]) && !in_array($option['set_value'], array_keys($choices)))
									$choices[$option['set_value']] = $option['title'];
							elseif( isset( $type['type'] )&& $type['type'] == 'select' )
								if(isset($option['value']) && in_array($option['value'], $return[$index]) && !in_array($option['value'], array_keys($choices)))							
									$choices[$option['value']] = $option['title'];
					}
				}
			}
			if( count( $choices ) > 0 ){
				sort($choices);
				$choices = array_unique(array_filter($choices));
				//print_r($choices);
				if ($single)
					return maybe_serialize($choices);
					//return '<ul><li>'.implode('</li><li>', $choices).'</li></ul>';
				else
					return $choices;
			}
		}
		return $return;
	}
	
	/*
	returning empty args would make default plugin post search results empty
	default args are filtered first because default tax_query select values were converted to int (no array of multiples was possible),
	then more heavily after using taxonomy names and term slugs instead of field indexes and ids as parameters (field names => json url)
	for nicer "talking" URLs
	*/
	function bypass_default_sf_query($args){
		
		//$args['posts_per_page'] = '-1';
		//$args = array('numberposts' => 0);
		$args['orderby'] = 'post_name';		
		$args['order'] = 'ASC';
		
		$taxargs = array(
		  'public'   => true,
		  '_builtin' => false
		);
		if(isset($args['post_type']))
			$taxargs['object_type'] = $args['post_type'];
		$active_taxonomies = get_taxonomies( $taxargs, 'names', 'and' );
		
		if ( $active_taxonomies && isset($active_taxonomies[$field['name']]) )
			return null;
		else{
			$fields = get_option( 'sf-fields' );
			$found = false;

			foreach( $fields as $field ){
				if( $field['name'] == $_POST['data']['search-id'] || in_array($field['name'], $_POST['data']['search-id']) ){
					$found = true;
					break;
				}
			}
			if( !$found )
				return $args;
			
			if(isset($args['tax_query']) && !empty($args['tax_query'])){
				foreach ($args['tax_query'] as $i => $tax_query){
					if($i === 'relation')
						continue;
					if(in_array($tax_query['terms']) && $tax_query['terms'] === 1)
						unset($args['tax_query'][$i]);
					//need to remove and recreate tax query since js changed select multiple val to int
					if(!is_array($tax_query['terms']) && $tax_query['terms'] === 1)
						unset($args['tax_query'][$i]);
				}
			} else 
				$args['tax_query'] = array();
			
			$args = sf_tax_query_meta_query_fields ( $args, $field );
			
			return $args;
		}
	}
		
	/*
	applying taxonomy and postmeta args to search and overview form queries
	*/
	function sf_tax_query_meta_query_fields ( $args, $field ) {
		$enabled_fields = $field['fields'];
		if(!empty($enabled_fields)){
			foreach($enabled_fields as $i => $enabled_field){
				if( isset( $enabled_field['datasource'] ) && !in_array( $enabled_field['type'], array( 'map','fulltext' ) ) ){
					preg_match_all( '^(.*)\[(.*)\]^', $enabled_field['datasource'], $match );
					$data_type = $match[1][0];
					$data_value = $match[2][0];
				} else {
					$data_type = $enabled_field['type'];
					$data_value = $enabled_field['type'] ;
				}
				if(preg_match( '^wpcf^', $data_value ) && $data_type == 'meta'){
					$data_value = substr( $data_value, 5 );
					$wpcf = 'wpcf-';
				}
				if( isset( $_POST['data'][ $data_value ] ) ){
					if( $data_type == 'tax' ){
						if( !isset( $args['tax_query'] ) ){
							$args['tax_query']['relation'] = 'AND';
						}
						if(!is_array($_POST['data'][$data_value]['val']))
							$postdata_terms = explode(',',$_POST['data'][$data_value]['val']);
						else
							$postdata_terms = $_POST['data'][$data_value]['val'];
						if( $enabled_field['type'] == 'select' && $_POST['data'][ $data_value ]['val']  != "" ){								
							$args['tax_query'][] = array( 
								'taxonomy'	=> $data_value, 
								'terms'		=> $postdata_terms,
								'field'		=> 'slug'
								//'field'		=> 'term_id'
							);
						} elseif( $enabled_field['type'] == 'checkbox' ){
							$operator = 'IN';
							$include_children = true;
							if( isset( $enabled_field['include_children'] ) && $enabled_field['include_children'] == 0 )
								$include_children = false;
							if( isset( $enabled_field['operator'] ) )
								$operator = $enabled_field['operator'];
								
							$args['tax_query'][] = array( 
								'taxonomy'	=> $data_value, 
								'terms'		=> $postdata_terms,
								'field'		=> 'slug',
								'operator'	=> $operator,
								'include_children' => $include_children
							);
						} elseif( $enabled_field['type'] == 'radiobox' ){						
							$args['tax_query'][] = array( 
								'taxonomy'	=> $data_value, 
								'terms'		=> $postdata_terms,
								'field'		=> 'slug'
							);
							
						}				
					} elseif( $data_type == 'meta' ){
						if( !isset( $args['meta_query'] ) )
							$args['meta_query'] = array();						
						if(!is_array($_POST['data'][$data_value]['val']))
							$postdata_metas = explode(',',$_POST['data'][$data_value]['val']);
						else
							$postdata_metas = $_POST['data'][$data_value]['val'];
						if( $enabled_field['type'] == 'select' ){
							$args['meta_query'][] = array(
										'key'		=>	$wpcf.$data_value,
										'value'		=>	$postdata_metas,
										'type' 		=> 'CHAR',
										'compare'	=> 'IN',
										//'compare'	=>	'='
							);
						}elseif( $enabled_field['type'] == 'checkbox' ){
							$args['meta_query'][] = array(
										'key'		=> $wpcf.$data_value,
										'value'		=> $postdata_metas,
										'type' 		=> 'CHAR',
										'compare'	=> 'IN'
							);
						}elseif( $enabled_field['type'] == 'radiobox' ){
							$args['meta_query'][] = array(
										'key'		=> $wpcf.$data_value,
										'value'		=> $postdata_metas,
										'type' 		=> 'CHAR',
										'compare'	=> '='
							);
						}
					}
					$args = sf_wpcf_views_query_set_value( $args, true );
				}
			}
		}
		return $args;
	}
	
	/*
	Applying actual meta keys and values to query for the Types plugin wpcf postmetas
	*/
	function sf_wpcf_views_query_set_value( $query, $view_settings ) {

		$meta_filter_required = false;

		$opt = get_option( 'wpcf-fields' );

		if ( isset( $query['meta_query'] ) ) {
			foreach ( $query['meta_query'] as $index => $meta ) {
				if ( is_array( $meta ) && isset( $meta['key'] ) ) {
					$field_name = $meta['key'];
					if ( _wpcf_is_checkboxes_field( $field_name ) ) {

						$orginal = $query['meta_query'][$index];

						unset($query['meta_query'][$index]);

						// We'll use SQL regexp to find the checked items.
						// Note that we are creating something here that
						// then gets modified to a proper SQL REGEXP in
						// the get_meta_sql filter.

						$field_name = substr( $field_name, 5 );

						$meta_filter_required = true;

						/* According to http://codex.wordpress.org/Class_Reference/WP_Meta_Query#Accepted_Arguments,
						 * $meta['value'] can be an array or a string. In case of a string we additionally allow
						 * multiple comma-separated values. */
						if( is_array( $meta['value'] ) ) {
							$values = $meta['value'];
						} elseif( is_string( $meta['value'] ) ) {
							$values = explode( ',', $meta['value'] );
						} else {
							// This can happen if $meta['value'] is a number, for example.
							$values = array( $meta['value'] );
						}
						$options = $opt[$field_name]['data']['options'];

						global $wp_version;

						if ( version_compare( $wp_version, '4.1', '<' ) ) {
							// We can not use nested meta_query entries
							foreach ( $values as $value ) {
								foreach ( $options as $key => $option ) {
									if ( $option['title'] == $value || $option['set_value'] == $value ) {
										$query['meta_query'][] = array(
											'key' => $meta['key'],
											'compare' => in_array( $orginal['compare'], array( '!=', 'NOT LIKE', 'NOT IN' ) ) ? 'NOT LIKE' : 'LIKE',
											'value' => $key,
											'type' => 'CHAR',
										);
										break;
									}
								}
							}
						} else {
							// We can use nested meta_query entries
							if ( count( $values ) < 2 ) {
								// Only one value to filter by, so no need to add nested meta_query entries
								foreach ( $values as $value ) {
									foreach ( $options as $key => $option ) {
										if ( $option['title'] == $value || $option['set_value'] == $value ) {
											$query['meta_query'][] = array(
												'key' => $meta['key'],
												'compare' => in_array( $orginal['compare'], array( '!=', 'NOT LIKE', 'NOT IN' ) ) ? 'NOT LIKE' : 'LIKE',
												'value' => $key,
												'type' => 'CHAR',
											);
											break;
										}
									}
								}
							} else {
								// We will translate each value into a meta_query clause and add them all as a nested meta_query entry
								$inner_relation = in_array( $orginal['compare'], array( '!=', 'NOT LIKE', 'NOT IN' ) ) ? 'AND' : 'OR';
								$inner_compare = in_array( $orginal['compare'], array( '!=', 'NOT LIKE', 'NOT IN' ) ) ? 'NOT LIKE' : 'LIKE';
								$inner_meta_query = array(
									'relation' => $inner_relation
								);
								foreach ( $values as $value ) {
									foreach ( $options as $key => $option ) {
										if ( $option['title'] == $value || $option['set_value'] == $value ) {
											$inner_meta_query[] = array(
												'key' => $meta['key'],
												'compare' => $inner_compare,
												'value' => $key,
												'type' => 'CHAR',
											);
											break;
										}
									}
								}
								$query['meta_query'][] = $inner_meta_query;
							}
						}
					}
				}
			}
		}
		if ( $meta_filter_required ) {
			add_filter( 'get_meta_sql', 'wpcf_views_get_meta_sql', 10, 6 );
		}
		return $query;
	}
	
	function sf_types_postmeta_values_of_select( $value ){
		global $wpdb;

		$meta_key = $value[0]->meta_key;
		if( !preg_match( '^wpcf^', $meta_key ) )
			return $value;
		
		$types = get_option( 'wpcf-fields' );
		$choices = array();
		foreach( $types as $type ){
			if( isset( $type['data']['options'] )&& isset( $type['type'] ) && $type['type'] == 'select' && isset( $type['meta_key'] ) && $type['meta_key'] == $meta_key ){
				foreach( $type['data']['options'] as $option ){
					if(isset($option['value']))
						$choices[] = array( 'meta_value' => $option['title'], 'meta_key' => $option['value'] );
				}
			}
		}
		//print_r($choices);
		$choices = json_encode( $choices );
		$choices = json_decode( $choices );

		if( count( $choices ) > 0 )
			return $choices;
		
		return $value;
	}
?>