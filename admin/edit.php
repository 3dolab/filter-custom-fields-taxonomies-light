<?php
	global $wpdb;
	$update = false;
	
	
	$fields = get_option( 'sf-fields' );
	
	/**
		Import
	*/
	if( isset( $_POST['import'] ) ):
		if( ! wp_verify_nonce( $_POST['sf-wpnonce'], 'update-sf' ) )
			wp_die( 'Wrong parameter.' );
		$_POST['import'] = stripslashes( $_POST['import'] );
		$field = unserialize( $_POST['import'] );
		echo $field['name'];
		echo '<pre>';print_r( $field );echo '</pre>';
		$fields[ $field['name'] ] = $field;
		update_option( 'sf-fields', $fields ); 
	endif;
	
	
	foreach( $fields as $field )
		if( $field['name'] == $_GET["ID"] )
			break;
	
	if( isset( $_POST['sf'] ) ):
		if( ! wp_verify_nonce( $_POST['sf-wpnonce'], 'update-sf' ) )
			wp_die( 'Wrong parameter.' );
		$sf = $_POST['sf'];
		
		if( isset( $_POST['sf_step'] ) && $_POST['sf_step'] == 1 ):
			foreach( $sf as $key => $val ):
				$field[ $key ] = $val;
			endforeach;
		endif;
		if( isset( $_POST['sf_step'] ) && $_POST['sf_step'] == 2 ):
			$field['tax'] = $sf['tax'];
			$field['meta'] = $sf['meta'];
		endif;
		if( isset( $_POST['sf_step'] ) && $_POST['sf_step'] == 3 ):
			
			foreach( $sf as $key => $val ):
				$field[ $key ] = $val;
			endforeach;
			
			if( !function_exists('is_multisite') || !is_multisite() )
				$file = SF_DIR . 'templates/template-' . $field['name'] . '.php';
			else
				$file = SF_DIR . 'templates/template-' . $wpdb->blogid . '-' . $field['name'] . '.php';
			$fp = fopen( $file, 'w' );
			fwrite( $fp, stripslashes( $_POST['template']['result'] ) );
			fclose( $fp );
		
			if( !function_exists('is_multisite') || !is_multisite() )
				$file = SF_DIR . 'templates/template-' . $field['name'] . '-noresult.php';
			else
				$file = SF_DIR . 'templates/template-' . $wpdb->blogid . '-' . $field['name'] . '-noresult.php';
			$fp = fopen( $file, 'w' );
			fwrite( $fp, stripslashes( $_POST['template']['noresult'] ) );
			fclose( $fp );			
		endif;
		
		
		if( isset( $_POST['sf_step'] ) && $_POST['sf_step'] == 4 ):
			
			foreach( $sf as $key => $val ):
				$field[ $key ] = $val;
			endforeach;
		endif;
		
		$fields[ $field['name'] ] = $field;
		update_option( 'sf-fields', $fields ); 
		$update = true;
	endif;
