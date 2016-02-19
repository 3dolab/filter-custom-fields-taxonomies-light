<?php
	$fields = get_option( 'sf-fields' );
?>
<div id="wrap">
	<a href="http://www.profisearchform.com" target="_blank" title="Profi Search Form"><img class="sf-logo" src="<?php echo SF_URL; ?>res/admin/logo.png" alt="" /></a>
	<h2><?php _e( 'Search Filter', 'sf' ); ?> 
	</h2>
	<a class="button" href="?page=search-filter-new"><?php _e( 'Create new search filter', 'sf' ); ?></a>
	<hr />
	<div class="sf-half">
	<?php if( is_array( $fields ) && count( $fields ) > 0 ): ?>
	<table class="wp-list-table widefat fixed pages">
		<thead>
			<tr>
				<th><?php _e( 'Name', 'sf' ); ?></th>
				<th><?php _e( 'Shortcode', 'sf' ); ?></th>
				<th><?php _e( 'Delete', 'sf' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th><?php _e( 'Name', 'sf' ); ?></th>
				<th><?php _e( 'Shortcode', 'sf' ); ?></th>
				<th><?php _e( 'Delete', 'sf' ); ?></th>
			</tr>
		</tfoot>
		<tbody id="the-list">
			<?php foreach( $fields as $field ): ?>
			<tr>
				<td><a href="?page=search-filter-edit&ID=<?php echo $field['name']; ?>"><?php if( !isset( $field['title'] ) || trim( $field['title'] ) == '' )echo $field['name']; else echo $field['title'];?></a></td>
				<td><code>[search-form id="<?php echo $field['name']; ?>"]</code></td>
				<td><a href="#" class="sf-form-delete button" data-id="<?php echo $field['name']; ?>"><?php _e( 'Delete', 'sf' ); ?></a></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php else: ?>
	<p><?php _e( 'No Search Filter yet.', 'sf' ); ?></p>
	<?php endif; ?>
	</div>
	<a href="http://codecanyon.net/item/filter-custom-fields-taxonomies/6964843?ref=websupporter" target="_blank"><img src="<?php echo SF_URL; ?>/res/admin/gopro.png" alt="Go Pro" class="sf-half" /></a>
	
	<hr class="sf"/>
	<p><strong>Current Version: <?php echo SF_CURRENT_VERSION; ?></strong></p>
</div>