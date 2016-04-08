<?php
/**
 * Admin View: Main Menu - Francias - Lieu - Edit
 *
 * @var string $view
 */
if (! defined ( 'ABSPATH' )) {
	exit ();
}
function validate_input() {
	$result = array();
	if (empty($_POST['zip_code'])) {
		$result[] = "Postal Code is required!";
	}

	if (empty($_POST['room_name'])) {
		$result[] = "Nom is required!";
	}

	include_once ( FC_PLUGIN_PATH . 'lib/EmailAddressValidator.php');
	if (empty($_POST['room_manager_email'])) {
		$result[] = "Mail du gestionnaire is required!";
	} else if (!(new EmailAddressValidator())->check_email_address($_POST['room_manager_email'])) {
		$result[] = "This ({$_POST['room_manager_email']}) email address is considered invalid.";
	}

	if (empty($_POST['room_manager_name'])) {
		$result[] = "Nom et prénom du gestionnaire is required!";
	} else if (strlen($_POST['room_manager_name']) < 3) {
		$result[] = "Login must be more than 3 characters";
	}

	if (intval($_POST['max_number']) <= 0) {
		$result[] = "Nombre de pers max must be numberic value (> 0)";
	}

	if (intval($_POST['area_m2']) <= 0) {
		$result[] = "Nombre m2 must be numberic value (> 0)";
	}

	return $result;
}
if ($_SERVER['REQUEST_METHOD'] === "GET") {
	if (isset($_REQUEST['movie'])) {
		global $wpdb;
		$sql = "SELECT * FROM " . $wpdb->prefix . "francais_room WHERE room_id = %d";
		$obj = $wpdb->get_results($wpdb->prepare($sql, intval($_REQUEST['movie'])));
		$data = json_decode(json_encode($obj), true);
		$data = $data[0];
		if (!$data) {
			wp_redirect( home_url() . "/wp-admin/admin.php?page=francais-lieu", 301);
			exit();
		}
	}
}

//update
if(isset($_POST['updatelieusubmit'])){
	global $wpdb;

	$errors = validate_input();
	$result = FALSE;
	if (count($errors) === 0) {
		$result = $wpdb->update(
				$wpdb->prefix . 'francais_room', //table
				array(
						'country' => $_POST['country'],
						'city' => $_POST['city'],
						'zip_code' => $_POST['zip_code'],
						'room_name' => $_POST['room_name'],
						'address' => $_POST['address'],
						'address_detail' => $_POST['address_detail'],
						'room_description' => $_POST['room_description'],
						'max_number' => intval($_POST['max_number']),
						'area_m2' => intval($_POST['area_m2']),
						'room_manager_name' => $_POST['room_manager_name'],
						'room_manager_tel' => $_POST['room_manager_tel'],
						'room_manager_email' => $_POST['room_manager_email'],
				), //data
				array(
						'room_id' => $_POST['room_id']
				),
				array('%s','%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s'), //data format
				array("%d") // where format
		);
	}
	//wp_die(var_dump( $wpdb->last_query ));
	if ($result) {
		// redirect
		$message.="Lieu updated successful!";
	} else {
		if (count($errors) == 0) {
			$message = "Update failure, Unknown reason!";
		} else {
			$message = implode("<br/>", $errors);
		}
	}
	
	$data = $_POST;
}
global $CITY_LIST;
?>
<div class="wrap">
	<h1>Update Lieu <a
			href="<?php echo admin_url('admin.php?page=francais-lieu-add'); ?>"
			class="page-title-action">Add New</a></h1>
	<?php if (isset($message)): ?><div class="<?php echo $result ? "updated": "error" ?>"><p><?php echo $message;?></p></div><?php endif;?>
	<p>Update lieu information.</p>
	<form method="post" name="createlieu" id="createlieu" class="validate"
		novalidate="novalidate" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<input name="room_id" type="hidden" id="room_id" value="<?= $data['room_id'] ?>"/>
		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row"><label for="country">Pays <span class="description">(required)</span></label></th>
					<td><select name="country" id="country">
							<option selected="selected" value="France">France</option>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="city">Ville</label></th>
					<td><select name="city" id="city">
							<?php foreach ($CITY_LIST as $city) {?>
							<option value="<?= $city ?>" <?php echo ($data['city'] == $city ? "selected='selected'" : "") ?>><?= $city ?></option>
							<?php }?>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="zip_code">Code Postal <span class="description">(required)</span></label></th>
					<td><input name="zip_code" type="text" id="zip_code" value="<?= $data['zip_code'] ?>" size="30"></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="room_name">Nom <span class="description">(required)</span></label></th>
					<td><input name="room_name" type="text" id="room_name" class="code"
						value="<?= $data['room_name'] ?>" size="30"></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="address">Adresse</label></th>
					<td><input name="address" type="text" id="address" value="<?= $data['address'] ?>" size="30"></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="address_detail">Complément d'adresse</label></th>
					<td><input name="address_detail" type="text" id="address_detail"
						value="<?= $data['address_detail'] ?>" size="30"></td>
				</tr>

				<tr class="form-field">
					<th scope="row"><label for="room_description">Description</label></th>
					<td><textarea style="height: 320px; margin-top: 37px;" cols="40"
					     name="room_description" id="room_description"><?= $data['room_description'] ?></textarea></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="max_number">Nombre de pers max <span class="description">(required)</span></label></th>
					<td><input name="max_number" type="text" id="max_number" value="<?= $data['max_number'] ?>" size="30"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="area_m2">Nombre m2 <span class="description">(required)</span></label></th>
					<td><input name="area_m2" type="text" id="area_m2" value="<?= $data['area_m2'] ?>" size="30"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="room_manager_name">Nom et prénom du gestionnaire <span class="description">(required)</span></label></th>
					<td><input name="room_manager_name" type="text" id="room_manager_name" value="<?= $data['room_manager_name'] ?>" size="30"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="room_manager_tel">Tél du gestionnaire</label></th>
					<td><input name="room_manager_tel" type="text" id="room_manager_tel" value="<?= $data['room_manager_tel'] ?>" size="30"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="room_manager_email">Mail du gestionnaire <span class="description">(required)</span></label></th>
					<td><input name="room_manager_email" type="email" id="room_manager_email" value="<?= $data['room_manager_email'] ?>" size="30"></td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="updatelieusubmit" id="updatelieusubmit"
				class="button button-primary" value="Update Lieu">
			<a href="<?= home_url() . "/wp-admin/admin.php?page=francais-lieu" ?>" class="button button-primary">Back to Lieu List</a>
		</p>
	</form>
</div>