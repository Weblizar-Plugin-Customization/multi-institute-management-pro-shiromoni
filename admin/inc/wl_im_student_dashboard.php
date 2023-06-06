<?php
defined( 'ABSPATH' ) || die();
// session_start();
require_once WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php';
require_once WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_StudentHelper.php';
require_once WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_PaymentHelper.php';
require_once WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php';

$institute_id = WL_MIM_Helper::get_current_institute_id();

$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

$student = WL_MIM_StudentHelper::get_student();
$notices = WL_MIM_StudentHelper::get_notices( 8 );

if ( ! $student ) {
	die();
}
$id            = $student->id;
$enrollment_id = $student->enrollment_id;
$name          = $student->first_name;
if ( $student->last_name ) {
	$name .= " $student->last_name";
}
$course = WL_MIM_Helper::get_course( $student->course_id );
$course = ( ! empty( $course ) ) ? "{$course->course_name} ({$course->course_code})" : '';

$batch = WL_MIM_Helper::get_batch( $student->batch_id );
if ( ! $batch ) {
	$batch_status = '<strong class="text-warning">' . esc_html__( 'Unknown', WL_MIM_DOMAIN ) . '</strong>';
	$batch_info   = '-';
} else {
	$batch_status = WL_MIM_Helper::get_batch_status( $batch->start_date, $batch->end_date );
	$time_from    = date( 'g:i A', strtotime( $batch->time_from ) );
	$time_to      = date( 'g:i A', strtotime( $batch->time_to ) );
	$timing       = "$time_from - $time_to";
	$batch_info   = $batch->batch_code;
	if ( $batch->batch_name ) {
		$batch_info .= " ( $batch->batch_name ) ( " . $timing . ' )';
	}
}

$pending_fees = 1;

// ANCHOR Stduents certificates Query
global $wpdb;
$certificates = $wpdb->get_results(
	'SELECT cfsr.ID as id, cfsr.certificate_number, cfsr.date_issued, sr.ID as student_id, sr.first_name as student_name, mb.batch_code, mb.batch_name, mc.course_code, mc.course_name, sr.phone, cf.label FROM ' . "{$wpdb->prefix}" . 'wl_min_certificate_student' . ' as cfsr
JOIN ' . "{$wpdb->prefix}" . 'wl_min_certificate' . ' as cf ON cf.ID = cfsr.certificate_id
JOIN ' . "{$wpdb->prefix}" . 'wl_min_students' . ' as sr ON sr.ID = cfsr.student_record_id
JOIN ' . "{$wpdb->prefix}" . 'wl_min_batches' . ' as mb ON mb.id = sr.batch_id
JOIN ' . "{$wpdb->prefix}" . 'wl_min_courses' . ' as mc ON mc.id = sr.course_id
WHERE sr.ID = ' . absint( $id )
);

?>
<style>
	body{
		font-size: 18px !important;
	}
	#adminmenu .wp-submenu a{
		font-size: 18px !important;
	}#adminmenu .wp-menu-name{
		font-size: 18px !important;
	}

</style>

