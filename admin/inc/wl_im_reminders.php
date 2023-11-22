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
global $wpdb;
$status_codes = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_custom_fields WHERE institute_id = $institute_id ORDER BY id DESC" );

?>

<div class="container-fluid wl_im_container">
    <!-- row 1 -->
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <!-- <h1 class="text-center wlim-institute-dashboard-title">
                <span class="border-bottom"><i class="fa fa-tachometer"></i> <?php echo esc_html( $institute_name ); ?></span>
            </h1> -->
            <!-- <h2 class="text-center font-weight-normal">
                <span class=""><?php esc_html_e( 'Reminders', WL_MIM_DOMAIN ); ?></span>
            </h2> -->
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <!-- <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can find reminders or add a new reminder.', WL_MIM_DOMAIN ); ?>
            </div> -->
            <!-- end main header content -->
        </div>
    </div>
    <!-- end - row 1 -->

	<!-- row 2 -->
	<div class="row">
		<div class="card col">
			<div class="card-header bg-primary text-white">
				<!-- card header content -->
				<div class="row">
                    <div class="col-md-9 col-xs-12">
						<div class="h4"><?php esc_html_e( 'Manage Reminders', WL_MIM_DOMAIN ); ?></div>
					</div>
                    <div class="col-md-3 col-xs-12">
                        <button type="button" class="btn btn-outline-light float-right add-reminder" data-toggle="modal" data-target="#add-reminder" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Reminder', WL_MIM_DOMAIN ); ?>
                        </button>
					</div>
				</div>
				<!-- end - card header content -->
			</div>
			<div class="card-body">

			<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-reminder-filter-form" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-sm-12 col-md-3">
                            <div class="form-group">
                                <label for="wlim-reminder_start" class="col-form-label"><?php esc_html_e( 'From', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="start_date" type="text" class="form-control wlim-filter-reminder" id="wlim-reminder_start" placeholder="<?php esc_html_e( "From", WL_MIM_DOMAIN ); ?>">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3">
                            <div class="form-group">
                                <label for="wlim-reminder_end" class="col-form-label"><?php esc_html_e( 'To', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="end_date" type="text" class="form-control wlim-filter-reminder" id="wlim-reminder_end" placeholder="<?php esc_html_e( "To", WL_MIM_DOMAIN ); ?>">
                            </div>
                        </div>
						<div class="col-sm-12 col-md-3">
                            <div class="form-group col-6">
                                <label for="wlim-reminder_end" class="col-form-label"><?php esc_html_e( '', WL_MIM_DOMAIN ); ?>.</label>
								<button type="button" class="btn btn-primary btn-sm filter-reminder-submit form-control" id="wlim-reminder-filter"><?php esc_html_e( "Filter", WL_MIM_DOMAIN ); ?></button>
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
						<table class="table table-hover table-striped table-bordered" id="reminder-table">
							<thead>
								<tr>
						        	<th scope="col"><?php esc_html_e( 'ID', WL_MIM_DOMAIN ); ?></th>
						        	<!-- <th scope="col"><?php esc_html_e( 'Title', WL_MIM_DOMAIN ); ?></th> -->
						        	<th scope="col"><?php esc_html_e( 'Message', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Follow Up Date', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Follow Up Time', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Status', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Student Name', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?></th>
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

<!-- add new reminder modal -->
<div class="modal fade" id="add-reminder" tabindex="-1" role="dialog" aria-labelledby="add-reminder-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="add-reminder-label"><?php esc_html_e( 'Add New Reminder', WL_MIM_DOMAIN ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body pr-4 pl-4">
				<form id="wlim-add-reminder-form">
					<?php $nonce = wp_create_nonce( 'add-reminder' ); ?>
	                <input type="hidden" name="add-reminder" value="<?php echo $nonce; ?>">
	                <div class="wlim-add-reminder-form-fields">
						<div class="form-group">
	                        <label for="wlim-reminder-student" class="col-form-label"><?php esc_html_e( "Student", WL_MIM_DOMAIN ); ?>:</label>
	                        <select name="student_id" class="form-control selectpicker" id="wlim-reminder-student" data-live-search="true">
	                            <option value="">-------- <?php esc_html_e( "Select a Student", WL_MIM_DOMAIN ); ?> --------</option>
	                        <?php
	                        if ( count( $wlim_active_students ) > 0 ) {
	                            foreach ( $wlim_active_students as $active_student ) {
									// if (get_option( 'multi_institute_enable_seprate_enrollment_id', 'yes' )) {
										$student_id = $active_student->enrollment_id;
									// } else {
										// $student_id = $active_student->id;
									// }
									?>
	                            <option value="<?php echo $active_student->id; ?>"><?php echo "$active_student->first_name $active_student->last_name (" . WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix ) . ")"; ?></option>
	                        <?php
	                            }
	                        } ?>
	                        </select>
	                	</div>
						<!-- <div class="from-group">
							<label for="wlim-reminder-title" class="col-form-label"><?php esc_html_e( "Title", WL_MIM_DOMAIN ); ?>:</label>
							<input name="title" type="text" class="form-control" id="wlim-reminder-title" placeholder="<?php _e( "Title", WL_MIM_DOMAIN ); ?>">
						</div> -->
						<div class="from-group">
							<label for="wlim-message" class="col-form-label"><?php esc_html_e( "Message", WL_MIM_DOMAIN ); ?>:</label>
							<textarea name="message" class="form-control" id="wlim-message" cols="30" rows="5"></textarea>
						</div>

						<div class="from-group">
							<label for="wlim-followup" class="col-form-label"><?php esc_html_e( "Follow Up Date", WL_MIM_DOMAIN ); ?>:</label>
							<input name="follow_up" type="text" class="form-control wlim-created_at" id="wl-min-reminder" placeholder="<?php _e( "Reminder followup", WL_MIM_DOMAIN ); ?>">
						</div>
						<div class="from-group">
							<label for="wlim-status" class="col-form-label"><?php esc_html_e( "Response Code", WL_MIM_DOMAIN ); ?>:</label>
							<!-- <input name="status" type="text" class="form-control" id="wlim-status" placeholder="<?php _e( "Reminder status", WL_MIM_DOMAIN ); ?>"> -->
							<select name="status" id="status" class="form-control" data-live-search="true">
								<?php foreach ($status_codes as $code): ?>
								<option value="<?= $code->field_name; ?>"><?php echo esc_html($code->field_name); ?></option>
								<?php endforeach ?>
							</select>
						</div>
						<div class="from-group">
							<label for="wlim-followuptime" class="col-form-label"><?php esc_html_e( "Follow Up Time", WL_MIM_DOMAIN ); ?>:</label>
							<input name="follow_up_time" type="time" class="form-control " id="wlim_tt_class_startTime" placeholder="<?php _e( "Follow up time", WL_MIM_DOMAIN ); ?>">
						</div>
	                </div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
				<button type="button" class="btn btn-primary add-reminder-submit"><?php esc_html_e( 'Add New Reminder', WL_MIM_DOMAIN ); ?></button>
			</div>
		</div>
	</div>
</div>
<!-- end - add new reminder modal -->

<!-- update reminder modal -->
<div class="modal fade" id="update-reminder" tabindex="-1" role="dialog" aria-labelledby="update-reminder-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="update-reminder-label"><?php esc_html_e( 'Update Reminder', WL_MIM_DOMAIN ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body pr-4 pl-4" id="fetch_reminder"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
				<button type="button" class="btn btn-primary update-reminder-submit"><?php esc_html_e( 'Update Reminder', WL_MIM_DOMAIN ); ?></button>
			</div>
		</div>
	</div>
</div>
<!-- end - update reminder modal -->

<!-- print reminder fee reminder -->
<!-- <div class="modal fade" id="print-reminder-fee-reminder" tabindex="-1" role="dialog" aria-labelledby="print-reminder-fee-reminder-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" id="print-reminder-fee-reminder-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title w-100 text-center" id="print-reminder-fee-reminder-label"><?php esc_html_e( 'View and Print Fee Reminder Fee', WL_MIM_DOMAIN ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body pr-4 pl-4" id="print_reminder_fee_reminder"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
			</div>
		</div>
	</div>
</div> -->
<!-- end - print reminder fee reminder -->