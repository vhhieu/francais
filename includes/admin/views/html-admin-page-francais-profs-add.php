<?php
/**
 * Admin View: Main Menu - Francias - Profs - Add
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
	} else if (strlen($_POST['password']) < 6) {
		$result[] = "Password must be more than 6 characters";
	}
	
	return $result;
}
//insert
if(isset($_POST['createprofssubmit']) || isset($_POST['createprofsandcontinue'])){
	global $wpdb;

	$password = md5($_POST['password']);
	$errors = validate_input();
	$result = FALSE;
	if (count($errors) === 0) {
		$result = $wpdb->insert(
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
				array('%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s') //data format
		);
	}
	//wp_die(var_dump( $wpdb->last_query ));
	if ($result !== FALSE) {
		
		if ($_POST['createprofssubmit']) {
			// redirect
			wp_redirect( home_url() . "/wp-admin/admin.php?page=francais-profs", 301);
			exit();
		} else {
			$_POST = array();
			$message = "Profs inserted";
		}
	} else {
		if (count($errors) == 0) {
			$message = "Insert failure, login name or email may be existed!";
		} else {
			$message = implode("<br/>", $errors);
		}
	}
}
global $CITY_LIST;
?>
<div class="wrap">
	<h1>Add New Profs</h1>
	<?php if (isset($message)): ?><div class="<?php echo $result ? "updated": "error" ?>"><p><?php echo $message;?></p></div><?php endif;?>
	<p>Create a new profs and add them to this site.</p>
	<form method="post" name="createprofs" id="createprofs" class="validate"
		novalidate="novalidate" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row"><label for="first_name">Prénom <span class="description">(required)</span></label></th>
					<td><input type="text" name="first_name" value="<?= $_POST['first_name'] ?>"></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="family_name">Nom <span class="description">(required)</span></label></th>
					<td><input type="text" name="family_name" value="<?= $_POST['family_name'] ?>"></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="phone">Tel (pas d’espace, pas de point)</label></th>
					<td><input type="text" name="phone" value="<?= $_POST['phone'] ?>"></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="email">Email <span class="description">(required)</span> (exemple : exemple@domaine.com)</label></th>
					<td><input type="email" name="email" value="<?= $_POST['email'] ?>"></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="login_name">Login <span class="description">(required)</span></label></th>
					<td><input type="text" name="login_name" value="<?= $_POST['login_name'] ?>"></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="password">Password <span class="description">(required)</span></label></th>
					<td><input type="password" name="password" value="<?= $_POST['password'] ?>"></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="description">Description</label></th>
					<td><textarea style="height: 320px; margin-top: 37px;" cols="40"
					     name="description" id="description"><?= $_POST['description'] ?></textarea></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="city_1">Ville 1</label></th>
					<td><select name="city_1" id="city_1">
							<option selected="<?php echo $_POST['city_1'] == "" ? "selected" : ""?>" value="">Selected a Ville</option>
							<?php foreach ($CITY_LIST as $city) {?>
							<option value="<?= $city ?>" <?php echo ($_POST['city_1'] == $city ? "selected='selected'" : "") ?>><?= $city ?></option>
							<?php }?>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="city_2">Ville 2</label></th>
					<td><select name="city_2" id="city_2">
							<option selected="<?php echo $_POST['city_2'] == "" ? "selected" : ""?>" value="">Selected a Ville</option>
							<?php foreach ($CITY_LIST as $city) {?>
							<option value="<?= $city ?>" <?php echo ($_POST['city_2'] == $city ? "selected='selected'" : "") ?>><?= $city ?></option>
							<?php }?>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="city_3">Ville 3</label></th>
					<td><select name="city_3" id="city_3">
							<option selected="<?php echo $_POST['city_3'] == "" ? "selected" : ""?>" value="">Selected a Ville</option>
							<?php foreach ($CITY_LIST as $city) {?>
							<option value="<?= $city ?>" <?php echo ($_POST['city_3'] == $city ? "selected='selected'" : "") ?>><?= $city ?></option>
							<?php }?>
					</select></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="micro_discipline_1">Micro discipline 1</label></th>
					<td><input name="micro_discipline_1" type="text" id="micro_discipline_1" value="<?= $_POST['micro_discipline_1'] ?>" size="30"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="micro_discipline_2">Micro discipline 2</label></th>
					<td><input name="micro_discipline_2" type="text" id="micro_discipline_2" value="<?= $_POST['micro_discipline_2'] ?>" size="30"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="micro_discipline">Micro discipline 3</label></th>
					<td><input name="micro_discipline_3" type="text" id="micro_discipline_3" value="<?= $_POST['micro_discipline_3'] ?>" size="30"></td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="createprofssubmit" id="createprofssubmit"
				class="button button-primary" value="Add New Profs">
			<input type="submit" name="createprofsandcontinue" id="createprofsandcontinue"
				class="button button-primary" value="Add and Continue">
				<a href="<?= home_url() . "/wp-admin/admin.php?page=francais-profs" ?>" class="button button-primary">Back to Profs List</a>
		</p>
	</form>
</div>