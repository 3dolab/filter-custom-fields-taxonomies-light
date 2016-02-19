<?php
	
	if( isset( $_POST['sf'] ) )
		$sf = $_POST['sf'];
?>

<div id="wrap" class="sf-wrap">
	<small><a href="?page=search-filter"><?php _e( 'Search Filter', 'sf' ); ?></a> &raquo;</small>
	<h2><?php _e( 'New search filter', 'sf' ); ?></h2>
	<hr />
	<?php if( !isset( $_POST['sf_step'] ) ): ?>
	<form method="post" class="sf-form">
		<input type="hidden" value="1" name="sf_step" />
		<?php if( isset( $sf ) ): foreach( $sf as $key => $val ):
			if( is_array( $val ) ):
			foreach( $val as $v ):
			?>
			<input type="hidden" name="sf[<?php echo $key;?>][]" value="<?php echo $v; ?>" />			
			<?php
			endforeach;
			else:
			?>
			<input type="hidden" name="sf[<?php echo $key;?>]" value="<?php echo $val; ?>" />
			<?php
			endif;
		endforeach; endif; ?>
		<fieldset>
		<legend><?php _e( 'General Settings' ,'sf' ); ?></legend>
		<section>
			<label for="sf_id"><?php _e( 'ID', 'sf' ); ?>:</label>
			<input id="sf_id" name="sf[name]" value="" />		
		</section>
		<section>
			<label for="sf_name"><?php _e( 'Name', 'sf' ); ?>:</label>
			<input id="sf_name" name="sf[title]" value="" />		
		</section>
		<section>
			<label for="sf_posttype"><?php _e( 'Post Type', 'sf' ); ?>:</label>
			<select id="sf_posttype" name="sf[posttype][]">
				<option></option>
				<?php 				
				$args = array(
					'public'	=> true
				);
				$posttypes = get_post_types( $args, 'objects' );
				foreach( $posttypes as $key => $p ): ?>
				<option value="<?php echo $key; ?>"><?php echo $p->labels->name; ?></option>
				<?php endforeach; ?>
			</select>
		</section>
		<hr />
		<input class="button" type="submit" value="<?php _e( 'Next Step', 'sf' ); ?> &#10148;" />
		</fieldset>
	</form>
	<?php elseif( $_POST['sf_step'] == 1 ): ?>
		<h3><?php _e( 'Taxonomies & Postmetas' ,'sf' ); ?></h3>
		<p><?php _e( 'Please drag the Taxonomies and Postmetas, which you want to use in your search form from the left field to the right one.', 'sf' ); ?></p>
		<?php
			$metas = get_all_postmetas_from_post_type( $sf['posttype'] );
		?>
		<ul class="sf-group1">
			<li><?php _e( 'Taxonomies', 'sf' ); ?>
				<?php $tax = get_all_post_taxonomies( $sf['posttype'] ); ?>
				<ul class="sf-tax-ul">
				<?php foreach( $tax as $key => $t ): ?>
				<li class="sf-drag"><input name="sf[tax][]" value="<?php echo $key; ?>" type="hidden" /><?php echo $t->labels->name; ?> (<?php echo $key; ?>)</li>
				<?php endforeach; ?>
				</ul>
			</li>
			
			<li><?php _e( 'Postmeta', 'sf' ); ?><ul class="sf-meta-ul">
				<?php foreach( $metas as $key => $val ): ?>
				<li class="sf-drag"><input name="sf[meta][]" value="<?php echo $key; ?>" type="hidden" /><?php echo ucfirst( $key ); ?></li>
				<?php endforeach; ?>
			</ul></li>
		</ul>
		
		<form method="post" class="sf-form">
		<input type="hidden" value="2" name="sf_step" />
				<?php if( isset( $sf ) ): foreach( $sf as $key => $val ):
			if( is_array( $val ) ):
			foreach( $val as $v ):
			?>
			<input type="hidden" name="sf[<?php echo $key;?>][]" value="<?php echo $v; ?>" />			
			<?php
			endforeach;
			else:
			?>
			<input type="hidden" name="sf[<?php echo $key;?>]" value="<?php echo $val; ?>" />
			<?php
			endif;
		endforeach; endif; ?>
		<ul class="sf-group2">
		
		</ul>
		<div class="sf-clear"></div>
		<hr />
		
		
		<hr />
		<input class="button" type="submit" value="<?php _e( 'Next Step', 'sf' ); ?> &#10148;" />
	</form>
	<?php elseif( $_POST['sf_step'] == 3 ): ?>	
	<?php
		global $wpdb;
		if( ! wp_verify_nonce( $_POST['sf-wpnonce'], 'save-new-sf' ) )
			wp_die( 'Wrong parameter.' );
		if( !function_exists('is_multisite') || !is_multisite() )
			$file = SF_DIR . 'templates/template-' . $sf['name'] . '.php';
		else
			$file = SF_DIR . 'templates/template-' . $wpdb->blogid . '-' . $sf['name'] . '.php';
		$fp = fopen( $file, 'w' );
		fwrite( $fp, stripslashes( $_POST['template']['result'] ) );
		fclose( $fp );
		
		
		if( !function_exists('is_multisite') || !is_multisite() )
			$file = SF_DIR . 'templates/template-' . $sf['name'] . '-noresult.php';
		else
			$file = SF_DIR . 'templates/template-' . $wpdb->blogid . '-' . $sf['name'] . '-noresult.php';
		$fp = fopen( $file, 'w' );
		fwrite( $fp, stripslashes( $_POST['template']['noresult'] ) );
		fclose( $fp );
	?>
		<h3><?php _e( 'Form Elements' ,'sf' ); ?></h3>
		<p><?php _e( 'Move the form elements, which you want to have in your form, from the right to the left pane. You can edit the elements attributes by clicking on it in the pane "Chosen Form Elements". In this dialog, you can set the necessary attributes.', 'sf' ); ?></p>
		<div style="display:none;">
			<select id="sf-datasource">
				<optgroup label="<?php _e( 'Taxonomies', 'sf' ); ?>">
					<?php foreach( $sf['tax'] as $meta ): ?>
					<option value="tax[<?php echo $meta; ?>]"><?php echo $meta; ?></option>
					<?php endforeach; ?>
				</optgroup><optgroup label="<?php _e( 'Postmetas', 'sf' ); ?>">
				<?php foreach( $sf['meta'] as $meta ): ?>
					<option value="meta[<?php echo $meta; ?>]"><?php echo $meta; ?></option>
				<?php endforeach; ?></optgroup>
			</select>
			
			<div id="sf-orderbysource">
					<?php 
					$i = 0;
					if( is_array( $sf['meta'] ) ):
					foreach( $sf['meta'] as $meta ): ?>
					<?php echo $meta; ?> <?php _e( 'ascending', 'sf' ); ?>:<br /><input class="sf-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="meta[<?php echo $meta; ?>|asc]"> <input class="sf-orderbylabel sf-array" name="orderbylabel[<?php echo $i; ?>]" value="<?php echo $meta; ?> <?php _e( 'ascending', 'sf' ); ?>" /><br />
					<?php echo $meta; ?> <?php _e( 'descending', 'sf' ); ?>:<br /><input class="sf-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="meta[<?php echo $meta; ?>|desc]"> <input class="sf-orderbylabel sf-array" name="orderbylabel[<?php echo $i; ?>]" value="<?php echo $meta; ?> <?php _e( 'descending', 'sf' ); ?>" /><br /><br />
					<?php $i++; endforeach; endif;?>	
					<?php _e( 'Date ascending', 'sf' ); ?>:<br /><input class="sf-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="post[date|asc]"> <input class="sf-orderbylabel sf-array" name="orderbylabel[<?php echo $i++; ?>]" value="<?php _e( 'Date ascending', 'sf' );  ?>" /><br />
					<?php _e( 'Date descending', 'sf' ); ?>:<br /><input class="sf-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="post[date|desc]"> <input class="sf-orderbylabel sf-array" name="orderbylabel[<?php echo $i++; ?>]" value="<?php _e( 'Date descending', 'sf' ); ?>" /><br />
			</div>
			
			<select id="sf-allpostmeta">
				<?php foreach( $sf['meta'] as $meta ): ?>
					<option value="<?php echo $meta; ?>"><?php echo $meta; ?></option>
				<?php endforeach; ?></optgroup>
			</select>
		</div>	

		<form method="post" class="sf-form">
			<?php wp_nonce_field( 'save-new-sf', 'sf-wpnonce', false ); ?>
			<input name="sf_step" value="4" type="hidden" />
					<?php if( isset( $sf ) ): foreach( $sf as $key => $val ):
			if( is_array( $val ) ):
			foreach( $val as $v ):
			?>
			<input type="hidden" name="sf[<?php echo $key;?>][]" value="<?php echo $v; ?>" />			
			<?php
			endforeach;
			else:
			?>
			<input type="hidden" name="sf[<?php echo $key;?>]" value="<?php echo $val; ?>" />
			<?php
			endif;
		endforeach; endif; ?>	
			<div class="field filter">
				<p><strong><?php _e( 'Chosen Form Elements', 'sf' ); ?></strong></p>
		
			</div>
		
			<div class="field elements">
				<p><strong><?php _e( 'All Form Elements', 'sf' ); ?></strong></p>
				<div data-attr='{"type":"fulltext"}'>
					<img src="<?php echo SF_URL ?>res/admin/input-fulltext.png" alt="<?php __( 'Fulltext Search', 'sf' ); ?>" />
					<span><?php _e( 'Fulltext Search', 'sf' ); ?></span>
				</div>
				<div data-attr='{"type":"select"}'>
					<img src="<?php echo SF_URL ?>res/admin/select.png" alt="<?php __( 'Selectbox', 'sf' ); ?>" />
					<span><?php _e( 'Selectbox', 'sf' ); ?></span>
				</div>
				<div data-attr='{"type":"checkbox"}'>
					<img src="<?php echo SF_URL ?>res/admin/checkbox.png" alt="<?php __( 'Checkbox', 'sf' ); ?>" />
					<span><?php _e( 'Checkbox', 'sf' ); ?></span>
				</div>
				<div data-attr='{"type":"radiobox"}'>
					<img src="<?php echo SF_URL ?>res/admin/radiobox.png" alt="<?php __( 'Radiobox', 'sf' ); ?>" />
					<span><?php _e( 'Radiobox', 'sf' ); ?></span>
				</div>
				
			</div>
			<div class="sf-clear"></div>
			<hr />
		<input class="button" type="submit" value="<?php _e( 'Next Step', 'sf' ); ?> &#10148;" />
		</form>
	<?php elseif( $_POST['sf_step'] == 2 ): ?>
	<h3><?php _e( 'Layout' ,'sf' ); ?></h3>
	<form method="post" class="sf-form">
		<?php wp_nonce_field( 'save-new-sf', 'sf-wpnonce', false, true ); ?>	
		<input name="sf_step" value="3" type="hidden" />
				<?php if( isset( $sf ) ): foreach( $sf as $key => $val ):
			if( is_array( $val ) ):
			foreach( $val as $v ):
			?>
			<input type="hidden" name="sf[<?php echo $key;?>][]" value="<?php echo $v; ?>" />			
			<?php
			endforeach;
			else:
			?>
			<input type="hidden" name="sf[<?php echo $key;?>]" value="<?php echo $val; ?>" />
			<?php
			endif;
		endforeach; endif; ?>
		
		
		<fieldset>
			<legend><?php _e( 'Search Result Columns' ,'sf' ); ?></legend>
		<div class="sf-4columns">
			<label>
				<img src="<?php echo SF_URL; ?>/res/admin/layout-li-column1.png" alt="1 Column" />
				<br />
				<input name="sf[columns]" value="1" type="radio"/>
				<?php _e( '1 Column', 'sf' ); ?>
			</label>
		</div>
		<div class="sf-4columns">
			<label>
				<img src="<?php echo SF_URL; ?>/res/admin/layout-li-column2.png" alt="2 Columns" />
				<br />
				<input name="sf[columns]" value="2" type="radio"/>
				<?php _e( '2 Columns', 'sf' ); ?>
			</label>
		</div>
		<div class="sf-4columns">
			<label>
				<img src="<?php echo SF_URL; ?>/res/admin/layout-li-column3.png" alt="3 Columns" />
				<br />
				<input name="sf[columns]" value="3" type="radio"/>
				<?php _e( '3 Columns', 'sf' ); ?>
			</label>
		</div>
		<div class="sf-4columns">
			<label>
				<img src="<?php echo SF_URL; ?>/res/admin/layout-li-column4.png" alt="4 Columns" />
				<br />
				<input name="sf[columns]" value="4" type="radio"/>
				<?php _e( '4 Columns', 'sf' ); ?>
			</label>
		</div>
		</fieldset>		
		<fieldset>
			<legend><?php _e( 'Border & Background', 'sf' ); ?></legend>
			<label for="sfborder"><?php _e( 'Border Color' ); ?>: </label><input id="sfborder" type="text" value="#cacaca" name="sf[border]" class="sf-colorfield" />
			<label for="sfbackground"><?php _e( 'Background Color' ); ?>: </label><input id="sfbackground" type="text" value="#f0f0f0" name="sf[background]" class="sf-colorfield" />
		</fieldset>
		<fieldset class="big">
		<legend><?php _e( 'Single Result Element', 'sf' ); ?></legend>
		<?php 
			$resdir = SF_DIR . 'templates/res/';
			$dir = SF_DIR . 'templates/';
			$files = array();
			$dh = opendir( $dir );
			if (!is_writable( $dir ) ):
				?>
				<div class="error"><? _e( 'The directory ' . $dir . ' is not writeable.' ); ?></div>
				<?php
			endif;
			
			$files = scandir( $resdir );
			unset( $files[0] );
			unset( $files[1] );
		if( count( $files ) > 2 ): ?>
		<p><label for="template"><?php _e( 'Choose a template', 'sf' ); ?>:</label></p>
		<select id="template">
			<?php foreach( $files as $file ): 
				if( preg_match( '^template\-(.*)\.php^', $file ) && !preg_match( '^\-noresult\.php^', $file ) ):
					preg_match_all( '^template\-(.*)\.php^', $file, $match )
					?>
					<option <?php if( $match[1][0] == 'standard' ) echo 'selected="selected"'; ?> value="<?php echo $file; ?>"><?php echo ucfirst( $match[1][0] ); ?></option>
			<?php 
			endif;
			endforeach; ?>
		</select>
		<?php endif; ?>
		<?php
			if( !is_file( SF_DIR . 'templates/template-' . $_POST['sf']['name'] . '.php' ) )
				$file = SF_DIR . 'templates/res/template-standard.php';
			else
				$file = SF_DIR . 'templates/template-' . $_POST['sf']['name'] . '.php';
			if( !is_file( SF_DIR . 'templates/template-' . $_POST['sf']['name'] . '-noresult.php' ) )
				$file_no_result = SF_DIR . 'templates/res/template-standard-noresult.php';
			else
				$file_no_result = SF_DIR . 'templates/template-' . $_POST['sf']['name'] . '-noresult.php';
				
		?>
		<div class="sf-2columns">			
			<?php _e( 'Adjust template for Result Elemet', 'sf' ); ?>
			<textarea class="sf" name="template[result]"><?php echo file_get_contents( $file ); ?></textarea>
			<?php _e( 'Adjust template for No Result Elemet', 'sf' ); ?>
			<textarea class="sf" name="template[noresult]"><?php echo file_get_contents( $file_no_result ); ?></textarea>		
		</div>
		<div class="sf-2columns">
			<strong><?php _e( 'Template Tags', 'sf' ); ?></strong><br />
			<?php _e( 'You can enrich your template with Taxonomies, Postmeta-Values and much more. Here, you see the list of Template Tags you can use:', 'sf' ); ?>
			<table>
				<thead>
					<tr><th><?php _e( 'Name', 'sf' ); ?></th><th><?php _e( 'Displays', 'sf' ); ?></th></tr>
				</thead>
				<tbody>
					<tr><td><code>#the_title#</code></td><td><?php _e( 'Displays the title of the post', 'sf' ); ?></td></tr>
					<tr><td><code>#the_content#</code></td><td><?php _e( 'Displays the content of the post', 'sf' ); ?></td></tr>
					<tr><td><code>#the_excerpt#</code></td><td><?php _e( 'Displays the excerpt of the post', 'sf' ); ?></td></tr>
					<tr><td><code>#the_author#</code></td><td><?php _e( 'Displays the authors name', 'sf' ); ?></td></tr>
					<tr><td><code>#count_comments#</code></td><td><?php _e( 'Displays the number of comments on this post', 'sf' ); ?></td></tr>
					<tr><td><code>#the_permalink#</code></td><td><?php _e( 'Displays the link to the post', 'sf' ); ?></td></tr>
					<tr><td><code>#thumbnail#</code></td><td><?php _e( 'Displays the thumbnail of the post', 'sf' ); ?></td></tr>
					<?php
					if( isset( $sf['tax'] ) && is_array( $sf['tax'] ) ):
						foreach( $sf['tax'] as $tax ):
						?>
						<tr><td><code>#tax_<?php echo $tax; ?>#</code></td><td><?php printf( __( 'Displays the used terms of the taxonomy "%s"', 'sf' ), $tax ); ?></td></tr>					
						<?php
						endforeach;
					?><?php
					endif;
					if( isset( $sf['meta'] ) && is_array( $sf['meta'] ) ):
						foreach( $sf['meta'] as $tax ):
						?>
						<tr><td><code>#meta_<?php echo $tax; ?>#</code></td><td><?php printf( __( 'Displays the value of the Postmeta "%s"', 'sf' ), $tax ); ?></td></tr>					
						<?php
						endforeach;
					endif;
					?>
				</tbody>
				<tfoot>
					<tr><th><?php _e( 'Name', 'sf' ); ?></th><th><?php _e( 'Displays', 'sf' ); ?></th></tr>
				</tfoot>
			</table>
		</div>
		</fieldset>
		<div class="sf-clear"></div>
		<hr />
		<input class="button" type="submit" value="<?php _e( 'Next Step', 'sf' ); ?> &#10148;" />
	</form>	
	<?php elseif( $_POST['sf_step'] == 4 ): 
	if( ! wp_verify_nonce( $_POST['sf-wpnonce'], 'save-new-sf' ) )
			wp_die( 'Wrong parameter.' );
	?>
	<h3><?php _e( 'Saved' ,'sf' ); ?></h3>
		<form class="sf-form">
			<fieldset>
				<legend><?php _e( 'Yeeeeha!', 'sf' ); ?></legend>
				<div class="update-nag">
					<p><?php _e( 'Your detail search is now updated. You can insert this form by using the following shortcode:', 'sf' ); ?></p>
					<input onclick="this.select();" onfocus="this.select();" value='[search-form id="<?php echo $sf['name']; ?>"]' />
				</div>
			</fieldset>
			<fieldset class="big">
				<legend><?php _e( 'What\'s next?', 'sf' ); ?></legend>
				<p><?php printf( __( 'You have created your search from. This form needs to be inserted into a Page. We recommend to <a target="_blank" href="%s" target="_blank">create a new Page</a> and insert this shortcode [search-form id="%s"] into the Editor.', 'sf' ), 'post-new.php?post_type=page', $sf['name'] ); ?><br />
				<?php printf( __( 'If you are not sure, what Shortcodes are, have a look into the <a href="%s" target="_blank">WordPress Documentation</a>.', 'sf' ), 'http://en.support.wordpress.com/shortcodes/' ); ?><br />
				<?php _e( 'Once, you have done this and published the new Page, your visitors can access this page and search your WordPress Database very detailed.', 'sf' ); ?><br />		
				<?php printf( __( 'Thanks a lot for using <a href="%s" target="_blank">Profi Search Form</a>', 'sf' ), 'http://profisearchform.com/' ); ?></p>
			</fieldset>
		</form>
		<?php  
		$fields = get_option( 'sf-fields' );
		$fields[ $sf['name'] ] = $sf;
		update_option( 'sf-fields', $fields ); ?>
	</form>
	<?php endif; ?>
	
</div>