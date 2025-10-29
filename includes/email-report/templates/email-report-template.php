<?php
/**
 * Email template for Gutena Forms Email Reports
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

$total_entries = apply_filters( 'gutena_forms__get_total_entries', 0, 'week' );
$entries 	   = apply_filters( 'gutena_forms__get_entries', array(), 'week' );
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
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5;">
	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
		<tr>
			<td align="center" style="padding: 20px 0; background-color: #f5f5f5;">
				<!-- Main Container -->
				<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background: #ffffff; border-radius: 10px; border-collapse: collapse; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

					<!-- Header Section -->
					<tr>
						<td align="center" style="padding: 50px 30px 40px 30px; border-bottom: 1px solid #eeeeee;">
							<!-- Logo Container -->
							<table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom: 30px; border-collapse: collapse;">
								<tr>
									<td align="center" style="vertical-align: middle;">
										<img src="<?php echo esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/email/header-logo.png' ); ?>" alt="<?php esc_attr_e( 'Gutena Logo', 'gutena-forms' ); ?>" style="display: inline-block; vertical-align: middle; margin-right: 10px;">
									</td>
								</tr>
							</table>
						<!-- Report Title -->
						<h1 style="color: #21222F; font-size: 24px; font-weight: 700; margin: 0;"><?php esc_html_e( 'Email Summary Report', 'gutena-forms' ); ?></h1>
							</td>
						</tr>

					<!-- Content Section -->
					<tr>
						<td style="padding: 40px 30px;">
						<!-- Intro Text -->
						<p style="color: #21222F; font-size: 14px; font-weight: 500; line-height: 1.6; margin: 0 0 30px 0;">
							<?php esc_html_e( 'Hi there,', 'gutena-forms' ); ?><br><br>
							<?php esc_html_e( "Here's a quick look at how your forms performed over the past week.", 'gutena-forms' ); ?><br><br>
							<?php esc_html_e( "We've gathered the total number of submissions along with a form-by-form breakdown for you.", 'gutena-forms' ); ?>
							</p>

							<!-- Total Entries Box -->
							<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background: #f8f8f7; border: 1px solid #dff2ee; border-radius: 10px; padding: 20px; margin: 30px 0; border-collapse: collapse;">
								<tr>
									<td style="padding: 20px 10px; font-size: 16px; color: #21222F; font-weight: 700;"><?php esc_html_e( 'Total entries this week:', 'gutena-forms' ); ?></td>
									<td align="right" style="padding: 20px 10px;">
										<span style="background: #0DA88C; color: #FFF; padding: 10px 20px; border-radius: 10px; font-size: 24px; font-weight: 700; display: inline-block;">
											<?php echo esc_html( $total_entries ); ?>
										</span>
									</td>
								</tr>
							</table>

					<!-- Form Breakdown Title -->
					<h2 style="color: #0DA88C; font-size: 24px; font-weight: 700; margin: 30px 0 20px 0; text-align: center;"><?php esc_html_e( 'Form breakdown', 'gutena-forms' ); ?></h2>

							<?php foreach ( $entries as $entry ) : ?>
								<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background: #f8f8f7; border: 1px solid #dff2ee; border-radius: 10px; margin-bottom: 15px; border-collapse: collapse;">
									<tr>
										<td style="padding: 15px 20px; font-size: 16px; color: #21222F; font-weight: 700;"><?php echo esc_html( $entry['form-name'] ); ?></td>
										<td align="right" style="padding: 15px 20px;">
											<span style="background: #F0F8F7; color: #0DA88C; padding: 8px 16px; border-radius: 10px; font-size: 24px; font-weight: 700; display: inline-block;"><?php echo esc_html( $entry['count'] ); ?></span>
										</td>
									</tr>
								</table>
							<?php endforeach; ?>

						<!-- Closing Text -->
						<p style="color: #21222F; font-size: 14px; font-weight: 500; line-height: 1.8; margin: 30px 0 0 0;">
							<?php esc_html_e( 'These numbers show how many times people filled out and submitted each of your forms. The total at the top adds everything together, giving you an at-a-glance view of your form activity for the week.', 'gutena-forms' ); ?><br><br>
							<?php esc_html_e( "Thanks for using Gutena Forms — we're happy to help you keep track of your submissions!", 'gutena-forms' ); ?><br><br>
							<?php esc_html_e( 'Warm regards.', 'gutena-forms' ); ?>
						</p>

						<!-- Signature -->
						<p style="color: #0DA88C; font-size: 15px; font-weight: 500; margin-top: 20px; margin-bottom: 0;">
							<?php esc_html_e( 'The Gutena Forms Team', 'gutena-forms' ); ?>
						</p>
						</td>
					</tr>

					<!-- Footer Banner -->
					<tr>
						<td style="background: linear-gradient(265deg, #0DA88C -39.82%, #015D61 87.8%); background-color: #015D61; padding: 40px 30px; border-radius: 8px;">
							<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
								<tr>
									<!-- Left Column: Features -->
									<td style="vertical-align: top;">
										<!-- Footer Logo -->
										<table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom: 20px; border-collapse: collapse;">
											<tr>
												<td>
													<img src="<?php echo esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/email/footer-logo.png' ); ?>" alt="<?php esc_attr_e( 'Gutena Logo', 'gutena-forms' ); ?>" style="display: inline-block; vertical-align: middle; margin-right: 10px;">
												</td>
											</tr>
										</table>

										<!-- Features List - Two Columns -->
										<table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom: 25px; border-collapse: collapse; width: 100%;">
											<tr>
												<!-- Left Column -->
												<td style="color: #ffffff; font-size: 10px; padding-right: 20px; vertical-align: top; width: 50%;">
													<table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%;">
														<tr>
															<td style="color: #ffffff; font-size: 10px; font-weight: 700; padding-bottom: 12px;">
																<img src="<?php echo esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/email/check.png' ); ?>" alt="<?php esc_attr_e( 'Check', 'gutena-forms' ); ?>" style="width: 12px; height: 12px; vertical-align: middle; margin-right: 5px; display: inline-block;"> <?php esc_html_e( 'Advance Filter for Entries', 'gutena-forms' ); ?>
															</td>
														</tr>
														<tr>
															<td style="color: #ffffff; font-size: 10px; font-weight: 700; padding-bottom: 12px;">
																<img src="<?php echo esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/email/check.png' ); ?>" alt="<?php esc_attr_e( 'Check', 'gutena-forms' ); ?>" style="width: 12px; height: 12px; vertical-align: middle; margin-right: 5px; display: inline-block;"> <?php esc_html_e( 'Entry Notes', 'gutena-forms' ); ?>
															</td>
														</tr>
														<tr>
															<td style="color: #ffffff; font-size: 10px; font-weight: 700; padding-bottom: 0;">
																<img src="<?php echo esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/email/check.png' ); ?>" alt="<?php esc_attr_e( 'Check', 'gutena-forms' ); ?>" style="width: 12px; height: 12px; vertical-align: middle; margin-right: 5px; display: inline-block;"> <?php esc_html_e( 'Status Management', 'gutena-forms' ); ?>
															</td>
														</tr>
													</table>
												</td>
												<!-- Right Column -->
												<td style="color: #ffffff; font-size: 10px; vertical-align: top; width: 50%;">
													<table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%;">
														<tr>
															<td style="color: #ffffff; font-size: 10px; font-weight: 700; padding-bottom: 12px;">
																<img src="<?php echo esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/email/check.png' ); ?>" alt="<?php esc_attr_e( 'Check', 'gutena-forms' ); ?>" style="width: 12px; height: 12px; vertical-align: middle; margin-right: 5px; display: inline-block;"> <?php esc_html_e( 'Tags Management', 'gutena-forms' ); ?>
															</td>
														</tr>
														<tr>
															<td style="color: #ffffff; font-size: 10px; font-weight: 700; padding-bottom: 12px;">
																<img src="<?php echo esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/email/check.png' ); ?>" alt="<?php esc_attr_e( 'Check', 'gutena-forms' ); ?>" style="width: 12px; height: 12px; vertical-align: middle; margin-right: 5px; display: inline-block;"> <?php esc_html_e( 'User Access Management', 'gutena-forms' ); ?>
							</td>
						</tr>
						<tr>
															<td style="color: #ffffff; font-size: 10px; font-weight: 700; padding-bottom: 0;">
																<img src="<?php echo esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/email/check.png' ); ?>" alt="<?php esc_attr_e( 'Check', 'gutena-forms' ); ?>" style="width: 12px; height: 12px; vertical-align: middle; margin-right: 5px; display: inline-block;"> <?php esc_html_e( 'Premium Support', 'gutena-forms' ); ?>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>

										<!-- CTA Button -->
										<table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
											<tr>
												<td>
													<a href="#" style="background: linear-gradient(90deg, #F6B642 0%, #FFD382 100%); color: #21222F; padding: 10px; border-radius: 4px; text-decoration: none; font-weight: 700; font-size: 12px; display: inline-block; width: 110px; text-align: center;"><?php esc_html_e( 'Get Pro Now', 'gutena-forms' ); ?></a>
												</td>
											</tr>
										</table>
									</td>

									<!-- Right Column: Footer Form Image -->
									<td align="right" width="200" style="vertical-align: top; padding-left: 30px;">
										<img src="<?php echo esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/email/footer-form.png' ); ?>" alt="<?php esc_attr_e( 'Form Preview', 'gutena-forms' ); ?>" style="display: block; max-width: 100%; height: auto;">
									</td>
						</tr>
					</table>
						</td>
					</tr>

				</table>
			</td>
			</tr>
		</table>
	</body>
</html>
