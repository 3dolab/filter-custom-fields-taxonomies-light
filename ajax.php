<?php
	add_action('wp_ajax_sf-deleteform', 'sf_ajax_deleteform');
	function sf_ajax_deleteform(){		
		$fields = get_option( 'sf-fields' );
		unset( $fields[ $_POST['id'] ] );
		update_option( 'sf-fields', $fields );
		echo 'OK';
		die();
	}
	
	add_action('wp_ajax_sf-optionsearch', 'sf_ajax_optionsearch');
	function sf_ajax_optionsearch(){
		$data = array();
		$i = 0;
		preg_match_all( '^(.*)\[(.*)\]^', $_POST['val'], $match );
		$data_type = $match[1][0];
		$data_value = $match[2][0];
		if( $data_type == 'meta' ){
			$terms = get_postmeta_values( $data_value );
			if( is_array( $terms ) ):
			foreach( $terms as $term ){
				$data[ $i ]['key'] = $term->meta_value;
				$data[ $i ]['val'] = $term->meta_value;
				$i++;
			}
			endif;
		}elseif( $data_type == 'tax' ){
			$args = array(
						'orderby'       => 'name', 
						'order'         => 'ASC',
						'hide_empty'    => true
					);
			$terms = get_terms( $data_value, $args );
			if( is_array( $terms ) ):
			foreach( $terms as $term ){
				$data[ $i ]['key'] = $term->term_id;
				$data[ $i ]['val'] = $term->name;
				$i++;
			}
			endif;
		}
		
		echo json_encode( $data );
		die();
	}
	
	add_action('wp_ajax_sf-search', 'sf_ajax_search');
	add_action('wp_ajax_nopriv_sf-search', 'sf_ajax_search');
	function sf_ajax_search(){	
		error_reporting( 0 );
		echo json_encode( sf_do_search() );
		die();	
	}
	
	function sf_do_search( $exclude = array() ){
		global $wpdb;
			
		if( !isset( $_POST['data']['page'] ) || $_POST['data']['page'] == 1 )
			$_SESSION['sf'] = $_POST['data'];
		$data['post'] = $_POST['data'];		
		
		if( isset( $_POST['data']['wpml'] ) ):
			global $sitepress;
			$sitepress->switch_lang( $_POST['data']['wpml'], true );
			unset( $_POST['data']['wpml'] );
		endif;
		
		$fulltext = "";
		$fields = get_option( 'sf-fields' );
		$found = false;
		foreach( $fields as $field ):
			if( $field['name'] == $_POST['data']['search-id'] ):
				$found = true;
				break;
			endif;
		endforeach;
		
		if( !$found )
			die( 'Wrong parameter' );
				
		$args = array(
			'post_type'		=> $field['posttype'],
			'post_status'	=> 'publish'
		);

		
		$template_file = SF_DIR . 'templates/template-' . $field['name'];
		if( function_exists('is_multisite') && is_multisite() )
			$template_file = SF_DIR . 'templates/template-' . $wpdb->blogid . '-' . $field['name'] ;
		
		
		if( !is_file( $template_file . '.php' ) )
			$template_file = SF_DIR . 'templates/res/template-standard';
			
		$data_tmp = array();
		foreach( $_POST['data'] as $key => $val ):
			if( $val == '' || empty( $val ) )
				continue;
				
			$key = explode( '|', $key );
			if( !isset( $key[1] ) )
				$data_tmp[ $key[0] ]['val'] = $val;
			if( isset( $key[1] ) )
				$data_tmp[ $key[0] ][ $key[1] ] = $val;
		endforeach;		
		$_POST['data'] = $data_tmp;
		
		$operator = array( 'like' => 'LIKE', 'between' => 'BETWEEN', 'equal' => '=', 'bt' => '>', 'st' => '<', 'bte' => '>=', 'ste' => '<=' );
		foreach( $field['fields'] as $key => $val ):
			if( isset( $val['datasource'] ) && !in_array( $val['type'], array( 'map','fulltext' ) ) ):
				preg_match_all( '^(.*)\[(.*)\]^', $val['datasource'], $match );
				$data_type = $match[1][0];
				$data_value = $match[2][0];
			else:
				$data_type = $val['type'];
				$data_value = $val['type'] ;
			endif;
			
			if( isset( $_POST['data'][ $key ] ) ):
				/**
				Taxonomy Query
				*/
				if( $data_type == 'tax' ):
					if( !isset( $args['tax_query'] ) ):
						$args['tax_query']['relation'] = 'AND';
					endif;
					
					/** Select Field */
					if( $val['type'] == 'select' && $_POST['data'][ $key ]['val']  != "" ):
						$args['tax_query'][] = array( 
							'taxonomy'	=> $data_value, 
							'terms'		=> (int) $_POST['data'][ $key ]['val'] 
						);
					/** Input Field */
					elseif( $val['type'] == 'checkbox' ):
						$operator = 'IN';
						$include_children = true;
						if( isset( $val['include_children'] ) && $val['include_children'] == 0 )
							$include_children = false;
						if( isset( $val['operator'] ) )
							$operator = $val['operator'];
							
						$args['tax_query'][] = array( 
							'taxonomy'	=> $data_value, 
							'terms'		=> $_POST['data'][ $key ]['val'],
							'operator'	=> $operator,
							'include_children' => $include_children
						);
						
					/** Input Field */
					elseif( $val['type'] == 'radiobox' ):						
						$args['tax_query'][] = array( 
							'taxonomy'	=> $data_value, 
							'terms'		=> $_POST['data'][ $key ]['val'] 
						);
						
					endif;
				/**
				Postmeta Query				
				*/					
				elseif( $data_type == 'meta' ):
					if( !isset( $args['meta_query'] ) )
						$args['meta_query'] = array();
					
					/** Select Field */
					if( $val['type'] == 'select' ):
						$args['meta_query'][] = array(
									'key'		=>	$data_value,
									'value'		=>	$_POST['data'][ $key ]['val'],
									'compare'	=>	'='
						);
					elseif( $val['type'] == 'checkbox' ):
						$args['meta_query'][] = array(
									'key'		=> $data_value,
									'value'		=> $_POST['data'][ $key ]['val'],
									'type' 		=> 'CHAR',
									'compare'	=> 'IN'
						);
					elseif( $val['type'] == 'radiobox' ):
						$args['meta_query'][] = array(
									'key'		=> $data_value,
									'value'		=> $_POST['data'][ $key ]['val'],
									'type' 		=> 'CHAR',
									'compare'	=> '='
						);
					endif;
						
				elseif( $val['type'] == 'fulltext' && !empty( $_POST['data'][ $key ]['val'] ) ):
					if( in_array( 'the_title', $val['contents'] ) )
						$args['sf-title'] = $_POST['data'][ $key ]['val'];
					if( in_array( 'the_content', $val['contents'] ) )
						$args['sf-content'] = $_POST['data'][ $key ]['val'];
					if( in_array( 'the_excerpt', $val['contents'] ) )
						$args['sf-excerpt'] = $_POST['data'][ $key ]['val'];
					foreach( $val['contents'] as $v ):
						if( preg_match( '^meta\[(.*)\]^', $v ) ):
							if( !isset( $args['sf-meta'] ) ):
								$args['sf-meta'] = array();
							endif;
							$args['sf-meta'][ $v ] = $_POST['data'][ $key ]['val'];
						endif;
					endforeach;
					add_filter( 'posts_where', 'sf_content_filter', 10, 2 );
					if( isset( $args['sf-meta'] ) )
						add_filter( 'posts_join_paged', 'sf_content_filter_join', 10, 2 );
					
					$fulltext = $_POST['data'][ $key ]['val'] ;
				endif;
			endif;				
		endforeach;
		
		
		if( isset( $_POST['data']['page'] ) )
			$args['paged'] = (int) $_POST['data']['page']['val'];
		
		$data['result'] = array();
		$args = apply_filters( 'sf-filter-args', $args );
		$wpdb->query( 'SET OPTION SQL_BIG_SELECTS = 1' );
		$query = new WP_Query( $args );
		if( isset( $field['debug'] ) && $field['debug'] == 1 ):
			$data['args'] = $args;
			$data['query'] = $query;
		endif;
		remove_filter( 'posts_join_paged', 'sf_content_filter_join' );
		remove_filter( 'posts_where', 'sf_content_filter' );
		if( $query->have_posts() ):
			while( $query->have_posts() ): $query->the_post();
				ob_start();
				require( $template_file . '.php' );
				$template = ob_get_contents();
				ob_end_clean();
				
				$template_single = preg_replace( '^#the_title#^', get_the_title(), $template );
				$template_single = preg_replace( '^#the_excerpt#^', get_the_excerpt(), $template_single );
				$template_single = preg_replace( '^#the_content#^', get_the_content(), $template_single );
				
				$template_single = preg_replace( '^#the_author#^', get_the_author(), $template_single );
				$template_single = preg_replace( '^#the_date#^', get_the_date(), $template_single );
				$template_single = preg_replace( '^#the_permalink#^', get_permalink(), $template_single );
				$template_single = preg_replace( '^#the_id#^', get_the_ID(), $template_single );
				$template_single = preg_replace( '^#count_comments#^', wp_count_comments( get_the_ID() )->approved, $template_single );
				
				if( isset( $field['tax'] ) && is_array( $field['tax'] ) ):
				foreach( $field['tax'] as $t ):
					$terms = get_the_terms( get_the_ID(), $t );
					$termstring = '';
					if( is_array( $terms ) ):
						foreach( $terms as $term ):
							if( $termstring != '' )
								$termstring .= ', ';
							$termname = $term->name;
							$termstring .= $termname;
						endforeach;
					endif;
					$template_single = preg_replace( '^#tax_' . preg_quote( $t ) . '#^', $termstring, $template_single );
				endforeach;
				endif;
				
				if( isset( $field['meta'] ) && is_array( $field['meta'] ) ):
				foreach( $field['meta'] as $m ):
					$meta = get_post_meta( get_the_ID(), $m, true );
					if( is_array( $meta ) )
						continue;

					$template_single = preg_replace( '^#meta_' . preg_quote( $m ) . '#^', $meta, $template_single );					
				endforeach;
				endif;
				
				$image = "";
				if( has_post_thumbnail() ):
					$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumb' );
					$image = '<img src="' . $image[0] . '" width="' . $image[1] . '" height="' . $image[2] . '" alt="' . get_the_title() . '" />';
				endif;				
				$template_single = preg_replace( '^#thumbnail#^', $image, $template_single );
				$data['result'][] = '<li>' . apply_filters( 'sf-results-single-result', $template_single ) . '</li>';
			endwhile;
		endif;
		
		if( count( $data['result'] ) == 0 ):			
			ob_start();
			require( $template_file . '-noresult.php' );
			$template_noresult = ob_get_contents();
			ob_end_clean();
			$data['result'][] = '<li class="sf-noresult">' . apply_filters( 'sf-results-noresult', $template_noresult ) . '</li>';
		endif;
		
		
		
		if( defined( 'ICL_LANGUAGE_CODE' )  ):
			global $sitepress;
			$num_of_posts = sf_count_posts( $sitepress->get_current_language(), $field['posttype'] );
		else:
			$num_of_posts = 0;
			if( is_array( $field['posttype'] ) ):
				foreach( $field['posttype'] as $posttype )
					$num_of_posts += wp_count_posts( $posttype )->publish;
			else:
					$num_of_posts += wp_count_posts( $field['posttype'] )->publish;
			endif;
		endif;
		
		$data['head'] = sprintf( __( '<span class="sf-foundcount">%d results</span> out of <span class="sf-totalcount">%d posts</span>', 'sf' ), $query->found_posts, $num_of_posts );
			
		$data['nav'] = array();
		if( $query->max_num_pages > 1 ):
			$pages_around_result = 4;
			if( !isset( $_POST['data']['page'] ) )
				$paged = 1;
			else
				$paged = (int) $_POST['data']['page']['val'];
			$i = 0;
			
			if( $paged > 1 )
				$data['nav'][]='<li><span class="sf-nav-click sf-nav-left-arrow" data-href="' . ( $paged - 1 ) . '">&laquo;</span></li>';
			while( $i < $query->max_num_pages ){
				$i++;
				if( $i == 1 || ( $i > $paged - $pages_around_result && $i < $paged + $pages_around_result ) || $i == $query->max_num_pages ){
					if( $i != $paged )
						$data['nav'][]='<li><span class="sf-nav-click" data-href="' . ( $i ) . '">' . $i . '</span></li>';
					else
						$data['nav'][]='<li><span class="sf-nav-current">' . $i . '</span></li>';
				} elseif( ( $i == $paged - $pages_around_result || $i == $paged + $pages_around_result )  ){
						$data['nav'][]='<li><span class="sf-nav-three-points">...</span></li>';
				}
			}
			if( $paged < $query->max_num_pages )
				$data['nav'][]='<li><span class="sf-nav-click sf-nav-right-arrow" data-href="' . ( $paged + 1 ) . '">&raquo;</span></li>';		
			
		endif;
		return $data;
	}
?>