?>
<script>var sf_tab_index = <?php if( isset( $_POST['sf_step'] ) ): echo ( $_POST['sf_step'] - 1 ); else: echo '0'; endif; ?>;</script>
<div id="wrap" class="sf-wrap">
	<p><a href="?page=search-filter"><?php _e( 'Search Filter', 'sf' ); ?></a> &raquo;</p>
	<?php if( isset( $field['title'] ) && trim( $field['title'] ) ) $title = $field['title']; else $title = $field['name']; ?>
	<h2><?php printf( __( 'Edit "%s"', 'sf' ),  $title ); ?></h2>
	<?php if( $update ): ?>
	<div class="updated below-h2"><p><?php _e( 'Filter updated.', 'sf' ); ?></p></div>
	<?php endif; ?>
	<div class="sf-tabs">
		<ul>
			<li><a href="#general-settings"><?php _e( 'General Settings', 'sf' ); ?></a></li>
			<li><a href="#taxonomies-postmeta"><?php _e( 'Taxonomies & Postmetas' ,'sf' ); ?></a></li>
			<li><a href="#layout"><?php _e( 'Layout' ,'sf' ); ?></a></li>
			<li><a href="#form-elements"><?php _e( 'Form Elements' ,'sf' ); ?></a></li>
			<li><a href="#import-export"><?php _e( 'Import & Export', 'sf' ); ?></a></li>
		</ul>
		
		<!-- 1. Tab: General Settings -->
		<div id="general-settings">
			<form method="post" class="sf-form"><?php wp_nonce_field( 'update-sf', 'sf-wpnonce', false ); ?>
				<input type="hidden" value="1" name="sf_step" />				

				<fieldset>
					<legend><?php _e( 'General Settings' ,'sf' ); ?></legend>
					<section>
						<label for="sf_id"><?php _e( 'ID', 'sf' ); ?>:</label>
						<input id="sf_id" readonly name="sf[name]" value="<?php echo $field['name']; ?>" />		
					</section>
					<section>
						<label for="sf_name"><?php _e( 'Name', 'sf' ); ?>:</label>
						<input id="sf_name" name="sf[title]" value="<?php if( isset( $field['title'] ) && trim( $field['title'] ) != '' ) echo $field['title']; else echo $field['name']; ?>" />		
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
								<option <?php if( ( is_array( $field['posttype'] ) && in_array( $key, $field['posttype'] ) ) || ( !is_array( $field['posttype'] ) && $key == $field['posttype'] ) ) echo 'selected="selected"'; ?> value="<?php echo $key; ?>"><?php echo $p->labels->name; ?></option>				
							<?php endforeach; ?>
						</select>
					</section>
					<section>
						<label for="sf_debug"><?php _e( 'Debug Mode', 'sf' ); ?>:</label>
						<select id="sf_debug" name="sf[debug]">
							<option <?php if( !isset( $field['debug'] ) || 0 == $field['debug'] ) echo 'selected="selected"'; ?> value="0"><?php _e( 'Off', 'sf' ); ?></option>
							<option <?php if( isset( $field['debug'] ) && 1 == $field['debug'] ) echo 'selected="selected"'; ?> value="1"><?php _e( 'On', 'sf' ); ?></option>
						</select>
						
						<small><?php _e( 'Turn this mode on, in order to get additional data on the WP_Query like the args or the SQL statement. Please turn it off in live mode', 'sf' ); ?></small>
					</section>
					<hr />
					<input class="button" type="submit" value="<?php _e( 'Update', 'sf' ); ?>" />
				</fieldset>
			</form>
		</div>
		
		<!-- 2. Tab: Taxonomies and Postmeta -->
		<div id="taxonomies-postmeta">
			<h3><?php _e( 'Taxonomies & Postmetas' ,'sf' ); ?></h3>
			<p><?php _e( 'Please drag the Taxonomies and Postmetas, which you want to use in your search form from the left field to the right one.', 'sf' ); ?></p>
			<?php
				$metas = get_all_postmetas_from_post_type( $field['posttype'] );
			?>
			<ul class="sf-group1">
				<li><?php _e( 'Taxonomies', 'sf' ); ?>
					<?php $tax = get_all_post_taxonomies( $field['posttype'] ); ?>
					<ul class="sf-tax-ul">
						<?php foreach( $tax as $key => $t ): 
						if( !isset( $field['tax'] ) || ( is_array( $field['tax'] ) && !in_array( $key, $field['tax'] ) ) ): 
						?>
						<li class="sf-drag"><input name="sf[tax][]" value="<?php echo $key; ?>" type="hidden" /><?php echo $t->labels->name; ?> (<?php echo $key; ?>)</li>
						<?php 
						endif;
						endforeach; ?>
					</ul>
				</li>
			
				<li><?php _e( 'Postmeta', 'sf' ); ?>
					<ul class="sf-meta-ul">
					<?php foreach( $metas as $key => $val ): 
					if( !isset( $field['meta'] ) || ( is_array( $field['meta'] ) && !in_array( $key, $field['meta'] ) ) ): ?>
					<li class="sf-drag"><input name="sf[meta][]" value="<?php echo $key; ?>" type="hidden" /><?php echo ucfirst( $key ); ?></li>
					<?php 
					endif;
					endforeach; ?>
					</ul>
				</li>
			</ul>
			
			<form method="post" class="sf-form"><?php wp_nonce_field( 'update-sf', 'sf-wpnonce', false ); ?>
				<input type="hidden" value="2" name="sf_step" />

				<ul class="sf-group2">
					<?php foreach( $tax as $key => $t ): 
					if( isset( $field['tax'] ) && is_array( $field['tax'] ) && in_array( $key, $field['tax'] ) ): 
					?>
					<li class="sf-drag"><input name="sf[tax][]" value="<?php echo $key; ?>" type="hidden" /><?php echo $t->labels->name; ?></li>
					<?php 
					endif;
					endforeach; ?>
					<?php foreach( $metas as $key => $val ): 
					if( isset( $field['meta'] ) && is_array( $field['meta'] ) && in_array( $key, $field['meta'] ) ): ?>
					<li class="sf-drag"><input name="sf[meta][]" value="<?php echo $key; ?>" type="hidden" /><?php echo ucfirst( $key ); ?></li>
					<?php 
					endif;
					endforeach; ?>
				</ul>
				<div class="sf-clear"></div>
				<hr />
				
				<hr />
				<input class="button" type="submit" value="<?php _e( 'Update', 'sf' ); ?>" />
			</form>
		</div>
		
		<!-- 3. Tab: Layout -->
		<div id="layout">
			<h3><?php _e( 'Layout' ,'sf' ); ?></h3>
			<form method="post" class="sf-form"><?php wp_nonce_field( 'update-sf', 'sf-wpnonce', false ); ?>
				<input name="sf_step" value="3" type="hidden" />
				
			<div class="sf-accordion">
				<h3><?php _e( 'Search Result Columns' ,'sf' ); ?></h3>
				<div>
					<div class="sf-4columns">
						<label>
							<img src="<?php echo SF_URL; ?>/res/admin/layout-li-column1.png" alt="1 Column" />
							<br />
							<input <?php if( !isset( $field['columns'] ) || $field['columns'] == 1 ) echo 'checked="checked" '; ?>name="sf[columns]" value="1" type="radio"/>
							<?php _e( '1 Column', 'sf' ); ?>
						</label>
					</div>
					<div class="sf-4columns">
						<label>
							<img src="<?php echo SF_URL; ?>/res/admin/layout-li-column2.png" alt="2 Columns" />
							<br />
							<input <?php if( isset( $field['columns'] ) && $field['columns'] == 2 ) echo 'checked="checked" '; ?>name="sf[columns]" value="2" type="radio"/>
							<?php _e( '2 Columns', 'sf' ); ?>
						</label>
					</div>
					<div class="sf-4columns">
						<label>
							<img src="<?php echo SF_URL; ?>/res/admin/layout-li-column3.png" alt="3 Columns" />
							<br />
							<input <?php if( isset( $field['columns'] ) && $field['columns'] == 3 ) echo 'checked="checked" '; ?>name="sf[columns]" value="3" type="radio"/>
							<?php _e( '3 Columns', 'sf' ); ?>
						</label>
					</div>
					<div class="sf-4columns">
						<label>
							<img src="<?php echo SF_URL; ?>/res/admin/layout-li-column4.png" alt="4 Columns" />
							<br />
							<input <?php if( isset( $field['columns'] ) && $field['columns'] == 4 ) echo 'checked="checked" '; ?>name="sf[columns]" value="4" type="radio"/>
							<?php _e( '4 Columns', 'sf' ); ?>
						</label>
					</div>
					<div class="sf-clear"></div>
				</div>
				<h3><?php _e( 'Border & Background', 'sf' ); ?></h3>
				<div>
					<label for="sfborder"><?php _e( 'Border Color' ); ?>: </label><input id="sfborder" type="text" value="<?php echo $field['border']; ?>" name="sf[border]" class="sf-colorfield" />
					<label for="sfbackground"><?php _e( 'Background Color' ); ?>: </label><input id="sfbackground" type="text" value="<?php echo $field['background']; ?>" name="sf[background]" class="sf-colorfield" />
				</div>
				
				<h3><?php _e( 'Single Result Element', 'sf' ); ?></h3>
				<div>
					<?php 
					$resdir = SF_DIR . 'templates/res/';
					$dir = SF_DIR . 'templates/';
					$files = array();
					
					if (!is_writable( $dir ) ):
						?>
						<div class="error"><? _e( 'The directory ' . $dir . ' is not writeable.' ); ?></div>
						<?php
					endif;
				
					$template_name = $field['name'];
					if( function_exists('is_multisite') && is_multisite() )
						$template_name = $wpdb->blogid . '-' . $field['name'];
					if( !is_file( SF_DIR . 'templates/template-' . $template_name . '.php' ) )
						$file = SF_DIR . 'templates/res/template-standard.php';
					else
						$file = SF_DIR . 'templates/template-' . $template_name . '.php';
					if( !is_file( SF_DIR . 'templates/template-' . $template_name . '-noresult.php' ) )
						$file_no_result = SF_DIR . 'templates/res/template-standard-noresult.php';
					else
						$file_no_result = SF_DIR . 'templates/template-' . $template_name . '-noresult.php';
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
								if( isset( $field['tax'] ) && is_array( $field['tax'] ) ):
									foreach( $field['tax'] as $tax ):
								?>
								<tr><td><code>#tax_<?php echo $tax; ?>#</code></td><td><?php printf( __( 'Displays the used terms of the taxonomy "%s"', 'sf' ), $tax ); ?></td></tr>					
								<?php
									endforeach;
								?><?php
								endif;
						
								if( isset( $field['meta'] ) && is_array( $field['meta'] ) ):
									foreach( $field['meta'] as $tax ):
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
					<div class="sf-clear"></div>
				</div>
				<h3 class="last"><?php _e( 'Custom CSS', 'sf' ); ?></h3>
				<div class="last">
					<?php _e( 'You can enter here your custom CSS for the search form.', 'sf' ); ?>
					<textarea id="newcontent" class="sf" name="sf[custom_css]"><?php if( isset( $field['custom_css'] ) ) echo  stripslashes( $field['custom_css'] ); ?></textarea>
				</div>
				
			</div>
			<div class="sf-clear"></div>
			<hr />
			<input class="button" type="submit" value="<?php _e( 'Update', 'sf' ); ?>" />
		</form>	
	</div>
	
	
	<!-- 4. Form Elements -->
	<div id="form-elements">
		<h3><?php _e( 'Form Elements' ,'sf' ); ?></h3>
		<p><?php _e( 'Move the form elements, which you want to have in your form, from the right to the left pane. You can edit the elements attributes by clicking on it in the pane "Chosen Form Elements". In this dialog, you can set the necessary attributes.', 'sf' ); ?></p>
		<div style="display:none;">
			<select id="sf-datasource">
				<optgroup label="<?php _e( 'Taxonomies', 'sf' ); ?>">
					<?php foreach( $field['tax'] as $meta ): ?>
					<option value="tax[<?php echo $meta; ?>]"><?php echo $meta; ?></option>
					<?php endforeach; ?>
				</optgroup><optgroup label="<?php _e( 'Postmetas', 'sf' ); ?>">
				<?php foreach( $field['meta'] as $meta ): ?>
					<option value="meta[<?php echo $meta; ?>]"><?php echo $meta; ?></option>
				<?php endforeach; ?></optgroup>
				
			</select>
			
			<div id="sf-orderbysource">
					<?php 
					$i = 0;
					if( is_array( $field['meta'] ) ):
					foreach( $field['meta'] as $meta ): ?>
					<?php echo $meta; ?> <?php _e( 'ascending', 'sf' ); ?>:<br /><input class="sf-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="meta[<?php echo $meta; ?>|asc]"> <input class="sf-orderbylabel sf-array" name="orderbylabel[<?php echo $i; ?>]" value="<?php echo $meta; ?> <?php _e( 'ascending', 'sf' ); ?>" /><br />
					<?php echo $meta; ?> <?php _e( 'descending', 'sf' ); ?>:<br /><input class="sf-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="meta[<?php echo $meta; ?>|desc]"> <input class="sf-orderbylabel sf-array" name="orderbylabel[<?php echo $i; ?>]" value="<?php echo $meta; ?> <?php _e( 'descending', 'sf' ); ?>" /><br /><br />
					<?php $i++; endforeach; endif;?>
					<?php _e( 'Date ascending', 'sf' ); ?>:<br /><input class="sf-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="post[date|asc]"> <input class="sf-orderbylabel sf-array" name="orderbylabel[<?php echo $i++; ?>]" value="<?php _e( 'Date ascending', 'sf' );  ?>" /><br />
					<?php _e( 'Date descending', 'sf' ); ?>:<br /><input class="sf-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="post[date|desc]"> <input class="sf-orderbylabel sf-array" name="orderbylabel[<?php echo $i++; ?>]" value="<?php _e( 'Date descending', 'sf' ); ?>" /><br />
			</div>
			
			<select id="sf-allpostmeta">
				<?php foreach( $field['meta'] as $meta ): ?>
					<option value="<?php echo $meta; ?>"><?php echo $meta; ?></option>
				<?php endforeach; ?></optgroup>
			</select>
		</div>	

		<form method="post" class="sf-form"><?php wp_nonce_field( 'update-sf', 'sf-wpnonce', false ); ?>
			<input name="sf_step" value="4" type="hidden" />
			<div class="field filter">
				<p><strong><?php _e( 'Chosen Form Elements', 'sf' ); ?></strong></p>
				<?php 
				$img_array = array(
								'fulltext'	=>	'input-fulltext.png',
								'select'	=>	'select.png',
								'input'		=>	'input.png',
								'checkbox'	=>	'checkbox.png',
								'radiobox'	=>	'radiobox.png',
								'range'		=>	'range.png',
								'map'		=>	'maps.png',
								'orderby'	=>	'order-by.png',
								'hidden'	=>	'hidden.png',
								'btnsearch'	=>	'btn-search.png',
								'btnreset'	=>	'btn-reset.png',
								'date'		=>	'date.png'
				);
				$i = 0;
				foreach( $field['fields'] as $key => $f ): 
				$i++;
				?>
				<div data-attr='<?php echo json_encode( $f ); ?>' style="" data-id="<?php echo $i; ?>">
