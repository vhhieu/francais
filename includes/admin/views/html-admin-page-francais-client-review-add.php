<?php
/**
 * Admin View: Main Menu - Francias - Client review - Add
 *
 * @var string $view
 */
if (! defined ( 'ABSPATH' )) {
	exit ();
}

function validate_input() {
	$result = array();
	if (empty($_POST['client_name'])) {
		$result[] = "PréNom is required!";
	}
	
	include_once ( FC_PLUGIN_PATH . 'lib/EmailAddressValidator.php');
	if (!empty($_POST['client_email']) && !(new EmailAddressValidator())->check_email_address($_POST['client_email'])) {
	    $result[] = "This ({$_POST['client_email']}) email address is considered invalid.";
	}
	
	if (empty($_POST['client_address'])) {
		$result[] = "Villa is required!";
	}
	
	if (empty($_POST['content'])) {
		$result[] = "Content is required!";
	}
	
// 	if (!empty($_POST['rate'])) {
// 		$result[] = "Rate is required!";
// 	}
	
	return $result;
}

//insert
if(isset($_POST['createclientreviewsubmit']) || isset($_POST['createclientreviewandcontinue'])){
	global $wpdb;

	$errors = validate_input();
	$result = FALSE;
	
	if (count($errors) === 0) {
		$_POST      = array_map('stripslashes_deep', $_POST);
		$result = $wpdb->insert(
				$wpdb->prefix . 'francais_client_review', //table
				array(
						'client_name' => $_POST['client_name'],
						'client_email' => $_POST['client_email'],
						'client_address' => $_POST['client_address'],
						'content' => stripslashes_deep($_POST['content']),
						'rate' => $_POST['rate'],
				), //data
				array('%s','%s', '%s', '%s', '%f') //data format
		);
	}
	//wp_die(var_dump( $wpdb->last_query ));
	if ($result !== FALSE) {
		
		if ($_POST['createclientreviewsubmit']) {
			// redirect
			wp_redirect( home_url() . "/wp-admin/admin.php?page=francais-client-review", 301);
			exit();
		} else {
			$_POST = array();
			$message = "Client Review inserted";
		}
	} else {
		if (count($errors) == 0) {
			$message = "Insert failure, Debug: " . $wpdb->last_query;
		} else {
			$message = implode("<br/>", $errors);
		}
	}
}
?>
<div class="wrap">
	<h1>Add New Client Review</h1>
	<?php if (isset($message)): ?><div class="<?php echo $result !== FALSE ? "updated": "error" ?>"><p><?php echo $message;?></p></div><?php endif;?>
	<p>Create Client Review</p>
	<form method="post" name="createprofs" id="createprofs" class="validate" 
		novalidate="novalidate" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row"><label for="client_name">Prénom <span class="description">(required)</span></label></th>
					<td><input type="text" name="client_name" value="<?= $_POST['client_name'] ?>"></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="client_email">Email (exemple : exemple@domaine.com)</label></th>
					<td><input type="email" name="client_email" value="<?= $_POST['client_email'] ?>"></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="client_address">Ville <span class="description">(required)</span></label></th>
					<td><input type="text" name="client_address" value="<?= $_POST['client_address'] ?>"></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="content">Content <span class="description">(required)</span></label></th>
					<td>
						<?php
						$settings =array(
						    'wpautop' => true,
							"textarea_name" => "content",
						    'media_buttons' => false,
						    'quicktags' => true
						);
						
						wp_editor($_POST["content"], "content", $settings);
						?>
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="rate">Rate <span class="description">(required)</span></label></th>
					<td><input type="number" step="0.5" min="0.5" max="5" name="rate" value="<?= $_POST['rate'] ?>" style="width: 20%"></td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="createclientreviewsubmit" id="createclientreviewsubmit"
				class="button button-primary" value="Add New">
			<input type="submit" name="createpclientreviewandcontinue" id="createclientreviewandcontinue"
				class="button button-primary" value="Add and Continue">
				<a href="<?= home_url() . "/wp-admin/admin.php?page=francais-client-review" ?>" class="button button-primary">Back to Client Review List</a>
		</p>
	</form>
</div>