<?php
defined( 'ABSPATH' ) or die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

class WL_MIM_Invoice {
	/* Get invoice data to display on table */
	public static function get_invoice_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}

		$start_date  = ( isset( $_REQUEST['start_date'] ) && ! empty( $_REQUEST['start_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['start_date'] ) ) ) : NULL;
		$end_date  = ( isset( $_REQUEST['end_date'] ) && ! empty( $_REQUEST['end_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['end_date'] ) ) ) : NULL;


		global $wpdb;
		$institute_id              = WL_MIM_Helper::get_current_institute_id();
		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$data = $wpdb->get_results( "SELECT i.id, i.fees, i.invoice_title, i.status, i.created_at, i.added_by, s.first_name, s.last_name, s.enrollment_id, s.phone, i.payable_amount, i.due_date_amount, i.due_date, s.id as student_id FROM {$wpdb->prefix}wl_min_invoices as i, {$wpdb->prefix}wl_min_students as s WHERE i.student_id = s.id AND i.institute_id = $institute_id ORDER BY i.id DESC" );

		if ($start_date && $end_date) {
			$data = $wpdb->get_results( "SELECT i.id, i.fees, i.invoice_title, i.status, i.created_at, i.added_by, s.first_name, s.last_name, s.enrollment_id, s.phone, i.payable_amount, i.due_date_amount, i.due_date, s.id as student_id FROM {$wpdb->prefix}wl_min_invoices as i, {$wpdb->prefix}wl_min_students as s WHERE i.student_id = s.id AND i.institute_id = $institute_id AND i.due_date BETWEEN CAST('$start_date' AS DATE) AND CAST('$end_date' AS DATE) ORDER BY i.id DESC" );
		}
		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id             = $row->id;
				$invoice_number = WL_MIM_Helper::get_invoice( $id );
				$invoice_title  = $row->invoice_title;
				$status_text    = ucwords( $row->status );
				$status         = ( $row->status == 'paid' ) ? "<strong class='text-success'>$status_text</strong>" : "<strong class='text-danger'>$status_text</strong>";
				$amount         =  number_format( $row->payable_amount, 2, '.', '' ) ;
				$date           = date_format( date_create( $row->created_at ), "d-m-Y" );
				// $due_date       = date_format( date_create( $row->due_date ), "d-m-Y" );
				$added_by       = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

				$pending_amount = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_installments WHERE institute_id = $institute_id AND invoice_id = $row->id ORDER BY id DESC" );

				if ($pending_amount) {
					$due_amount=0;
					foreach ($pending_amount as $pending) {
						$due_amount += $pending->paid_amount;
					}
					$total_due_amount = ($amount - $due_amount);
				} else {
						$total_due_amount = $amount;
				}

				$phone = $row->phone;
				$student_name = $row->first_name;
				if ( $row->last_name ) {
					$student_name .= " $row->last_name";
				}
				if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
					$student_id = $row->enrollment_id;
				} else {
					$student_id = $row->id;
				}
				$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );

				$results["data"][] = array(
					esc_html( $invoice_number ) . '<a class="ml-2" href="#print-invoice-fee-invoice" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . esc_html( $id ) . '"><i class="fa fa-print"></i></a>',
					esc_html( $invoice_title ),
					esc_html( $amount ),
					esc_html( $enrollment_id ),
					esc_html( $student_name ),
					esc_html( $phone ),
					esc_html( $total_due_amount ),
					wp_kses( $status, array( 'strong' => array( 'class' => 'text-danger', 'text-success' ) ) ),
					esc_html( date_format( date_create( $row->due_date ), "d-m-Y" ) ),
					esc_html( $added_by ),
					esc_html( $date ),
					$row->status != 'paid' ? '<a class="mr-3" href="#update-invoice" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . esc_html( $id ) . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-invoice-security="' . wp_create_nonce( "delete-invoice-$id" ) . '"delete-invoice-id="' . esc_html( $id ) . '" class="delete-invoice"> <i class="fa fa-trash text-danger"></i></a>' : '' . '<a href="javascript:void(0)" delete-invoice-security="' . wp_create_nonce( "delete-invoice-$id" ) . '"delete-invoice-id="' . esc_html( $id ) . '" class="delete-invoice"> <i class="fa fa-trash text-danger"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
			$results["paid"] = array();
			$results["pending"] = array();
		}
		wp_send_json( $results );
	}

	public static function get_student_invoice_data() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id              = WL_MIM_Helper::get_current_institute_id();
		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$user_id = get_current_user_id();

		$student_id = $wpdb->get_row("SELECT id FROM {$wpdb->prefix}wl_min_students WHERE `user_id`=$user_id;");
		$student_id = $student_id->id;

		$data = $wpdb->get_results( "SELECT i.id, i.fees, i.invoice_title, i.status, i.created_at, i.added_by, s.first_name, s.last_name, s.enrollment_id, i.payable_amount, i.due_date_amount, i.due_date as student_id FROM {$wpdb->prefix}wl_min_invoices as i, {$wpdb->prefix}wl_min_students as s WHERE i.student_id = s.id AND i.student_id=$student_id AND i.institute_id = $institute_id ORDER BY i.id DESC" );

		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id             = $row->id;
				$invoice_number  = WL_MIM_Helper::get_invoice( $id );
				$invoice_title   = $row->invoice_title;
				$status_text     = ucwords( $row->status );
				$status          = ( $row->status == 'paid' ) ? "<strong class='text-success'>$status_text</strong>" : "<strong class='text-danger'>$status_text</strong>";
				$amount          = number_format( $row->payable_amount, 2, '.', '' );
				$due_date_amount = number_format( $row->due_date_amount, 2, '.', '' );
				$date            = date_format( date_create( $row->created_at ), "d-m-Y" );
				$due_date        = date_format( date_create( $row->due_date ), "d-m-Y" );
				$added_by        = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

				$student_name = $row->first_name;
				if ( $row->last_name ) {
					$student_name .= " $row->last_name";
				}
				if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
					$student_id = $row->enrollment_id;
				} else {
					$student_id = $row->id;
				}
				$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );

				$results["data"][] = array(
					esc_html( $invoice_title ),
					esc_html( $amount ),
					esc_html( $due_date ),
					esc_html( $due_date_amount ),
					wp_kses( $status, array( 'strong' => array( 'class' => 'text-danger', 'text-success' ) ) ),
					esc_html( $date ),
					// '<a class="mr-3 btn btn-primary" href="#pay" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . esc_html( $id ) . '">Pay Now</a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	/* Fetch Fee */
	public static function fetch_fees() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id      = intval( sanitize_text_field( $_POST['id'] ) );
		$student = $wpdb->get_row( "SELECT fees, course_id FROM {$wpdb->prefix}wl_min_students WHERE institute_id = $institute_id AND is_deleted = 0 AND is_active = 1 AND id = $id" );
		if ( ! $student ) {
			die();
		}

		$course = $wpdb->get_row( "SELECT course_code, course_name, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE id = $student->course_id AND institute_id = $institute_id" );
		if ( ! $course ) {
			die();
		}

		$duration_in_month = WL_MIM_Helper::get_course_months_count( $course->duration, $course->duration_in );

		// $fees         = unserialize( $student->fees );
		// $pending_fees = number_format( WL_MIM_Helper::get_fees_total( $fees['payable'] ) - WL_MIM_Helper::get_fees_total( $fees['paid'] ), 2, '.', '' );

		ob_start();
		?>
		<div class="form-group">
			<label for="wlim-invoice-title" class="col-form-label"><?php esc_html_e( 'Installment Title', WL_MIM_DOMAIN ); ?>:</label>
			<input name="invoice_title" type="text" class="form-control" id="wlim-invoice-title"  value="<?php echo date_i18n( "F Y" ); ?>"  placeholder="<?php esc_attr_e( "Installment Title", WL_MIM_DOMAIN ); ?>">
		</div>
		<div class="form-group">
			<label for="wlim-invoice-payable" class="col-form-label"><?php esc_html_e( 'Installment Payable Amount', WL_MIM_DOMAIN ); ?>:</label>
			<input name="invoice_payable_amount" type="text" class="form-control" id="wlim-invoice-payable" placeholder="<?php esc_attr_e( "Installment Payable", WL_MIM_DOMAIN ); ?>">
		</div>
		<div class="form-group">
			<label for="wlim-invoice-due-date" class="col-form-label"><?php esc_html_e( 'Installment Due Date', WL_MIM_DOMAIN ); ?>:</label>
			<input name="invoice_due_date" type="text" class="form-control" id="wlim-invoice-due-date" placeholder="<?php esc_attr_e( "Installment Payable", WL_MIM_DOMAIN ); ?>" required>
		</div>
		<div class="form-group">
			<label for="wlim-invoice-due-date-amount" class="col-form-label"><?php esc_html_e( 'Installment Due Date Amount', WL_MIM_DOMAIN ); ?>:</label>
			<input name="invoice_due_date_amount" type="text" class="form-control" id="wlim-invoice-due-date-amount" placeholder="<?php esc_attr_e( "Installment Payable", WL_MIM_DOMAIN ); ?>">
		</div>

        <div class="form-group">
            <label for="wlim-invoice-created_at" class="col-form-label">* <strong><?php esc_html_e( 'Installment Date', WL_MIM_DOMAIN ); ?>:</strong></label>
            <input name="created_at" type="text" class="form-control wlim-created_at" id="wlim-invoice-created_at" placeholder="<?php esc_html_e( "Date", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( date('d-m-Y') ); ?>">
        </div>
		<?php
		$html = ob_get_clean();

		$wlim_created_at_selector = '.wlim-created_at';

		$json = json_encode( array(
			// 'created_at_exist'         => boolval( $row->created_at ),
			'wlim_created_at_selector' => esc_attr( $wlim_created_at_selector ),
			// 'created_at'               => esc_attr( $row->created_at ),
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Add new invoice */
	public static function add_invoice() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-invoice'], 'add-invoice' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$student_id      = isset( $_POST['student'] ) ? intval( sanitize_text_field( $_POST['student'] ) ) : NULL;
		$invoice_title   = isset( $_POST['invoice_title'] ) ? sanitize_text_field( $_POST['invoice_title'] ) : '';
		$payable_amount  = isset( $_POST['invoice_payable_amount'] ) ? sanitize_text_field( $_POST['invoice_payable_amount'] ) : '';
		$due_date_amount = isset( $_POST['invoice_due_date_amount'] ) ? sanitize_text_field( $_POST['invoice_due_date_amount'] ) : '';
		$created_at      = ( isset( $_POST['created_at'] ) && ! empty( $_POST['created_at'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['created_at'] ) ) ) : NULL;
		$due_date        = ( isset( $_POST['invoice_due_date'] ) && ! empty( $_POST['invoice_due_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['invoice_due_date'] ) ) ) : NULL;

		$errors = array();
		if ( empty( $invoice_title ) ) {
			$errors['invoice_title'] = esc_html__( 'Please provide a unique invoice title.', WL_MIM_DOMAIN );
		}

		if ( empty( $due_date ) ) {
			$errors['invoice_due_date'] = esc_html__( 'Please provide a Due Date.', WL_MIM_DOMAIN );
		}

		if ( strlen( $invoice_title ) > 191 ) {
			$errors['invoice_title'] = esc_html__( 'Maximum length cannot exceed 191 characters.', WL_MIM_DOMAIN );
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_invoices WHERE invoice_title = '$invoice_title' AND student_id = $student_id AND institute_id = $institute_id" );

		if ( $count ) {
			$errors['invoice_title'] = esc_html__( 'Installment title already exists.', WL_MIM_DOMAIN );
		}

		if ( empty( $payable_amount ) ) {
			wp_send_json_error( esc_html__( 'Please provide a valid invoice amount.', WL_MIM_DOMAIN ) );
		}

		$student = $wpdb->get_row( "SELECT fees, course_id FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND is_active = 1 AND id = $student_id AND institute_id = $institute_id" );

		if ( ! $student ) {
			$errors['student'] = esc_html__( 'Please select a valid student.', WL_MIM_DOMAIN );
		}

		$course = $wpdb->get_row( "SELECT fees FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND id = $student->course_id AND institute_id = $institute_id" );

		if ( ! $course ) {
			$errors['student'] = esc_html__( 'Student is not enrolled in any course.', WL_MIM_DOMAIN );
		}

		if ( count( $errors ) < 1 ) {
			try {
			  	$wpdb->query( 'BEGIN;' );

				$data = array(
					'invoice_title'  => $invoice_title,
					'payable_amount' => $payable_amount,
					'due_date_amount'=> $due_date_amount,
					'student_id'     => $student_id,
					'created_at'     => $created_at,
					'due_date'       => $due_date,
					'invoice_date'   => $created_at,
					'added_by'       => get_current_user_id(),
					'institute_id'   => $institute_id
				);

				$data['created_at'] = current_time( 'Y-m-d H:i:s' );

				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_invoices", $data );
				if ( ! $success ) {
		  			throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

		  		$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Installment added successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
		  		$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch invoice to update */
	public static function fetch_invoice() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$id   = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_invoices WHERE id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}
		$student = $wpdb->get_row( "SELECT id, course_id, fees, first_name, last_name FROM {$wpdb->prefix}wl_min_students WHERE id = $row->student_id AND institute_id = $institute_id" );
		if ( ! $student ) {
			die();
		}
		$course = $wpdb->get_row( "SELECT course_code, course_name, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE id = $student->course_id" );
		if ( ! $course ) {
			die();
		}

		$duration_in_month = WL_MIM_Helper::get_course_months_count( $course->duration, $course->duration_in );

		// $fees         = unserialize( $student->fees );
		// $pending_fees = number_format( WL_MIM_Helper::get_fees_total( $fees['payable'] ) - WL_MIM_Helper::get_fees_total( $fees['paid'] ), 2, '.', '' );
		// $invoice      = unserialize( $row->fees );

		$created_at = date_format( date_create( $row->created_at ), "d-m-Y" );
		$due_date = date_format( date_create( $row->due_date ), "d-m-Y" );

		?>
		<form id="wlim-update-invoice-form">
			<?php $nonce = wp_create_nonce( "update-invoice-$id" ); ?>
		    <input type="hidden" name="update-invoice-<?php echo $id; ?>" value="<?php echo $nonce; ?>">
			<div class="row" id="wlim-student-enrollment_id">
				<div class="col">
					<label  class="col-form-label pb-0"><?php _e( 'Student', WL_MIM_DOMAIN ); ?>:</label>
					<div class="card mb-3 mt-2">
						<div class="card-block">
						<?php if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
                                            $student_id = $student->enrollment_id;
                                        } else {
                                            $student_id = $student->id;
                                        } ?>
		    				<span class="text-dark"><?php echo $student->first_name . " " . $student->last_name; ?> (<?php echo WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix ); ?>)</span>
		  				</div>
					</div>

					<div class="form-group">
						<label for="wlim-invoice-title_update" class="col-form-label"><?php _e( 'Installment Title', WL_MIM_DOMAIN ); ?>:</label>
						<input name="invoice_title" type="text" class="form-control" id="wlim-invoice-title_update" placeholder="<?php _e( "Installment Title", WL_MIM_DOMAIN ); ?>" value="<?php echo $row->invoice_title; ?>">
					</div>

					<div class="form-group">
						<label for="wlim-invoice-payable" class="col-form-label"><?php esc_html_e( 'Installment Payable Amount', WL_MIM_DOMAIN ); ?>:</label>
						<input name="invoice_payable_amount" type="text" class="form-control" id="wlim-invoice-payable" placeholder="<?php esc_attr_e( "Installment Payable", WL_MIM_DOMAIN ); ?>" value="<?php echo $row->payable_amount; ?>">
					</div>
					<div class="form-group">
						<label for="wlim-invoice-due-date" class="col-form-label"><?php esc_html_e( 'Installment Due Date', WL_MIM_DOMAIN ); ?>:</label>
						<input name="invoice_due_date" type="text" class="form-control wlim-created_at" id="wlim-invoice-due-date" placeholder="<?php esc_attr_e( "Installment Payable", WL_MIM_DOMAIN ); ?>" value="<?php echo $due_date; ?>">
					</div>
					<div class="form-group">
						<label for="wlim-invoice-due-date-amount" class="col-form-label"><?php esc_html_e( 'Installment Due Date Amount', WL_MIM_DOMAIN ); ?>:</label>
						<input name="invoice_due_date_amount" type="text" class="form-control" id="wlim-invoice-due-date-amount" placeholder="<?php esc_attr_e( "Installment Payable", WL_MIM_DOMAIN ); ?>" value="<?php echo $row->due_date_amount; ?>">
					</div>

			        <div class="form-group">
			            <label for="wlim-installment-created_at_update" class="col-form-label">* <strong><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?>:</strong></label>
			            <input name="created_at" type="text" class="form-control wlim-created_at_update" id="wlim-installment-created_at_update" placeholder="<?php esc_html_e( "Date", WL_MIM_DOMAIN ); ?>" <?php echo esc_attr( $created_at ); ?>>
			        </div>
				</div>
			</div>
			<input type="hidden" name="invoice_id" value="<?php echo $row->id; ?>">
		</form>
	<?php
		$html = ob_get_clean();

		$wlim_created_at_selector = '.wlim-created_at_update';

		$json = json_encode( array(
			// 'wlim_date_selector'       => esc_attr( $wlim_date_selector ),
			'wlim_created_at_selector' => esc_attr( $wlim_created_at_selector ),
			'created_at_exist'         => boolval( $row->created_at ),
			'created_at'               => esc_attr( $row->created_at ),
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Update invoice */
	public static function update_invoice() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['invoice_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-invoice-$id"], "update-invoice-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$invoice_title = isset( $_POST['invoice_title'] ) ? sanitize_text_field( $_POST['invoice_title'] ) : '';
		$payable_amount = isset( $_POST['invoice_payable_amount'] ) ? sanitize_text_field( $_POST['invoice_payable_amount'] ) : '';
		$due_date_amount = isset( $_POST['invoice_due_date_amount'] ) ? sanitize_text_field( $_POST['invoice_due_date_amount'] ) : '';
		$created_at    = ( isset( $_POST['created_at'] ) && ! empty( $_POST['created_at'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['created_at'] ) ) ) : NULL;
		$invoice_due_date    = ( isset( $_POST['invoice_due_date'] ) && ! empty( $_POST['invoice_due_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['invoice_due_date'] ) ) ) : NULL;

		$errors = array();
		$invoice = $wpdb->get_row( "SELECT fees, student_id, status FROM {$wpdb->prefix}wl_min_invoices WHERE id = $id AND status = 'pending' AND institute_id = $institute_id" );

		if ( ! $invoice ) {
			wp_send_json_error( esc_html__( "Installment not found.", WL_MIM_DOMAIN ) );
		}

		$student = $wpdb->get_row( "SELECT id, fees, course_id FROM {$wpdb->prefix}wl_min_students WHERE id = $invoice->student_id AND institute_id = $institute_id" );

		if ( ! $student ) {
			wp_send_json_error( esc_html__( "Student doesn't exist for this invoice.", WL_MIM_DOMAIN ) );
		}

		if ( empty( $invoice_title ) ) {
			$errors['invoice_title'] = esc_html__( 'Please provide a unique invoice title.', WL_MIM_DOMAIN );
		}

		if ( strlen( $invoice_title ) > 191 ) {
			$errors['invoice_title'] = esc_html__( 'Maximum length cannot exceed 191 characters.', WL_MIM_DOMAIN );
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_invoices WHERE invoice_title = '$invoice_title' AND id != $id AND student_id = {$student->id} AND institute_id = $institute_id" );

		if ( $count ) {
			$errors['invoice_title'] = esc_html__( 'Installment title already exists.', WL_MIM_DOMAIN );
		}

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

			  	$invoice = serialize( $invoice );

				$data = array(
					'invoice_title'   => $invoice_title,
					'payable_amount'  => $payable_amount,
					'due_date_amount' => $due_date_amount,
					'created_at'      => $created_at,
					'due_date'        => $invoice_due_date,
					'updated_at'      => date('Y-m-d H:i:s')
				);

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_invoices", $data, array( 'id' => $id, 'institute_id' => $institute_id ) );
				if ( $success === false ) {
		  			throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Installment updated successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
		  		$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* View and print invoice fee invoice */
	public static function print_invoice_fee_invoice() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_invoices WHERE id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}
		$student = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE id = $row->student_id AND institute_id = $institute_id" );
		if ( ! $student ) {
			die();
		}
		// $fees     = unserialize( $student->fees );
		// $invoices = unserialize( $row->fees );
		?>
		<div class="row">
			<div class="col">
				<div class="mb-3 mt-2">
					<div class="text-center">
						<button type="button" id="wl-invoice-fee-invoice-print" class="btn btn-sm btn-success"><i class="fa fa-print text-white"></i>&nbsp;<?php esc_html_e( 'Print Fee Installment', WL_MIM_DOMAIN ); ?></button><hr>
					</div>
					<div>
						<?php require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/partials/wl_im_fee_invoice.php' ); ?>
	  				</div>
				</div>
			</div>
		</div>
	<?php
		die();
	}

	/* Delete invoice */
	public static function delete_invoice() {

		if (!current_user_can('administrator')) {
			die();
		}
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-invoice-$id"], "delete-invoice-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		try {
			$invoice = $wpdb->get_row( "SELECT fees, student_id FROM {$wpdb->prefix}wl_min_invoices WHERE id = $id AND institute_id = $institute_id" );

			if ( ! $invoice ) {
	  			throw new Exception( esc_html__( 'Installment not found.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->delete( "{$wpdb->prefix}wl_min_invoices", array( 'id' => $id, 'institute_id' => $institute_id ) );
			if ( $success === false ) {
	  			throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array( 'message' => esc_html__( 'Installment removed successfully.', WL_MIM_DOMAIN ) ) );
		} catch ( Exception $exception ) {
	  		$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}

	/* Check permission to manage invoice */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_fees' ) || ! $institute_id ) {
			die();
		}
	}
}
