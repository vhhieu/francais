<?php
/**
* The sigle template file.
*
*/
$post_id = get_the_ID();
global $wpdb;
$table_prefix = $wpdb->prefix . "francais_";
$sql = "SELECT
			r.country, r.city, r.zip_code, r.room_name, r.photo_1 AS room_photo, r.address as room_address, r.room_description,
			d.course_type, d.macro_discipline, d.micro_discipline, d.age_group, d.discipline_description, d.lesson_target, d.photo,
			d.application_fee, d.price, d.lesson_duration,
			p.photo AS prof_photo, p.description AS prof_description, CONCAT(p.first_name, ' ', p.family_name) AS prof_name, 
			c.*
		FROM {$table_prefix}course c
			LEFT JOIN {$table_prefix}discipline d USING (discipline_id)
			LEFT JOIN {$table_prefix}room r USING (room_id)
			LEFT JOIN {$table_prefix}profs p USING (profs_id)
		WHERE c.post_id = %d";
$sql = $wpdb->prepare($sql, $post_id);
$course = $wpdb->get_row($sql);

setlocale(LC_TIME, get_locale());
$img_url = home_url() . "/" . $course->photo;
$from_time = DateTime::createFromFormat('H:i:s', $course->start_time)->getTimestamp();
$to_time = $from_time + $course->lesson_duration * 60;
$start_date = DateTime::createFromFormat('Y-m-d', $course->start_date)->getTimestamp();

$from_time_str = date("H", $from_time) . "h" . date("i", $from_time);
$to_time_str = date("H", $to_time) . "h" . date("i", $to_time);
$start_date_str = strftime("%d %b %Y", $start_date);
$day_of_week = strftime("%A", $start_date);
$product_id = $course->product_id;
include_once(WP_PLUGIN_DIR . "/francais/includes/class-fc-woocommerce-api.php");
$client = new FC_Product_Api();
$product = $client->wc_client->products->get( $product_id )->product;
$quantity = $product->stock_quantity;
$checkout_page_id = get_option("woocommerce_checkout_page_id");

