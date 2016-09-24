<?php
/**
 * Francais Exporter Class: using to export CSV, Excel
 *
 * @author   hieuvh
 * @category Admin
 * @package  Francais/Admin
 * @version  1.0.0
 */
if (! defined ( 'ABSPATH' )) {
	exit (); // Exit if accessed directly
}

if (! class_exists ( 'FC_Exporter' )) :
	/**
	 * FC_Exporter.
	 */
	class FC_Exporter {
		public function init() {
			$this->process_manual_action();
		}
		function process_manual_action() {
			if (isset ( $_GET ['manual_action'] )) {
				$manual_action = $_GET ['manual_action'];
				if ($manual_action === "export_essai") {
					$this->export_essai ();
					exit ();
				}
			}
		}
		function export_essai() {
			global $wpdb;
			$prefix = $wpdb->prefix;
			$sql = "SELECT ctr.*, ct.start_date, ct.start_time, p.POST_TITLE AS course_title,
				c.trial_mode,
				CONCAT(prof.first_name, ' ', prof.family_name) AS prof_name,
				CONCAT(r.country, '-', r.city, '-', r.zip_code, '-', r.room_name) AS room_name,
				(CASE WHEN ctr.register_time IS NULL THEN 1 ELSE 0 END) AS POSITION
				FROM  `{$prefix}francais_course_trial_registration` ctr
				LEFT JOIN `{$prefix}francais_course_trial` ct USING (COURSE_ID, TRIAL_NO)
				INNER JOIN `{$prefix}francais_course` c USING (COURSE_ID)
				LEFT JOIN `{$prefix}francais_profs` prof USING (PROFS_ID)
				LEFT JOIN `{$prefix}francais_room` r USING (ROOM_ID)
				LEFT JOIN `{$prefix}posts` p ON c.POST_ID = p.ID
				ORDER BY POSITION ASC, ctr.register_time DESC";
			$data = $wpdb->get_results ( $sql );
			
			header ( "Pragma: public" );
			header ( "Expires: 0" );
			header ( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
			header ( "Cache-Control: private", false );
			header ( "Content-Type: application/octet-stream" );
			header ( "Content-Disposition: attachment; filename=\"essai_report.csv\";" );
			header ( "Content-Transfer-Encoding: binary" );
			
			$columns = array(
				"course_title" => "Course",
				"trial_mode" => "Seance essai Status",
				"start_date" => "Essai Start Time",
				"first_name" => "Prénom",
				"last_name" => "Nom",
				"email" => "Adresse e-mail",
				"phone" => "Téléphone",
				"prof_name" => "Prof",
				"room_name" => "Room",
			);
			foreach ( $columns as $col_name => $col_desc ) {
				echo "\"{$col_desc}\"";
				if ($col_name !== 'room_name') {
					echo ",";
				} else {
					echo "\n";
				}
			}
			foreach ( $data as $obj ) {
				foreach ( $columns as $col_name => $col_desc ) {
					if ($col_name === 'start_date') {
						echo '"' . $obj->start_date . ' ' . $obj->start_time . '",';
					} else if ($col_name === 'trial_mode') {
						global $COURSE_TRIAL;
						echo '"' . $COURSE_TRIAL[$obj->$col_name] . '",';
					} else {
						echo '"' . $obj->$col_name . '"';
						if ($col_name !== 'room_name') {
							echo ",";
						} else {
							echo "\n";
						}
					}
				}
			}
		}
	}

endif;

(new FC_Exporter ())->init ();