<div class="container-fluid wl_im_container">
	<!-- row 1 -->
	<div class="row">
		<div class="col">
			<!-- main header content -->
			<h1 class="display-4 text-center">
				<span class=""><?php esc_html_e( 'Shiromani Institute Private Limited', WL_MIM_DOMAIN ); ?></span>
			</h1>
			<!-- <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can find your details.', WL_MIM_DOMAIN ); ?>
			</div> -->
			<!-- end main header content -->
		</div>
	</div>
	<!-- end - row 1 -->

	<!-- row 2 -->
	<div class="row">
		<div class="card col">
			<div class="card-header">
				<!-- card header content -->
				<div class="row">
					<div class="col-md-12 wlim-noticboard-background pt-2 pb-2">
						<div class="wlim-student-heading text-center display-4">
							<span class="text-white"><?php esc_html_e( 'Welcome', WL_MIM_DOMAIN ); ?>
								<span class="wlim-student-name-heading"><?php echo esc_html( $student->first_name ); ?></span> !</span>
						</div>
					</div>
				</div>
				<!-- end - card header content -->
			</div>
			<div class="card-body">
				<!-- card body content -->
				<div class="row">
					<div class="card col-sm-6 col-xs-12">
						<div class="card-header wlim-noticboard-background">
							<h5 class="text-white border-light"><?php esc_html_e( 'Your Details', WL_MIM_DOMAIN ); ?></h5>
						</div>
						<ul class="list-group list-group-flush">
							<?php
					
							if (get_option( 'multi_institute_enable_seprate_enrollment_id', $institute_id )) {
								$student_id = $row->enrollment_id;
							} else {
								$student_id = $row->id;
							}
							
							?>
							<li class="list-group-item mt-2">
								<strong><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></strong>:&nbsp;
								<?php echo WL_MIM_Helper::get_enrollment_id_with_prefix( $student->enrollment_id, $general_enrollment_prefix ); ?>
							</li>
							<li class="list-group-item">
								<strong><?php esc_html_e( 'Name', WL_MIM_DOMAIN ); ?></strong>:&nbsp;
								<?php echo esc_html( $name ); ?>
							</li>
							<li class="list-group-item">
								<strong><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></strong>:&nbsp;
								<?php echo esc_html( $course ); ?>
							</li>
							<?php
							if ( $batch ) {
								?>
								<li class="list-group-item">
									<strong><?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?></strong>:&nbsp;
									<?php echo esc_html( $batch_info ); ?>
								</li>
								<?php
							}
							?>
							<!-- <li class="list-group-item">
								<strong><?php esc_html_e( 'Batch Status', WL_MIM_DOMAIN ); ?></strong>:&nbsp;
								<?php
								echo wp_kses(
									$batch_status,
									array(
										'strong' => array(
											'class' => 'text-danger',
											'text-primary',
											'text-success',
											'text-warning',
										),
									)
								);
								?>
							</li> -->
							<li class="list-group-item">
								<strong><?php esc_html_e( 'ID Card', WL_MIM_DOMAIN ); ?></strong>:
								<a class="ml-2" href="#print-student" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="<?php echo esc_attr( $id ); ?>"><i class="fa fa-print"></i></a>
							</li>

						</ul>
					</div>
					<div class="card col-sm-6 col-xs-12">
						<div class="">
							<!-- card header content -->
							<div class="card-header wlim-noticboard-background">
								<h5 class="text-white border-light"><?php esc_html_e( 'Pay Fees', WL_MIM_DOMAIN ); ?></h5>
							</div>
							<!-- end - card header content -->
						</div>
						<div class="card-body">
							<?php
							if ( isset( $_SESSION['payment'] ) && ! empty( $_SESSION['payment'] ) ) {
								extract( $_SESSION );
								?>
								<div class="alert <?php echo $payment['type'] === 'success' ? ' alert-success' : ' alert-danger'; ?>" role="alert">
									<span class="wlim-student-fee-status"><i class="fa fa-clock-o"></i> <?php esc_html_e( $payment['message'] . ' for amount ', WL_MIM_DOMAIN ); ?></span><strong class="wlim-student-fee-amount"><?php echo WL_MIM_PaymentHelper::get_currency_symbol_institute( $institute_id ) . $payment['amount']; ?></strong>
								</div>
								<?php
							}
							if ( isset( $_SESSION['payment'] ) ) {
								unset( $_SESSION['payment'] );
							};
							?>
							<!-- card body content -->
							<?php
							if ( $pending_fees > 0 ) {
								?>
								<!-- <div class="alert alert-info" role="alert">
						<span class="wlim-student-fee-status"><i class="fa fa-clock-o"></i> <?php esc_html_e( 'You have pending fee: ', WL_MIM_DOMAIN ); ?></span><strong class="wlim-student-fee-amount"><?php echo WL_MIM_PaymentHelper::get_currency_symbol_institute( $institute_id ) . $pending_fees; ?></strong>
					</div> -->
								<?php
								if ( ! WL_MIM_PaymentHelper::payment_methods_unavailable_institute( $institute_id ) ) {
									?>
									<div class="row">
										<div class="col-md-6 wlim-pay-fees-now">
											<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-pay-fees">
												<?php $nonce = wp_create_nonce( 'pay-fees' ); ?>
												<input type="hidden" name="pay-fees" value="<?php echo esc_attr( $nonce ); ?>">
												<input type="hidden" name="action" value="wl-mim-pay-fees">
												<input type="hidden" name="current_page_url" value="<?php menu_page_url( 'multi-institute-management-student-dashboard' ); ?>">

												<label class="col-form-label mb-2">
													<strong class="text-dark"><?php esc_html_e( 'Fee Payment', WL_MIM_DOMAIN ); ?>:</strong>
												</label>
												<!-- <div class="form-group">
										<label class="radio-inline mr-3">
											<input checked type="radio" name="fee_payment" value="total_pending_fee" id="wlim-payment-total-pending-fee"><?php esc_html_e( 'Pay Total Pending Fee', WL_MIM_DOMAIN ); ?>
											-
											<strong><?php echo WL_MIM_PaymentHelper::get_currency_symbol_institute( $institute_id ) . $pending_fees; ?></strong>
										</label>
									</div> -->
												<div class="form-group">
													<label class="radio-inline mr-3">
														<input type="radio" name="fee_payment" value="individual_fee" id="wlim-payment-individual-fee"><?php esc_html_e( 'Show Installments', WL_MIM_DOMAIN ); ?>
													</label>
												</div>
												<div class="fee_types_box wlim-payment-individual-fee">
													<table class="table table-bordered">
														<thead>
															<tr>
																<th><?php esc_html_e( 'Installment', WL_MIM_DOMAIN ); ?></th>
																<th><?php esc_html_e( 'Amount Pending', WL_MIM_DOMAIN ); ?></th>
																<th><?php esc_html_e( 'Status', WL_MIM_DOMAIN ); ?></th>
															</tr>
														</thead>
														<tbody class="fee_types_rows fee_types_table">
															<?php
															$invoices = $wpdb->get_results( "SELECT i.id, i.fees, i.invoice_title, i.status, i.created_at, i.added_by, s.first_name, s.last_name, s.enrollment_id, i.payable_amount, i.due_date_amount, i.due_date as student_id FROM {$wpdb->prefix}wl_min_invoices as i, {$wpdb->prefix}wl_min_students as s WHERE i.student_id = s.id AND i.student_id=$student_id AND i.institute_id = $institute_id ORDER BY i.id DESC" );

															?>
															<?php
															foreach ( $invoices as $invoice ) {

																?>
																<tr>
																	<td>
																		<span class="text-dark"><?php echo esc_html( $invoice->invoice_title ); ?></span>
																	</td>
																	<td>
																		<span class="text-dark"><?php echo number_format( $invoice->payable_amount, 2, '.', '' ); ?></span>
																	</td>
																	<td>
																		<span class="text-dark"><?php echo ucwords( $invoice->status ); ?></span>
																	</td>
																</tr>
																<?php
															}
															?>
														</tbody>
														<tfoot>
															<tr>
																<!-- <th><span><?php esc_html_e( 'Total', WL_MIM_DOMAIN ); ?></span></th> -->
																<!-- <th><span><?php echo esc_html( $pending_fees ); ?></span></th> -->
															</tr>
														</tfoot>
													</table>

													<div class="form-group pt-3">
														<label for="wlim-invoice-id" class="col-form-label"><?php esc_html_e( 'Select Installment', WL_MIM_DOMAIN ); ?>:</label>
														<select name="invoice_id" class="form-control selectpicker" id="wlim-invoice-id" data-student_id="<?php echo esc_attr( $student->id ); ?>" data-live-search="true">
															<option value="">-------- <?php esc_html_e( 'Select Installment', WL_MIM_DOMAIN ); ?> --------</option>
															<?php
															foreach ( $invoices as $invoice ) {
																$invoice_number = WL_MIM_Helper::get_invoice( $invoice->id );
																$invoice_title  = $invoice->invoice_title;
																?>
																<?php if ( $invoice->status !== 'paid' ) : ?>
																	<option value="<?php echo esc_attr( $invoice->id ); ?>"><?php echo esc_html( $invoice_title . ' ( ' . $invoice_number . ' )' ); ?></option>
																<?php endif ?>
																<?php
															}
															?>
														</select>
													</div>

												</div>
												<div class="form-group">
													<label class="col-form-label">
														<strong class="text-dark"><?php esc_html_e( 'Payment Method', WL_MIM_DOMAIN ); ?>:</strong>
													</label>
													<br>
													<div class="row mt-2">
														<div class="col-sm-12">
															<?php
															if ( WL_MIM_PaymentHelper::razorpay_enabled_institute( $institute_id ) ) {
																?>
																<label class="radio-inline mr-3">
																	<input checked type="radio" name="payment_method" class="mr-2" value="razorpay" id="wlim-payment-razorpay"><?php esc_html_e( 'Razorpay', WL_MIM_DOMAIN ); ?>
																</label>
																<?php
															}
															if ( WL_MIM_PaymentHelper::instamojo_enabled_institute( $institute_id ) ) {
																?>
																<label class="radio-inline mr-3">
																	<input checked type="radio" name="payment_method" class="mr-2" value="instamojo" id="wlim-payment-instamojo"><?php esc_html_e( 'Instamojo', WL_MIM_DOMAIN ); ?>
																</label>
																<?php
															}
															if ( WL_MIM_PaymentHelper::paystack_enabled_institute( $institute_id ) ) {
																?>
																<label class="radio-inline mr-3">
																	<input checked type="radio" name="payment_method" class="mr-2" value="paystack" id="wlim-payment-paystack"><?php esc_html_e( 'Paystack', WL_MIM_DOMAIN ); ?>
																</label>
																<?php
															}
															if ( WL_MIM_PaymentHelper::stripe_enabled_institute( $institute_id ) ) {
																?>
																<label class="radio-inline mr-3">
																	<input checked type="radio" name="payment_method" class="mr-2" value="stripe" id="wlim-payment-stripe"><?php esc_html_e( 'Stripe', WL_MIM_DOMAIN ); ?>
																</label>
																<?php
															}
															if ( WL_MIM_PaymentHelper::paypal_enabled_institute( $institute_id ) ) {
																?>
																<label class="radio-inline mr-3">
																	<input checked type="radio" name="payment_method" class="mr-2" value="paypal" id="wlim-payment-paypal"><?php esc_html_e( 'Paypal', WL_MIM_DOMAIN ); ?>
																</label>
																<?php
															}
															?>
														</div>
													</div>
												</div>
												<button type="submit" class="mt-2 float-right btn btn-primary pay-fees-submit"><?php esc_html_e( 'Pay Now', WL_MIM_DOMAIN ); ?></button>
											</form>
										</div>
									</div>
									<?php
								}
							} else {
								?>
								<div class="alert alert-success" role="alert">
									<span class="wlim-student-fee-status"><i class="fa fa-check"></i> <?php esc_html_e( 'No pending fees.', WL_MIM_DOMAIN ); ?></span>
								</div>
								<?php
							}
							?>
							<!-- end - card body content -->
						</div>
					</div>


				</div>
				<!-- end - card body content -->
			</div>
		</div>
	</div>
	<!-- end - row 2 -->

	<!-- row 3 -->
	<div class="row">
		<div class="card col">
			<div class="card-header wlim-noticboard-background">
				<h5 class="text-white border-light"><?php esc_html_e( 'Noticeboard', WL_MIM_DOMAIN ); ?></h5>
			</div>
			<div class="card-body">
				<?php
				if ( count( $notices ) > 0 ) {
					?>
					<div class="wlim-noticeboard-section">
						<ul class="wlim-noticeboard">
							<?php
							foreach ( $notices as $key => $notice ) {
								if ( $notice->link_to == 'url' ) {
									$link_to = $notice->url;
								} elseif ( $notice->link_to == 'attachment' ) {
									$link_to = wp_get_attachment_url( $notice->attachment );
								} else {
									$link_to = '#';
								}
								?>
								<li class="mb-3">
									<span class="wlim-noticeboard-notice font-weight-bold">&#9656; </span>
									<a class="wlim-noticeboard-notice" target="_blank" href="<?php echo esc_url( $link_to ); ?>"><?php echo stripcslashes( $notice->title ); ?>
										(<?php echo date_format( date_create( $notice->created_at ), 'd M, Y' ); ?>
										)</a>
									<?php
									if ( $key < 3 ) {
										?>
										<img class="ml-1" src="<?php echo WL_MIM_PLUGIN_URL . 'assets/images/newicon.gif'; ?>">
										<?php
									}
									?>
								</li>
								<?php
							}
							?>
						</ul>
					</div>
					<div class="mt-4 mr-3 float-right">
						<a class="wlim-view-all-notice text-dark font-weight-bold" href="<?php menu_page_url( 'multi-institute-management-student-noticeboard' ); ?>"><?php esc_html_e( 'View all', WL_MIM_DOMAIN ); ?></a>
					</div>
					<?php
				} else {
					?>
					<span class="text-dark"><?php esc_html_e( 'There is no notice.', WL_MIM_DOMAIN ); ?></span>
					<?php
				}
				?>
			</div>
		</div>
	</div>
	<!-- end - row 3 -->
