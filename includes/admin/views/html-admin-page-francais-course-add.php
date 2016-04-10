
<?php
/**
 * Admin View: Main Menu - Francias - Course - Add
 *
 * @var string $view
 */
if (! defined ( 'ABSPATH' )) {
	exit ();
}
global $wpdb;
function validate_input() {
	$result = array();
	if (empty($_POST['number_available'])) {
		$result[] = "Nombre places disponibles is required!";
	} else if (intval($_POST['number_available']) <= 0) {
		$result[] = "Nombre places disponibles must be an unsigned number!";
	}
	
	if (empty($_POST['start_date'])) {
		$result[] = "Date début (1e cours) is required!";
	}
	
	if (empty($_POST['end_date'])) {
		$result[] = "Date fin (dernier cours) is required!";
	}
	
	if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
		$start_date = DateTime::createFromFormat('d-m-Y', $_POST['start_date']);
		$end_date = DateTime::createFromFormat('d-m-Y', $_POST['end_date']);
		if ($end_date <= $start_date) {
			$result[] = "Date fin (dernier cours) must be newer Date début (1e cours)";
		} else if ($start_date->format('w') !== $end_date->format('w') ) {
			$result[] = "Date fin (dernier cours) and Date début (1e cours) must be same day of week!";
		}
	}
	
	if (empty($_POST['start_time'])) {
		$result[] = "Heure de début is required!";
	}
	
	if (empty($_POST['promo_value'])) {
		$result[] = "Nombre places disponibles is required!";
	} else if (intval($_POST['promo_value']) <= 0) {
		$result[] = "Nombre places disponibles must be an unsigned number!";
	} else if (intval($_POST['promo_value']) >= intval($_POST['price'])) {
		$result[] = "Nombre places disponibles must be less than '<i>Prix initial'</i>!";
	}
	
	global $COURSE_TRIAL;
	if ($_POST['trial_mode'] === "1") {
		$essai_start_date = $_POST['essai_start_date'];
		$essai_start_time = $_POST['essai_start_time'];
		$essai_number_available = $_POST['essai_number_available'];
		for($index = 0; $index < count($essai_start_date); $index++) {
			if (empty($essai_start_date[$index])) {
				$result[] = "Séance essai " . ($index + 1) . " -> Date début (1e cours) is required !";
			}
			if (empty($essai_start_time[$index])) {
				$result[] = "Séance essai " . ($index + 1) . " -> Heure de début is required !";
			}
			if (empty($essai_number_available[$index])) {
				$result[] = "Séance essai " . ($index + 1) . " -> Nombre places disponibles is required !";
			} else if (intval($essai_number_available[$index]) <= 0) {
				$result[] = "Séance essai " . ($index + 1) . " -> Nombre places disponibles must be an unsigned number!";
			}
		}
	}
	return $result;
}

if(isset($_POST['createcoursesubmit']) || isset($_POST['createcourseandcontinue'])){
	$errors = validate_input();
	$result = FALSE;
	if (count($errors) === 0) {
		$start_date = DateTime::createFromFormat('d-m-Y', $_POST['start_date']);
		$end_date = DateTime::createFromFormat('d-m-Y', $_POST['end_date']);
		$result = $wpdb->insert(
				$wpdb->prefix . 'francais_course', //table
				array(
					'room_id' => $_POST['room_id'],
					'discipline_id' => $_POST['discipline_id'],
					'profs_id' => $_POST['profs_id'],
					'number_available' => $_POST['number_available'],
					'start_date' => date_format($start_date, "Y-m-d"),
					'start_time' =>  $_POST['start_time'],
					'end_date' => date_format($end_date,"Y-m-d"),
					'promo_value' => $_POST['promo_value'],
					'course_mode' => $_POST['course_mode'],
					'trial_mode' => $_POST['trial_mode']
				), //data
				array('%d','%d', '%d', '%d', '%s', '%s', '%s', '%d', '%d', '%d') //data format
		);
	}
	//wp_die(var_dump( $wpdb->last_query ));
	if ($result !== FALSE) {
		// INSERT COURSE TRIAL
		$course_id = $wpdb->insert_id;
		if ($_POST['trial_mode'] === "1") {
			$essai_start_date = $_POST['essai_start_date'];
			$essai_start_time = $_POST['essai_start_time'];
			$essai_number_available = $_POST['essai_number_available'];
			for($index = 0; $index < count($essai_start_date); $index++) {
				$start_date = DateTime::createFromFormat('d-m-Y', $essai_start_date[$index]);
				$wpdb->insert(
					$wpdb->prefix . 'francais_course_trial', //table
					array(
						"course_id" => $course_id,
						"trial_no" => $index + 1,
						"start_date" => date_format($start_date, "Y-m-d"),
						"start_time" => $essai_start_time[$index],
						"number_available" => $essai_number_available[$index]
					), //data
					array('%d','%d', '%s', '%s', '%d') //data format
				);
// 				wp_die($wpdb->last_query);
			}
		}
		
		if ($_POST['createcoursesubmit']) {
			// redirect
			wp_redirect( home_url() . "/wp-admin/admin.php?page=francais-course", 301);
			exit();
		} else {
			$_POST = array();
			$message = "Course inserted";
		}
	} else {
		if (count($errors) == 0) {
			$message = "Insert failure, Debug sql: " . $wpdb->last_query;
		} else {
			$message = implode("<br/>", $errors);
		}
	}
}

