<?php
/**
 * Admin View: Main Menu - Francias - Profs - Edit
 *
 * @var string $view
 */
if (! defined ( 'ABSPATH' )) {
	exit ();
}
function validate_input() {
	$result = array();
	if (empty($_POST['first_name'])) {
		$result[] = "Prénom is required!";
	}

	if (empty($_POST['family_name'])) {
		$result[] = "Nom is required!";
	}

	include_once ( FC_PLUGIN_PATH . 'lib/EmailAddressValidator.php');
	if (empty($_POST['email'])) {
		$result[] = "Mail is required!";
	} else if (!(new EmailAddressValidator())->check_email_address($_POST['email'])) {
		$result[] = "This ({$_POST['email']}) email address is considered invalid.";
	}

	if (empty($_POST['login_name'])) {
		$result[] = "Login is required!";
	} else if (strlen($_POST['login_name']) < 4) {
		$result[] = "Login must be more than 4 characters";
	}

	if (empty($_POST['password'])) {
		$result[] = "Password is required!";
	} else if (strlen($_POST['login_name']) < 6) {
		$result[] = "Login must be more than 6 characters";
	}

	return $result;
}
if ($_SERVER['REQUEST_METHOD'] === "GET") {
	if (isset($_REQUEST['movie'])) {
		global $wpdb;
		$sql = "SELECT * FROM " . $wpdb->prefix . "francais_profs WHERE profs_id = %d";
		$obj = $wpdb->get_results($wpdb->prepare($sql, intval($_REQUEST['movie'])));
		$data = json_decode(json_encode($obj), true);
		$data = $data[0];
		if (!$data) {
			wp_redirect( home_url() . "/wp-admin/admin.php?page=francais-profs", 301);
			exit();
		}
	}
}
//update
if(isset($_POST['updateprofssubmit'])) {
	global $wpdb;

	$password = $_POST['password'];
	if ($password) {
		$password = md5($password);
	}
	$errors = validate_input();
	$result = FALSE;
	if (count($errors) === 0) {
		$result = $wpdb->update(
				$wpdb->prefix . 'francais_profs', //table
				array(
						'first_name' => $_POST['first_name'],
						'family_name' => $_POST['family_name'],
						'phone' => $_POST['phone'],
						'email' => $_POST['email'],
						'login_name' => $_POST['login_name'],
						'password' => $password,
						'admin_type' => 'prof',
						'description' => $_POST['description'],
						'micro_discipline_1' => $_POST['micro_discipline_1'],
						'micro_discipline_2' => $_POST['micro_discipline_2'],
						'micro_discipline_3' => $_POST['micro_discipline_3'],
						'city_1' => $_POST['city_1'],
						'city_2' => $_POST['city_2'],
						'city_3' => $_POST['city_3']
				), //data
				array(
						'profs_id' => $_POST['profs_id']
				),
				array('%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'), //data format
				array("%d")
		);
	}
	//wp_die(var_dump( $wpdb->last_query ));
	if ($result) {
		// redirect
		$message = "Profs updated successful!";
	} else {
		$message = "Some data invalid, Please input valid data.";
	}
	
	$data = $_POST;
}
global $CITY_LIST;
?>
<div class="wrap">
	<h1>Edit Profs</h1>
	<?php if (isset($message)): ?><div class="<?php echo $result ? "updated": "error" ?>"><p><?php echo $message;?></p></div><?php endif;?>
	<p>Update profs information.</p>
	<form method="post" name="updateprofs" id="updateprofs" class="validate"
		novalidate="novalidate" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<input name="profs_id" type="hidden" id="profs_id" value="<?= $data['profs_id'] ?>"/>
		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row"><label for="first_name">Prénom<span class="description">(required)</span></label></th>
					<td><input type="text" name="first_name" value="<?= $data['first_name'] ?>"></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="family_name">Nom<span class="description">(required)</span></label></th>
					<td><input type="text" name="family_name" value="<?= $data['family_name'] ?>"></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="phone">Tel</label></th>
					<td><input type="text" name="phone" value="<?= $data['phone'] ?>"></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="email">Email</label></th>
					<td><input type="email" name="email" value="<?= $data['email'] ?>"></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="login_name">Login</label></th>
					<td><input type="text" name="login_name" value="<?= $data['login_name'] ?>"></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="password">Password</label></th>
					<td><input type="password" name="password" value=""></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="description">Description</label></th>
					<td><textarea style="height: 320px; margin-top: 37px;" cols="40"
					     name="description" id="description"><?= $data['description'] ?></textarea></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="city_1">Ville 1</label></th>
					<td><select name="city_1" id="city_1">
							<option selected="<?php echo $data['city_1'] == "" ? "selected" : ""?>" value="">Selected a Ville</option>
							<?php foreach ($CITY_LIST as $city) {?>
							<option value="<?= $city ?>" <?php echo ($data['city_1'] == $city ? "selected='selected'" : "") ?>><?= $city ?></option>
							<?php }?>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="city_2">Ville 2</label></th>
					<td><select name="city_2" id="city_2">
							<option selected="<?php echo $data['city_2'] == "" ? "selected" : ""?>" value="">Selected a Ville</option>
							<?php foreach ($CITY_LIST as $city) {?>
							<option value="<?= $city ?>" <?php echo ($data['city_2'] == $city ? "selected='selected'" : "") ?>><?= $city ?></option>
							<?php }?>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="city_3">Ville 3</label></th>
					<td><select name="city_3" id="city_3">
							<option selected="<?php echo $data['city_3'] == "" ? "selected" : ""?>" value="">Selected a Ville</option>
							<?php foreach ($CITY_LIST as $city) {?>
							<option value="<?= $city ?>" <?php echo ($data['city_3'] == $city ? "selected='selected'" : "") ?>><?= $city ?></option>
							<?php }?>
					</select></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="micro_discipline_1">Micro discipline 1</label></th>
					<td><input name="micro_discipline_1" type="text" id="micro_discipline_1" value="<?= $data['micro_discipline_1'] ?>" size="30"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="micro_discipline_2">Micro discipline 2</label></th>
					<td><input name="micro_discipline_2" type="text" id="micro_discipline_2" value="<?= $data['micro_discipline_2'] ?>" size="30"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="micro_discipline">Micro discipline 3</label></th>
					<td><input name="micro_discipline_3" type="text" id="micro_discipline_3" value="<?= $data['micro_discipline_3'] ?>" size="30"></td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="updateprofssubmit" id="updateprofssubmit"
				class="button button-primary" value="Update Profs">
			<a href="<?= home_url() . "/wp-admin/admin.php?page=francais-profs" ?>" class="button button-primary">Back to Profs List</a>
		</p>
	</form>
</div>