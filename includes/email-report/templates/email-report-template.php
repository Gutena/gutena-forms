<?php
/**
 * Gutena Forms Email Report Template
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

$plugin_url    		  = defined( 'GUTENA_FORMS_PLUGIN_URL' ) ? GUTENA_FORMS_PLUGIN_URL : '';
$has_pro       		  = is_gutena_forms_pro();
$forms_data           = apply_filters( 'gutena_forms__get_entries', array() );
$total_entries        = apply_filters( 'gutena_forms__get_total_entries', 0 );
$total_entries_change = apply_filters( 'gutena_forms__get_total_entries_change', 0 );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php bloginfo( 'name' ); ?> - <?php esc_html_e( 'Weekly Form Report', 'gutena-forms' ); ?></title>
	<!--[if mso]>
		<style type="text/css">
			table, td { border-collapse: collapse !important; }
		</style>
	<![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
	<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0; padding: 0; width: 100%; background-color: #f5f5f5;">
		<tr>
			<td align="center" style="padding: 40px 20px;">
				<!-- Main Container -->
				<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width: 600px; width: 100%; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">

					<!-- Header Section -->
					<tr>
						<td style="padding: 40px 40px 30px 40px; text-align: center;">
							<!-- Logo -->
							<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
								<tr>
									<td align="center" style="padding-bottom: 20px;">
										<img src="<?php echo esc_url( $plugin_url . 'assets/img/email/header-logo.png' ); ?>" alt="<?php echo esc_attr__( 'Gutena Forms', 'gutena-forms' ); ?>" style="display: block; max-width: 200px; height: auto; margin: 0 auto;">
									</td>
								</tr>
								<tr>
									<td align="center" style="padding-top: 10px;">
										<h1 style="margin: 0; font-size: 24px; font-weight: 700; color: #21222F; line-height: 1.2;"><?php echo esc_html__( 'Weekly Forms Summary', 'gutena-forms' ); ?></h1>
									</td>
								</tr>
							</table>
						</td>
					</tr>

					<!-- Introduction Section -->
					<tr>
						<td style="padding: 0 40px 30px 40px;">
							<p style="margin: 0 0 10px 0; font-size: 14px; line-height: 1.6; color: #21222F; font-weight: 500;"><?php echo esc_html__( 'Hi there,', 'gutena-forms' ); ?></p>
							<p style="margin: 0; font-size: 14px; line-height: 1.6; color: #21222F; font-weight: 500;"><?php echo esc_html__( "Here's a quick look at how your forms performed over the past week.", 'gutena-forms' ); ?></p>
						</td>
					</tr>

					<!-- Total Entries Summary Card -->
					<tr>
						<td style="padding: 0 40px 30px 40px;">
							<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #FFF; border-radius: 10px; border: 1px solid #DFF2EE; padding: 10px 20px;">
								<tr>
									<td width="40" style="padding-right: 16px; vertical-align: middle;">
										<!-- Icon Square -->
										<img src="<?php echo esc_url( $plugin_url . 'assets/img/email/total-entries-form.png' ); ?>" alt="<?php echo esc_attr__( 'Total Entries', 'gutena-forms' ); ?>" style="display: block;">
									</td>
									<td style="vertical-align: middle; padding-right: 16px;">
										<p style="margin: 0; font-size: 16px; font-weight: 700; color: #21222F;"><?php echo esc_html__( 'Total entries this week:', 'gutena-forms' ); ?></p>
									</td>
									<td align="right" style="vertical-align: middle;">
										<table role="presentation" cellspacing="0" cellpadding="0" border="0">
											<tr>
												<td style="border: 1px solid #0DA88C; border-radius: 6px; padding: 8px 16px; background-color: #ffffff;">
													<span style="font-size: 18px; font-weight: 700; color: #0DA88C;">
														<?php echo esc_html( $total_entries ); ?>
													</span>
												</td>
												<td style="padding-left: 20px;">
													<?php if ( $has_pro ) : ?>
														<?php $percentage = Gutena_Forms_Email_Reports::calculate_percentage_change( $total_entries, $total_entries_change ); ?>
														<?php
															$arrow_image = $percentage >= 0 ? 'arrow-up.png' : 'arrow-down.png';
															$rate_color  = $percentage >= 0 ? '#0DA88C' : '#A04';
														?>
														<span style="font-size: 12px; color: <?php echo esc_attr( $rate_color ); ?>;font-weight: 700;">
															<img src="<?php echo esc_url( $plugin_url . 'assets/img/email/' . $arrow_image ); ?>" alt="<?php echo $percentage >= 0 ? '↑' : '↓'; ?>" style="display: inline-block; vertical-align: middle; opacity: 0.8;">
															<?php echo esc_html( abs( $percentage ) ); ?>%
														</span>
													<?php endif; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>

					<!-- Forms Performance Table -->
					<tr>
						<td style="padding: 0 40px 30px 40px;">
							<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse: collapse; border-radius: 10px; border: 1px solid #DFF2EE; overflow: hidden;">
								<!-- Header Row -->
								<tr>
									<td style="padding: 12px 16px; background-color: #F0F8F7; border-bottom: 1px solid #DFF2EE;">
										<p style="margin: 0; font-size: 14px; font-weight: 600; color: #1F2937;"><?php echo esc_html__( 'Form', 'gutena-forms' ); ?></p>
									</td>
									<td align="right" style="padding: 12px 16px; background-color: #F0F8F7; border-bottom: 1px solid #DFF2EE;">
										<p style="margin: 0; font-size: 14px; font-weight: 600; color: #1F2937;"><?php echo esc_html__( 'Entries', 'gutena-forms' ); ?></p>
									</td>
									<td align="right" style="padding: 12px 16px; background-color: #F0F8F7; border-bottom: 1px solid #DFF2EE;">
										<!--	Placeholder	-->
									</td>
								</tr>

								<?php foreach ( $forms_data as $form ) : ?>

									<tr>
										<td style="padding: 12px 16px; border-bottom: 1px solid #DFF2EE;">
											<p style="margin: 0; font-size: 14px; font-weight: 400; color: #21222F;">
												<?php echo esc_html( $form['form_name'] ); ?>
											</p>
										</td>
										<td align="right" style="padding: 12px 16px; border-bottom: 1px solid #DFF2EE;">
											<span style="font-size: 14px; font-weight: 700; color: #0DA88C; margin-right: 20px;">
												<?php echo esc_html( $form['entries_count'] ); ?>
											</span>
										</td>
										<td align="right" style="padding: 12px 16px; border-bottom: 1px solid #DFF2EE;">
											<?php if ( $has_pro ) : ?>
												<?php
													$conversion_rate = Gutena_Forms_Email_Reports::calculate_percentage_change( $form['entries_count'], $form['conversion_rate'] );
													$arrow_image     = $conversion_rate >= 0 ? 'arrow-up.png' : 'arrow-down.png';
													$rate_color      = $conversion_rate >= 0 ? '#0DA88C' : '#A04';
												?>

												<span style="font-size: 12px; color: <?php echo esc_attr( $rate_color ); ?>; font-weight: 700;">
													<img src="<?php echo esc_url( $plugin_url . 'assets/img/email/' . $arrow_image ); ?>" alt="<?php echo $conversion_rate >= 0 ? '↑' : '↓'; ?>" style="display: inline-block; vertical-align: middle; opacity: 0.8;">
													<?php echo esc_html( abs( $conversion_rate ) ); ?>%
												</span>
											<?php endif; ?>
										</td>
									</tr>

								<?php endforeach; ?>
							</table>
						</td>
					</tr>

					<!-- Closing Section -->
					<tr>
						<td style="padding: 0 40px 40px 40px;">
							<p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #21222F; font-weight: 500;"><?php echo esc_html__( "Thanks for using Gutena Forms — we're happy to help you keep track of your submissions!", 'gutena-forms' ); ?></p>
							<p style="margin: 0 0 8px 0; font-size: 14px; line-height: 1.6; color: #21222F; font-weight: 500;"><?php echo esc_html__( 'Warm regards,', 'gutena-forms' ); ?></p>
							<p style="margin: 0; font-size: 14px; line-height: 1.6; color: #0DA88C; font-weight: 700;"><?php echo esc_html__( 'The Gutena Forms Team', 'gutena-forms' ); ?></p>
						</td>
					</tr>

					<?php if ( ! $has_pro ) : ?>

						<!-- Footer Promotional Banner -->
						<tr>
							<td style="padding: 0;">
								<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #E6FFFA; border-radius: 0 0 12px 12px;">
									<tr>
										<td style="padding: 40px;">
											<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
												<tr>
													<!-- Left Column: Features -->
													<td width="70%" valign="top" style="padding-right: 20px;">
														<!-- Logo -->
														<table role="presentation" cellspacing="0" cellpadding="0" border="0">
															<tr>
																<td style="padding-bottom: 24px;">
																	<img src="<?php echo esc_url( $plugin_url . 'assets/img/email/footer-logo.png' ); ?>" alt="<?php echo esc_attr__( 'Gutena Forms', 'gutena-forms' ); ?>" style="display: block; max-width: 180px; height: auto;">
																</td>
															</tr>
														</table>
														<!-- Features List -->
														<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
															<tr>
																<td width="50%" valign="top" style="padding-bottom: 12px; padding-right: 12px;">
																	<table role="presentation" cellspacing="0" cellpadding="0" border="0">
																		<tr>
																			<td width="20" valign="top" style="padding-right: 8px; padding-top: 2px;">
																				<img src="<?php echo esc_url( $plugin_url . 'assets/img/email/check.png' ); ?>" alt="✓" style="display: block;">
																			</td>
																			<td valign="top">
																				<p style="margin: 0; font-size: 10px; line-height: 1.5; color: #015D61; font-weight: 700;"><?php echo esc_html__( 'Advance Filter for Entries', 'gutena-forms' ); ?></p>
																			</td>
																		</tr>
																	</table>
																</td>
																<td width="50%" valign="top" style="padding-bottom: 12px;">
																	<table role="presentation" cellspacing="0" cellpadding="0" border="0">
																		<tr>
																			<td width="20" valign="top" style="padding-right: 8px; padding-top: 2px;">
																				<img src="<?php echo esc_url( $plugin_url . 'assets/img/email/check.png' ); ?>" alt="✓" style="display: block;">
																			</td>
																			<td valign="top">
																				<p style="margin: 0; font-size: 10px; line-height: 1.5; color: #015D61; font-weight: 700;"><?php echo esc_html__( 'Tags Management', 'gutena-forms' ); ?></p>
																			</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td width="50%" valign="top" style="padding-bottom: 12px; padding-right: 12px;">
																	<table role="presentation" cellspacing="0" cellpadding="0" border="0">
																		<tr>
																			<td width="20" valign="top" style="padding-right: 8px; padding-top: 2px;">
																				<img src="<?php echo esc_url( $plugin_url . 'assets/img/email/check.png' ); ?>" alt="✓" style="display: block;">
																			</td>
																			<td valign="top">
																				<p style="margin: 0; font-size: 10px; line-height: 1.5; color: #015D61; font-weight: 700;"><?php echo esc_html__( 'Entry Notes', 'gutena-forms' ); ?></p>
																			</td>
																		</tr>
																	</table>
																</td>
																<td width="50%" valign="top" style="padding-bottom: 12px;">
																	<table role="presentation" cellspacing="0" cellpadding="0" border="0">
																		<tr>
																			<td width="20" valign="top" style="padding-right: 8px; padding-top: 2px;">
																				<img src="<?php echo esc_url( $plugin_url . 'assets/img/email/check.png' ); ?>" alt="✓" style="display: block;">
																			</td>
																			<td valign="top">
																				<p style="margin: 0; font-size: 10px; line-height: 1.5; color: #015D61; font-weight: 700;"><?php echo esc_html__( 'User Access Management', 'gutena-forms' ); ?></p>
																			</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td width="50%" valign="top" style="padding-bottom: 12px; padding-right: 12px;">
																	<table role="presentation" cellspacing="0" cellpadding="0" border="0">
																		<tr>
																			<td width="20" valign="top" style="padding-right: 8px; padding-top: 2px;">
																				<img src="<?php echo esc_url( $plugin_url . 'assets/img/email/check.png' ); ?>" alt="✓" style="display: block;">
																			</td>
																			<td valign="top">
																				<p style="margin: 0; font-size: 10px; line-height: 1.5; color: #015D61; font-weight: 700;"><?php echo esc_html__( 'Status Management', 'gutena-forms' ); ?></p>
																			</td>
																		</tr>
																	</table>
																</td>
																<td width="50%" valign="top" style="padding-bottom: 24px;">
																	<table role="presentation" cellspacing="0" cellpadding="0" border="0">
																		<tr>
																			<td width="20" valign="top" style="padding-right: 8px; padding-top: 2px;">
																				<img src="<?php echo esc_url( $plugin_url . 'assets/img/email/check.png' ); ?>" alt="✓" style="display: block;">
																			</td>
																			<td valign="top">
																				<p style="margin: 0; font-size: 10px; line-height: 1.5; color: #015D61; font-weight: 700;"><?php echo esc_html__( 'Premium Support', 'gutena-forms' ); ?></p>
																			</td>
																		</tr>
																	</table>
																</td>
															</tr>
														</table>
														<!-- Get Pro Now Button -->
														<table role="presentation" cellspacing="0" cellpadding="0" border="0">
															<tr>
																<td style="padding-top: 8px;">
																	<a href="#" style="display: inline-block; width: 110px; height: 25px; background: linear-gradient(90deg, #F6B642 0%, #FFD382 100%); background-color: #F6B642; color: #21222F; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 700; text-align: center; line-height: 25px; box-sizing: border-box; vertical-align: middle;"><?php echo esc_html__( 'Get Pro Now', 'gutena-forms' ); ?></a>
																</td>
															</tr>
														</table>
													</td>
													<!-- Right Column: Form Illustration -->
													<td width="30%" valign="top" align="right">
														<img src="<?php echo esc_url( $plugin_url . 'assets/img/email/footer-form.png' ); ?>" alt="<?php echo esc_attr__( 'Form Illustration', 'gutena-forms' ); ?>" style="display: block;">
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>

					<?php endif; ?>

				</table>
			</td>
		</tr>
	</table>
</body>
</html>
