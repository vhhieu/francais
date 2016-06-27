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
	
	if (!empty($_POST['room_manager_tel']) && strlen($_POST['room_manager_tel']) > 16) {
		$result[] = "Tél du gestionnaire must be less than 16 characters";
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
	$_POST      = array_map('stripslashes_deep', $_POST);
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
						'address_detail' => stripslashes_deep($_POST['address_detail']),
						'room_description' => stripslashes_deep($_POST['room_description']),
						'max_number' => intval($_POST['max_number']),
						'area_m2' => intval($_POST['area_m2']),
						'room_manager_name' => $_POST['room_manager_name'],
						'room_manager_tel' => $_POST['room_manager_tel'],
						'room_manager_email' => $_POST['room_manager_email'],
						'is_erp' => $_POST['is_erp'],
				), //data
				array(
						'room_id' => $_POST['room_id']
				),
				array('%s','%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%d'), //data format
				array("%d") // where format
		);
	}
	
	if ($result !== FALSE) {
		// redirect
		$message.="Lieu updated successful!";
	} else {
		if (count($errors) == 0) {
			$message = "Update failure, Unknown reason! " . $wpdb->last_query;
		} else {
			$message = implode("<br/>", $errors);
		}
	}
	
	$data = $_POST;
}
include_once(FC_PLUGIN_PATH . "includes/admin/class-fc-util.php");
$cities = FC_Util::get_cities_list();
?>
<div class="wrap">
	<h1>Update Lieu <a
			href="<?php echo admin_url('admin.php?page=francais-lieu-add'); ?>"
			class="page-title-action">Add New</a></h1>
	<?php if (isset($message)): ?><div class="<?php echo $result !== FALSE ? "updated": "error" ?>"><p><?php echo $message;?></p></div><?php endif;?>
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
					<td><select name="city" id="city" class="selectbox-general">
							<?php foreach ($cities as $slug => $city) {?>
							<option value="<?= $slug ?>" <?php echo ($data['city'] == $slug ? "selected='selected'" : "") ?>><?= $city ?></option>
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
					<td><?php
						$settings =array(
						    'wpautop' => true,
							"textarea_name" => "room_description",
						    'media_buttons' => false,
						    'quicktags' => true
						);
						
						wp_editor($data["room_description"], "roomdescription", $settings);
						?></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="max_number">Nombre de pers max <span class="description">(required)</span></label></th>
					<td><input name="max_number" type="number" id="max_number" value="<?= $data['max_number'] ?>" size="30"
							onkeypress='return is_number(event);'
							placeholder="Only number" style="width: 10%"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="area_m2">Nombre m2 <span class="description">(required)</span></label></th>
					<td><input name="area_m2" type="number" id="area_m2" value="<?= $data['area_m2'] ?>" size="30"
							onkeypress='return is_number(event);'
							placeholder="Only number" style="width: 10%"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="room_manager_name">Nom et prénom du gestionnaire <span class="description">(required)</span></label></th>
					<td><input name="room_manager_name" type="text" id="room_manager_name" value="<?= $data['room_manager_name'] ?>" size="30"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="room_manager_tel">Tél du gestionnaire (<span style="color:red">pas d’espace, pas de point</span>)</label></th>
					<td><input name="room_manager_tel" type="text" id="room_manager_tel" value="<?= $data['room_manager_tel'] ?>" size="30"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="room_manager_email">Mail du gestionnaire <span class="description">(required)</span></label></th>
					<td><input name="room_manager_email" type="email" id="room_manager_email" value="<?= $data['room_manager_email'] ?>" size="30"></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="is_erp">Norme ERP ?</label></th>
					<td>
						<div class="cc-selector">
							<input style="display: none" id="is_erp_oui" type="radio" name="is_erp" value="1" <?= $data['is_erp'] != 0 ? "checked='checked'" : "" ?>>
							<label class="drinkcard-cc button-primary" for="is_erp_oui">OUI</label>
							<input style="display: none" id="is_erp_non" type="radio" name="is_erp" value="0" <?= $data['is_erp'] == 0 ? "checked='checked'" : "" ?>>
							<label class="drinkcard-cc button-primary" for="is_erp_non">NON</label>
						</div>
					</td>
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
<script type="text/javascript">
function is_number(event) {
	
    var key = window.event ? event.keyCode : event.which;
    if (event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 46
     || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 38 || event.keyCode == 40) {
        return true;
    } else if ( key < 48 || key > 57 ) {
        return false;
    }
    else return true;
}
</script>