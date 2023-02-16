<?php
defined( 'ABSPATH' ) or die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_SettingHelper.php' );

$institute_id         = WL_MIM_Helper::get_current_institute_id();
$wlim_active_students = WL_MIM_Helper::get_active_students();

$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

$general_institute = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );

if ( empty( $general_institute['institute_name'] ) ) {
	$institute_name = WL_MIM_Helper::get_current_institute_name();
} else {
	$institute_name = $general_institute['institute_name'];
}
?>

<div class="container-fluid wl_im_container">
    <!-- row 1 -->
    <!-- <div class="row">
        <div class="col">

            <h1 class="text-center wlim-institute-dashboard-title">
                <span class="border-bottom"><i class="fa fa-tachometer"></i> <?php echo esc_html( $institute_name ); ?></span>
            </h1>
            <h2 class="text-center font-weight-normal">
                <span class="border-bottom"><i class="fa fa-usd"></i> <?php esc_html_e( 'Installments', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can find invoices or add a new invoice.', WL_MIM_DOMAIN ); ?>
            </div>

        </div>
    </div> -->
    <!-- end - row 1 -->

	<!-- row 2 -->
	<div class="row">
		<div class="card col">
			<div class="card-header bg-primary text-white">
				<!-- card header content -->
				<div class="row">
                    <div class="col-md-9 col-xs-12">
						<div class="h4"><?php esc_html_e( 'Manage Installments', WL_MIM_DOMAIN ); ?></div>
					</div>
                    <div class="col-md-3 col-xs-12">
                        <button type="button" class="btn btn-outline-light float-right add-invoice" data-toggle="modal" data-target="#add-invoice" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Installment', WL_MIM_DOMAIN ); ?>
                        </button>
					</div>
				</div>
				<!-- end - card header content -->
			</div>
			<div class="card-body">

				<?php if ( current_user_can( 'wl_min_manage_enquiries' ) ) { ?>
                        <div class="col-md-12 col-sm-12 col-xs-4 ">
                            <div class="row text-center">
                                <!-- <div class="col-md-12">
                                    <ul class="list-group">
                                        <li class="list-group-item active h5"><i class="fa fa-envelope"></i>
                                            <?php esc_html_e( 'Total Installments Count', WL_MIM_DOMAIN ); ?>
                                        </li>
                                    </ul>
                                </div> -->
                                <?php
                                $yesterday = new DateTime( '-1day' );
                                $today     = new DateTime();
                                $tommorow  = new DateTime( '+1day' );

                                $yesterday = $yesterday->format('Y-m-d');
                                $today     = $today->format('Y-m-d');
                                $tommorow  = $tommorow->format('Y-m-d');

                                global $wpdb;

                                $total_pending = $wpdb->get_var( "SELECT sum(payable_amount) FROM {$wpdb->prefix}wl_min_invoices WHERE institute_id = $institute_id AND `status` = 'pending'" );
                                $total_paid    = $wpdb->get_var( "SELECT sum(payable_amount) FROM {$wpdb->prefix}wl_min_invoices WHERE institute_id = $institute_id AND `status` = 'paid'" );
                                $total_installments = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_invoices WHERE institute_id = $institute_id" );
                                ?>
								<div class="col-md-3 col-sm-4 col-xs-2 mb-4">
									<ul class="list-group">
										<li class="list-group-item active h5"><i class="fa fa-graduation-cap"></i>
											<span href="<?php menu_page_url( 'multi-institute-management-courses' ); ?>" class="text-white">
												<?php esc_html_e( 'Total', WL_MIM_DOMAIN ); ?>
											</span>
										</li>
										<li class="list-group-item h6">
											<span class="text-secondary"><?php esc_html_e( 'Total Pending', WL_MIM_DOMAIN ); ?>:</span>&nbsp;<span><?php echo esc_html( $total_pending ); ?></span>
										</li>
										<li class="list-group-item h6">
											<span class="text-secondary"><?php esc_html_e( 'Total Paid', WL_MIM_DOMAIN ); ?>:</span>&nbsp;<span><?php echo esc_html( $total_paid ); ?></span>
										</li>
										<li class="list-group-item h6">
											<span class="text-secondary"><?php esc_html_e( 'Installments', WL_MIM_DOMAIN ); ?>:</span>&nbsp;<span><?php echo esc_html( $total_installments ); ?></span>
										</li>
									</ul>
								</div>

								<?php
								$yesterday_pending = $wpdb->get_var( "SELECT sum(payable_amount) FROM {$wpdb->prefix}wl_min_invoices WHERE institute_id = $institute_id AND `status` = 'pending' AND due_date = '$yesterday'" );
                                $yesterday_paid    = $wpdb->get_var( "SELECT sum(payable_amount) FROM {$wpdb->prefix}wl_min_invoices WHERE institute_id = $institute_id AND `status` = 'paid' AND due_date = '$yesterday'" );
                                $yesterday_installments = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_invoices WHERE institute_id = $institute_id AND due_date = '$yesterday'" );
								?>

								<div class="col-md-3 col-sm-4 col-xs-2 mb-4">
									<ul class="list-group">
										<li class="list-group-item active h5"><i class="fa fa-graduation-cap"></i>
											<span href="<?php menu_page_url( 'multi-institute-management-courses' ); ?>" class="text-white">
												<?php esc_html_e( 'Yesterday', WL_MIM_DOMAIN ); ?>
											</span>
										</li>
										<li class="list-group-item h6">
											<span class="text-secondary"><?php esc_html_e( 'Yesterday Pending', WL_MIM_DOMAIN ); ?>:</span>&nbsp;<span><?php echo esc_html( $yesterday_pending ); ?></span>
										</li>
										<li class="list-group-item h6">
											<span class="text-secondary"><?php esc_html_e( 'Yesterday Paid', WL_MIM_DOMAIN ); ?>:</span>&nbsp;<span><?php echo esc_html( $yesterday_paid ); ?></span>
										</li>
										<li class="list-group-item h6">
											<span class="text-secondary"><?php esc_html_e( 'Installments', WL_MIM_DOMAIN ); ?>:</span>&nbsp;<span><?php echo esc_html( $yesterday_installments ); ?></span>
										</li>
									</ul>
								</div>

								<?php
								$today_pending = $wpdb->get_var( "SELECT sum(payable_amount) FROM {$wpdb->prefix}wl_min_invoices WHERE institute_id = $institute_id AND `status` = 'pending' AND due_date = '$today'" );
                                $today_paid    = $wpdb->get_var( "SELECT sum(payable_amount) FROM {$wpdb->prefix}wl_min_invoices WHERE institute_id = $institute_id AND `status` = 'paid' AND due_date = '$today'" );
                                $today_installments = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_invoices WHERE institute_id = $institute_id AND due_date = '$today'" );
								?>

								<div class="col-md-3 col-sm-4 col-xs-2 mb-4">
									<ul class="list-group">
										<li class="list-group-item active h5"><i class="fa fa-graduation-cap"></i>
											<span href="<?php menu_page_url( 'multi-institute-management-courses' ); ?>" class="text-white">
												<?php esc_html_e( 'Today', WL_MIM_DOMAIN ); ?>
											</span>
										</li>
										<li class="list-group-item h6">
											<span class="text-secondary"><?php esc_html_e( 'Today Pending', WL_MIM_DOMAIN ); ?>:</span>&nbsp;<span><?php echo esc_html( $today_pending ); ?></span>
										</li>
										<li class="list-group-item h6">
											<span class="text-secondary"><?php esc_html_e( 'Today Paid', WL_MIM_DOMAIN ); ?>:</span>&nbsp;<span><?php echo esc_html( $today_paid ); ?></span>
										</li>
										<li class="list-group-item h6">
											<span class="text-secondary"><?php esc_html_e( 'Installments', WL_MIM_DOMAIN ); ?>:</span>&nbsp;<span><?php echo esc_html( $today_installments ); ?></span>
										</li>
									</ul>
								</div>

								<?php
								$tommorow_pending = $wpdb->get_var( "SELECT sum(payable_amount) FROM {$wpdb->prefix}wl_min_invoices WHERE institute_id = $institute_id AND `status` = 'pending' AND due_date = '$tommorow'" );
                                $tommorow_paid    = $wpdb->get_var( "SELECT sum(payable_amount) FROM {$wpdb->prefix}wl_min_invoices WHERE institute_id = $institute_id AND `status` = 'paid' AND due_date = '$tommorow'" );
                                $tommorow_installments = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_invoices WHERE institute_id = $institute_id AND due_date = '$tommorow'" );
								?>

								<div class="col-md-3 col-sm-4 col-xs-2 mb-4">
									<ul class="list-group">
										<li class="list-group-item active h5"><i class="fa fa-graduation-cap"></i>
											<span href="<?php menu_page_url( 'multi-institute-management-courses' ); ?>" class="text-white">
												<?php esc_html_e( 'Tomorrow', WL_MIM_DOMAIN ); ?>
											</span>
										</li>
										<li class="list-group-item h6">
											<span class="text-secondary"><?php esc_html_e( 'Tomorrow Pending', WL_MIM_DOMAIN ); ?>:</span>&nbsp;<span><?php echo esc_html( $tommorow_pending ); ?></span>
										</li>
										<li class="list-group-item h6">
											<span class="text-secondary"><?php esc_html_e( 'Tomorrow Paid', WL_MIM_DOMAIN ); ?>:</span>&nbsp;<span><?php echo esc_html( $tommorow_paid ); ?></span>
										</li>
										<li class="list-group-item h6">
											<span class="text-secondary"><?php esc_html_e( 'Installments', WL_MIM_DOMAIN ); ?>:</span>&nbsp;<span><?php echo esc_html( $tommorow_installments ); ?></span>
										</li>
									</ul>
								</div>
                            </div>
                         </div>
				<?php } ?>

				<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-invoice-filter-form" enctype="multipart/form-data">
						<div class="row">
							<div class="col-sm-12 col-md-3">
								<div class="form-group">
									<label for="wlim-invoice_start" class="col-form-label"><?php esc_html_e( 'From', WL_MIM_DOMAIN ); ?>:</label>
									<input name="start_date" type="text" class="form-control wlim-filter-reminder" id="wlim-invoice_start" placeholder="<?php esc_html_e( "From", WL_MIM_DOMAIN ); ?>">
								</div>
							</div>
							<div class="col-sm-12 col-md-3">
								<div class="form-group">
									<label for="wlim-invoice_end" class="col-form-label"><?php esc_html_e( 'To', WL_MIM_DOMAIN ); ?>:</label>
									<input name="end_date" type="text" class="form-control wlim-filter-reminder" id="wlim-invoice_end" placeholder="<?php esc_html_e( "To", WL_MIM_DOMAIN ); ?>">
								</div>
							</div>
							<div class="col-sm-12 col-md-3">
								<div class="form-group col-6">
									<label for="wlim-invoice_end" class="col-form-label"><?php esc_html_e( '', WL_MIM_DOMAIN ); ?>.</label>
									<button type="button" class="btn btn-primary btn-sm filter-invoice-submit form-control" id="wlim-invoice-filter"><?php esc_html_e( "Filter", WL_MIM_DOMAIN ); ?></button>
								</div>
							</div>
						</div>
						<div class="row mb-4 text-left">
							<div class="col-sm-12 col-md-3">

							</div>
						</div>
				</form>

				<!-- card body content -->
				<div class="row">
					<div class="col">
						<table class="table table-hover table-striped table-bordered" id="invoice-table">
							<thead>
								<tr>
						        	<th scope="col"><?php esc_html_e( 'Installment No.', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Installment Title', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Amount Payable', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Student Name', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Phone NO.', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Due Amount', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Status', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Due Date', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Added On', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Edit', WL_MIM_DOMAIN ); ?></th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
				<!-- end - card body content -->
			</div>
		</div>
	</div>
	<!-- end - row 2 -->
</div>

<!-- add new invoice modal -->
<div class="modal fade" id="add-invoice" tabindex="-1" role="dialog" aria-labelledby="add-invoice-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="add-invoice-label"><?php esc_html_e( 'Add New Installment', WL_MIM_DOMAIN ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body pr-4 pl-4">
				<form id="wlim-add-invoice-form">
					<?php $nonce = wp_create_nonce( 'add-invoice' ); ?>
	                <input type="hidden" name="add-invoice" value="<?php echo $nonce; ?>">
	                <div class="wlim-add-invoice-form-fields">
						<div class="form-group">
	                        <label for="wlim-invoice-student" class="col-form-label"><?php esc_html_e( "Student", WL_MIM_DOMAIN ); ?>:</label>
	                        <select name="student" class="form-control selectpicker" id="wlim-invoice-student" data-live-search="true">
	                            <option value="">-------- <?php esc_html_e( "Select a Student", WL_MIM_DOMAIN ); ?> --------</option>
	                        <?php
	                        if ( count( $wlim_active_students ) > 0 ) {
	                            foreach ( $wlim_active_students as $active_student ) {
									if (get_option( 'multi_institute_enable_seprate_enrollment_id', 'yes' )) {
										$student_id = $active_student->enrollment_id;
									} else {
										$student_id = $active_student->id;
									}
									?>
	                            <option value="<?php echo $active_student->id; ?>"><?php echo "$active_student->first_name $active_student->last_name (" . WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix ) . ")"; ?></option>
	                        <?php
	                            }
	                        } ?>
	                        </select>
	                        <div id="wlim_add_invoice_fetch_fees"></div>
	                	</div>
	                </div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
				<button type="button" class="btn btn-primary add-invoice-submit"><?php esc_html_e( 'Add New Installment', WL_MIM_DOMAIN ); ?></button>
			</div>
		</div>
	</div>
</div>
<!-- end - add new invoice modal -->

<!-- update invoice modal -->
<div class="modal fade" id="update-invoice" tabindex="-1" role="dialog" aria-labelledby="update-invoice-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="update-invoice-label"><?php esc_html_e( 'Update Installment', WL_MIM_DOMAIN ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body pr-4 pl-4" id="fetch_invoice"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
				<?php if (current_user_can( 'wl_min_edit_fee' ) ): ?>
					<button type="button" class="btn btn-primary update-invoice-submit"><?php esc_html_e( 'Update Installment', WL_MIM_DOMAIN ); ?></button>
				<?php endif ?>
			</div>
		</div>
	</div>
</div>
<!-- end - update invoice modal -->

<!-- print invoice fee invoice -->
<div class="modal fade" id="print-invoice-fee-invoice" tabindex="-1" role="dialog" aria-labelledby="print-invoice-fee-invoice-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" id="print-invoice-fee-invoice-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title w-100 text-center" id="print-invoice-fee-invoice-label"><?php esc_html_e( 'View and Print Fee Installment Fee', WL_MIM_DOMAIN ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body pr-4 pl-4" id="print_invoice_fee_invoice"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
			</div>
		</div>
	</div>
</div>
<!-- end - print invoice fee invoice -->