// calculate Discipline
$sql = "SELECT
		   discipline_id, lesson_duration, price,
		   CONCAT(d.course_type, ' - ', d.macro_discipline, ' - ',
				d.micro_discipline, ' - ', d.age_group) AS discipline_index
		FROM {$wpdb->prefix}francais_discipline d";
$discipline_data = $wpdb->get_results ( $sql );
$discipline_data = json_decode(json_encode($discipline_data), true);

// calculate ROOM
$sql = "SELECT
room_id,
CONCAT(r.country, ' - ', r.city, ' - ', r.zip_code, ' - ', r.room_name) AS room_index
FROM {$wpdb->prefix}francais_room r";
$room_data = $wpdb->get_results ( $sql );
$room_data = json_decode(json_encode($room_data), true);

// calculate Teacher
$sql = "SELECT
profs_id,
CONCAT(p.first_name, ' ', p.family_name) AS prof_name
FROM {$wpdb->prefix}francais_profs p";
$profs_data = $wpdb->get_results ( $sql );
$profs_data = json_decode(json_encode($profs_data), true);
?>
<div class="wrap">
	<h1>Créer un cours</h1>
	<?php if (isset($message)): ?>
	<div class="<?php echo $result !== FALSE ? "updated": "error" ?>" >
		<p><?php echo $message;?></p>
	</div>
	<?php endif;?>
