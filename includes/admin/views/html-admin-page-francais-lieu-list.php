<?php
/**
 * Admin View: Main Menu - Francias
 *
 * @var string $view
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
<h1>Lieu
	<a href="<?php echo admin_url('admin.php?page=francais-lieu-add'); ?>" class="page-title-action">Add New</a>
</h1>
<?php
global $wpdb;
$table_name = $wpdb->prefix . 'francais_room';
$sql = "SELECT room_id,room_name FROM " . $table_name;
$rows = $wpdb->get_results($sql);

echo "<table class='wp-list-table widefat fixed striped'>";
echo "<tr><th>ID</th><th>Lieu Name</th><th>&nbsp;</th></tr>";
foreach ($rows as $row ){
	echo "<tr>";
	echo "<td>$row->room_id</td>";
	echo "<td>$row->room_name</td>";	
	echo "<td><a href='".admin_url('admin.php?page=francais-lieu-edit&room-id='.$row->room_id)."'>Edit</a></td>";
	echo "</tr>";}
echo "</table>";
?>
</div>
