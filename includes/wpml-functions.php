<?php	
	
	function sf_count_posts($language_code = '', $post_type = 'post', $post_status = 'publish'){
		
		global $sitepress, $wpdb;
		
		$default_language_code = $sitepress->get_default_language();
		$post_type = 'post_' . $post_type;
		

		$slc_param = $sitepress->get_default_language() == $language_code ? "IS NULL" : "= '{$default_language_code}'";
		$query = "SELECT COUNT( {$wpdb->prefix}posts.ID )
						FROM {$wpdb->prefix}posts
						LEFT JOIN {$wpdb->prefix}icl_translations ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}icl_translations.element_id
						WHERE {$wpdb->prefix}icl_translations.language_code = '{$language_code}'
							AND {$wpdb->prefix}icl_translations.source_language_code $slc_param
							AND {$wpdb->prefix}icl_translations.element_type = '{$post_type}'
							AND {$wpdb->prefix}posts.post_status = '$post_status'";
     
		return $wpdb->get_var( $query );
    }

?>