get_header(); 
?>
<div <?php post_class("clear"); ?> style="padding-top: 20px;">
	<div id="main" class="clearfix container">
		<div class="row">
			<div class="col-md-4">
				<div><img src="<?= $img_url ?>" ></div>
				<section class="blog-main text-center" role="main">
					<article class="post-entry text-left">
						<div class="entry-main no-img">
							<div class="entry-content">
								<p><strong>Prix:</strong> <?= $course->price ?>€<?php if ($course->application_fee > 0) { echo " / an + {$course->application_fee}€ de frais de dossier"; }?></p>
								<p><small><i>Paiement par carte bancaire, par chèque ou par prélèment bancaire (paiement possible en 3 fois sans frais)</i></small></p>
								<p style="text-align: center;"><strong><?= $quantity ?> places encore disponibles</strong></p>
								<p><strong>&gt;&gt; Pourquoi nous faire confiance?</strong></p>
								<ul style="list-style: none;">
									<li><i class="fa fa-check">&nbsp;</i>Nos clients sont satisfaits et nous attribuent la note moyenne de 17/20</li>
									<li><i class="fa fa-check">&nbsp;</i>Nos professeurs sont tous diplômés d'état et recrutés parmi les meilleurs</li>
									<li><i class="fa fa-check">&nbsp;</i>Nos cours se déroulent dans une ambiance conviviale et sympathique</li>
									<li><i class="fa fa-check">&nbsp;</i>Séance d'essai: satisfait ou emboursé à l'issue de la première séance!</li>
								</ul>
							</div>
						</div>
					</article>
				</section>
			</div>
			<div class="col-md-8">
				<section class="blog-main text-center" role="main">
					<article class="post-entry text-left">
						<div class="entry-main no-img">
							<div class="entry-header">
								<h1 class="entry-title" style="font-size: 20px"><?php the_title();?></h1>
                            </div>
							<div class="entry-content">
								<?php 
								$html_1 = "<p><strong>Jour et horaire du cours:</strong> Tous les {$day_of_week} de {$from_time_str} à {$to_time_str} à partir du {$start_date_str} (hors vacances scolaires)</p>";
								echo $html_1;
								?>
								<div style="margin-top: 10px;">
                                	<strong>Description du cours:</strong> <?= $course->discipline_description ?>
                                </div>
                                <div style="margin-top: 10px;">
                                	<strong>A qui s'adresse ce cours:</strong> <?= $course->lesson_target ?>
                                </div>
                                <div style="margin-top: 20px;">
									<div style="float: left; width: 50%">
										<p><strong>Professeur: <?= $course->prof_name ?></strong></p>
										<div style="float: left; width: 23%;">
											<img src="<?= home_url() . "/" . $course->prof_photo ?>">
										</div>
					                    <div style="padding-left: 25%;">
					                    	<?= $course->prof_description ?>
					                    </div>
									</div>
				                    <div style="padding-left: 50%">
				                    	<p><strong>Lieu Du Cours: <?= $course->room_name . ", " . $course->room_address ?></strong></p>
										<div style="float: left; width: 23%;">
											<img src="<?= home_url() . "/" . $course->room_photo ?>">
										</div>
					                    <div style="padding-left: 25%;">
					                    	<?= $course->room_description ?>
					                    </div>
				                    </div>
				                </div>
				                <div style="padding-top: 20px; clear: both;">
                                	vous pourrez confirmer votre inscription ou bien demander penant 7 jours le remboursement intégral de votre inscription si vous ne souhaitez pass porsuivre.
                                </div>
                                <div style="padding-top: 20px; clear: both; text-align: center;">
                                	<div style="float: left; width: 54%; background: RGB(65,113,156);">
                                		<div style="height: 60px; background: RGB(237,125,49); padding-top: 10px; padding-bottom: 10px; color: white; padding-left: 5px; padding-right: 5px; text-align: left;">
                                			<?php if ($quantity <= 0) {?>
                                				Le cours est complet. Inscrivez vous sur liste d’attente en cas de désistement de dernière minute.
                                			<?php } else if ($course->course_mode == 1) {?>
                                				Inscrivez-vous dès aujourd’hui et bénéficiez de 10% de remise grâce au code PREMIERSARRIVES
                                			<?php } else {?>
                                				Plus que quelques places disponibles - Inscrivez-vous dès aujourd’hui
                                			<?php }?>
 										</div>
                                		<div>
                                			<div style="padding-top: 10px; padding-bottom: 10px; color: white; padding-left: 5px; padding-right: 5px;">
                                				<?php if ($quantity <= 0) {?>
	                                				Si un élève se désiste, nous vous contacterons !
	                                			<?php } else if ($course->course_mode == 1) {?>
	                                				OFFRE LIMITEE : <?= $course->promo_value ?> € d’économies, c’est CHOUETTE : )
	                                			<?php } else {?>
	                                				Dernières places disponibles : Rejoignez nous vite : )
	                                			<?php }?>
                                			</div>
                                			<div style="color: white; padding-top: 5px; padding-bottom: 5px;">
			                                	<form class="cart" method="post" action="<?php echo esc_url( get_permalink( intval($checkout_page_id )) ); ?>" enctype="multipart/form-data">
													<input type="hidden" name="add-to-cart" value="<?= $product_id ?>">
													<button type="submit" class="btn btn-primary" >
													<?php if ($quantity <= 0) {?>
		                                				&gt;&gt; JE M’INSCRIS !
		                                			<?php } else if ($course->course_mode == 1) {?>
		                                				&gt;&gt; JE M’INSCRIS VITE !
		                                			<?php } else {?>
		                                				&gt;&gt; JE M’INSCRIS VITE ! 
		                                			<?php }?>
														
													</button>
												</form>
											</div>
											<div style="text-align: left; font-size: 11.5px; padding-top: 10px; padding-bottom: 10px; color: white; padding-left: 5px; padding-right: 5px;">Satisfait ou remboursé : A l’issue de votre 1ère séance
												d’essai, vous pourrez confirmer votre inscription ou bien
												demander pendant 7 jours le remboursement intégral de votre
												inscription si vous ne souhaitez pas poursuivre vos cours !
											</div>
										</div>
									</div>
								<?php
								// get séance d'essai
								if ($course->trial_mode !== 0) {
									$sql = "SELECT d.lesson_duration, ct.* FROM {$table_prefix}course_trial ct
												LEFT JOIN {$table_prefix}course c USING (course_id)
												LEFT JOIN {$table_prefix}discipline d USING(discipline_id)
											WHERE course_id = %d ORDER BY TRIAL_NO";
									$sql = $wpdb->prepare($sql, $course->course_id);
									$trials = $wpdb->get_results($sql);
									if ($trials) {
										$links = "";
										foreach ($trials as $trial) {
											$sql = "SELECT count(*) FROM {$table_prefix}course_trial_registration WHERE course_id = %d AND trial_no = %d";
											$sql = $wpdb->prepare($sql, $trial->course_id, $trial->trial_no);
											$count = $wpdb->get_var($sql);
											if ($count >= $trial->number_available) {
												continue;
											}
											setlocale(LC_TIME, get_locale());
											$from_time = DateTime::createFromFormat('H:i:s', $trial->start_time)->getTimestamp();
											$to_time = $from_time + $trial->lesson_duration * 60; // 1 hour
											$start_date = DateTime::createFromFormat('Y-m-d', $trial->start_date)->getTimestamp();
											
											$from_time_str = date("H", $from_time) . "h" . date("i", $from_time);
											$to_time_str = date("H", $to_time) . "h" . date("i", $to_time);
											$start_date_str = strftime("%d %b %Y", $start_date);
											$day_of_week = strftime("%A", $start_date);
											$url = home_url() . "/seance-dessai-registration?c={$trial->course_id}&t={$trial->trial_no}";
											$links .= "<div style='color: white; padding-top: 5px; padding-bottom: 5px;'><a href='{$url}'><button class='btn btn-primary'>&gt;&gt; {$day_of_week} {$start_date_str} de {$from_time_str} à {$to_time_str}</button></a></div>";
										}
										if (!empty($links)) {
											echo "
											<div style='margin-left: 56%; background: RGB(65,113,156);'>
												<div style='height: 60px; background: RGB(237,125,49); padding-top: 10px; padding-bottom: 10px; color: white; padding-left: 5px; padding-right: 5px; text-align: left;'>
													Ou bien faites une séance d’essai offerte !
												</div>
												<div style='padding-top: 10px; padding-bottom: 10px; color: white; padding-left: 5px; padding-right: 5px;'>
													{$links}
												</div>
												<div style='text-align: left; font-size: 11.5px; padding-top: 10px; padding-bottom: 10px; color: white; padding-left: 5px; padding-right: 5px;'>
													Vous pourrez vous inscrire et régler votre inscription annuelle à l’issue de la séance d’essai si vous êtes satisfaits !
												</div>
											</div>";
										}
									}
								}?>
                                </div>
							</div>
						</div>
					</article>
				</section>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?>