</div>

<!-- print student modal -->
<div class="modal fade" id="print-student" tabindex="-1" role="dialog" aria-labelledby="print-student-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" id="print-student-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title w-100 text-center" id="print-student-label"><?php esc_html_e( 'View and Print Student', WL_MIM_DOMAIN ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body pr-4 pl-4" id="print_student"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
			</div>
		</div>
	</div>
</div><!-- end - print student modal -->

<!-- print student admission detail modal -->
<div class="modal fade" id="print-student-admission-detail" tabindex="-1" role="dialog" aria-labelledby="print-student-admission-detail-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" id="print-student-admission-detail-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title w-100 text-center" id="print-student-admission-detail-label"><?php esc_html_e( 'View and Print Admission Detail', WL_MIM_DOMAIN ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body pr-4 pl-4" id="print_student_admission_detail"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
			</div>
		</div>
	</div>
</div><!-- end - print student admission detail modal -->

<!-- print student fees report -->
<div class="modal fade" id="print-student-fees-report" tabindex="-1" role="dialog" aria-labelledby="print-student-fees-report-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" id="print-student-fees-report-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title w-100 text-center" id="print-student-fees-report-label"><?php esc_html_e( 'View and Print Fees Report', WL_MIM_DOMAIN ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body pr-4 pl-4" id="print_student_fees_report"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
			</div>
		</div>
	</div>
</div><!-- end - print student fees report -->

<!-- print student certificate modal -->
<div class="modal fade" id="print-student-certificate" tabindex="-1" role="dialog" aria-labelledby="print-student-certificate-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" id="print-student-certificate-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title w-100 text-center" id="print-student-certificate-label"><?php esc_html_e( 'View and Print Completion Certificate', WL_MIM_DOMAIN ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body pr-4 pl-4" id="print_student_certificate"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
			</div>
		</div>
	</div>
</div><!-- end - print student certificate modal -->
