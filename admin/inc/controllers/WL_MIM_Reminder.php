<?php
defined( 'ABSPATH' ) or die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

class WL_MIM_Reminder {

	public static function get_reminder_data() {
		global $wpdb;
		$institute_id              = WL_MIM_Helper::get_current_institute_id();
		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$start_date  = ( isset( $_REQUEST['start_date'] ) && ! empty( $_REQUEST['start_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['start_date'] ) ) ) : NULL;
		$end_date  = ( isset( $_REQUEST['end_date'] ) && ! empty( $_REQUEST['end_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['end_date'] ) ) ) : NULL;
		$student_id  =  !isset( $_REQUEST['student_id'] ) ? sanitize_text_field( $_REQUEST['student_id'] ) : '';

		if ($start_date && $end_date) {
			$data = $wpdb->get_results( "SELECT r.id, r.title, r.message, r.follow_up, r.created_at, r.status_code, s.first_name, r.added_by, s.last_name, s.enrollment_id, s.phone FROM {$wpdb->prefix}wl_min_reminders as r, {$wpdb->prefix}wl_min_students as s WHERE r.student_id = s.id AND s.institute_id = $institute_id AND r.follow_up BETWEEN CAST('$start_date' AS DATE) AND CAST('$end_date' AS DATE) ORDER BY r.id DESC" );
			
		} else {
			$data = $wpdb->get_results( "SELECT r.id, r.title, r.message, r.follow_up, r.created_at, r.status_code, s.first_name, r.added_by, s.last_name, s.enrollment_id, s.phone FROM {$wpdb->prefix}wl_min_reminders as r, {$wpdb->prefix}wl_min_students as s WHERE r.student_id = s.id AND s.institute_id = $institute_id ORDER BY r.id DESC" );
		}

		if (!empty($student_id)) {
			$data = $wpdb->get_results( "SELECT r.id, r.title, r.message, r.follow_up, r.created_at, r.status_code, s.first_name, r.added_by, s.last_name, s.enrollment_id, s.phone, r.student_id FROM {$wpdb->prefix}wl_min_reminders as r, {$wpdb->prefix}wl_min_students as s WHERE r.student_id = s.id AND s.institute_id = $institute_id AND r.student_id = $student_id ORDER BY r.id DESC" );
		}

		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id         = $row->id;
				$title      = $row->title;
				$message    = ucwords( $row->message );
				$follow_up  = date_format( date_create( $row->follow_up ), "d-m-Y" );
				$status     = $row->status_code ;
				$created_at = date_format( date_create( $row->created_at ), "d-m-Y" );
				$phone      = $row->phone;
				$user_info  = get_userdata($row->added_by);
				$added_by   = $user_info->display_name;

				$student_name = $row->first_name;
				if ( $row->last_name ) {
					$student_name .= " $row->last_name";
				}

				if (current_user_can('administrator')) {
					$delete = '<a href="javascript:void(0)" delete-reminder-security="' . wp_create_nonce( "delete-reminder-$id" ) . '"delete-reminder-id="' . esc_html( $id ) . '" class="delete-reminder"> <i class="fa fa-trash text-danger"></i></a>';
				} else {
					$delete = '';
				}

				$results["data"][] = array(
					esc_html( '' ),
					// esc_html( $title ),
					esc_html( ($message) ),
					esc_html( $follow_up ),
					esc_html( $status ),
					esc_html( $student_name ),
					esc_html( $phone ),
					esc_html( ucfirst($added_by) ),
					esc_html( $created_at ),
					'<a class="mr-3" href="#update-reminder" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . esc_html( $id ) . '"><i class="fa fa-edit"></i></a> '. $delete
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	public static function add_reminder() {
		self::check_permission();
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$reminder_id = isset( $_POST['reminder_id'] ) ? intval( sanitize_text_field( $_POST['reminder_id'] ) ) : NULL;

		$student_id = isset( $_POST['student_id'] ) ? intval( sanitize_text_field( $_POST['student_id'] ) ) : NULL;
		$title      = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$message    = isset( $_POST['message'] ) ? sanitize_text_field( $_POST['message'] ) : '';
		$status     = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';
		$follow_up  = ( isset( $_POST['follow_up'] ) && ! empty( $_POST['follow_up'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['follow_up'] ) ) ) : NULL;

		$errors = array();
		// if ( empty( $title ) ) {
		// 	$errors['title'] = esc_html__( 'Please provide a unique reminder title.', WL_MIM_DOMAIN );
		// }

		// if ( strlen( $title ) > 191 ) {
		// 	$errors['title'] = esc_html__( 'Maximum length cannot exceed 191 characters.', WL_MIM_DOMAIN );
		// }

		if (!$reminder_id) {
			$student = $wpdb->get_row( "SELECT fees, course_id FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND is_active = 1 AND id = $student_id AND institute_id = $institute_id" );

			if ( ! $student ) {
				$errors['student'] = esc_html__( 'Please select a valid student.', WL_MIM_DOMAIN );
			}

		}
		
		if ( count( $errors ) < 1 ) {
			try {
			  	$wpdb->query( 'BEGIN;' );

				if ($reminder_id) {
					$data = array(
						'title'       => $title,
						'message'     => $message,
						'follow_up'   => $follow_up,
						'status_code' => $status,
						'added_by'    => get_current_user_id(),
						'institute_id'=> $institute_id,
					);
					$data['updated_at'] = current_time( 'Y-m-d H:i:s' );
					// var_dump($data); die;
					$success = $wpdb->update( "{$wpdb->prefix}wl_min_reminders", $data, array( 'id' => $reminder_id) );
					if ( ! $success ) {
						throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
					}
				} else {
					$data = array(
						'title'       => $title,
						'message'     => $message,
						'student_id'  => $student_id,
						'institute_id'=> $institute_id,
						'follow_up'   => $follow_up,
						'status_code' => $status,
						'added_by'    => get_current_user_id(),
					);
					$data['created_at'] = current_time( 'Y-m-d H:i:s' );
					$success = $wpdb->insert( "{$wpdb->prefix}wl_min_reminders", $data );
					if ( ! $success ) {
						throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
					}
				}
		  		$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Reminder Updated successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
		  		$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function fetch_reminder() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$id   = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_reminders WHERE id = $id" );

		if ( ! $row ) {
			die();
		}
		$student = $wpdb->get_row( "SELECT id, course_id, fees, first_name, last_name FROM {$wpdb->prefix}wl_min_students WHERE id = $row->student_id AND institute_id = $institute_id" );
		if ( ! $student ) {
			die();
		}

		$created_at = date_format( date_create( $row->created_at ), "d-m-Y" );
		$follow_up = date_format( date_create( $row->follow_up ), "d-m-Y" );

		?>
		<form id="wlim-update-reminder-form">
			<?php $nonce = wp_create_nonce( "update-reminder-$id" ); ?>
		    <input type="hidden" name="update-reminder-<?php echo $id; ?>" value="<?php echo $nonce; ?>">
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

					<!-- <div class="from-group">
						<label for="wlim-reminder-title" class="col-form-label"><?php esc_html_e( "Title", WL_MIM_DOMAIN ); ?>:</label>
						<input name="title" type="text" class="form-control" id="wlim-reminder-title" placeholder="<?php _e( "Title", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr($row->title);?>">
					</div> -->
					<div class="from-group">
						<label for="wlim-message" class="col-form-label"><?php esc_html_e( "Message", WL_MIM_DOMAIN ); ?>:</label>
						<textarea name="message" class="form-control" id="wlim-message" cols="30" rows="5"><?php echo esc_html($row->message);?></textarea>
					</div>
				
					<div class="from-group">
						<label for="wlim-followup" class="col-form-label"><?php esc_html_e( "Follow Up Date", WL_MIM_DOMAIN ); ?>:</label>
						<input name="follow_up" type="text" class="form-control wlim-created_at" id="wl-min-reminder" placeholder="<?php _e( "Reminder followup", WL_MIM_DOMAIN ); ?>" value="<?php echo date('d-m-Y',strtotime($row->follow_up));?>">
					</div>
					<div class="from-group">
						<label for="wlim-status" class="col-form-label"><?php esc_html_e( "Status", WL_MIM_DOMAIN ); ?>:</label>
						<input name="status" type="text" class="form-control" id="wlim-status" placeholder="<?php _e( "Reminder status", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr(($row->status_code));?>" >
					</div>

				</div>
			</div>
			<input type="hidden" name="reminder_id" value="<?php echo $row->id; ?>">
		</form>
	<?php
		$html = ob_get_clean();

		$wlim_created_at_selector = '.wlim-created_at';

		$json = json_encode( array(
			'wlim_created_at_selector' => esc_attr( $wlim_created_at_selector ),
			'created_at_exist'         => boolval( $row->created_at ),
			'created_at'               => esc_attr( $row->created_at ),
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Delete reminder */
	public static function delete_reminder() {

		if (!current_user_can('administrator')) {
			die();
		}
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-reminder-$id"], "delete-reminder-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		try {
			$reminder = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_reminders WHERE id = $id" );

			if ( ! $reminder ) {
	  			throw new Exception( esc_html__( 'Reminder not found.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->delete( "{$wpdb->prefix}wl_min_reminders", array( 'id' => $id ) );
			if ( $success === false ) {
	  			throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array( 'message' => esc_html__( 'Reminder removed successfully.', WL_MIM_DOMAIN ) ) );
		} catch ( Exception $exception ) {
	  		$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}

	/* Check permission to manage reminder */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_reminders' ) || ! $institute_id ) {
			die();
		}
	}
}