<?php if (count($discipline_data) > 0 && count($room_data) > 0 && count($profs_data) > 0) {?>
	<p>Créer un nouveau cours et les ajouter à ce site.</p>	
	<form method="post" name="createcourse" id="createcourse"
		class="validate" novalidate="novalidate"
		action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		
		<!-- Group 1: COURSE -->
		<table class="form-table" >
			<colgroup>
				<col span="1" class="header-block">
			</colgroup>
			<tbody>
				<tr class="form-field form-required">
					<td scope="rowgroup" rowspan="4" valign="middle" width="25%" style="text-align: center">
						COURS</td>
					<td width="25%"><label for="discipline_id">Formule de cours</label></td>
					<td>
						<select id="discipline_id" name="discipline_id">
							<?php foreach ($discipline_data as $discipline) {?>
							<option value="<?= $discipline['discipline_id'] ?>" <?php echo ($_POST['discipline_id'] == $discipline['discipline_id'] ? "selected='selected'" : "") ?>><?= $discipline['discipline_index'] ?></option>
							<?php }?>
						</select>
					</td>
				</tr>
				<tr class="form-field form-required">
					<td width="25%"><label for="room_id">Lieu</label></td>
					<td>
						<select id="room_id" name="room_id">
							<?php foreach ($room_data as $room) {?>
							<option value="<?= $room['room_id'] ?>" <?php echo ($_POST['room_id'] == $room['room_id'] ? "selected='selected'" : "") ?>><?= $room['room_index'] ?></option>
							<?php }?>
						</select>
					</td>
				</tr>
				<tr class="form-field form-required">
					<td width="25%"><label for="number_available">Nombre places disponibles <span
							class="description">(required)</span></label></td>
					
					<td><input type="number" name="number_available"
						value="<?= $_POST['number_available'] ?>" 
						onkeypress='return event.charCode >= 48 && event.charCode <= 57'
						placeholder="Only number"></td>
				</tr>
				<tr class="form-field form-required">
					<td width="25%"><label for="profs_id">Prof</label></td>
					<td>
						<select id="profs_id" name="profs_id">
							<?php foreach ($profs_data as $prof) {?>
							<option value="<?= $prof['profs_id'] ?>" <?php echo ($_POST['profs_id'] == $prof['profs_id'] ? "selected='selected'" : "") ?>><?= $prof['prof_name'] ?></option>
							<?php }?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
		<hr/>
		<!-- Group 2: COURSE - Data Time -->
		<table class="form-table" >
			<colgroup>
				<col span="1" class="header-block">
			</colgroup>
			<tbody>
				<tr class="form-field form-required">
					<td scope="rowgroup" rowspan="5" valign="middle" width="25%" style="text-align: center">
						DATES<br/>HORAIRES</td>
					<td width="25%"><label for="start_date">Date début (1e cours) <span
							class="description">(required)</span></label></td>
					<td><input type="text" id="start_date" name="start_date" class="datepicker" readonly="readonly"
						value="<?= $_POST['start_date'] ?>" placeholder="Select Date début (1e cours)"></td>
				</tr>
				<tr class="form-field form-required">
					<td width="25%"><label for="end_date">Date fin (dernier cours) <span
							class="description">(required)</span></label></td>
					<td><input type="text" id="end_date" name="end_date"
						value="<?= $_POST['end_date'] ?>" class="datepicker" readonly="readonly"
						placeholder="Select Date fin (dernier cours)"></td>
				</tr>
				<tr class="form-field form-required">
					<td width="25%"><label for="start_date_day">Jour (récurrence)</label></td>
					
					<td><input type="text" name="start_date_day"
						value="<?= $_POST['start_date_day'] ?>" readonly="readonly"></td>
				</tr>
				<tr class="form-field form-required">
					<td width="25%"><label for="lesson_duration">Temps de cours</label></td>
					<td><input type="text" id="lesson_duration" name="lesson_duration"
						value="<?= $_POST['lesson_duration'] ?>" readonly="readonly"></td>
				</tr>
				<tr class="form-field form-required">
					<td width="25%"><label for="start_time">Heure de début <span
							class="description">(required)</span></label></td>
					<td><input type="text" name="start_time" class="timepicker"
						value="<?= $_POST['start_time'] ?>" placeholder="Select Heure de début"></td>
				</tr>
			</tbody>
		</table>
		<hr/>
		<!-- Group 3: COURSE - PRIX -->
		<table class="form-table" >
			<colgroup>
				<col span="1" class="header-block">
			</colgroup>
			<tbody>
				<tr class="form-field form-required">
					<td scope="rowgroup" rowspan="2" valign="middle" width="25%" style="text-align: center">
						<label>PRIX</label></td>
					<td width="25%"><label for="price">Prix initial (€)</label></td>
					<td><input type="text" id="price" name="price"
						value="<?= $_POST['price'] ?>" readonly="readonly" ></td>
				</tr>
				<tr class="form-field form-required">
					<td width="25%"><label for="promo_value">Promo (€) <span
							class="description">(required)</span></label></td>
					<td><input type="number" name="promo_value"
						value="<?= $_POST['promo_value'] ?>"
						onkeypress='return event.charCode >= 48 && event.charCode <= 57'
						placeholder="Only number"></td>
				</tr>
			</tbody>
		</table>
		<hr/>
		<!-- Group 4: COURSE - MODE -->
		<table class="form-table" >
			<colgroup>
				<col span="1" class="header-block">
			</colgroup>
			<tbody>
				<tr class="form-field form-required">
					<td scope="rowgroup" valign="middle" width="25%" style="text-align: center">
						<label>MODE</label></td>
					<td width="25%"><label for="course_mode">Mode <span
							class="description">(required)</span></label></td>
					<td>
						<div class="cc-selector">
							<input style="display: none" id="early_bird" type="radio" name="course_mode" value="1" <?= $_POST['course_mode'] != 2 ? "checked='checked'" : "" ?>>
							<label class="drinkcard-cc button-primary" for="early_bird">Early Bird</label>
							<input style="display: none" id="last_call" type="radio" name="course_mode" value="2" <?= $_POST['course_mode'] == 2 ? "checked='checked'" : "" ?>>
							<label class="drinkcard-cc button-primary" for="last_call">Last Call</label>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<hr/>
		<!-- Group 5: COURSE - SEANCE ESSAI -->
		<table class="form-table" id="t_trial_mode">
			<colgroup>
				<col span="1" class="header-block">
			</colgroup>
			<tbody>
				<tr class="form-field form-required">
					<td scope="rowgroup" rowspan="100" valign="middle" width="25%"
						style="text-align: center"><label>SEANCE ESSAI</label></td>
					<td width="25%"><label for="trial_mode">Programmer séance essai <span
							class="description">(required)</span></label></td>
					<td colspan="3">
						<div class="cc-selector">
							<input style="display: none" id="trial_qui" type="radio"
								name="trial_mode" value="1"
								<?= $_POST['trial_mode'] !== '0' ? "checked='checked'" : "" ?>> <label
								class="drinkcard-cc button-primary" for="trial_qui">Qui</label> <input
								style="display: none" id="trial_non" type="radio"
								name="trial_mode" value="0"
								<?= $_POST['trial_mode'] === '0' ? "checked='checked'" : "" ?>> <label
								class="drinkcard-cc button-primary" for="trial_non">Non</label>
						</div>
					</td>
				</tr>
				<tr class="expand">
					<td colspan="4">
						<input type="button" id="addmore" value="+"
						onclick="insertRow()" class="button button-primary" />
						<input type="button" id="removetrial" value="-"
						onclick="removeRow()" <?php echo count($_POST['essai_start_date']) <= 1 ? "disabled=\"disabled\"" : ""?> class="button button-primary" />
					</td>
				</tr>
				<?php if (!isset($_POST['essai_start_date'])) {?>
				<tr class="expand">
					<td>Séance essai 1</td>
					<td><input type="text" class="datepicker" name="essai_start_date[]"
						readonly="readonly" placeholder="date"></td>
					<td><input type="text" class="timepicker" name="essai_start_time[]"
						readonly="readonly" placeholder="heure"></td>
					<td><input type="number" name="essai_number_available[]"
						onkeypress='return event.charCode >= 48 && event.charCode <= 57'
						placeholder="nb places"></td>
				</tr>
				<?php }  else {
					  for($index = 0; $index < count($_POST['essai_start_date']); $index++) {
					  
				?>
				<tr class="expand">
					<td>Séance essai 1</td>
					<td><input type="text" class="datepicker" name="essai_start_date[]"
						readonly="readonly" placeholder="date" value="<?= $_POST['essai_start_date'][$index] ?>"></td>
					<td><input type="text" class="timepicker" name="essai_start_time[]"
						readonly="readonly" placeholder="heure" value="<?= $_POST['essai_start_time'][$index] ?>"></td>
					<td><input type="number" name="essai_number_available[]"
						onkeypress='return event.charCode >= 48 && event.charCode <= 57'
						placeholder="nb places" value="<?= $_POST['essai_number_available'][$index] ?>"></td>
				</tr>
				<?php }}?>
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="createcoursesubmit" id="createcoursesubmit"
				class="button button-primary" value="Add New Course">
			<input type="submit" name="createcourseandcontinue" id="createcourseandcontinue"
				class="button button-primary" value="Add and Continue">
				<a href="<?= home_url() . "/wp-admin/admin.php?page=francais-course" ?>" class="button button-primary">Back to Course List</a>
		</p>
	</form>
</div>
<script type="text/javascript">
var weekday = new Array(7);
weekday[0]=  "Sunday";
weekday[1] = "Monday";
weekday[2] = "Tuesday";
weekday[3] = "Wednesday";
weekday[4] = "Thursday";
weekday[5] = "Friday";
weekday[6] = "Saturday";
var discipline_duration = {};
<?php foreach ($discipline_data as $discipline) {?>
discipline_duration[<?= $discipline['discipline_id']?>] = <?= $discipline['lesson_duration']?>;
<?php }?>

var discipline_price = {};
<?php foreach ($discipline_data as $discipline) {?>
discipline_price[<?= $discipline['discipline_id']?>] = <?= $discipline['price']?>;
<?php }?>

jQuery(document).ready(function() {
	var duration = discipline_duration[jQuery("#discipline_id").val()];
	var duration_text = "" + duration;
	if (duration > 60) {
		duration_text = Math.floor(duration / 60) + "h";
		if ((duration % 60) > 0) {
			if ((duration % 60) < 10) {
				duration_text += "0";
			}
			duration_text += (duration % 60);
		}
	}

	jQuery("input[name='lesson_duration']").val(duration_text);
	jQuery("input[name='price']").val(discipline_price[jQuery("#discipline_id").val()]);
	if (jQuery('#trial_non').is(':checked')) {
    	jQuery(".expand").toggle();
	}
	
    jQuery('.datepicker').live('focusin', function() {
    	jQuery(this).datepicker({
            dateFormat : 'dd-mm-yy',
            showOn: 'focus'
        });
    });
    jQuery('.timepicker').live('focusin', function() {
    	jQuery(this).timepicker({
	    	format: 'HH:ii',
	        autoclose: true,
	    });
    });
});
jQuery("#start_date").on("change", function() {
	var parts = this.value.split("-");
	var dt = new Date(parseInt(parts[2], 10),
	                  parseInt(parts[1], 10) - 1,
	                  parseInt(parts[0], 10));
	var day_value = weekday[dt.getDay()];
	jQuery("input[name='start_date_day']").val(day_value);
});
jQuery("#discipline_id").on("change", function() {
	var duration = discipline_duration[this.value];
	var duration_text = "" + duration;
	if (duration > 60) {
		duration_text = Math.floor(duration / 60) + "h";
		if ((duration % 60) > 0) {
			if ((duration % 60) < 10) {
				duration_text += "0";
			}
			duration_text += (duration % 60);
		}
	}
	jQuery("input[name='lesson_duration']").val(duration_text);
	jQuery("input[name='lesson_duration']").val();
	jQuery("input[name='price']").val(discipline_price[jQuery("#discipline_id").val()]);
});
jQuery('input:radio[name="trial_mode"]').change(
    function(){
        if (jQuery(this).is(':checked') && jQuery(this).val() == '1') {
        	jQuery(".expand").toggle();
        } else {
        	jQuery(".expand").toggle();
        }
    });

function insertRow() {
    var x = document.getElementById('t_trial_mode');
    var new_row = x.rows[x.rows.length - 1].cloneNode(true);
    var len = x.rows.length;
    new_row.cells[0].innerHTML = "Séance essai " + (len-1);

    var inp1 = new_row.cells[1].getElementsByTagName('input')[0];
    inp1.value = '';
    inp1.id = "trial_date_" + (len-1);
    jQuery(inp1).removeClass('hasDatepicker')
    var inp2 = new_row.cells[2].getElementsByTagName('input')[0];
    inp2.value = '';
    inp2.id = "trial_time_" + (len-1);
    
    var inp3 = new_row.cells[3].getElementsByTagName('input')[0];
    inp3.value = '';
    
    x.getElementsByTagName('tbody')[0].appendChild(new_row);
    jQuery("#removetrial").prop("disabled", false);
}
function removeRow() {
    var x = document.getElementById('t_trial_mode');
    var len = x.rows.length;
    if (len <= 3) return;
    
    x.deleteRow(len - 1);

    if (x.rows.length <= 3) {
    	jQuery("#removetrial").prop("disabled", true);
    }
}
</script>
<?php } else {?>
	<div class="error" >
		<?php if (count($room_data) == 0) {?>
		<p>No Lieu is available. Please <a href="<?= home_url() . "/wp-admin/admin.php?page=francais-lieu-add" ?>" class="button button-primary">Add Lieu</a></p>
		<?php } if (count($discipline_data) == 0) {?>
		<p>No Formule de cours is available. Please <a href="<?= home_url() . "/wp-admin/admin.php?page=francais-discipline-add" ?>" class="button button-primary">Add Formule de cours</a></p>
		<?php } if (count($profs_data) == 0) { ?>
		<p>No Prof is available. Please <a href="<?= home_url() . "/wp-admin/admin.php?page=francais-profs-add" ?>" class="button button-primary">Add Prof</a></p>
		<?php } ?>
	</div>
<?php } ?>