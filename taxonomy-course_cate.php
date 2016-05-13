<?php
/**
 * The course_cate Taxonomy template file.
 *
 * @since onetake 1.0.0
 */
global $wpdb;
$slug = get_query_var ( 'course_cate' );
$prefix = $wpdb->prefix . "francais_";
$category_table = $prefix . "category";
$sql = "SELECT * FROM {$category_table} WHERE slug = %s";
$sql = $wpdb->prepare ( $sql, $slug );

$result = $wpdb->get_row ( $sql );

if (! $result) {
	wp_redirect( home_url( '404' ), 302 );
	exit ();
}
$city = "";
if (isset($_GET['city'])) {
	$city = $_GET['city'];
	if (!term_exists($city, 'city')) {
		wp_redirect( home_url( '404' ), 302 );
		exit ();
	}
}

$description = $result->description;
$title = $result->title;
$meta_keyword = $result->meta_keyword;
$meta_description = $result->meta_description;

if (!empty($city)) {
	$description = $result->description_with_city;
	$description= str_replace("{{CITY}}", $city, $description);
	$title = $result->title_with_city;
	$title  = str_replace("{{CITY}}", $city, $title);
	$meta_keyword = $result->meta_keyword_with_city;
	$meta_keyword = str_replace("{{CITY}}", $city, $meta_keyword);
	$meta_description = $result->meta_description_with_city;
	$meta_description = str_replace("{{CITY}}", $city, $meta_description);
}

$cate_img = home_url() . "/" . $result->photo;
get_header();
?>
<div class="breadcrumb-box">
	<div class="container">
    	<h1 class="text-center"><?= $title ?></h1>
    </div>
</div>
<div class="blog-list">
	<div class="container">
		<div class="row">
			<div class='col-md-3'><img src="<?= $cate_img ?>"></div>
			<div class='col-md-9'><?= $description ?></div>
		</div>
		<br/>
	</div>	
</div>
<?php 
get_footer();
?>