<img alt="" src="<?php echo SF_URL ?>res/admin/<?php echo $img_array[ $f['type'] ]; ?>">
<span><?php echo $f['fieldname']; ?></span>
	<?php foreach( $f as $k => $v ): ?>
		<?php if( is_array( $v ) ): ?>
			<?php foreach( $v as $single_v ): ?>
			<input type="hidden" value="<?php echo $single_v; ?>" name="sf[fields][<?php echo $i; ?>][<?php echo $k; ?>][]">
			<?php endforeach; ?>
		<?php else: ?>
			<input type="hidden" value="<?php echo $v; ?>" name="sf[fields][<?php echo $i; ?>][<?php echo $k; ?>]">
		<?php endif; ?>
	<?php endforeach; ?>
</div>
				<?php endforeach; ?>
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
		<input class="button" type="submit" value="<?php _e( 'Update', 'sf' ); ?>" />
		</form>
	</div>
	
	<!-- 5. Tab: Import & Export -->
	<div id="import-export">
		<h3><?php _e( 'Import & Export', 'sf' ); ?></h3>
		<p><?php _e( 'Here you can import & export your search field. Copy the text below and save it in order to export your search field. Paste the settings here, in order to import your exported search field.', 'sf' ); ?></p>
		<form method="post"><?php wp_nonce_field( 'update-sf', 'sf-wpnonce', false ); ?>
			
			<input name="sf_step" value="5" type="hidden" />
			<textarea style="width:100%;height:250px" name="import"><?php echo serialize( $field ); ?></textarea>
			<input class="button" type="submit" value="<?php _e( 'Import', 'sf' ); ?>" />
		</form>
	</div>
</div>
	
</div>