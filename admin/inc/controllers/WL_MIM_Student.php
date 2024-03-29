<?php
defined('ABSPATH') || die();

require_once(WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php');
require_once(WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php');

class WL_MIM_Student
{
	/* Get student data to display on table */
	public static function get_student_data() {
		self::check_permission();
		if (!wp_verify_nonce($_REQUEST['security'], 'wl-ima')) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings($institute_id);

		/* Filters */
		$filter_by_year  = (isset($_REQUEST['filter_by_year']) && !empty($_REQUEST['filter_by_year'])) ? intval(sanitize_text_field($_REQUEST['filter_by_year'])) : null;
		$filter_by_month = (isset($_REQUEST['filter_by_month']) && !empty($_REQUEST['filter_by_month'])) ? intval(sanitize_text_field($_REQUEST['filter_by_month'])) : null;
		$status          = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : null;
		$course_id       = isset($_REQUEST['course_id']) ? intval(sanitize_text_field($_REQUEST['course_id'])) : null;
		$batch_id        = isset($_REQUEST['batch_id']) ? intval(sanitize_text_field($_REQUEST['batch_id'])) : null;

		$filters = array();

		/* Add Filter: year */
		if (!empty($filter_by_year)) {
			array_push($filters, "YEAR(created_at) = $filter_by_year");

			/* Add Filter: month */
			if (!empty($filter_by_month)) {
				array_push($filters, "MONTH(created_at) = $filter_by_month");
			}
		}

		/* Add Filter: status */
		if (!empty($status)) {
			if ($status == 'all') {
			} elseif ($status == 'active') {
				array_push($filters, "is_active = 1");
			}
		}

		/* Add Filter: course */
		if (!empty($course_id)) {
			array_push($filters, "course_id = $course_id");
		}

		/* Add Filter: branch */
		if (!empty($batch_id)) {
			array_push($filters, "batch_id = $batch_id");
		}
		/* End filters */

		if (count($filters)) {
			$filter_query = 'AND ' . implode(' AND ', $filters);
		} else {
			$filter_query = '';
		}

		// if user is not admin.
		if (!current_user_can('administrator')) {
			// get current user id.
			$user_id = get_current_user_id();
			// get user staff data by user id.
			$user_staff_data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_staffs WHERE user_id = $user_id");
			// if user have batch_id then add batch_id in filter query.
			if ($user_staff_data->batch_id) {
				$filter_query .= " AND batch_id = $user_staff_data->batch_id";
			}
			// var_dump($filter_query); die;
		}

		if (!empty($filter_query)) {
			$data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id $filter_query ORDER BY id DESC");
		} else {
			$data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id $filter_query ORDER BY id DESC");
		}



		$course_data = $wpdb->get_results("SELECT id, course_name, course_code, fees, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE institute_id = $institute_id ORDER BY course_name", OBJECT_K);

		$batch_data = $wpdb->get_results("SELECT id, batch_code, batch_name, time_from, time_to, start_date, end_date FROM {$wpdb->prefix}wl_min_batches WHERE institute_id = $institute_id ORDER BY id", OBJECT_K);

		$page_url = WL_MIM_Helper::get_reminder_page_url();

		if (count($data) !== 0) {
			foreach ($data as $row) {
				$id                = $row->id;
				$enrollment_id     = WL_MIM_Helper::get_enrollment_id_with_prefix($row->enrollment_id, $general_enrollment_prefix);
				$first_name        = $row->first_name ? $row->first_name : '-';
				$last_name         = $row->last_name ? $row->last_name : '-';
				$qualification     = $row->qualification ? $row->qualification : '-';
				$registration_date = $row->created_at ? date_format(date_create($row->created_at), 'd-m-Y') : '-';
				$expire_at         = $row->created_at ? date_format(date_create($row->expire_at), 'd-m-Y') : '-';
				$father_name       = $row->father_name ? $row->father_name : '-';
				$class             = $row->class ? $row->class : '-';
				$business_manager  = $row->business_manager ? $row->business_manager : '-';
				$source            = $row->source ? $row->source : '-';
				$state             = $row->state ? $row->state : '-';
				$teacher           = $row->teacher ? $row->teacher : '-';
				$updated_by        = $row->updated_by ? $row->updated_by : '-';

				// get wordpress username by user id.
				$user_info = get_userdata($updated_by);
				$user_info = $user_info->user_login;

				// $fees          = unserialize($row->fees);
				// $fees_payable  = WL_MIM_Helper::get_fees_total($fees['payable']);
				// $fees_paid     = WL_MIM_Helper::get_fees_total($fees['paid']);
				// $pending_fees  = number_format($fees_payable - $fees_paid, 2, '.', '');
				$phone         = $row->phone ? $row->phone : '-';
				$phon2         = $row->phone2 ? $row->phone2 : '-';
				$email         = $row->email ? $row->email : '-';
				$date_of_birth = (!empty($row->date_of_birth)) ? date_format(date_create($row->date_of_birth), "d M, Y") : '-';
				$is_acitve     = $row->is_active ? esc_html__('Yes', WL_MIM_DOMAIN) : esc_html__('No', WL_MIM_DOMAIN);
				$date          = date_format(date_create($row->created_at), "d-m-Y");
				$added_by      = ($user = get_userdata($row->added_by)) ? $user->user_login : '-';

				$course   = '-';
				$duration = '-';
				$batch    = '-';
				if ($row->course_id && isset($course_data[$row->course_id])) {
					$course_name = $course_data[$row->course_id]->course_name;
					$course_code = $course_data[$row->course_id]->course_code;
					$course      = "$course_name ($course_code)";
					$duration    = $course_data[$row->course_id]->duration . " " . $course_data[$row->course_id]->duration_in;
				}

				if ($row->batch_id && isset($batch_data[$row->batch_id])) {
					$time_from    = date("g:i A", strtotime($batch_data[$row->batch_id]->time_from));
					$time_to      = date("g:i A", strtotime($batch_data[$row->batch_id]->time_to));
					$timing       = "$time_from - $time_to";
					$batch        = esc_html($batch_data[$row->batch_id]->batch_code) . ' ( ' . esc_html($batch_data[$row->batch_id]->batch_name) . ' )';
					// <br>( ' . esc_html($timing) . ' )';
					$batch_status = WL_MIM_Helper::get_batch_status($batch_data[$row->batch_id]->start_date, $batch_data[$row->batch_id]->end_date);
				}

				// if ($pending_fees > 0) {
				// 	$fees_status = '<strong class="text-danger">' . esc_html__('Pending', WL_MIM_DOMAIN) . ': </strong><br><strong>' . $pending_fees . '</strong>';
				// } else {
				// 	$fees_status = '<strong class="text-success">' . esc_html__('Paid', WL_MIM_DOMAIN) . '</strong>';
				// }

				if (current_user_can( 'wl_min_student_view_only' ) && !current_user_can('administrator')) {
					$edit = '<a class="mr-3" href="#update-student" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-student-security="' . wp_create_nonce("delete-student-$id") . '"delete-student-id="' . $id . '" class="delete-student"> <i class="fa fa-trash text-danger"></i></a>';
				}
				if (current_user_can('administrator')) {
					$edit = '<a class="mr-3" href="#update-student" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-student-security="' . wp_create_nonce("delete-student-$id") . '"delete-student-id="' . $id . '" class="delete-student"> <i class="fa fa-trash text-danger"></i></a>';
				}

				$results["data"][] = array(
					'<input type="checkbox" class="wl-mim-select-single wl-mim-bulk-students" name="bulk_data[]" value="' . esc_attr($row->id) . '">',
					esc_html($enrollment_id),
					esc_html(ucwords($first_name)),
					esc_html($last_name),
					esc_html($course),
					$batch,
					$business_manager,
					$source,
					$state,
					$teacher,
					$user_info,
					esc_html($phone),
					esc_html($phon2),
					esc_html($email),
					esc_html($qualification),
					esc_html($class),
					esc_html($registration_date),
					esc_html($expire_at),
					esc_html($father_name),
					// esc_html($date_of_birth),
					esc_html($is_acitve),
					esc_html($added_by),
					'<i class="fa fa-eye text-success"><a class="mr-3" href="'. $page_url. '&student_id='. $id .'" ></i> Follow Up</a>',
					// esc_html($date),
					$edit
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json($results);
	}

	public static function get_student_fees_report_data_dash(){
		$course_id       = isset($_POST['course']) ? intval(sanitize_text_field($_POST['course'])) : null;
		$batch_id        = isset($_POST['batch']) ? intval(sanitize_text_field($_POST['batch'])) : null;

		$institute_id = WL_MIM_Helper::get_current_institute_id();

		global $wpdb;
		$students = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id AND course_id = $course_id AND batch_id = $batch_id AND is_active = 1 ORDER BY id DESC");

		$total_students = count($students);
		$paid_students = 0;
		$unpaid_students = 0;
		$total_payable_amount = 0;
		$total_paid_amount = 0;
		$total_unpaid_amount = 0;
		$paid_amount=0;

		foreach ($students as $student) {
			$student_id = $student->id;
			// get total fees payable amount from invoices table.
			$fees_payable = $wpdb->get_var("SELECT SUM(payable_amount) FROM {$wpdb->prefix}wl_min_invoices WHERE student_id = $student_id");
			// get total fees paid amount from installments table.
			$fees_paid = $wpdb->get_var("SELECT SUM(paid_amount) FROM {$wpdb->prefix}wl_min_installments WHERE student_id = $student_id");
			// get total fees pending amount from fees_payable and fees_paid.
			$pending_fees = ($fees_payable - $fees_paid);
			$paid_amount += $fees_paid;

			if ($pending_fees == 0) {
				$paid_students++;
				$total_paid_amount += $fees_paid;
			} else {
				$unpaid_students++;
				$total_unpaid_amount += $pending_fees;
			}

			$total_payable_amount += $fees_payable;
		}

		// return all variabls in json
		wp_send_json(array(
			'total_students' => $total_students,
			'paid_students' => $paid_students,
			'paid_amount' => $paid_amount,
			'unpaid_students' => $unpaid_students,
			'total_payable_amount' => $total_payable_amount,
			'total_paid_amount' => $total_paid_amount,
			'total_unpaid_amount' => $total_unpaid_amount,
		));


	}

	public static function get_student_fees_report_data() {
		self::check_permission();

		global $wpdb;

		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings($institute_id);

		/* Filters */
		$filter_by_year  = (isset($_REQUEST['filter_by_year']) && !empty($_REQUEST['filter_by_year'])) ? intval(sanitize_text_field($_REQUEST['filter_by_year'])) : null;
		$filter_by_month = (isset($_REQUEST['filter_by_month']) && !empty($_REQUEST['filter_by_month'])) ? intval(sanitize_text_field($_REQUEST['filter_by_month'])) : null;
		$status          = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : null;
		$course_id       = isset($_REQUEST['course_id']) ? intval(sanitize_text_field($_REQUEST['course_id'])) : null;
		$batch_id        = isset($_REQUEST['batch_id']) ? intval(sanitize_text_field($_REQUEST['batch_id'])) : null;

		$filters = array();

		/* Add Filter: year */
		if (!empty($filter_by_year)) {
			array_push($filters, "YEAR(created_at) = $filter_by_year");

			/* Add Filter: month */
			if (!empty($filter_by_month)) {
				array_push($filters, "MONTH(created_at) = $filter_by_month");
			}
		}

		/* Add Filter: status */
		if (!empty($status)) {
			if ($status == 'all') {
			} elseif ($status == 'active') {
				array_push($filters, "is_active = 1");
			}
		}

		/* Add Filter: course */
		if (!empty($course_id)) {
			array_push($filters, "course_id = $course_id");
		}

		/* Add Filter: branch */
		if (!empty($batch_id)) {
			array_push($filters, "batch_id = $batch_id");
		}
		/* End filters */

		if (count($filters)) {
			$filter_query = 'AND ' . implode(' AND ', $filters);
		} else {
			$filter_query = '';
		}

		if (!empty($filter_query)) {
			// $data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id $filter_query ORDER BY id DESC");

			$data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id AND course_id = $course_id AND batch_id = $batch_id ORDER BY id DESC");

		} else {
			$data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY id DESC");
		}

		$course_data = $wpdb->get_results("SELECT id, course_name, course_code, fees, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE institute_id = $institute_id ORDER BY course_name", OBJECT_K);

		$batch_data = $wpdb->get_results("SELECT id, batch_code, batch_name, time_from, time_to, start_date, end_date FROM {$wpdb->prefix}wl_min_batches WHERE institute_id = $institute_id ORDER BY id", OBJECT_K);

		$page_url = WL_MIM_Helper::get_reminder_page_url();

		if (count($data) !== 0) {
			foreach ($data as $row) {
				$id            = $row->id;
				$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix($row->enrollment_id, $general_enrollment_prefix);
				$first_name    = $row->first_name ? $row->first_name : '-';
				$last_name     = $row->last_name ? $row->last_name : '-';
				$qualification     = $row->qualification ? $row->qualification : '-';
				$registration_date     = $row->created_at ? date_format(date_create($row->created_at), 'd-m-Y') : '-';
				$expire_at     = $row->created_at ? date_format(date_create($row->expire_at), 'd-m-Y') : '-';
				$father_name   = $row->father_name ? $row->father_name : '-';
				$class         = $row->class ? $row->class : '-';
				$business_manager         = $row->business_manager ? $row->business_manager : '-';
				$source         = $row->source ? $row->source : '-';
				// $fees          = unserialize($row->fees);
				// $fees_payable  = WL_MIM_Helper::get_fees_total($fees['payable']);
				// $fees_paid     = WL_MIM_Helper::get_fees_total($fees['paid']);
				// $pending_fees  = number_format($fees_payable - $fees_paid, 2, '.', '');
				$phone         = $row->phone ? $row->phone : '-';
				$phon2         = $row->phone2 ? $row->phone2 : '-';
				$email         = $row->email ? $row->email : '-';
				$date_of_birth = (!empty($row->date_of_birth)) ? date_format(date_create($row->date_of_birth), "d M, Y") : '-';
				$is_acitve     = $row->is_active ? esc_html__('Yes', WL_MIM_DOMAIN) : esc_html__('No', WL_MIM_DOMAIN);
				$date          = date_format(date_create($row->created_at), "d-m-Y");
				$added_by      = ($user = get_userdata($row->added_by)) ? $user->user_login : '-';

				$course   = '-';
				$duration = '-';
				$batch    = '-';
				if ($row->course_id && isset($course_data[$row->course_id])) {
					$course_name = $course_data[$row->course_id]->course_name;
					$course_code = $course_data[$row->course_id]->course_code;
					$course      = "$course_name ($course_code)";
					$duration    = $course_data[$row->course_id]->duration . " " . $course_data[$row->course_id]->duration_in;
				}

				if ($row->batch_id && isset($batch_data[$row->batch_id])) {
					$time_from    = date("g:i A", strtotime($batch_data[$row->batch_id]->time_from));
					$time_to      = date("g:i A", strtotime($batch_data[$row->batch_id]->time_to));
					$timing       = "$time_from - $time_to";
					$batch        = esc_html($batch_data[$row->batch_id]->batch_code) . ' ( ' . esc_html($batch_data[$row->batch_id]->batch_name) . ' )';
					// <br>( ' . esc_html($timing) . ' )';
					$batch_status = WL_MIM_Helper::get_batch_status($batch_data[$row->batch_id]->start_date, $batch_data[$row->batch_id]->end_date);
				}

				if ($row->id) {
					// create a new query to get the fees payable_amount total from the invoices table.
					$fees_payable = $wpdb->get_var("SELECT SUM(payable_amount) FROM {$wpdb->prefix}wl_min_invoices WHERE student_id = $row->id");

					// create a new query to get the fees paid total from the wp_wl_min_installments .
					$fees_paid = $wpdb->get_var("SELECT SUM(paid_amount) FROM {$wpdb->prefix}wl_min_installments WHERE student_id = $row->id");

					// create condition to get fees status from fees_payable and fees_paid total.
					if ($fees_payable > $fees_paid) {
						$fees_status = '<strong class="text-danger">' . esc_html__('Pending', WL_MIM_DOMAIN) . ': </strong><br><strong>' . ($fees_payable - $fees_paid) . '</strong>';
					} else {
						$fees_status = '<strong class="text-success">' . esc_html__('Paid', WL_MIM_DOMAIN) . '</strong>';
					}
				} else {
					$fees_payable = 0;
					$fees_paid = 0;
				}

				if (current_user_can( 'wl_min_student_view_only' ) && !current_user_can('administrator')) {
					$edit = '<a class="mr-3" href="#update-student" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-student-security="' . wp_create_nonce("delete-student-$id") . '"delete-student-id="' . $id . '" class="delete-student"> <i class="fa fa-trash text-danger"></i></a>';
				}
				if (current_user_can('administrator')) {
					$edit = '<a class="mr-3" href="#update-student" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-student-security="' . wp_create_nonce("delete-student-$id") . '"delete-student-id="' . $id . '" class="delete-student"> <i class="fa fa-trash text-danger"></i></a>';
				}

				$results["data"][] = array(
					// '<input type="checkbox" class="wl-mim-select-single wl-mim-bulk-students" name="bulk_data[]" value="' . esc_attr($row->id) . '">',
					esc_html($enrollment_id),
					esc_html(ucwords($first_name)),
					// esc_html($last_name),
					esc_html($course),
					$batch,
					$business_manager,
					$source,
					// esc_html($duration),
					// $batch_status,
					esc_html($fees_payable),
					esc_html($fees_paid),
					$fees_status,
					esc_html($phone),
					esc_html($phon2),
					esc_html($email),
					esc_html($qualification),
					esc_html($class),
					esc_html($registration_date),
					esc_html($expire_at),
					esc_html($father_name),
					// esc_html($date_of_birth),
					esc_html($is_acitve),
					esc_html($added_by),
					'<i class="fa fa-eye text-success"><a class="mr-3" href="'. $page_url. '&student_id='. $id .'" ></i> Follow Up</a>',
					// esc_html($date),
					$edit
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json($results);
	}

	/* Add new student */
	public static function add_student() {
		self::check_permission();
		if (!wp_verify_nonce($_POST['add-student'], 'add-student')) {
			die();
		}
		global $wpdb;
		$institute_id               = WL_MIM_Helper::get_current_institute_id();
		$general_enrollment_prefix  = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings($institute_id);

		$general_enable_roll_number = WL_MIM_SettingHelper::get_general_enable_roll_number_settings($institute_id);

		$course_id       = isset($_POST['course']) ? intval(sanitize_text_field($_POST['course'])) : null;
		$batch_id        = isset($_POST['batch']) ? intval(sanitize_text_field($_POST['batch'])) : null;
		$first_name      = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
		$last_name       = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
		$gender          = isset($_POST['gender']) ? sanitize_text_field($_POST['gender']) : '';
		$date_of_birth   = (isset($_POST['date_of_birth']) && !empty($_POST['date_of_birth'])) ? date("Y-m-d", strtotime(sanitize_text_field($_REQUEST['date_of_birth']))) : null;
		$roll_number     = isset($_POST['roll_number']) ? sanitize_text_field($_POST['roll_number']) : '';
		$enrollment_id   = isset($_POST['enrollment_id']) ? sanitize_text_field($_POST['enrollment_id']) : '';

		$id_proof        = (isset($_FILES['id_proof']) && is_array($_FILES['id_proof'])) ? $_FILES['id_proof'] : null;
		$id_proof_in_db  = isset($_POST['id_proof_in_db']) ? intval(sanitize_text_field($_POST['id_proof_in_db'])) : null;
		$father_name     = isset($_POST['father_name']) ? sanitize_text_field($_POST['father_name']) : '';
		$mother_name     = isset($_POST['mother_name']) ? sanitize_text_field($_POST['mother_name']) : '';
		$address         = isset($_POST['address']) ? sanitize_textarea_field($_POST['address']) : '';
		$city            = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
		$zip             = isset($_POST['zip']) ? sanitize_text_field($_POST['zip']) : '';
		$state           = isset($_POST['state']) ? sanitize_text_field($_POST['state']) : '';
		$nationality     = isset($_POST['nationality']) ? sanitize_text_field($_POST['nationality']) : '';
		$phone           = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
		$phone2           = isset($_POST['phone2']) ? sanitize_text_field($_POST['phone2']) : '';
		$qualification   = isset($_POST['qualification']) ? sanitize_text_field($_POST['qualification']) : '';
		$email           = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
		$photo           = (isset($_FILES['photo']) && is_array($_FILES['photo'])) ? $_FILES['photo'] : null;
		$photo_in_db     = isset($_POST['photo_in_db']) ? intval(sanitize_text_field($_POST['photo_in_db'])) : null;
		$signature       = (isset($_FILES['signature']) && is_array($_FILES['signature'])) ? $_FILES['signature'] : null;
		$signature_in_db = isset($_POST['signature_in_db']) ? intval(sanitize_text_field($_POST['signature_in_db'])) : null;
		$is_active       = isset($_POST['is_active']) ? boolval(sanitize_text_field($_POST['is_active'])) : 0;
		$enquiry         = isset($_POST['enquiry']) ? intval(sanitize_text_field($_POST['enquiry'])) : null;
		$from_enquiry    = isset($_POST['from_enquiry']) ? boolval(sanitize_text_field($_POST['from_enquiry'])) : 0;
		$enquiry_action  = isset($_POST['enquiry_action']) ? sanitize_text_field($_POST['enquiry_action']) : '';
		$amount          = (isset($_POST['amount']) && is_array($_POST['amount'])) ? $_POST['amount'] : null;
		$period          = (isset($_POST['period']) && is_array($_POST['period'])) ? $_POST['period'] : null;

		$allow_login      = isset($_POST['allow_login']) ? boolval(sanitize_text_field($_POST['allow_login'])) : 0;
		$username         = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
		$password         = isset($_POST['password']) ? $_POST['password'] : '';
		$password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

		$total_course_fee  = isset($_POST['total_course_fee']) ? sanitize_text_field($_POST['total_course_fee']) : '';
		$course_discount   = isset($_POST['course_discount']) ? sanitize_text_field($_POST['course_discount']) : '';
		$course_payable    = isset($_POST['course_payable']) ? sanitize_text_field($_POST['course_payable']) : '';
		$installment_count = isset($_POST['installment_count']) ? sanitize_text_field($_POST['installment_count']) : '';

		$invoice_title   = (isset($_POST['invoice_title']) && is_array($_POST['invoice_title'])) ? $_POST['invoice_title'] : null;
		$payable_amount  = (isset($_POST['payable_amount']) && is_array($_POST['payable_amount'])) ? $_POST['payable_amount'] : null;
		$due_date        = (isset($_POST['due_date']) && is_array($_POST['due_date'])) ? $_POST['due_date'] : null;
		$due_date_amount = (isset($_POST['due_date_amount']) && is_array($_POST['due_date_amount'])) ? $_POST['due_date_amount'] : null;

		$created_at      = (isset($_POST['created_at']) && !empty($_POST['created_at'])) ? date("Y-m-d", strtotime(sanitize_text_field($_REQUEST['created_at']))) : NULL;
		$expire_at      = (isset($_POST['expire_at']) && !empty($_POST['expire_at'])) ? date("Y-m-d", strtotime(sanitize_text_field($_REQUEST['expire_at']))) : NULL;
		$class = isset($_POST['class']) ? sanitize_text_field($_POST['class']) : '';
		$business_manager = isset($_POST['business_manager']) ? sanitize_text_field($_POST['business_manager']) : '';
		$student_status = isset($_POST['student_status']) ? sanitize_text_field($_POST['student_status']) : '';
		$source = isset($_POST['source']) ? sanitize_text_field($_POST['source']) : '';
		$teacher = isset($_POST['teacher']) ? sanitize_text_field($_POST['teacher']) : '';


		if (empty($invoice_title)) {
			$errors['invoice_title'] = esc_html__('Please provide invoice_title.', WL_MIM_DOMAIN);
		}
		if (empty($payable_amount)) {
			$errors['payable_amount'] = esc_html__('Please provide payable_amount.', WL_MIM_DOMAIN);
		}

		/* Validations */
		$errors = array();
		if (empty($course_id)) {
			$errors['course'] = esc_html__('Please select a course.', WL_MIM_DOMAIN);
		}

		if (empty($batch_id)) {
			$errors['batch'] = esc_html__('Please select a batch.', WL_MIM_DOMAIN);
		}

		if (empty($first_name)) {
			$errors['first_name'] = esc_html__('Please provide first name.', WL_MIM_DOMAIN);
		}

		if (strlen($first_name) > 255) {
			$errors['first_name'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($last_name) > 255) {
			$errors['last_name'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		$course = $wpdb->get_row("SELECT course_code, course_name, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND id = $course_id AND institute_id = $institute_id");

		if (!$course) {
			$errors['course'] = esc_html__('Please select a valid course.', WL_MIM_DOMAIN);
		}

		$duration_in_month = WL_MIM_Helper::get_course_months_count($course->duration, $course->duration_in);
		if (empty($batch_id)) {
			wp_send_json_error(esc_html__('Please select a batch', WL_MIM_DOMAIN));
		}

		$count = $wpdb->get_var("SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND is_active = 1 AND id = $batch_id AND course_id = $course_id AND institute_id = $institute_id");

		// get batch details
		$batch = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE id = $batch_id");

		if (!$count) {
			$errors['batch'] = esc_html__('Please select a valid batch.', WL_MIM_DOMAIN);
		}

		if ($allow_login) {
			if (empty($username)) {
				$errors['username'] = esc_html__('Please provide username.', WL_MIM_DOMAIN);
			}

			if (empty($password)) {
				$errors['password'] = esc_html__('Please provide password.', WL_MIM_DOMAIN);
			}

			if (empty($password_confirm)) {
				$errors['password_confirm'] = esc_html__('Please confirm password.', WL_MIM_DOMAIN);
			}

			if ($password !== $password_confirm) {
				$errors['password'] = esc_html__('Passwords do not match.', WL_MIM_DOMAIN);
			}
		}

		if (!in_array($gender, WL_MIM_Helper::get_gender_data())) {
			throw new Exception(esc_html__('Please select valid gender.', WL_MIM_DOMAIN));
		}

		if (!empty($date_of_birth) && (strtotime(date('Y') - 2) <= strtotime($date_of_birth))) {
			$errors['date_of_birth'] = esc_html__('Please provide valid date of birth.', WL_MIM_DOMAIN);
		}

		if (empty($date_of_birth)) {
			$errors['date_of_birth'] = esc_html__('Please provide date of birth.', WL_MIM_DOMAIN);
		}

		if (!empty($id_proof)) {
			$file_name          = sanitize_file_name($id_proof['name']);
			$file_type          = $id_proof['type'];
			$allowed_file_types = WL_MIM_Helper::get_id_proof_file_types();

			if (!in_array($file_type, $allowed_file_types)) {
				$errors['id_proof'] = esc_html__('Please provide ID Proof in PDF, JPG, JPEG or PNG format.', WL_MIM_DOMAIN);
			}
		}

		if (strlen($father_name) > 255) {
			$errors['father_name'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($mother_name) > 255) {
			$errors['mother_name'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($city) > 255) {
			$errors['city'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($zip) > 255) {
			$errors['zip'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($state) > 255) {
			$errors['state'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($nationality) > 255) {
			$errors['nationality'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (empty($phone)) {
			$errors['phone'] = esc_html__('Please provide phone number.', WL_MIM_DOMAIN);
		}

		if (strlen($phone) > 255) {
			$errors['phone'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($qualification) > 255) {
			$errors['qualification'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($email) > 255) {
			$errors['email'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors['email'] = esc_html__('Please provide a valid email address.', WL_MIM_DOMAIN);
		}

		if ($general_enable_roll_number) {
			if (!empty($roll_number)) {
				$count = $wpdb->get_var("SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND roll_number = '$roll_number' AND institute_id = $institute_id");

				if ($count) {
					$errors['roll_number'] = esc_html__('Student with this roll number already exists.', WL_MIM_DOMAIN);
				}
			}
		}
		if (!$enrollment_id) {
			$enrollment_id = $wpdb->get_var("SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_students WHERE institute_id = $institute_id ");
			$enrollment_id = $enrollment_id + 1;
		}

		if (!empty($photo)) {
			$file_name          = sanitize_file_name($photo['name']);
			$file_type          = $photo['type'];
			$allowed_file_types = WL_MIM_Helper::get_image_file_types();

			if (!in_array($file_type, $allowed_file_types)) {
				$errors['photo'] = esc_html__('Please provide photo in JPG, JPEG or PNG format.', WL_MIM_DOMAIN);
			}
		}

		if (!empty($signature)) {
			$file_name          = sanitize_file_name($signature['name']);
			$file_type          = $signature['type'];
			$allowed_file_types = WL_MIM_Helper::get_image_file_types();
			if (!in_array($file_type, $allowed_file_types)) {
				$errors['signature'] = esc_html__('Please provide signature in JPG, JPEG or PNG format.', WL_MIM_DOMAIN);
			}
		}

		$valid_enquiry_action = false;
		if ($from_enquiry) {
			$count = $wpdb->get_var("SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_enquiries WHERE is_deleted = 0 AND is_active = 1 AND id = $enquiry AND institute_id = $institute_id");

			if (!$count) {
				wp_send_json_error(esc_html__('Please select a valid enquiry', WL_MIM_DOMAIN));
			} else {
				if (!in_array($enquiry_action, WL_MIM_Helper::get_enquiry_action_data())) {
					throw new Exception(esc_html__('Please select valid action to perform after adding student.', WL_MIM_DOMAIN));
				} else {
					$valid_enquiry_action = true;
				}
			}
		}
		/* End validations */

		if (count($errors) < 1) {
			try {
				$wpdb->query('BEGIN;');

				$inactive_at = null;
				if (!$is_active) {
					$inactive_at = null;
				}
				$data = array(
					'course_id'     => $course_id,
					'enrollment_id' => $enrollment_id,
					'batch_id'      => $batch_id,
					'first_name'    => $first_name,
					'last_name'     => $last_name,
					'gender'        => $gender,
					'date_of_birth' => $date_of_birth,
					'father_name'   => $father_name,
					'mother_name'   => $mother_name,
					'address'       => $address,
					'city'          => $city,
					'zip'           => $zip,
					'state'         => $state,
					'nationality'   => $nationality,
					'phone'         => $phone,
					'phone2'         => $phone2,
					'qualification' => $qualification,
					'email'         => $email,
					'is_active'     => $is_active,
					'inactive_at'   => $inactive_at,
					'added_by'      => get_current_user_id(),
					'institute_id'  => $institute_id,

					'total_course_fee'  => $total_course_fee,
					'course_discount'   => $course_discount,
					'course_payable'    => $course_payable,
					'installment_count' => $installment_count,

					'created_at' => $created_at,
					'expire_at'  => $expire_at,
					'class'      => $class,
					'business_manager'      => $business_manager,
					'source'      => $source,
					'teacher'      => $teacher,
					'student_status'        => $student_status,
				);

				if ($general_enable_roll_number) {
					$data['roll_number'] = $roll_number;
				}

				if (!empty($id_proof)) {
					$id_proof = media_handle_upload('id_proof', 0);
					if (is_wp_error($id_proof)) {
						throw new Exception(esc_html__($id_proof->get_error_message(), WL_MIM_DOMAIN));
					}
					$data['id_proof'] = $id_proof;
				} else {
					$data['id_proof'] = $id_proof_in_db;
				}

				if (!empty($photo)) {
					$photo = media_handle_upload('photo', 0);
					if (is_wp_error($photo)) {
						throw new Exception(esc_html__($photo->get_error_message(), WL_MIM_DOMAIN));
					}
					$data['photo_id'] = $photo;
				} else {
					$data['photo_id'] = $photo_in_db;
				}

				if (!empty($signature)) {
					$signature = media_handle_upload('signature', 0);
					if (is_wp_error($signature)) {
						throw new Exception(esc_html__($signature->get_error_message(), WL_MIM_DOMAIN));
					}
					$data['signature_id'] = $signature;
				} else {
					$data['signature_id'] = $signature_in_db;
				}

				if ($allow_login) {
					/* Student login data */
					$login_data = array(
						'first_name' => $first_name,
						'last_name'  => $last_name,
						'user_login' => $username,
						'user_pass'  => $password
					);

					$user_id = wp_insert_user($login_data);
					if (is_wp_error($user_id)) {
						wp_send_json_error(esc_html__($user_id->get_error_message(), WL_MIM_DOMAIN));
					}

					$user = new WP_User($user_id);
					$user->add_cap(WL_MIM_Helper::get_student_capability());

					if ($user_id) {
						$data['user_id']     = $user_id;
						$data['allow_login'] = $allow_login;
						update_user_meta($user_id, 'wlim_institute_id', $institute_id);
					}
				}

				$data['created_at'] = current_time('Y-m-d H:i:s');

				$success = $wpdb->insert("{$wpdb->prefix}wl_min_students", $data);
				$student_id = $wpdb->insert_id;
				$fees = array(); // Initialize the $fees array

				if ($invoice_title) {
					foreach ($invoice_title as $key => $value) {
						$invoice_data = array(
							'invoice_title'  => $invoice_title[$key],
							'payable_amount' => $payable_amount[$key],
							'due_date_amount' => $due_date_amount[$key],
							'student_id'     => $student_id,
							'due_date'       => date("Y-m-d", strtotime($due_date[$key])),
							'invoice_date'   => date('Y-m-d'),
							'added_by'       => get_current_user_id(),
							'institute_id'   => $institute_id
						);

						$invoice_data['created_at'] = current_time('Y-m-d H:i:s');
						$success = $wpdb->insert("{$wpdb->prefix}wl_min_invoices", $invoice_data);

						// Add the $invoice_data to the $fees array
						$fees[] = $invoice_data;
					}
				}

				// installment table
				$table = '<table cellspacing="0" style="border: 2px solid #000000;">';
				$table .= '<tr style="border: 1px solid #000000;">
							<th style="border: 1px solid #000000;">Invoice Title</th>
							<th style="border: 1px solid #000000;">Payable Amount</th>
							<th style="border: 1px solid #000000;">Due Date</th>
							<th style="border: 1px solid #000000;">Due Date Amount</th>
						</tr>';
				foreach ($fees as $fee) {
					$table .= '<tr style="border: 1px solid #000000;">';
					$table .= '<td style="border: 1px solid #000000;">' . $fee['invoice_title'] . '</td>';
					$table .= '<td style="border: 1px solid #000000;">' . $fee['payable_amount'] . '</td>';
					$table .= '<td style="border: 1px solid #000000;">' . $fee['due_date'] . '</td>';
					$table .= '<td style="border: 1px solid #000000;">' . $fee['due_date_amount'] . '</td>';
					$table .= '</tr>';
				}
				$table .= '</table>';

				if (!$success) {
					throw new Exception(esc_html__('An unexpected error occurred.', WL_MIM_DOMAIN));
				}

				if ($valid_enquiry_action) {
					if ($enquiry_action == WL_MIM_Helper::get_enquiry_action_data()[1]) {
						$success = $wpdb->update(
							"{$wpdb->prefix}wl_min_enquiries",
							array(
								'is_active'  => 0,
								'updated_at' => date('Y-m-d H:i:s')
							),
							array('is_deleted' => 0, 'id' => $enquiry, 'institute_id' => $institute_id)
						);
					} else {
						$success = $wpdb->update(
							"{$wpdb->prefix}wl_min_enquiries",
							array(
								'is_deleted' => 1,
								'deleted_at' => date('Y-m-d H:i:s')
							),
							array('is_deleted' => 0, 'id' => $enquiry, 'institute_id' => $institute_id)
						);
					}

					if ($success === false) {
						throw new Exception(esc_html__('An unexpected error occurred.', WL_MIM_DOMAIN));
					}
				}

				if (!empty($id_proof) && !empty($id_proof_in_db)) {
					wp_delete_attachment($id_proof_in_db, true);
				}

				if (!empty($photo) && !empty($photo_in_db)) {
					wp_delete_attachment($photo_in_db, true);
				}

				if (!empty($signature) && !empty($signature_in_db)) {
					wp_delete_attachment($signature_in_db, true);
				}

				// Send email to student
				$template = WL_MIM_SettingHelper::get_template_settings($institute_id);
				if ($template['et_inquiry_processing_subject']) {

						$subject = $template['et_inquiry_processing_subject'];
						$body    = $template['et_inquiry_processing_body'];

						$body = str_replace('[COURSE_NAME]', $course->course_name, $body);
						$body = str_replace('[STUDENT_NAME]', $first_name." ".$last_name, $body);
						$body = str_replace('[STUDENT_EMAIL]', $email, $body);
						$body = str_replace('[STUDENT_BATCH]', $batch->batch_name, $body);
						$body = str_replace('[REGISTRATION_DATE]', $created_at, $body);
						$body = str_replace('[EXPIRATION_DATE]', $expire_at, $body);
						$body = str_replace('[TOTAL_COURSE_FEE]', $total_course_fee, $body);
						$body = str_replace('[COURSE_DISCOUNT]', $course_discount, $body);
						$body = str_replace('[COURSE_PAYABLE]', $course_payable, $body);
						$body = str_replace('[INSTALLMENT_COUNT]', $installment_count, $body);
						$body = str_replace('[INSTALLMENTS]', $table, $body);
						$body = str_replace('[STUDENT_USERNAME]', $username, $body);
						$body = str_replace('[STUDENT_PASSWORD]', $password, $body);
						$body = str_replace('[ENROLLMENT_NUMBER]', $enrollment_id, $body);

// 						$body .= "<pre>
// LINK:- HTTPS://SHIROMANIINSTITUTE.IN/

// IF YOU PAY THROUGH ANY OTHER MODE THEN YOU ARE ONLY RESPONSIBLE FOR THE PAYMENT DONE…INSTITUE WILL NOT TAKE ANY RESPONSIBILITY FOR ANY FRAUD.

// IF THIS PAYMENT DATE FAILS THEN LATE FINE WILL BE CHARGED SEPARATELY 100/- PER DAY.

// CLASS TIMINGS - ANY SLOT BETWEEN 4PM-10PM TERMS & CONDITIONS:-
// READ THE TERMS & CONDITIONS CAREFULLY. YOU’RE ACCESS TO & USE OF THE SERVICES IS CONDITIONED ON YOUR ACCEPTANCE OF & COMPLIANCE WITH THESE TERMS.
// AFTER READING ALL THESE CONDITIONS, YOU SHOULD REPLY TO THE FINAL APPROVAL ON THIS EMAIL, ONLY THEN THE CLASS WILL BE STARTED.
// IF YOU DISAGREE WITH ANY PART OF THE TERMS, THEN YOU MAY NOT ACCESS THE SERVICES.
// STUDENT REGISTRATION FORM
// REGISTRATION AGREEMENT: THE 'STUDENT REGISTRATION FORM' IS THE REGISTRATION AGREEMENT (HEREINAFTER REFERRED TO AS THE AGREEMENT.
// BETWEEN THE APPLICANT AND SHIROMANI INSTITUTE.
// 1. IT CONSTITUTES AND EXPRESSES THE ENTIRE AGREEMENT AND UNDERSTANDING BETWEEN THE SHIROMANI INSTITUTE AND THE STUDENT IN REFERENCE TO ALL MATTERS HEREIN REFERRED TO, ALL PREVIOUS DISCUSSIONS, PROMISES, REPRESENTATIONS AND UNDERSTANDINGS RELATIVE THERETO, IF ANY, HAD BETWEEN THE PARTIES HERETO, BEING HEREIN MERGED.
// 2. SHIROMANI INSTITUTE WILL BE PROVIDING ALL SERVICES TO STUDENTS.


// COURSE DURATION: -
//  1.THE FEE IS VALID TILL NVS  2024. FEE IS MENTIONED ON THE FORM, DEMAND DRAFT PAYMENTS SHOULD BE MADE FAVORING 'SHIROMANI INSTITUTE' PAYABLE AT NEW DELHI.

// 2. FEE ONCE PAID IS NOT REFUNDABLE UNDER ANY CIRCUMSTANCES. HOWEVER, THE FEE MAY BE REFUNDED ONLY IF 'SHIROMANI INSTITUTE' FAILS TO START A COURSE (NOT APPLICABLE FOR UNAVOIDABLE INSTANCES). DELAY IN STARTING OF CLASS BY A FORTNIGHT SHALL NOT BE CONSIDERED AS NOT STARTING.
// PRIVACY
// I (STUDENT) WILL USE ANY NOTES OR VIDEOS OF THE ORGANIZATION FOR MY STUDY, I WILL NOT BUY AND SELL IN ANY WAY.
// 2. I WILL NOT GIVE MY MOBILE NUMBER OR SOCIAL MEDIA LINK TO ANYONE TO MAINTAIN THE PRIVACY OF THE ORGANIZATION, AS THIS RIGHT COMES UNDER THE PRIVACY ACT, SO BY DOING SO, THE ORGANIZATION CAN ALSO TAKE LEGAL ACTION.
// METHODOLOGY
// ALL COURSES HAVE DIFFERENT STARTING DATES AND DIFFERENT FEES AND ALL INFORMATION IS STATED IN OUR WEBSITE AND APPLICATION. THE CONTENT OF THE PAGES OF THIS WEBSITE IS FOR YOUR GENERAL INFORMATION AND USE ONLY. IT IS SUBJECT TO CHANGE WITHOUT NOTICE. NEITHER WE NOR ANY THIRD PARTIES PROVIDE ANY WARRANTY OR GUARANTEE AS TO THE ACCURACY, TIMELINESS, PERFORMANCE, COMPLETENESS OR SUITABILITY OF THE INFORMATION AND MATERIALS FOUND OR OFFERED ON THIS WEBSITE FOR ANY PARTICULAR PURPOSE. YOU ACKNOWLEDGE THAT SUCH INFORMATION AND MATERIALS MAY CONTAIN INACCURACIES OR ERRORS AND WE EXPRESSLY EXCLUDE LIABILITY FOR ANY SUCH INACCURACIES OR ERRORS TO THE FULLEST EXTENT PERMITTED BY LAW.
// THE SYLLABUS IS DIVIDED INTO VARIOUS UNITS SUCH THAT THE COURSE IS PLANNED TO BE COMPLETED WITHIN A SPECIFIC TIME FRAME OR ENTIRE. THE COURSE SCHEDULE MAY BE CHANGED ANYTIME.
// THE STUDENT IS EXPECTED TO ATTEND ALL THE CLASSES AND TESTS TO GET THE FULL BENEFIT OF THE COURSE AND SUSTAIN HIS ELIGIBILITY FOR COURSE BENEFITS.
// REGISTRATION/ADMISSION TO A COURSE IS NOT TRANSFERABLE TO ANY OTHER INDIVIDUAL/ENTITY. STUDENTS HAVE NO ENTITLEMENT TO PROVIDE, SELL OR LOAN ANY MATERIAL, ASSETS, WORKBOOKS, NOTES ETC PROVIDED TO HIM, TO A THIRD PARTY. IF ANY STUDENT FIND OUT WITH SHARING THE ID AND PASSWORD WITH OTHER STUDENTS THEN HIS/HER CLASSES WILL BE BLOCKED. STUDENTS WILL BE ISSUED WITH ID CARDS UPON REGISTRATION, WHICH IS EXPECTED TO BE THERE WITH STUDENTS AT ALL TIME DURING CLASSES. AND PRODUCE IT UPON REQUEST. (FACE TO FACE CLASSES) YOUR USE OF OUR PRODUCTS AND SERVICES PROVIDED FOR HEREIN IS SOLELY FOR YOUR PERSONAL AND NON-COMMERCIAL USE. ANY USE OF THE PEN DRIVE OR ITS CONTENT OTHER THAN FOR PERSONAL PURPOSES IS PROHIBITED. YOUR PERSONAL AND NON-COMMERCIAL USE OF THIS PEN DRIVE AND / OR OUR SERVICES SHALL BE SUBJECTED TO THE FOLLOWING RESTRICTIONS: • YOU MAY NOT DECOMPILE,
// REVERSE ENGINEER, OR DISASSEMBLE THE CONTENTS OF THE PEN DRIVE AND / OR OUR PRODUCTS, OR REMOVE ANY COPYRIGHT, TRADEMARK REGISTRATION, OR OTHER PROPRIETARY NOTICES FROM THE CONTENTS OF THE PEN DRIVE AND / OR OUR PRODUCTS.
// • YOU WILL NOT
// (A) USE OUR PRODUCT OR SERVICE FOR COMMERCIAL PURPOSES OF ANY KIND, OR
// (B) ADVERTISE OR SELL ANY PRODUCTS, SERVICES OR OTHERWISE (WHETHER OR NOT FOR PROFIT), OR SOLICIT OTHERS (INCLUDING, WITHOUT LIMITATION, SOLICITATIONS FOR CONTRIBUTIONS OR DONATIONS) OR USE ANY PUBLIC FORUM FOR COMMERCIAL PURPOSES OF ANY KIND, OR
// (C) USE THE PEN DRIVE AND / OR OUR PRODUCTS AND SERVICES IN ANY WAY THAT IS UNLAWFUL, OR HARMS SHIROMANI INSTITUTE.
// 10. WE MAY ALSO CONTACT THE USER THROUGH SMS, EMAIL AND CALL TO GIVE NOTIFICATIONS ON VARIOUS IMPORTANT UPDATES. THEREFORE, SHIROMANI INSTITUTE IS NON LIABLE TO ANY LIABILITIES INCLUDING FINANCIAL PENALTIES, DAMAGES, EXPENSES IN CASE THE USERS MOBILE NUMBER IS REGISTERED WITH DO NOT CALL (DNC) DATABASE.

// DISCIPLINE–

// STUDENTS ARE ADVISED-
// 1. DO NOT CARRY ANY VALUABLES TO THE STUDY CENTER AND THE MANAGEMENT IS NOT RESPONSIBLE FOR ANY LOSS OR DAMAGE OF THE SAME. ARE EXPECTED TO KEEP THE UTMOST LEVEL OF DISCIPLINE, WHILE THEY ARE IN THE STUDY CENTER PREMISES. A STUDENT SHOULD NOT INVOLVE HIM/HERSELF IN DISRUPTION/DISTURBANCE OF TEACHING STUDENT, ADMINISTRATIVE WORK, AND CURRICULAR ACTIVITY, INCLUDING ANY ATTEMPT TO PREVENT ANY STAFF MEMBER OF SHIROMANI INSTITUTE FROM CARRYING ON HIS/HER WORK AND ANY ACT REASONABLY LIKE TO CAUSE SUCH DISRUPTION.
// NOT TO INVOLVE IN DAMAGING OR DEFACING STUDY CENTER'S PROPERTY, EQUIPMENT AND FACILITIES AVAILABLE AT THE STUDY CENTER. STUDENTS ARE EXPECTED TO BEHAVE RESPONSIBLY AND HELP SHIROMANI INSTITUTE FOR BETTER UPKEEP AND MAINTENANCE OF THE PROPERTY AND EQUIPMENT AVAILABLE AT THE STUDY CENTER.
// THE STUDENT SHOULD NOT USE ABUSIVE AND DEROGATORY SLOGANS AT INTIMIDATOR LANGUAGE WHILE THEY ARE PRESENT AT THE STUDY CENTER. EATABLES AND ANY TYPE OF DRINKING ITEMS ARE STRICTLY PROHIBITED IN THE
// CLASSROOMS.
// STUDENTS SHOULD KEEP THEIR MOBILE PHONE ON SILENT MODE. STAFF MEMBERS OF SHIROMANI INSTITUTE ARE EMPOWERED TO WITHHOLD/ CONFISCATE IN THE INTEREST OF OTHER STUDENTS. SHIROMANI INSTITUTE MAY LEVY A SUITABLE PENALTY OR FINE A STUDENT INCLUDING CANCELLATION OF REGISTRATION FOR VIOLATION OF GUIDELINES.
// TRANSFERS

// IN CASE A STUDENT DESIRES TO SEEK A TRANSFER FROM ONE STUDY CENTER TO ANOTHER, THEN HE/SHE HAS TO MAKE AN APPLICATION, STATING THE REASONS FOR THE REQUEST.
// A TRANSFER IS SUBJECT TO-
// AVAILABILITY OF SEATS. THE DIFFERENCE IN THE COURSE FEE, UPON TRANSFERS, SHALL BE PAID BY THE STUDENT ADDITIONALLY.

// PERFORMANCE
// 1. A STUDENT MUST ENSURE TO ATTEND ALL THE CLASSES.

// WHILE SHIROMANI INSTITUTE ARE AIMED AT ENHANCING THE ACADEMIC PERFORMANCE OF A STUDENT TO SCORE HIGH MARKS, IT IS ESSENTIAL THAT THE STUDENT DISPLAYS CONTINUING, PARTICIPATIVE INTEREST AND INVOLVEMENT DURING THE CLASSES. MERELY JOINING AT SHIROMANI INSTITUTE DOES NOT ASSURE HIGH MARKS. MERELY JOINING AT SHIROMANI INSTITUTE DOES NOT ASSURE HIGH MARKS.

// COMMUNICATION TO THE STUDENTS
// 1. COMMUNICATION REGARDING CHANGES IN SPECIFICATIONS OF SERVICES PROVIDED BY SHIROMANI INSTITUTE OR CHANGES IN STUDENT'S RULES WILL BE INTIMATED THROUGH THE WEBSITE.

// IF THERE IS ANY CLASS CANCELLATION OR CHANGE IN THE SCHEDULE OF THE PROGRAM, STUDENTS SHALL BE INFORMED BY MAIL/SMS/THROUGH THE SHIROMANI INSTITUTE WEBSITE.
// STUDENTS ARE RESPONSIBLE TO CHECK FOR UPDATES FROM TIME TO TIME.

// STUDENT FEEDBACK & GRIEVANCE REDRESSAL
// 1. IN ORDER TO IMPROVE THE QUALITY OF SERVICES PROVIDED TO STUDENTS, SHIROMANI INSTITUTE ACTIVELY SEEKS, APPRECIATES AND ACTS UPON FEEDBACK FROM STUDENTS ABOUT ITS SERVICES FROM TIME TO TIME. STUDENTS/PARENTS CAN APPROACH THE RECEPTION, WITH THEIR QUERIES FOR
// ASSISTANCE GUIDANCE.
// STUDENTS MAY TAKE A PHOTOCOPY OF THE REGISTRATION FORM FOR FUTURE REFERENCE. SHIROMANI INSTITUTE RESERVES THE RIGHT TO TAKE DISCIPLINARY ACTION, INCLUDING SUSPENSION OF THE STUDENTS FROM THE CLASSES, IN CASE THE STUDENT IS FOUND NOT FOLLOWING ANY RULES AND REGULATIONS AT THE STUDY CENTER. SHIROMANI INSTITUTE DECISION IN ANY OF THE ABOVE SHALL BE FINAL AND BINDING.
// ALL DISPUTES ARE SUBJECT TO DELHI JURISDICTION COURTS OF LAW.

// NOTE- I TOOK THE DEMO CLASS OF THE INSTITUTION, AFTER EXAMINING ITS QUALITY A LOT, I AM TAKING ADMISSION, OUR RESULT DEPENDS ON OUR HARD WORK, THE INSTITUTION WILL NOT BE RESPONSIBLE FOR THIS, IN FUTURE I WILL TAKE THE CLASS OR NOT, THE INSTITUTION IS NOT RESPONSIBLE FOR THIS YES, I WILL NEVER ASK FOR THE FEE BACK IN FUTURE. THE INSTITUTION WILL NOT BE BOUND TO RETURN THIS FEE.

// PARENTS RISK - WHEN THE AGE OF THE STUDENT IS LESS THAN 18 YEARS, THIS FORM IS BEING FILLED BY THE PARENTS, ALL THE RESPONSIBILITY WILL BE OF THE PARENTS.

// THANKS & REGARDS

// RASHI
// BUSINESS DEVELOPER MANAGER
// SHIROMANI INSTITUTE PVT LTD </pre>";

						WL_MIM_SMSHelper::send_email( $institute_id, $email, $subject, $body );
				}
				$wpdb->query('COMMIT;');
				/* Get SMS template */
				$sms_template_student_registered = WL_MIM_SettingHelper::get_sms_template_student_registered($institute_id);

				/* Get SMS settings */
				$sms = WL_MIM_SettingHelper::get_sms_settings($institute_id);

				if ($sms_template_student_registered['enable']) {
					$sms_message = $sms_template_student_registered['message'];
					$sms_message = str_replace('[ENROLLMENT_ID]', WL_MIM_Helper::get_enrollment_id_with_prefix($student_id, $general_enrollment_prefix), $sms_message);
					$sms_message = str_replace('[USERNAME]', $username, $sms_message);
					$sms_message = str_replace('[PASSWORD]', $password, $sms_message);
					$sms_message = str_replace('[LOGIN_URL]', admin_url('admin.php?page=multi-institute-management-student-dashboard'), $sms_message);
					/* Send SMS */
					WL_MIM_SMSHelper::send_sms($sms, $institute_id, $sms_message, $phone);
				}

				wp_send_json_success(array('message' => esc_html__('Student added successfully.', WL_MIM_DOMAIN)));
			} catch (Exception $exception) {
				$wpdb->query('ROLLBACK;');
				wp_send_json_error($exception->getMessage());
			}
		}
		wp_send_json_error($errors);
	}

	/* Bulk Import new students */
	public static function import_students()
	{
		self::check_permission();
		if (!wp_verify_nonce($_POST['import-student'], 'import-student')) {
			die();
		}
		try {
			ob_start();
			global $wpdb;

			// Start validation.
			$errors = array();
			$import_file = (isset($_FILES['import_file']) && is_array($_FILES['import_file'])) ? $_FILES['import_file'] : NULL;

			if (isset($import_file['tmp_name']) && !empty($import_file['tmp_name'])) {
				if (!WL_MIM_Helper::is_valid_file($import_file, 'csv')) {
					$errors['import_file'] = esc_html__('Please provide valid csv file.', WL_MIM_DOMAIN);
				}
			} else {
				$errors['import_file'] = esc_html__('Please provide valid csv file.', WL_MIM_DOMAIN);
			}

			if (count($errors) >= 1) {
				wp_send_json_error($errors);
			}
		} catch (Exception $exception) {
			$buffer = ob_get_clean();
			if (!empty($buffer)) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error($response);
		}
		if (count($errors) < 1) {
			try {
				$csv_file     = fopen($import_file['tmp_name'], 'r');
				$keys_arr     = fgetcsv($csv_file);
				$suceccfully  = $row = 1;
				$_keys        = $students_data =  array();
				$institute_id = WL_MIM_Helper::get_current_institute_id();
				extract($keys_arr);
				foreach ($keys_arr as $key => $value) {
					$_keys[$value] = $key;
				}

				while ($line = fgetcsv($csv_file)) {
					$row++;
					$data = $login_data = null;
					$course = $fees = "";
					$data['institute_id'] = $institute_id;
					$allow_login = 0;

					if (in_array('course_code', $keys_arr) && !empty($line[$_keys['course_code']])) {
						$course_code = sanitize_text_field($line[$_keys['course_code']]);
						$course = WL_MIM_Helper::get_course_by_code($course_code);
						if (strtolower(trim($course_code)) === strtolower(trim($course->course_code))) {
							$data['course_id'] = $course->id;
							$fees = serialize(
								array(
									'type' => array($course->course_name),
									'payable' => array($course->fees),
									'paid' => array('0.00'),
									'period' => array($course->period),
								)
							);
							$data['fees'] = $fees;
						} else {
							wp_send_json_error(array('import_file' => esc_html__('In row ' . ($row - 1) . ' course_code Not related To any cource', WL_MIM_DOMAIN)));
						}
					}

					if (in_array('batch_code', $keys_arr) && !empty($line[$_keys['batch_code']])) {
						$batch_code = sanitize_text_field($line[$_keys['batch_code']]);
						$batch = WL_MIM_Helper::get_batch_by_course_id($course->id);
						if (strtolower(trim($batch_code)) === strtolower(trim($batch->batch_code))) {
							$data['batch_id'] = $batch->id;
						} else {
							wp_send_json_error(array('import_file' => esc_html__('In row ' . ($row - 1) . ' batch_code Not related To any batch', WL_MIM_DOMAIN)));
						}
					}

					if (in_array('first_name', $keys_arr) && !empty($line[$_keys['first_name']])) {
						$login_data['first_name'] = $data['first_name'] = sanitize_text_field($line[$_keys['first_name']]);
					}

					if (in_array('last_name', $keys_arr) && !empty($line[$_keys['last_name']])) {
						$login_data['last_name'] = $data['last_name'] = sanitize_text_field($line[$_keys['last_name']]);
					}

					if (in_array('gender', $keys_arr) && !empty($line[$_keys['gender']])) {
						$data['gender'] = strtolower(sanitize_text_field($line[$_keys['gender']]));
					}

					if (in_array('date_of_birth', $keys_arr) && !empty($line[$_keys['date_of_birth']])) {
						$date_of_birth = date("Y-m-d", strtotime(sanitize_text_field($line[$_keys['date_of_birth']])));
						if (!empty($date_of_birth) && (strtotime(date('Y') - 2) <= strtotime($date_of_birth))) {
							wp_send_json_error(array('import_file' => esc_html__('In row ' . ($row - 1) . ' provide valid date_of_birth.', WL_MIM_DOMAIN)));
						}
						$data['date_of_birth'] = $date_of_birth;
					}

					if (in_array('father_name', $keys_arr) && !empty($line[$_keys['father_name']])) {
						$data['father_name'] = sanitize_text_field($line[$_keys['father_name']]);
					}

					if (in_array('mother_name', $keys_arr) && !empty($line[$_keys['mother_name']])) {
						$data['mother_name'] = sanitize_text_field($line[$_keys['mother_name']]);
					}

					if (in_array('address', $keys_arr) && !empty($line[$_keys['address']])) {
						$data['address'] = sanitize_text_field($line[$_keys['address']]);
					}

					if (in_array('city', $keys_arr) && !empty($line[$_keys['city']])) {
						$data['city'] = sanitize_text_field($line[$_keys['city']]);
					}

					if (in_array('zip', $keys_arr) && !empty($line[$_keys['zip']])) {
						$data['zip'] = sanitize_text_field($line[$_keys['zip']]);
					}

					if (in_array('state', $keys_arr) && !empty($line[$_keys['state']])) {
						$data['state'] = sanitize_text_field($line[$_keys['state']]);
					}

					if (in_array('nationality', $keys_arr) && !empty($line[$_keys['nationality']])) {
						$data['nationality'] = sanitize_text_field($line[$_keys['nationality']]);
					}

					if (in_array('phone', $keys_arr) && !empty($line[$_keys['phone']])) {
						$data['phone'] = sanitize_text_field($line[$_keys['phone']]);
					}

					if (in_array('qualification', $keys_arr) && !empty($line[$_keys['qualification']])) {
						$data['qualification'] = sanitize_text_field($line[$_keys['qualification']]);
					}

					if (in_array('is_active', $keys_arr) && !empty($line[$_keys['is_active']])) {
						$data['is_active'] = (bool) sanitize_text_field($line[$_keys['is_active']]);
					}
					if (in_array('email', $keys_arr) && !empty($line[$_keys['email']])) {
						$email = sanitize_email($line[$_keys['email']]);
						if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
							wp_send_json_error(array('import_file' => esc_html__('In row ' . ($row - 1) . ' provide a valid email address.', WL_MIM_DOMAIN)));
						}
						$user = get_user_by('email', $email);
						if (!empty($user)) {
							wp_send_json_error(array('import_file' => esc_html__('In row ' . ($row - 1) . ' User Exist with email address.', WL_MIM_DOMAIN)));
						}
						$data['email'] = $email;
						$login_data['email'] = $email;
					}

					if (in_array('username', $keys_arr) && !empty($line[$_keys['username']])) {
						$login_data['user_login'] = sanitize_text_field($line[$_keys['username']]);

						$user = get_user_by('login', $login_data['user_login']);
						if (!empty($user)) {
							wp_send_json_error(array('import_file' => esc_html__('In row ' . ($row - 1) . ' User Exist with username - ' . $login_data['user_login'], WL_MIM_DOMAIN)));
						}
						$allow_login = true;
					}

					if (in_array('password', $keys_arr) && !empty($line[$_keys['password']])) {
						$login_data['user_pass'] = sanitize_text_field($line[$_keys['password']]);
					}

					// Data Saved in Array for insert
					if (!empty($login_data) && !empty($data)) {
						$students_data[] = array(
							'login_data' => $login_data,
							'student_data' => $data,
						);
					} else {
						wp_send_json_error(array('import_file' => esc_html__('In row ' . ($row - 1) . ' Data Not Exist', WL_MIM_DOMAIN)));
					}
				}
				if ($row - 1 !== count($students_data)) {
					wp_send_json_error(array('import_file' => esc_html__('Some data not Correct.', WL_MIM_DOMAIN)));
				}

				foreach ($students_data as $key => $student) {
					extract($student);

					$user_id = wp_insert_user($login_data);
					if (is_wp_error($user_id)) {
						wp_send_json_error(esc_html__($user_id->get_error_message(), WL_MIM_DOMAIN));
					}

					$user = new WP_User($user_id);
					$user->add_cap(WL_MIM_Helper::get_student_capability());

					if ($user_id) {
						$student_data['user_id']     = $user_id;
						$student_data['allow_login'] = true;
						update_user_meta($user_id, 'wlim_institute_id', $institute_id);
					}
					$student_data['created_at'] = current_time('Y-m-d H:i:s');

					$success = $wpdb->insert("{$wpdb->prefix}wl_min_students", $student_data);
					if (!$success) {
						throw new Exception(esc_html__('An unexpected error occurred.', WL_MIM_DOMAIN));
					} else {
						$suceccfully++;
					}
					$student_id = $wpdb->insert_id;

					$wpdb->query('COMMIT;');

					// Get SMS template
					$sms_template_student_registered = WL_MIM_SettingHelper::get_sms_template_student_registered($institute_id);

					// Get SMS settings
					$sms = WL_MIM_SettingHelper::get_sms_settings($institute_id);

					if ($sms_template_student_registered['enable']) {
						$sms_message = $sms_template_student_registered['message'];
						$sms_message = str_replace('[ENROLLMENT_ID]', WL_MIM_Helper::get_enrollment_id_with_prefix($student_id, $general_enrollment_prefix), $sms_message);
						$sms_message = str_replace('[USERNAME]', $username, $sms_message);
						$sms_message = str_replace('[PASSWORD]', $password, $sms_message);
						$sms_message = str_replace('[LOGIN_URL]', admin_url('admin.php?page=multi-institute-management-student-dashboard'), $sms_message);
						// Send SMS
						WL_MIM_SMSHelper::send_sms($sms, $institute_id, $sms_message, $phone);
					}
				}

				fclose($csv_file);
				if ($row == $suceccfully) {
					$message = esc_html__('Students imported successfully.', WL_MIM_DOMAIN);
				} elseif ($suceccfully > 1) {
					$message = esc_html__('Some Students imported successfully.', WL_MIM_DOMAIN);
				} else {
					$message = esc_html__('Students not imported !!.', WL_MIM_DOMAIN);
					wp_send_json_error(array('import_file' => $message));
				}
				wp_send_json_success(array('message' => $message));
			} catch (Exception $exception) {
				$wpdb->query('ROLLBACK;');
				fclose($csv_file);
				wp_send_json_error($exception->getMessage());
			}
		}
		wp_send_json_error($errors);
	}

	// Bulk action
	public static function bulk_action()
	{
		$action = isset($_POST['bulk_action']) ? sanitize_text_field($_POST['bulk_action']) : '';
		$entity = isset($_POST['entity']) ? sanitize_text_field($_POST['entity']) : '';

		if (!wp_verify_nonce($_POST['nonce'], 'bulk-action-' . $entity)) {
			die();
		}

		if (empty($action)) {
			wp_send_json_error(esc_html__('Please select an option.', WL_MIM_DOMAIN));
		}

		$method_name = $action . '_' . $entity;


		// Call action_entity() method.
		if (!method_exists('WL_MIM_Student', $method_name)) {
			wp_send_json_error(esc_html__('Invalid selection.', WL_MIM_DOMAIN));
		}
		self::$method_name();
	}


	public static function delete_students()
	{
		global $wpdb;

		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$student_ids = (isset($_POST['bulk_values']) && is_array($_POST['bulk_values'])) ? array_map('absint', $_POST['bulk_values']) : array();

		if (empty($student_ids)) {
			wp_send_json_error(esc_html__('Please select atleast one student.', WL_MIM_DOMAIN));
		}

		try {
			$wpdb->query('BEGIN;');

			ob_start();

			foreach ($student_ids as $student_id) {
				// Checks if student exists.
				$student = WL_MIM_StudentHelper::fetch_student($institute_id, $student_id);

				if (!$student) {
					throw new Exception(esc_html__('Student not found.', WL_MIM_DOMAIN));
				}

				$success = $wpdb->delete("{$wpdb->prefix}wl_min_students", array('id' => $student_id));

				WL_MIM_StudentHelper::check_buffer();

				if (false === $success) {
					throw new Exception($wpdb->last_error);
				}
			}

			$wpdb->query('COMMIT;');

			$message = esc_html__('Students deleted successfully.', WL_MIM_DOMAIN);

			wp_send_json_success(array('message' => $message));
		} catch (Exception $exception) {
			$wpdb->query('ROLLBACK;');
			wp_send_json_error($exception->getMessage());
		}
	}

	public static function delete_inquiry()
	{
		global $wpdb;

		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$inquiry_ids = (isset($_POST['bulk_values']) && is_array($_POST['bulk_values'])) ? array_map('absint', $_POST['bulk_values']) : array();

		if (empty($inquiry_ids)) {
			wp_send_json_error(esc_html__('Please select atleast one inquiry.', WL_MIM_DOMAIN));
		}

		try {
			$wpdb->query('BEGIN;');

			ob_start();

			foreach ($inquiry_ids as $inquiry_id) {
				// Checks if student exists.
				$inquiry = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_enquiries WHERE is_deleted = 0 AND id = $inquiry_id AND institute_id = $institute_id");

				if (!$inquiry) {
					throw new Exception(esc_html__('Enquiry not found.', WL_MIM_DOMAIN));
				}

				$success = $wpdb->update(
					"{$wpdb->prefix}wl_min_enquiries",
					array(
						'is_deleted' => 1,
						'deleted_at' => date('Y-m-d H:i:s')
					),
					array('is_deleted' => 0, 'id' => $inquiry_id, 'institute_id' => $institute_id)
				);

				WL_MIM_StudentHelper::check_buffer();

				if (false === $success) {
					throw new Exception($wpdb->last_error);
				}
			}

			$wpdb->query('COMMIT;');

			$message = esc_html__('inquiry deleted successfully.', WL_MIM_DOMAIN);

			wp_send_json_success(array('message' => $message));
		} catch (Exception $exception) {
			$wpdb->query('ROLLBACK;');
			wp_send_json_error($exception->getMessage());
		}
	}

	public static function delete_course()
	{
		global $wpdb;

		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$course_ids = (isset($_POST['bulk_values']) && is_array($_POST['bulk_values'])) ? array_map('absint', $_POST['bulk_values']) : array();

		if (empty($course_ids)) {
			wp_send_json_error(esc_html__('Please select atleast one course.', WL_MIM_DOMAIN));
		}

		try {
			$wpdb->query('BEGIN;');

			ob_start();

			foreach ($course_ids as $course_id) {
				// Checks if student exists.
				$course = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND id = $course_id AND institute_id = $institute_id");

				if (!$course) {
					throw new Exception(esc_html__('Course not found.', WL_MIM_DOMAIN));
				}

				$success = $wpdb->update(
					"{$wpdb->prefix}wl_min_courses",
					array(
						'is_deleted' => 1,
						'deleted_at' => date('Y-m-d H:i:s')
					),
					array('is_deleted' => 0, 'id' => $course_id, 'institute_id' => $institute_id)
				);

				WL_MIM_StudentHelper::check_buffer();

				if (false === $success) {
					throw new Exception($wpdb->last_error);
				}
			}

			$wpdb->query('COMMIT;');

			$message = esc_html__('course deleted successfully.', WL_MIM_DOMAIN);

			wp_send_json_success(array('message' => $message));
		} catch (Exception $exception) {
			$wpdb->query('ROLLBACK;');
			wp_send_json_error($exception->getMessage());
		}
	}

	public static function delete_note()
	{
		global $wpdb;

		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$note_ids = (isset($_POST['bulk_values']) && is_array($_POST['bulk_values'])) ? array_map('absint', $_POST['bulk_values']) : array();

		if (empty($note_ids)) {
			wp_send_json_error(esc_html__('Please select atleast one note.', WL_MIM_DOMAIN));
		}

		try {
			$wpdb->query('BEGIN;');

			ob_start();

			foreach ($note_ids as $note_id) {
				// Checks if student exists.
				$note = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_notes WHERE id = $note_id AND institute_id = $institute_id");

				if (!$note) {
					throw new Exception(esc_html__('study material not found.', WL_MIM_DOMAIN));
				}

				$success = $wpdb->delete("{$wpdb->prefix}wl_min_notes", array(
					'id'           => $note_id,
					'institute_id' => $institute_id
				));

				WL_MIM_StudentHelper::check_buffer();

				if (false === $success) {
					throw new Exception($wpdb->last_error);
				}
			}

			$wpdb->query('COMMIT;');

			$message = esc_html__('note deleted successfully.', WL_MIM_DOMAIN);

			wp_send_json_success(array('message' => $message));
		} catch (Exception $exception) {
			$wpdb->query('ROLLBACK;');
			wp_send_json_error($exception->getMessage());
		}
	}



	public static function delete_batch()
	{
		global $wpdb;

		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$batch_ids = (isset($_POST['bulk_values']) && is_array($_POST['bulk_values'])) ? array_map('absint', $_POST['bulk_values']) : array();

		if (empty($batch_ids)) {
			wp_send_json_error(esc_html__('Please select atleast one batch.', WL_MIM_DOMAIN));
		}

		try {
			$wpdb->query('BEGIN;');

			ob_start();

			foreach ($batch_ids as $batch_id) {
				// Checks if student exists.
				$batch = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND id = $batch_id AND institute_id = $institute_id");

				if (!$batch) {
					throw new Exception(esc_html__('Enquiry not found.', WL_MIM_DOMAIN));
				}

				$success = $wpdb->update(
					"{$wpdb->prefix}wl_min_batches",
					array(
						'is_deleted' => 1,
						'deleted_at' => date('Y-m-d H:i:s')
					),
					array('is_deleted' => 0, 'id' => $batch_id, 'institute_id' => $institute_id)
				);

				WL_MIM_StudentHelper::check_buffer();

				if (false === $success) {
					throw new Exception($wpdb->last_error);
				}
			}

			$wpdb->query('COMMIT;');

			$message = esc_html__('batch deleted successfully.', WL_MIM_DOMAIN);

			wp_send_json_success(array('message' => $message));
		} catch (Exception $exception) {
			$wpdb->query('ROLLBACK;');
			wp_send_json_error($exception->getMessage());
		}
	}

	public static function delete_installment()
	{
		global $wpdb;

		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$installment_ids = (isset($_POST['bulk_values']) && is_array($_POST['bulk_values'])) ? array_map('absint', $_POST['bulk_values']) : array();

		if (empty($installment_ids)) {
			wp_send_json_error(esc_html__('Please select atleast one installment.', WL_MIM_DOMAIN));
		}

		try {
			$wpdb->query('BEGIN;');

			ob_start();

			foreach ($installment_ids as $installment_id) {
				// Checks if student exists.
				$installment = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_installments WHERE is_deleted = 0 AND id = $installment_id AND institute_id = $institute_id");

				if (!$installment) {
					throw new Exception(esc_html__('Enquiry not found.', WL_MIM_DOMAIN));
				}

				$success = $wpdb->update(
					"{$wpdb->prefix}wl_min_installments",
					array(
						'is_deleted' => 1,
						'deleted_at' => date('Y-m-d H:i:s')
					),
					array('is_deleted' => 0, 'id' => $installment_id, 'institute_id' => $institute_id)
				);
				WL_MIM_StudentHelper::check_buffer();

				if (false === $success) {
					throw new Exception($wpdb->last_error);
				}
			}

			$wpdb->query('COMMIT;');

			$message = esc_html__('installment deleted successfully.', WL_MIM_DOMAIN);

			wp_send_json_success(array('message' => $message));
		} catch (Exception $exception) {
			$wpdb->query('ROLLBACK;');
			wp_send_json_error($exception->getMessage());
		}
	}

	public static function delete_expense()
	{
		global $wpdb;

		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$expense_ids = (isset($_POST['bulk_values']) && is_array($_POST['bulk_values'])) ? array_map('absint', $_POST['bulk_values']) : array();

		if (empty($expense_ids)) {
			wp_send_json_error(esc_html__('Please select atleast one expense.', WL_MIM_DOMAIN));
		}

		try {
			$wpdb->query('BEGIN;');

			ob_start();

			foreach ($expense_ids as $expense_id) {
				// Checks if student exists.
				$expense = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_expense WHERE id = $expense_id AND institute_id = $institute_id");

				if (!$expense) {
					throw new Exception(esc_html__('Enquiry not found.', WL_MIM_DOMAIN));
				}

				$success = $wpdb->delete("{$wpdb->prefix}wl_min_expense", array(
					'id'           => $expense_id,
					'institute_id' => $institute_id
				));
				WL_MIM_StudentHelper::check_buffer();

				if (false === $success) {
					throw new Exception($wpdb->last_error);
				}
			}

			$wpdb->query('COMMIT;');

			$message = esc_html__('expense deleted successfully.', WL_MIM_DOMAIN);

			wp_send_json_success(array('message' => $message));
		} catch (Exception $exception) {
			$wpdb->query('ROLLBACK;');
			wp_send_json_error($exception->getMessage());
		}
	}

	public static function delete_notice()
	{
		global $wpdb;

		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$notice_ids = (isset($_POST['bulk_values']) && is_array($_POST['bulk_values'])) ? array_map('absint', $_POST['bulk_values']) : array();

		if (empty($notice_ids)) {
			wp_send_json_error(esc_html__('Please select atleast one notice.', WL_MIM_DOMAIN));
		}

		try {
			$wpdb->query('BEGIN;');

			ob_start();

			foreach ($notice_ids as $notice_id) {
				// Checks if student exists.
				$notice = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_notices WHERE is_deleted = 0 AND id = $notice_id AND institute_id = $institute_id");

				if (!$notice) {
					throw new Exception(esc_html__('Enquiry not found.', WL_MIM_DOMAIN));
				}

				$success = $wpdb->update(
					"{$wpdb->prefix}wl_min_notices",
					array(
						'is_deleted' => 1,
						'deleted_at' => date('Y-m-d H:i:s')
					),
					array('is_deleted' => 0, 'id' => $notice_id, 'institute_id' => $institute_id)
				);
				WL_MIM_StudentHelper::check_buffer();

				if (false === $success) {
					throw new Exception($wpdb->last_error);
				}
			}

			$wpdb->query('COMMIT;');

			$message = esc_html__('notice deleted successfully.', WL_MIM_DOMAIN);

			wp_send_json_success(array('message' => $message));
		} catch (Exception $exception) {
			$wpdb->query('ROLLBACK;');
			wp_send_json_error($exception->getMessage());
		}
	}

	public static function delete_exam()
	{
		global $wpdb;

		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$exam_ids = (isset($_POST['bulk_values']) && is_array($_POST['bulk_values'])) ? array_map('absint', $_POST['bulk_values']) : array();

		if (empty($exam_ids)) {
			wp_send_json_error(esc_html__('Please select atleast one exam.', WL_MIM_DOMAIN));
		}

		try {
			$wpdb->query('BEGIN;');

			ob_start();

			foreach ($exam_ids as $exam_id) {
				// Checks if student exists.
				$exam = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND institute_id = $institute_id");

				if (!$exam) {
					throw new Exception(esc_html__('Enquiry not found.', WL_MIM_DOMAIN));
				}

				$success = $wpdb->update(
					"{$wpdb->prefix}wl_min_exams",
					array(
						'is_deleted' => 1,
						'deleted_at' => date('Y-m-d H:i:s')
					),
					array('is_deleted' => 0, 'id' => $exam_id, 'institute_id' => $institute_id)
				);
				WL_MIM_StudentHelper::check_buffer();

				if (false === $success) {
					throw new Exception($wpdb->last_error);
				}
			}

			$wpdb->query('COMMIT;');

			$message = esc_html__('exam deleted successfully.', WL_MIM_DOMAIN);

			wp_send_json_success(array('message' => $message));
		} catch (Exception $exception) {
			$wpdb->query('ROLLBACK;');
			wp_send_json_error($exception->getMessage());
		}
	}

	/* Fetch student to update */
	public static function fetch_student()
	{
		self::check_permission();
		if (!wp_verify_nonce($_REQUEST['security'], 'wl-ima')) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings($institute_id);

		$general_enable_roll_number = WL_MIM_SettingHelper::get_general_enable_roll_number_settings($institute_id);

		$id  = intval(sanitize_text_field($_POST['id']));
		$row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id");
		if (!$row) {
			die();
		}
		$custom_fields = unserialize($row->custom_fields);

		$wlim_institute_active_categories = WL_MIM_Helper::get_active_categories_institute($institute_id);
		$wlim_active_courses              = WL_MIM_Helper::get_active_courses();

		$course = $wpdb->get_row("SELECT course_category_id, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE id = $row->course_id AND institute_id = $institute_id");

		$duration_in_month = WL_MIM_Helper::get_course_months_count($course->duration, $course->duration_in);

		$batches      = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND is_active = 1 AND course_id = $row->course_id AND institute_id = $institute_id ORDER BY id DESC");


		$username = '';
		if ($row->user_id) {
			$user              = $wpdb->get_row("SELECT * FROM {$wpdb->base_prefix}users WHERE ID = $row->user_id");
			$user_institute_id = get_user_meta($user->ID, 'wlim_institute_id', true);
			if ($user_institute_id !== $institute_id) {
				die();
			}
			$username = $user ? $user->user_login : '';
		}

		$data = date_format(date_create($row->created_at), "d-m-Y");

		$nonce = wp_create_nonce("update-student-$id");
		ob_start(); ?>
		<input type="hidden" name="update-student-<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($nonce); ?>">
		<input type="hidden" name="action" value="wl-mim-update-student">
		<div class="row" id="wlim-student-enrollment_id">
			<div class="col">
				<label class="col-form-label pb-0"><?php esc_html_e('Enrollment ID', WL_MIM_DOMAIN); ?>:</label>
				<div class="card mb-3 mt-2">
					<div class="card-block">
						<span class="text-dark"><?php echo WL_MIM_Helper::get_enrollment_id_with_prefix($row->enrollment_id, $general_enrollment_prefix); ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php
		if (count($wlim_institute_active_categories) > 0) { ?>
			<div class="form-group">
				<label for="wlim-student-category_update" class="col-form-label">* <?php _e("Category", WL_MIM_DOMAIN); ?>:</label>
				<select name="category" class="form-control" id="wlim-student-category_update">
					<option value="">-------- <?php _e("Select a Category", WL_MIM_DOMAIN); ?>--------</option>
					<?php
					foreach ($wlim_institute_active_categories as $active_category) { ?>
						<option <?php selected($course ? $course->course_category_id : '', $active_category->id, true); ?> value="<?php echo esc_attr($active_category->id); ?>"><?php echo esc_html($active_category->name); ?></option>
					<?php
					} ?>
				</select>
			</div>
			<div id="wlim-student-fetch-category-courses_update">
				<div class="form-group">
					<label for="wlim-student-course_update" class="col-form-label">* <?php _e("Admission For", WL_MIM_DOMAIN); ?>:</label>
					<select name="course" class="form-control selectpicker" id="wlim-student-course_update" data-batch_id='<?php echo esc_attr($row->batch_id); ?>'>
						<option value="">-------- <?php _e("Select a Course", WL_MIM_DOMAIN); ?> --------</option>
						<?php
						if (count($wlim_active_courses) > 0) {
							foreach ($wlim_active_courses as $active_course) { ?>
								<option value="<?php echo esc_attr($active_course->id); ?>">
									<?php echo esc_html("$active_course->course_name ($active_course->course_code) (" . __("Fees", WL_MIM_DOMAIN) . ": " . $active_course->fees . ")"); ?>
								</option>
						<?php
							}
						} ?>
					</select>
				</div>
			</div>
		<?php
		} else { ?>
			<div id="wlim-student-fetch-category-courses_update">
				<div class="form-group">
					<label for="wlim-student-course_update" class="col-form-label">* <?php _e("Admission For", WL_MIM_DOMAIN); ?>:</label>
					<select name="course" class="form-control selectpicker" id="wlim-student-course_update" data-batch_id='<?php echo esc_attr($row->batch_id); ?>'>
						<option value="">-------- <?php _e("Select a Course", WL_MIM_DOMAIN); ?> --------</option>
						<?php
						if (count($wlim_active_courses) > 0) {
							foreach ($wlim_active_courses as $active_course) { ?>
								<option value="<?php echo esc_attr($active_course->id); ?>">
									<?php echo esc_html("$active_course->course_name ($active_course->course_code) (" . __("Fees", WL_MIM_DOMAIN) . ": " . $active_course->fees . ")"); ?>
								</option>
						<?php
							}
						} ?>
					</select>
				</div>
			</div>
			<?php
		} ?><?php
			if (count($batches) > 0) { ?>
			<div id="wlim-add-student-course-update-batches">
				<div class="form-group pt-3">
					<label for="wlim-student-batch_update" class="col-form-label"><?php esc_html_e("Batch", WL_MIM_DOMAIN); ?> :</label>
					<select name="batch" class="form-control selectpicker" id="wlim-student-batch_update">
						<option value="">-------- <?php esc_html_e("Select a Batch", WL_MIM_DOMAIN); ?> --------</option>
						<?php
						foreach ($batches as $batch) {
							$time_from  = date("g:i A", strtotime($batch->time_from));
							$time_to    = date("g:i A", strtotime($batch->time_to));
							$timing     = "$time_from - $time_to";
							$batch_info = $batch->batch_code;
							if ($batch->batch_name) {
								$batch_info .= " ( $batch->batch_name )";
							}
						?>
							<option value="<?php echo esc_attr($batch->id); ?>"><?php echo esc_html($batch_info) . " ( " . esc_html($timing) . " ) ( " . WL_MIM_Helper::get_batch_status($batch->start_date, $batch->end_date) . " )"; ?></option>
						<?php
						} ?>
					</select>
				</div>
			</div>
		<?php
			} ?>

			<!-- Create a select input with options 1 to 10 for class selection -->
			<div class="form-group">
				<label for="wlim-enquiry-class-student" class="col-form-label"> <?php esc_html_e( "Select Class", WL_MIM_DOMAIN ); ?>:</label>
				<select name="class" class="form-control " id="wlim-enquiry-class-student">
					<option value=""> -------- <?php esc_html_e( "Select a Class", WL_MIM_DOMAIN ); ?> --------
					</option>
					<?php for($i = 1; $i <= 10; $i++): ?>
						<option value="<?php echo $i; ?>" <?php if($row->class == $i) echo 'selected'; ?> ><?php esc_html_e( 'Class '.$i, WL_MIM_DOMAIN ); ?></option>
					<?php endfor; ?>
				</select>
			</div>
			<div class="row">
			<div class="col-sm-6 form-group">
			<label for="wlim-enquiry-note" class="col-form-label"><?php esc_html_e( 'Business manager', WL_MIM_DOMAIN ); ?>:</label>
			<input name="business_manager" type="text" class="form-control" id="wlim-enquiry-business_manager" placeholder="<?php esc_html_e( "Business manager", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->business_manager ); ?>">
			</div>
		</div>

		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="wlim-student-first_name_update" class="col-form-label">* <?php esc_html_e('First Name', WL_MIM_DOMAIN); ?>:</label>
				<input name="first_name" type="text" class="form-control" id="wlim-student-first_name_update" placeholder="<?php esc_html_e("First Name", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->first_name); ?>">
			</div>
			<div class="col-sm-6 form-group">
				<label for="wlim-student-last_name_update" class="col-form-label"><?php esc_html_e('Last Name', WL_MIM_DOMAIN); ?>:</label>
				<input name="last_name" type="text" class="form-control" id="wlim-student-last_name_update" placeholder="<?php esc_html_e("Last Name", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->last_name); ?>">
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label class="col-form-label">* <?php esc_html_e('Gender', WL_MIM_DOMAIN); ?>:</label><br>
				<div class="row mt-2">
					<div class="col-sm-12">
						<label class="radio-inline mr-3"><input type="radio" name="gender" class="mr-2" value="male" id="wlim-student-male_update"><?php esc_html_e('Male', WL_MIM_DOMAIN); ?>
						</label>
						<label class="radio-inline"><input type="radio" name="gender" class="mr-2" value="female" id="wlim-student-female_update"><?php esc_html_e('Female', WL_MIM_DOMAIN); ?>
						</label>
					</div>
				</div>
			</div>
			<div class="col-sm-6 form-group">
				<label for="wlim-student-date_of_birth_update" class="col-form-label">* <?php esc_html_e('Date of Birth', WL_MIM_DOMAIN); ?>:</label>
				<input name="date_of_birth" type="text" class="form-control wlim-date_of_birth_update" id="wlim-student-date_of_birth_update" placeholder="<?php esc_html_e("Date of Birth", WL_MIM_DOMAIN); ?>">
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="wlim-student-father_name_update" class="col-form-label"><?php esc_html_e("Father's Name", WL_MIM_DOMAIN); ?>:</label>
				<input name="father_name" type="text" class="form-control" id="wlim-student-father_name_update" placeholder="<?php esc_html_e("Father's Name", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->father_name); ?>">
			</div>
			<div class="col-sm-6 form-group">
				<label for="wlim-student-mother_name_update" class="col-form-label"><?php esc_html_e("Mother's Name", WL_MIM_DOMAIN); ?>:</label>
				<input name="mother_name" type="text" class="form-control" id="wlim-student-mother_name_update" placeholder="<?php esc_html_e("Mother's Name", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->mother_name); ?>">
			</div>
		</div>
		<div class="row">
		<div class="col-sm-6 form-group">
				<label for="wlim-student-phone_update" class="col-form-label">* <?php esc_html_e('Father Phone', WL_MIM_DOMAIN); ?>:</label>
				<input name="phone" type="text" class="form-control" id="wlim-student-phone_update" placeholder="<?php esc_html_e("Phone", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->phone); ?>">
			</div>
			<div class="col-sm-6 form-group">
				<label for="wlim-student-phone_update" class="col-form-label">* <?php esc_html_e('Mother Phone', WL_MIM_DOMAIN); ?>:</label>
				<input name="phone2" type="text" class="form-control" id="wlim-student-phone_update" placeholder="<?php esc_html_e("Phone", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->phone2); ?>">
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="wlim-student-address_update" class="col-form-label"><?php esc_html_e('Address', WL_MIM_DOMAIN); ?>:</label>
				<textarea name="address" class="form-control" rows="4" id="wlim-student-address_update" placeholder="<?php esc_html_e("Address", WL_MIM_DOMAIN); ?>"><?php echo esc_html($row->address); ?></textarea>
			</div>
			<div class="col-sm-6 form-group">
				<div>
					<label for="wlim-student-city_update" class="col-form-label"><?php esc_html_e('City', WL_MIM_DOMAIN); ?>:</label>
					<input name="city" type="text" class="form-control" id="wlim-student-city_update" placeholder="<?php esc_html_e("City", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->city); ?>">
				</div>
				<div>
					<label for="wlim-student-zip_update" class="col-form-label"><?php esc_html_e('Zip Code', WL_MIM_DOMAIN); ?>:</label>
					<input name="zip" type="text" class="form-control" id="wlim-student-zip_update" placeholder="<?php esc_html_e("Zip Code", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->zip); ?>">
				</div>
			</div>
		</div>
		<div class="row">
			<!-- <div class="col-sm-6 form-group">
				<label for="wlim-student-state_update" class="col-form-label"><?php esc_html_e('State', WL_MIM_DOMAIN); ?>:</label>
				<input name="state" type="text" class="form-control" id="wlim-student-state_update" placeholder="<?php esc_html_e("State", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->state); ?>">
			</div> -->
			<?php $states = WL_MIM_Helper::get_states(); ?>
			<div class="form-group col-md-6">
				<label for="wlim-student-state_update"  class="col-form-label"><?php esc_html_e('State', WL_MIM_DOMAIN); ?>:</label>
				<select name="state" id="wlim-student-state_update" class="form-control">
				<option value="">Select State</option>
					<?php foreach ($states as $state): ?>
						<option value="<?php echo $state; ?>" <?php if($row->state == $state) echo 'selected';
							?>><?php echo esc_html($state); ?></option>
					<?php endforeach ?>
				</select>
		</div>
			<div class="col-sm-6 form-group">
				<label for="wlim-student-nationality_update" class="col-form-label"><?php esc_html_e('Nationality', WL_MIM_DOMAIN); ?>:</label>
				<input name="nationality" type="text" class="form-control" id="wlim-student-nationality_update" placeholder="<?php esc_html_e("Nationality", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->nationality); ?>">
			</div>
		</div>
		<div class="row">

			<div class="col-sm-6 form-group">
				<label for="wlim-student-email_update" class="col-form-label"><?php esc_html_e('Email', WL_MIM_DOMAIN); ?>:</label>
				<input name="email" type="text" class="form-control" id="wlim-student-email_update" placeholder="<?php esc_html_e("Email", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->email); ?>">
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="wlim-student-qualification_update" class="col-form-label"><?php esc_html_e('Course', WL_MIM_DOMAIN); ?>:</label>
				<input name="qualification" type="text" class="form-control" id="wlim-student-qualification_update" placeholder="<?php esc_html_e("Course", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->qualification); ?>">
			</div>
			<div class="col-sm-6 form-group">
				<label for="wlim-student-id_proof_update" class="col-form-label"><?php esc_html_e('ID Proof', WL_MIM_DOMAIN); ?>:</label><br>
				<?php if (!empty($row->id_proof)) { ?>
					<a href="<?php echo wp_get_attachment_url($row->id_proof); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><?php esc_html_e('View ID Proof', WL_MIM_DOMAIN); ?></a>
					<input type="hidden" name="id_proof_in_db" value="<?php echo esc_attr($row->id_proof); ?>">
				<?php } ?>
				<input name="id_proof" type="file" id="wlim-student-id_proof_update">
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="wlim-student-photo_update" class="col-form-label"><?php esc_html_e('Photo', WL_MIM_DOMAIN); ?>:</label><br>
				<?php if (!empty($row->photo_id)) { ?>
					<img src="<?php echo wp_get_attachment_url($row->photo_id); ?>" class="img-responsive photo-signature">
					<input type="hidden" name="photo_in_db" value="<?php echo esc_attr($row->photo_id); ?>">
				<?php } ?>
				<input name="photo" type="file" id="wlim-student-photo_update">
			</div>
			<div class="col-sm-6 form-group">
				<label for="wlim-student-signature_update" class="col-form-label"><?php esc_html_e('Signature', WL_MIM_DOMAIN); ?>:</label><br>
				<?php if (!empty($row->signature_id)) { ?>
					<img src="<?php echo wp_get_attachment_url($row->signature_id); ?>" class="img-responsive photo-signature">
					<input type="hidden" name="signature_in_db" value="<?php echo esc_attr($row->signature_id); ?>">
				<?php } ?>
				<input name="signature" type="file" id="wlim-student-signature_update">
			</div>
		</div>
		<?php
		if (isset($custom_fields['name']) && is_array($custom_fields['name']) && count($custom_fields['name'])) { ?>
			<div class="row">
				<?php
				foreach ($custom_fields['name'] as $key => $custom_field_name) { ?>
					<div class="col-sm-6 form-group">
						<label for="wlim-student-custom_fields_<?php echo esc_attr($key); ?>_update" class="col-form-label"><?php echo esc_html($custom_field_name); ?>:</label>
						<input type="hidden" name="custom_fields[name][]" value="<?php echo esc_attr($custom_field_name); ?>">
						<input name="custom_fields[value][]" type="text" class="form-control" id="wlim-student-custom_fields_<?php echo esc_attr($key); ?>_update" placeholder="<?php echo esc_attr($custom_field_name); ?>" value="<?php echo esc_attr($custom_fields['value'][$key]); ?>">
					</div>
				<?php
				} ?>
			</div>
		<?php
		} ?>

		<div class="row">
			<div class="form-group col-sm-6">
				<label for="wlim-student-created_at_update" class="col-form-label"><?php esc_html_e('Registration Date', WL_MIM_DOMAIN); ?>:</label>
				<input name="created_at" type="text" class="form-control wlim-created_at_update" id="wlim-student-created_at_update" placeholder="<?php esc_html_e('Registration Date', WL_MIM_DOMAIN); ?>" value="<?php echo date_format(date_create($row->created_at), 'd-m-Y'); ?>">
			</div>
			<div class="form-group col-sm-6">
				<label for="wlim-student-expired_at_update" class="col-form-label"><?php esc_html_e('Registration Expiry Date', WL_MIM_DOMAIN); ?>:</label>
				<input name="expire_at" type="text" class="form-control wlim-created_at_update" id="wlim-student-expired_at_update" placeholder="<?php esc_html_e('Registration Expiry Date', WL_MIM_DOMAIN); ?>" value="<?php echo date_format(date_create($row->expire_at), 'd-m-Y'); ?>">
			</div>
		</div>
		<?php if ($general_enable_roll_number) { ?>
			<div class="form-group">
				<label for="wlim-student-roll_number_update" class="col-form-label"><?php esc_html_e('Roll Number', WL_MIM_DOMAIN); ?>:</label>
				<input name="roll_number" type="text" class="form-control" id="wlim-student-roll_number_update" placeholder="<?php esc_html_e('Roll Number', WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->roll_number); ?>">
			</div>
		<?php } ?>
		<?php
		$sources       = WL_MIM_Helper::get_sources();
		$wlim_teachers = WL_MIM_Helper::get_staff_teachers();
		?>
	<div class="row">
		<div class="form-group col-md-4">
			<label for="wlim-student-source_update"  class="col-form-label"><?php esc_html_e('Source', WL_MIM_DOMAIN); ?>:</label>
			<select name="source" id="wlim-student-source_update" class="form-control">
			<option value="">Select Source</option>
				<?php foreach ($sources as $source): ?>
					<option value="<?php echo $source->source; ?>" <?php if($row->source == $source->source) echo 'selected';
						?>><?php echo esc_html($source->source); ?></option>
				<?php endforeach ?>
			</select>
		</div>

		<div class="form-group col-md-4">
			<label for="wlim-student-teacher_update"  class="col-form-label"><?php esc_html_e('Teacher', WL_MIM_DOMAIN); ?>:</label>
			<select name="teacher" id="wlim-student-teacher_update" class="form-control">
			<option value="">Select Teacher</option>
				<?php foreach ($wlim_teachers as $teacher): ?>
					<option value="<?php echo $teacher->first_name; ?>" <?php if($row->teacher == $teacher->first_name) echo 'selected';
						?> ><?php echo esc_html($teacher->first_name." ". $teacher->last_name); ?></option>
				<?php endforeach ?>
			</select>
		</div>

	</div>

		<hr>
		<div class="form-check pl-0">
			<input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlmp-student-is_active_update" <?php echo boolval($row->is_active) ? "checked" : ""; ?>>
			<label class="form-check-label" for="wlmp-student-is_active_update">
				<?php esc_html_e('Is Active?', WL_MIM_DOMAIN); ?>
			</label>
		</div>
		<hr>
		<div class="form-group">
			<label for="wlim-enquiry-student-status" class="col-form-label"> <?php esc_html_e( "Status", WL_MIM_DOMAIN ); ?>:</label>
			<select name="student_status" class="form-control " id="wlim-enquiry-student-status">
				<option value="processing" <?php echo ($row->student_status == "processing") ? esc_html_e("selected") : "";  ?>>Processing</option>
				<option value="approved" <?php echo ($row->student_status == "approved") ? esc_html_e("selected") : "";   ?>>Approved</option>
			</select>
		</div>
		<div class="form-check pl-0">
			<input name="allow_login" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-student-allow_login_update" <?php echo boolval($row->allow_login) ? "checked" : ""; ?>>
			<label class="form-check-label" for="wlim-student-allow_login_update">
				<strong class="text-primary"><?php esc_html_e('Allow Student to Login?', WL_MIM_DOMAIN); ?></strong>
			</label>
		</div>
		<div class="wlim-allow-login-fields">
			<hr>
			<div class="form-group">
				<label for="wlim-student-username_update" class="col-form-label"><?php esc_html_e('Username', WL_MIM_DOMAIN); ?>:
					<?php
					if ($username) { ?>
						&nbsp;
						<small class="text-secondary">
							<em><?php esc_html_e("cannot be changed.", WL_MIM_DOMAIN); ?></em>
						</small>
					<?php
					} ?>
				</label>
				<input name="username" type="text" class="form-control" id="wlim-student-username_update" placeholder="<?php esc_html_e("Username", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($username); ?>" <?php echo boolval($username) ? "disabled" : ''; ?>>
			</div>
			<div class="form-group">
				<label for="wlim-student-password_update" class="col-form-label"><?php esc_html_e('Password', WL_MIM_DOMAIN); ?>:</label>
				<input name="password" type="password" class="form-control" id="wlim-student-password_update" placeholder="<?php esc_html_e("Password", WL_MIM_DOMAIN); ?>">
			</div>
			<div class="form-group">
				<label for="wlim-student-password_confirm_update" class="col-form-label"><?php esc_html_e('Confirm Password', WL_MIM_DOMAIN); ?>:</label>
				<input name="password_confirm" type="password" class="form-control" id="wlim-student-password_confirm_update" placeholder="<?php esc_html_e("Confirm Password", WL_MIM_DOMAIN); ?>">
			</div>
		</div><input type="hidden" name="student_id" value="<?php echo esc_attr($row->id); ?>">
		<?php $html         = ob_get_clean();
		$wlim_date_selector = '.wlim-date_of_birth_update';
		$wlim_created_at_selector = '.wlim-created_at_update';

		$json = json_encode(array(
			'wlim_date_selector'       => esc_attr($wlim_date_selector),
			'wlim_created_at_selector' => esc_attr($wlim_created_at_selector),
			'course_id'                => esc_attr($row->course_id),
			'batch_id'                 => esc_attr($row->batch_id),
			'gender'                   => esc_attr($row->gender),
			'date_of_birth_exist'      => boolval($row->date_of_birth),
			'date_of_birth'            => esc_attr($row->date_of_birth),
			'created_at_exist'         => boolval($row->created_at),
			'created_at'               => esc_attr($row->created_at),
			'allow_login'              => boolval($row->allow_login)
		));
		wp_send_json_success(array('html' => $html, 'json' => $json));
	}

	/* Update student */
	public static function update_student()
	{
		self::check_permission();
		$id = intval(sanitize_text_field($_POST['student_id']));
		if (!wp_verify_nonce($_POST["update-student-$id"], "update-student-$id")) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enable_roll_number = WL_MIM_SettingHelper::get_general_enable_roll_number_settings($institute_id);

		$course_id       = isset($_POST['course']) ? intval(sanitize_text_field($_POST['course'])) : null;
		$batch_id        = isset($_POST['batch']) ? intval(sanitize_text_field($_POST['batch'])) : null;
		$first_name      = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
		$last_name       = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
		$gender          = isset($_POST['gender']) ? sanitize_text_field($_POST['gender']) : '';
		$date_of_birth   = (isset($_POST['date_of_birth']) && !empty($_POST['date_of_birth'])) ? date("Y-m-d", strtotime(sanitize_text_field($_REQUEST['date_of_birth']))) : null;
		$roll_number     = isset($_POST['roll_number']) ? sanitize_text_field($_POST['roll_number']) : '';
		$created_at      = (isset($_POST['created_at']) && !empty($_POST['created_at'])) ? date("Y-m-d H:i:s", strtotime(sanitize_text_field($_REQUEST['created_at']))) : NULL;
		$id_proof        = (isset($_FILES['id_proof']) && is_array($_FILES['id_proof'])) ? $_FILES['id_proof'] : null;
		$id_proof_in_db  = isset($_POST['id_proof_in_db']) ? intval(sanitize_text_field($_POST['id_proof_in_db'])) : null;
		$father_name     = isset($_POST['father_name']) ? sanitize_text_field($_POST['father_name']) : '';
		$mother_name     = isset($_POST['mother_name']) ? sanitize_text_field($_POST['mother_name']) : '';
		$address         = isset($_POST['address']) ? sanitize_textarea_field($_POST['address']) : '';
		$city            = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
		$zip             = isset($_POST['zip']) ? sanitize_text_field($_POST['zip']) : '';
		$state           = isset($_POST['state']) ? sanitize_text_field($_POST['state']) : '';
		$nationality     = isset($_POST['nationality']) ? sanitize_text_field($_POST['nationality']) : '';
		$phone           = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
		$phone2           = isset($_POST['phone2']) ? sanitize_text_field($_POST['phone2']) : '';
		$qualification   = isset($_POST['qualification']) ? sanitize_text_field($_POST['qualification']) : '';
		$email           = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
		$photo           = (isset($_FILES['photo']) && is_array($_FILES['photo'])) ? $_FILES['photo'] : null;
		$photo_in_db     = isset($_POST['photo_in_db']) ? intval(sanitize_text_field($_POST['photo_in_db'])) : null;
		$signature       = (isset($_FILES['signature']) && is_array($_FILES['signature'])) ? $_FILES['signature'] : null;
		$signature_in_db = isset($_POST['signature_in_db']) ? intval(sanitize_text_field($_POST['signature_in_db'])) : null;
		$is_active       = isset($_POST['is_active']) ? boolval(sanitize_text_field($_POST['is_active'])) : 0;
		$amount          = (isset($_POST['amount']) && is_array($_POST['amount'])) ? $_POST['amount'] : null;
		$custom_fields   = (isset($_POST['custom_fields']) && is_array($_POST['custom_fields'])) ? $_POST['custom_fields'] : array();

		$allow_login      = isset($_POST['allow_login']) ? boolval(sanitize_text_field($_POST['allow_login'])) : 0;
		$username         = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
		$password         = isset($_POST['password']) ? $_POST['password'] : '';
		$password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

		$expire_at      = (isset($_POST['expire_at']) && !empty($_POST['expire_at'])) ? date("Y-m-d", strtotime(sanitize_text_field($_REQUEST['expire_at']))) : NULL;
		$class = isset($_POST['class']) ? sanitize_text_field($_POST['class']) : '';
		$business_manager = isset($_POST['business_manager']) ? sanitize_text_field($_POST['business_manager']) : '';
		$source = isset($_POST['source']) ? sanitize_text_field($_POST['source']) : '';
		$teacher = isset($_POST['teacher']) ? sanitize_text_field($_POST['teacher']) : '';
		$student_status = isset($_POST['student_status']) ? sanitize_text_field($_POST['student_status']) : '';
		$row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id");
		$old_batch_id = $row->batch_id;
		if (!$row) {
			die();
		}

		$user = null;
		if ($row->user_id) {
			$user              = $wpdb->get_row("SELECT * FROM {$wpdb->base_prefix}users WHERE ID = $row->user_id");
			$user_institute_id = get_user_meta($user->ID, 'wlim_institute_id', true);
			if ($user_institute_id !== $institute_id) {
				die();
			}
		}

		$fees = unserialize($row->fees);

		/* Validations */
		$errors = array();
		if (empty($course_id)) {
			$errors['course'] = esc_html__('Please select a course.', WL_MIM_DOMAIN);
		}

		if (empty($batch_id)) {
			$errors['batch'] = esc_html__('Please select a batch.', WL_MIM_DOMAIN);
		}

		if (empty($first_name)) {
			$errors['first_name'] = esc_html__('Please provide first name.', WL_MIM_DOMAIN);
		}

		if (strlen($first_name) > 255) {
			$errors['first_name'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($last_name) > 255) {
			$errors['last_name'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		$course = $wpdb->get_row("SELECT course_name, course_code, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND id = $course_id AND institute_id = $institute_id");

		if (!$course) {
			$errors['course'] = esc_html__('Please select a valid course.', WL_MIM_DOMAIN);
		}

		$duration_in_month = WL_MIM_Helper::get_course_months_count($course->duration, $course->duration_in);

		$count = $wpdb->get_var("SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND is_active = 1 AND id = $batch_id AND course_id = $course_id AND institute_id = $institute_id");

		$batch = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND is_active = 1 AND id = $batch_id AND course_id = $course_id AND institute_id = $institute_id");

		if (!$count) {
			$errors['batch'] = esc_html__('Please select a valid batch.', WL_MIM_DOMAIN);
		}

		// if (empty($amount)) {
		// 	wp_send_json_error(esc_html__('Please provide valid payable amount.', WL_MIM_DOMAIN));
		// }

		// if (!array_key_exists('payable', $amount)) {
		// 	wp_send_json_error(esc_html__('Invalid payable amount.', WL_MIM_DOMAIN));
		// }

		// if (count($amount['payable']) != count($fees['type'])) {
		// 	wp_send_json_error(esc_html__('Invalid payable amount.', WL_MIM_DOMAIN));
		// }

		foreach ($amount['payable'] as $key => $value) {
			if ($value < 0 || (!is_numeric($value))) {
				wp_send_json_error(esc_html__('Please provide a valid amount payable for a fee type.', WL_MIM_DOMAIN));
			} else {
				$amount['payable'][$key] = number_format(isset($value) ? max(floatval(sanitize_text_field($value)), 0) : 0, 2, '.', '');
				if ('monthly' === $fees['period'][$key]) {
					if (($duration_in_month * $amount['payable'][$key]) < $fees['paid'][$key]) {
						wp_send_json_error(esc_html__("Paid amount exceeded payable amount for " . $fees['type'][$key] . ".", WL_MIM_DOMAIN));
					}
				} else {
					if ($amount['payable'][$key] < $fees['paid'][$key]) {
						wp_send_json_error(esc_html__("Paid amount exceeded payable amount for " . $fees['type'][$key] . ".", WL_MIM_DOMAIN));
					}
				}
			}
			if (isset($fees['period']) && ($fees['period'][$key] == 'monthly')) {
				$amount['payable'][$key] = number_format($duration_in_month * $amount['payable'][$key], 2, '.', '');
			}
		}

		if ($allow_login) {
			if ($user) {
				if (!empty($password) && ($password !== $password_confirm)) {
					$errors['password_confirm'] = esc_html__('Please confirm password.', WL_MIM_DOMAIN);
				}
			} else {
				if (empty($username)) {
					$errors['username'] = esc_html__('Please provide username.', WL_MIM_DOMAIN);
				}

				if (empty($password_confirm)) {
					$errors['password_confirm'] = esc_html__('Please confirm password.', WL_MIM_DOMAIN);
				}

				if ($password !== $password_confirm) {
					$errors['password'] = esc_html__('Passwords do not match.', WL_MIM_DOMAIN);
				}
			}
		}

		if (!in_array($gender, WL_MIM_Helper::get_gender_data())) {
			throw new Exception(esc_html__('Please select valid gender.', WL_MIM_DOMAIN));
		}

		if (!empty($date_of_birth) && (strtotime(date('Y') - 2) <= strtotime($date_of_birth))) {
			$errors['date_of_birth'] = esc_html__('Please provide valid date of birth.', WL_MIM_DOMAIN);
		}

		if (empty($date_of_birth)) {
			$errors['date_of_birth'] = esc_html__('Please provide date of birth.', WL_MIM_DOMAIN);
		}

		if (!empty($id_proof)) {
			$file_name          = sanitize_file_name($id_proof['name']);
			$file_type          = $id_proof['type'];
			$allowed_file_types = WL_MIM_Helper::get_id_proof_file_types();

			if (!in_array($file_type, $allowed_file_types)) {
				$errors['id_proof'] = esc_html__('Please provide ID Proof in PDF, JPG, JPEG or PNG format.', WL_MIM_DOMAIN);
			}
		}

		if (strlen($father_name) > 255) {
			$errors['father_name'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($mother_name) > 255) {
			$errors['mother_name'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($city) > 255) {
			$errors['city'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($zip) > 255) {
			$errors['zip'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($state) > 255) {
			$errors['state'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($nationality) > 255) {
			$errors['nationality'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (empty($phone)) {
			$errors['phone'] = esc_html__('Please provide phone number.', WL_MIM_DOMAIN);
		}

		if (strlen($phone) > 255) {
			$errors['phone'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($qualification) > 255) {
			$errors['qualification'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (strlen($email) > 255) {
			$errors['email'] = esc_html__('Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN);
		}

		if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors['email'] = esc_html__('Please provide a valid email address.', WL_MIM_DOMAIN);
		}

		if (!empty($custom_fields)) {
			if (!array_key_exists('name', $custom_fields) || !array_key_exists('value', $custom_fields)) {
				wp_send_json_error(esc_html__('Invalid field.', WL_MIM_DOMAIN));
			} elseif (!is_array($custom_fields['name']) || !is_array($custom_fields['value'])) {
				wp_send_json_error(esc_html__('Invalid field.', WL_MIM_DOMAIN));
			} elseif (count($custom_fields['name']) != count($custom_fields['value'])) {
				wp_send_json_error(esc_html__('Invalid field.', WL_MIM_DOMAIN));
			} else {
				$custom_field_name_data = array();
				$custom_fields_data     = unserialize($row->custom_fields);
				$custom_field_name_data = isset($custom_fields_data['name']) ? $custom_fields_data['name'] : array();
				foreach ($custom_fields['name'] as $key => $field_name) {
					$custom_fields['name'][$key]  = sanitize_text_field($field_name);
					$custom_fields['value'][$key] = sanitize_text_field($custom_fields['value'][$key]);
				}
				if ($custom_fields['name'] !== $custom_field_name_data) {
					wp_send_json_error(esc_html__('Invalid field.', WL_MIM_DOMAIN));
				}
			}
		}

		if ($general_enable_roll_number) {
			if (!empty($roll_number)) {
				$count = $wpdb->get_var("SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id != $id AND roll_number = '$roll_number' AND institute_id = $institute_id");

				if ($count) {
					$errors['roll_number'] = esc_html__('Student with this roll number already exists.', WL_MIM_DOMAIN);
				}
			}
		}

		if (!empty($photo)) {
			$file_name          = sanitize_file_name($photo['name']);
			$file_type          = $photo['type'];
			$allowed_file_types = WL_MIM_Helper::get_image_file_types();

			if (!in_array($file_type, $allowed_file_types)) {
				$errors['photo'] = esc_html__('Please provide photo in JPG, JPEG or PNG format.', WL_MIM_DOMAIN);
			}
		}

		if (!empty($signature)) {
			$file_name          = sanitize_file_name($signature['name']);
			$file_type          = $signature['type'];
			$allowed_file_types = WL_MIM_Helper::get_image_file_types();
			if (!in_array($file_type, $allowed_file_types)) {
				$errors['signature'] = esc_html__('Please provide signature in JPG, JPEG or PNG format.', WL_MIM_DOMAIN);
			}
		}
		/* End validations */

		if (count($errors) < 1) {
			try {
				$wpdb->query('BEGIN;');

				unset($fees['payable']);
				$fees['payable'] = $amount['payable'];
				$fees            = serialize($fees);

				$inactive_at = null;
				if (!$is_active) {
					$inactive_at = date('Y-m-d H:i:s');
				}

				$custom_fields = serialize($custom_fields);
				// if staff is updating the student status.
				if ($row->student_status == "processing" && $student_status == "approved") {
					$updated_by = get_current_user_id();
				} else {
				  	$updated_by = null;
				}
				$data = array(
					'course_id'     => $course_id,
					'batch_id'      => $batch_id,
					'first_name'    => $first_name,
					'last_name'     => $last_name,
					'gender'        => $gender,
					'date_of_birth' => $date_of_birth,
					'father_name'   => $father_name,
					'mother_name'   => $mother_name,
					'address'       => $address,
					'city'          => $city,
					'zip'           => $zip,
					'state'         => $state,
					'nationality'   => $nationality,
					'phone'         => $phone,
					'phone2'        => $phone2,
					'qualification' => $qualification,
					'email'         => $email,
					'fees'          => $fees,
					'is_active'     => $is_active,
					'inactive_at'   => $inactive_at,
					'custom_fields' => $custom_fields,
					'created_at'    => $created_at,
					'class'         => $class,
					'business_manager' => $business_manager,
					'source'       => $source,
					'teacher'       => $teacher,
					'student_status'   => $student_status,
					'expire_at'        => $expire_at,
					'updated_by'       => $updated_by,
					'updated_at'       => date('Y-m-d H:i:s')
				);

				if ($general_enable_roll_number) {
					$data['roll_number'] = $roll_number;
				}

				if (!empty($id_proof)) {
					$id_proof = media_handle_upload('id_proof', 0);
					if (is_wp_error($id_proof)) {
						throw new Exception(esc_html__($id_proof->get_error_message(), WL_MIM_DOMAIN));
					}
					$data['id_proof'] = $id_proof;
				}

				if (!empty($photo)) {
					$photo = media_handle_upload('photo', 0);
					if (is_wp_error($photo)) {
						throw new Exception(esc_html__($photo->get_error_message(), WL_MIM_DOMAIN));
					}
					$data['photo_id'] = $photo;
				}

				if (!empty($signature)) {
					$signature = media_handle_upload('signature', 0);
					if (is_wp_error($signature)) {
						throw new Exception(esc_html__($signature->get_error_message(), WL_MIM_DOMAIN));
					}
					$data['signature_id'] = $signature;
				}

				$reload = false;
				if ($allow_login) {
					if ($user) {
						/* Student login data */
						$login_data = array(
							'ID'         => $user->ID,
							'first_name' => $first_name,
							'last_name'  => $last_name
						);

						if (!empty($password)) {
							$login_data['user_pass'] = $password;
							if (get_current_user_id() == $id) {
								$reload = true;
							}
						}

						$user_id = wp_update_user($login_data);
						if (is_wp_error($user_id)) {
							wp_send_json_error(esc_html__($user_id->get_error_message(), WL_MIM_DOMAIN));
						}
					} else {
						/* Student login data */
						$login_data = array(
							'first_name' => $first_name,
							'last_name'  => $last_name,
							'user_login' => $username,
							'user_pass'  => $password
						);

						$user_id = wp_insert_user($login_data);
						if (is_wp_error($user_id)) {
							wp_send_json_error(esc_html__($user_id->get_error_message(), WL_MIM_DOMAIN));
						}

						$user = new WP_User($user_id);
						$user->add_cap(WL_MIM_Helper::get_student_capability());

						if ($user_id) {
							$data['user_id']     = $user_id;
							$data['allow_login'] = $allow_login;
							update_user_meta($user_id, 'wlim_institute_id', $institute_id);
						}
					}
				} else {
					if ($user) {
						$user = new WP_User($user->ID);
						$user->remove_cap(WL_MIM_Helper::get_student_capability());
						$user_deleted = is_multisite() ? wpmu_delete_user($user->ID) : wp_delete_user($user->ID);
						if (!$user_deleted) {
							throw new Exception(esc_html__('An unexpected error occurred.', WL_MIM_DOMAIN));
						} else {
							delete_user_meta($user->ID, 'wlim_institute_id');
						}
						$data['user_id']     = null;
						$data['allow_login'] = null;
					}
				}

				$success = $wpdb->update("{$wpdb->prefix}wl_min_students", $data, array(
					'is_deleted'   => 0,
					'id'           => $id,
					'institute_id' => $institute_id
				));

				$student = $wpdb->get_row( "SELECT mb.user_login FROM {$wpdb->prefix}wl_min_students as s
				JOIN {$wpdb->prefix}users as mb ON mb.id = s.user_id
				WHERE s.id = $id" );

				if ($success === false) {
					throw new Exception(esc_html__('An unexpected error occurred.', WL_MIM_DOMAIN));
				}
				$wpdb->query('COMMIT;');
				// Send email to student
				if ($student_status == 'approved') {
					$template = WL_MIM_SettingHelper::get_template_settings($institute_id);
					if ($template['et_inquiry_approved_subject']) {

							$subject = $template['et_inquiry_approved_subject'];
							$body    = $template['et_inquiry_approved_body'];

							$body = str_replace('[COURSE_NAME]', $course->course_name, $body);
							$body = str_replace('[STUDENT_NAME]', $first_name." ".$last_name, $body);
							$body = str_replace('[STUDENT_EMAIL]', $email, $body);
							$body = str_replace('[STUDENT_BATCH]', $batch->batch_name, $body);
							$body = str_replace('[STUDENT_USERNAME]', $student->user_login, $body);
							$body = str_replace('[REGISTRATION_DATE]', $created_at, $body);
							$body = str_replace('[EXPIRATION_DATE]', $expire_at, $body);
							// add string to $body

							WL_MIM_SMSHelper::send_email( $institute_id, $email, $subject, $body );
					}
				}

				if ($old_batch_id != $batch_id) {
					// get student phone with student_id.
					$phone = $row->phone;

					$sms_template_student_batch_change = WL_MIM_SettingHelper::sms_template_student_batch_change($institute_id);
					$sms = WL_MIM_SettingHelper::get_sms_settings($institute_id);

					if ($sms_template_student_batch_change['enable']) {
						$sms_message = $sms_template_student_batch_change['message'];
						$template_id = $sms_template_student_batch_change['template_id'];

						$sms_message = str_replace('[FIRST_NAME]', $row->first_name, $sms_message);
						$sms_message = str_replace('[LAST_NAME]', $row->last_name, $sms_message);
						WL_MIM_SMSHelper::send_sms($sms, $institute_id, $sms_message, $phone, $template_id);
					}
				}

				if (!empty($id_proof) && !empty($id_proof_in_db)) {
					wp_delete_attachment($id_proof_in_db, true);
				}

				if (!empty($photo) && !empty($photo_in_db)) {
					wp_delete_attachment($photo_in_db, true);
				}

				if (!empty($signature) && !empty($signature_in_db)) {
					wp_delete_attachment($signature_in_db, true);
				}

				wp_send_json_success(array(
					'message' => esc_html__('Student updated successfully.', WL_MIM_DOMAIN),
					'reload'  => $reload
				));
			} catch (Exception $exception) {
				$wpdb->query('ROLLBACK;');
				wp_send_json_error($exception->getMessage());
			}
		}
		wp_send_json_error($errors);
	}

	/* Delete student */
	public static function delete_student() {

		if (!current_user_can('administrator')) {
			die();
		}

		self::check_permission();
		$id = intval(sanitize_text_field($_POST['id']));
		if (!wp_verify_nonce($_POST["delete-student-$id"], "delete-student-$id")) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id");
		if (!$row) {
			die();
		}

		$user = null;
		if ($row->user_id) {
			$user = $wpdb->get_row("SELECT * FROM {$wpdb->base_prefix}users WHERE ID = $row->user_id");
			if ($user) {
				$user = new WP_User($user->ID);
			}
		}

		try {
			$wpdb->query('BEGIN;');

			$success = $wpdb->update(
				"{$wpdb->prefix}wl_min_students",
				array(
					'user_id'     => null,
					'allow_login' => false,
					'is_deleted'  => 1,
					'deleted_at'  => date('Y-m-d H:i:s')
				),
				array('is_deleted' => 0, 'id' => $id, 'institute_id' => $institute_id)
			);
			if ($success === false) {
				throw new Exception(esc_html__('An unexpected error occurred.', WL_MIM_DOMAIN));
			}

			if ($user) {
				$user->remove_cap(WL_MIM_Helper::get_student_capability());
				$user_deleted = is_multisite() ? wpmu_delete_user($user->ID) : wp_delete_user($user->ID);
				if (!$user_deleted) {
					throw new Exception(esc_html__('An unexpected error occurred.', WL_MIM_DOMAIN));
				} else {
					delete_user_meta($user->ID, 'wlim_institute_id');
				}
			}

			$success = $wpdb->update(
				"{$wpdb->prefix}wl_min_results",
				array(
					'is_deleted' => 1,
					'deleted_at' => date('Y-m-d H:i:s')
				),
				array('is_deleted' => 0, 'student_id' => $id, 'institute_id' => $institute_id)
			);

			$wpdb->query('COMMIT;');
			wp_send_json_success(array('message' => esc_html__('Student removed successfully.', WL_MIM_DOMAIN)));
		} catch (Exception $exception) {
			$wpdb->query('ROLLBACK;');
			wp_send_json_error(esc_html__($exception->getMessage(), WL_MIM_DOMAIN));
		}
	}

	/* Fetch course batches */
	public static function fetch_course_batches() {
		self::check_permission();
		if (!wp_verify_nonce($_REQUEST['security'], 'wl-ima')) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$course_id = intval(sanitize_text_field($_POST['id']));
		$row       = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND id = $course_id AND institute_id = $institute_id");

		if (!$row) {
			die();
		}

		$batches = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND is_active = 1 AND course_id = $course_id AND institute_id = $institute_id ORDER BY id DESC");
		ob_start();
		if (count($batches) > 0) {
		?>
			<div class="form-group pt-3">
				<label for="wlim-student-batch" class="col-form-label"><?php esc_html_e("Batch", WL_MIM_DOMAIN); ?>:</label>
				<select name="batch" class="form-control selectpicker" id="wlim-student-batch">
					<option value="">-------- <?php esc_html_e("Select a Batch", WL_MIM_DOMAIN); ?> --------</option>
					<?php
					foreach ($batches as $batch) {
						$time_from  = date("g:i A", strtotime($batch->time_from));
						$time_to    = date("g:i A", strtotime($batch->time_to));
						$timing     = "$time_from - $time_to";
						$batch_info = $batch->batch_code;
						if ($batch->batch_name) {
							$batch_info .= " ( $batch->batch_name )";
						} ?>
						<option value="<?php echo esc_attr($batch->id); ?>"><?php echo esc_html($batch_info) . " ( " . esc_html($timing) . " ) ( " . WL_MIM_Helper::get_batch_status($batch->start_date, $batch->end_date) . " )"; ?></option>
					<?php
					} ?>
				</select>
			</div>
		<?php
			$json = json_encode(array(
				'element' => '#wlim-student-batch'
			));
		} else {
			$json = json_encode(array(
				'element' => ''
			));
		?>
			<div class="text-danger pt-3 pb-3 border-bottom"><?php esc_html_e("Batches not found.", WL_MIM_DOMAIN); ?></div>
		<?php
			$json = json_encode(array(
				'element' => ''
			));
		}
		$html = ob_get_clean();
		wp_send_json_success(array('html' => $html, 'json' => $json));
	}

	// Fetch course batches and return json.
	public static function wlim_get_batches(){
		$course_id = intval(sanitize_text_field($_POST['course_id']));

		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$batches = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND is_active = 1 AND course_id = $course_id AND institute_id = $institute_id ORDER BY id DESC");

		$batch_data = array();

		if (count($batches) > 0) {
			foreach ($batches as $batch) {
				$time_from  = date("g:i A", strtotime($batch->time_from));
				$time_to    = date("g:i A", strtotime($batch->time_to));
				$timing     = "$time_from - $time_to";
				$batch_info = $batch->batch_code;
				if ($batch->batch_name) {
					$batch_info .= " ( $batch->batch_name )";
				}
				$batch_data[] = array(
					'id' => $batch->id,
					'batch_info' => $batch_info,
					'timing' => $timing,
					'status' => WL_MIM_Helper::get_batch_status($batch->start_date, $batch->end_date)
				);
			}
		}

		wp_send_json_success($batches);

	}

	/* Fetch course update batches */
	public static function fetch_course_update_batches()
	{
		self::check_permission();
		if (!wp_verify_nonce($_REQUEST['security'], 'wl-ima')) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$course_id = intval(sanitize_text_field($_POST['id']));
		$batch_id  = intval(sanitize_text_field($_POST['batch_id']));
		$row       = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND id = $course_id AND institute_id = $institute_id");

		if (!$row) {
			die();
		}

		$batches = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND is_active = 1 AND course_id = $course_id AND institute_id = $institute_id ORDER BY id DESC");
		ob_start();
		if (count($batches) > 0) {
		?>
			<div class="form-group pt-3">
				<label for="wlim-student-batch_update" class="col-form-label"><?php esc_html_e("Batch", WL_MIM_DOMAIN); ?>:</label>
				<select name="batch" class="form-control selectpicker" id="wlim-student-batch_update">
					<option value="">-------- <?php esc_html_e("Select a Batch", WL_MIM_DOMAIN); ?> --------</option>
					<?php
					foreach ($batches as $batch) {
						$time_from  = date("g:i A", strtotime($batch->time_from));
						$time_to    = date("g:i A", strtotime($batch->time_to));
						$timing     = "$time_from - $time_to";
						$batch_info = $batch->batch_code;
						if ($batch->batch_name) {
							$batch_info .= " ( $batch->batch_name )";
						}
					?>
						<option value="<?php echo esc_attr($batch->id); ?>"><?php echo esc_html($batch_info) . " ( " . esc_html($timing) . " ) ( " . WL_MIM_Helper::get_batch_status($batch->start_date, $batch->end_date) . " )"; ?></option>
					<?php
					} ?>
				</select>
			</div>
		<?php
			$json = json_encode(array(
				'element'  => '#wlim-student-batch_update',
				'batch_id' => esc_attr($batch_id)
			));
		} else { ?>
			<div class="text-danger pt-3 pb-3 border-bottom"><?php esc_html_e("Batches not found.", WL_MIM_DOMAIN); ?></div>
		<?php
			$json = json_encode(array(
				'element'  => '',
				'batch_id' => ''
			));
		}
		$html = ob_get_clean();
		wp_send_json_success(array('html' => $html, 'json' => $json));
	}

	/* Fetch enquiries */
	public static function fetch_enquiries()
	{
		self::check_permission();
		if (!wp_verify_nonce($_REQUEST['security'], 'wl-ima')) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$enquiries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_enquiries WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY id DESC");
		ob_start();
		if (count($enquiries) > 0) {
		?>
			<div class="form-group pt-3">
				<label for="wlim-student-enquiry" class="col-form-label"><?php esc_html_e("Enquiry", WL_MIM_DOMAIN); ?>:</label>
				<select name="enquiry" class="form-control selectpicker" id="wlim-student-enquiry">
					<option value="">-------- <?php esc_html_e("Select an Enquiry", WL_MIM_DOMAIN); ?> --------</option>
					<?php
					foreach ($enquiries as $enquiry) { ?>
						<option value="<?php echo esc_attr($enquiry->id); ?>"><?php echo esc_html("$enquiry->first_name $enquiry->last_name (") . WL_MIM_Helper::get_enquiry_id($enquiry->id) . ")"; ?></option>
					<?php
					} ?>
				</select>
			</div>
		<?php
			$json = json_encode(array(
				'element' => '#wlim-student-enquiry'
			));
		} else {
			$json = json_encode(array(
				'element' => ''
			));
		?>
			<div class="text-danger pt-3 pb-3 border-bottom"><?php esc_html_e("Enquiries not found.", WL_MIM_DOMAIN); ?></div>
			<?php
		}
		$html = ob_get_clean();
		wp_send_json_success(array('html' => $html, 'json' => $json));
	}

	/* Fetch enquiry */
	public static function fetch_enquiry()
	{
		self::check_permission();
		if (!wp_verify_nonce($_REQUEST['security'], 'wl-ima')) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enable_roll_number = WL_MIM_SettingHelper::get_general_enable_roll_number_settings($institute_id);

		$id                               = intval(sanitize_text_field($_POST['id']));
		$row                              = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_enquiries WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id");
		$custom_fields                    = unserialize($row->custom_fields);
		$wlim_institute_active_categories = WL_MIM_Helper::get_active_categories_institute($institute_id);
		$wlim_active_courses              = WL_MIM_Helper::get_active_courses();
		$wlim_states                      = WL_MIM_Helper::get_states();


		if ($row) {
			$course = $wpdb->get_row("SELECT course_category_id FROM {$wpdb->prefix}wl_min_courses WHERE id = $row->course_id AND institute_id = $institute_id");
			ob_start();
			?><?php
				if (count($wlim_institute_active_categories) > 0) { ?>
			<div class="form-group">
				<label for="wlim-student-category" class="col-form-label">* <?php _e("Category", WL_MIM_DOMAIN); ?>:</label>
				<select name="category" class="form-control" id="wlim-student-category">
					<option value="">-------- <?php _e("Select a Category", WL_MIM_DOMAIN); ?>-------- </option>
					<?php
					foreach ($wlim_institute_active_categories as $active_category) { ?>
						<option <?php selected($course ? $course->course_category_id : '', $active_category->id, true); ?> value="<?php echo esc_attr($active_category->id); ?>"><?php echo esc_html($active_category->name); ?></option>
					<?php
					}
					?>
				</select>
			</div>
			<div id="wlim-student-fetch-category-courses">
				<div class="form-group">
					<label for="wlim-student-course" class="col-form-label">* <?php _e("Admission For", WL_MIM_DOMAIN); ?>:</label>
					<select name="course" class="form-control selectpicker" id="wlim-student-course">
						<option value="">-------- <?php _e("Select a Course", WL_MIM_DOMAIN); ?> --------</option>
						<?php
						if (count($wlim_active_courses) > 0) {
							foreach ($wlim_active_courses as $active_course) { ?>
								<option value="<?php echo esc_attr($active_course->id); ?>">
									<?php echo "$active_course->course_name ($active_course->course_code) (" . __("Fees", WL_MIM_DOMAIN) . ": " . $active_course->fees . ")"; ?>
								</option>
						<?php
							}
						} ?>
					</select>
				</div>
			</div>
		<?php
				} else { ?>
			<div class="form-group">
				<label for="wlim-student-course" class="col-form-label">* <?php _e("Admission For", WL_MIM_DOMAIN); ?>:</label>
				<select name="course" class="form-control selectpicker" id="wlim-student-course">
					<option value="">-------- <?php _e("Select a Course", WL_MIM_DOMAIN); ?> --------</option>
					<?php
					if (count($wlim_active_courses) > 0) {
						foreach ($wlim_active_courses as $active_course) { ?>
							<option value="<?php echo esc_attr($active_course->id); ?>">
								<?php echo esc_html("$active_course->course_name ($active_course->course_code) (" . __("Fees", WL_MIM_DOMAIN) . ": " . $active_course->fees . ")"); ?>
							</option>
					<?php
						}
					} ?>
				</select>
			</div>
		<?php
				} ?>

		<!-- Create a select input with options 1 to 10 for class selection -->
		<div class="form-group">
			<label for="wlim-enquiry-class-student" class="col-form-label"> <?php esc_html_e( "Select Class", WL_MIM_DOMAIN ); ?>:</label>
			<select name="class" class="form-control " id="wlim-enquiry-class-student">
				<option value=""> -------- <?php esc_html_e( "Select a Class", WL_MIM_DOMAIN ); ?> --------
				</option>
				<?php for($i = 1; $i <= 10; $i++): ?>
					<option value="<?php echo $i; ?>" <?php if($row->class == $i) echo 'selected'; ?>><?php esc_html_e( 'Class '.$i, WL_MIM_DOMAIN ); ?></option>
				<?php endfor; ?>
			</select>
		</div>
		<div class="form-group">
			<label for="wlim-enquiry-note" class="col-form-label"><?php esc_html_e( 'Business manager', WL_MIM_DOMAIN ); ?>:</label>
			<input name="business_manager" type="text" class="form-control" id="wlim-enquiry-business_manager" placeholder="<?php esc_html_e( "Business manager", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr($row->business_manager); ?>">
		</div>
		<div id="wlim-add-student-course-batches"></div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="wlim-student-first_name" class="col-form-label">* <?php esc_html_e('First Name', WL_MIM_DOMAIN); ?>:</label>
				<input name="first_name" type="text" class="form-control" id="wlim-student-first_name" placeholder="<?php esc_html_e("First Name", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->first_name); ?>">
			</div>
			<div class="col-sm-6 form-group">
				<label for="wlim-student-last_name" class="col-form-label"><?php esc_html_e('Last Name', WL_MIM_DOMAIN); ?>:</label>
				<input name="last_name" type="text" class="form-control" id="wlim-student-last_name" placeholder="<?php esc_html_e("Last Name", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->last_name); ?>">
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label class="col-form-label">* <?php esc_html_e('Gender', WL_MIM_DOMAIN); ?>:</label><br>
				<div class="row mt-2">
					<div class="col-sm-12">
						<label class="radio-inline mr-3"><input type="radio" name="gender" class="mr-2" value="male" id="wlim-student-male"><?php esc_html_e('Male', WL_MIM_DOMAIN); ?>
						</label>
						<label class="radio-inline"><input type="radio" name="gender" class="mr-2" value="female" id="wlim-student-female"><?php esc_html_e('Female', WL_MIM_DOMAIN); ?>
						</label>
					</div>
				</div>
			</div>
			<div class="col-sm-6 form-group">
				<label for="wlim-student-date_of_birth" class="col-form-label">* <?php esc_html_e('Date of Birth', WL_MIM_DOMAIN); ?>:</label>
				<input name="date_of_birth" type="text" class="form-control wlim-date_of_birth" id="wlim-student-date_of_birth" placeholder="<?php esc_html_e("Date of Birth", WL_MIM_DOMAIN); ?>">
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="wlim-student-father_name" class="col-form-label"><?php esc_html_e("Father's Name", WL_MIM_DOMAIN); ?>:</label>
				<input name="father_name" type="text" class="form-control" id="wlim-student-father_name" placeholder="<?php esc_html_e("Father's Name", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->father_name); ?>">
			</div>
			<div class="col-sm-6 form-group">
				<label for="wlim-student-mother_name" class="col-form-label"><?php esc_html_e("Mother's Name", WL_MIM_DOMAIN); ?>:</label>
				<input name="mother_name" type="text" class="form-control" id="wlim-student-mother_name" placeholder="<?php esc_html_e("Mother's Name", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->mother_name); ?>">
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="wlim-student-address" class="col-form-label"><?php esc_html_e('Address', WL_MIM_DOMAIN); ?>:</label>
				<textarea name="address" class="form-control" rows="4" id="wlim-student-address" placeholder="<?php esc_html_e("Address", WL_MIM_DOMAIN); ?>"><?php echo esc_html($row->address); ?></textarea>
			</div>
			<div class="col-sm-6 form-group">
				<div>
					<label for="wlim-student-city" class="col-form-label"><?php esc_html_e('City', WL_MIM_DOMAIN); ?>:</label>
					<input name="city" type="text" class="form-control" id="wlim-student-city" placeholder="<?php esc_html_e("City", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->city); ?>">
				</div>
				<div>
					<label for="wlim-student-zip" class="col-form-label"><?php esc_html_e('Zip Code', WL_MIM_DOMAIN); ?>:</label>
					<input name="zip" type="text" class="form-control" id="wlim-student-zip" placeholder="<?php esc_html_e("Zip Code", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->zip); ?>">
				</div>
			</div>
		</div>
		<div class="row">
		<?php  	$wlim_states = WL_MIM_Helper::get_states(); ?>
		<div class="form-group col-md-6">
			<label for="wlim-state" class="col-form-label"><?php esc_html_e('State', WL_MIM_DOMAIN); ?>:</label>
			<select name="state" id="wlim-state" class="form-control">
				<option value="">Select State</option>
				<?php foreach ($wlim_states as $state): ?>
					<option value="<?php echo $state; ?>"><?php echo esc_html($state); ?></option>
				<?php endforeach ?>
			</select>
		</div>
			<div class="col-sm-6 form-group">
				<label for="wlim-student-nationality" class="col-form-label"><?php esc_html_e('Nationality', WL_MIM_DOMAIN); ?>:</label>
				<input name="nationality" type="text" class="form-control" id="wlim-student-nationality" placeholder="<?php esc_html_e("Nationality", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->nationality); ?>">
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="wlim-student-phone" class="col-form-label">* <?php esc_html_e('Phone', WL_MIM_DOMAIN); ?>:</label>
				<input name="phone" type="text" class="form-control" id="wlim-student-phone" placeholder="<?php esc_html_e("Phone", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->phone); ?>">
			</div>
			<div class="col-sm-6 form-group">
				<label for="wlim-student-phone" class="col-form-label">* <?php esc_html_e('Mother Phone', WL_MIM_DOMAIN); ?>:</label>
				<input name="phone2" type="text" class="form-control" id="wlim-student-phone" placeholder="<?php esc_html_e("Phone", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->phone2); ?>">
			</div>
			<div class="col-sm-6 form-group">
				<label for="wlim-student-email" class="col-form-label"><?php esc_html_e('Email', WL_MIM_DOMAIN); ?>:</label>
				<input name="email" type="text" class="form-control" id="wlim-student-email" placeholder="<?php esc_html_e("Email", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->email); ?>">
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="wlim-student-qualification" class="col-form-label"><?php esc_html_e('Course', WL_MIM_DOMAIN); ?>:</label>
				<input name="qualification" type="text" class="form-control" id="wlim-student-qualification" placeholder="<?php esc_html_e("Course", WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($row->qualification); ?>">
			</div>
			<div class="col-sm-6 form-group">
				<input type="hidden" name="id_proof_in_db" value="<?php echo esc_attr($row->id_proof); ?>">
				<label for="wlim-student-id_proof" class="col-form-label"><?php esc_html_e('ID Proof', WL_MIM_DOMAIN); ?>:</label><br>
				<?php if (!empty($row->id_proof)) { ?>
					<a href="<?php echo wp_get_attachment_url($row->id_proof); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><?php esc_html_e('View ID Proof', WL_MIM_DOMAIN); ?></a>
					<input type="hidden" name="id_proof_in_db" value="<?php echo esc_attr($row->id_proof); ?>">
				<?php } ?>
				<input name="id_proof" type="file" id="wlim-student-id_proof">
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="wlim-student-photo" class="col-form-label"><?php esc_html_e('Photo', WL_MIM_DOMAIN); ?>:</label><br>
				<?php if (!empty($row->photo_id)) { ?>
					<img src="<?php echo wp_get_attachment_url($row->photo_id); ?>" class="img-responsive photo-signature">
					<input type="hidden" name="photo_in_db" value="<?php echo esc_attr($row->photo_id); ?>">
				<?php } ?>
				<input name="photo" type="file" id="wlim-student-photo">
			</div>
			<div class="col-sm-6 form-group">
				<label for="wlim-student-signature" class="col-form-label"><?php esc_html_e('Signature', WL_MIM_DOMAIN); ?>:</label><br>
				<?php if (!empty($row->signature_id)) { ?>
					<img src="<?php echo wp_get_attachment_url($row->signature_id); ?>" class="img-responsive photo-signature">
					<input type="hidden" name="signature_in_db" value="<?php echo esc_attr($row->signature_id); ?>">
				<?php } ?>
				<input name="signature" type="file" id="wlim-student-signature">
			</div>
		</div>
		<?php
			if (isset($custom_fields['name']) && is_array($custom_fields['name']) && count($custom_fields['name'])) { ?>
			<div class="row">
				<?php
				foreach ($custom_fields['name'] as $key => $custom_field_name) { ?>
					<div class="col-sm-6 form-group">
						<label for="wlim-student-custom_fields_<?php echo esc_attr($key); ?>" class="col-form-label"><?php echo esc_html($custom_field_name); ?>:</label>
						<input type="hidden" name="custom_fields[name][]" value="<?php echo esc_attr($custom_field_name); ?>">
						<input name="custom_fields[value][]" type="text" class="form-control" id="wlim-student-custom_fields_<?php echo esc_attr($key); ?>" placeholder="<?php echo esc_attr($custom_field_name); ?>" value="<?php echo esc_attr($custom_fields['value'][$key]); ?>">
					</div>
				<?php
				} ?>
			</div>
			<?php
			} ?><?php if (!empty($row->message)) { ?>
			<div class="row" id="wlim-student-message">
				<div class="col">
					<label class="col-form-label pb-0"><?php esc_html_e('Message', WL_MIM_DOMAIN); ?>:</label>
					<div class="card mb-3 mt-2">
						<div class="card-block">
							<span class="text-secondary"><?php echo esc_html($row->message); ?></span>
						</div>
					</div>
				</div>
			</div>
		<?php
				} ?>
		<div id="wlim-add-student-fetch-fees-payable">
			<div class="fee_types_box">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th><?php esc_html_e('Fee Type', WL_MIM_DOMAIN); ?></th>
							<th><?php esc_html_e('Amount Payable', WL_MIM_DOMAIN); ?></th>
							<th><?php esc_html_e('Amount Paid', WL_MIM_DOMAIN); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody class="fee_types_rows fee_types_table">
						<?php
						$fee_types = WL_MIM_Helper::get_active_fee_types();
						if (count($fee_types)) {
							foreach ($fee_types as $fee_type) { ?>
								<tr>
									<td>
										<input type="text" name="amount[type][]" class="form-control" placeholder="<?php esc_html_e('Fee Type', WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($fee_type->fee_type); ?>">
									</td>
									<td>
										<input type="number" name="fee_type_amount[payable][]" class="form-control" placeholder="<?php esc_html_e('Payable', WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($fee_type->amount); ?>">
									</td>
									<td>
										<input type="number" name="fee_type_amount[paid][]" class="form-control" placeholder="<?php esc_html_e('Paid', WL_MIM_DOMAIN); ?>" value="0.00">
									</td>
									<td>
										<button class="remove_row btn btn-danger btn-sm" type="button">
											<i class="fa fa-remove" aria-hidden="true"></i>
										</button>
									</td>
								</tr>
							<?php
							}
						} else { ?>
							<tr>
								<td>
									<input type="text" name="amount[type][]" class="form-control" placeholder="<?php esc_html_e('Fee Type', WL_MIM_DOMAIN); ?>">
								</td>
								<td>
									<input type="number" name="fee_type_amount[payable][]" class="form-control" placeholder="<?php esc_html_e('Amount', WL_MIM_DOMAIN); ?>">
								</td>
								<td>
									<input type="number" name="fee_type_amount[paid][]" class="form-control" placeholder="<?php esc_html_e('Amount', WL_MIM_DOMAIN); ?>">
								</td>
								<td>
									<button class="remove_row btn btn-danger btn-sm" type="button">
										<i class="fa fa-remove" aria-hidden="true"></i>
									</button>
								</td>
							</tr>
						<?php
						} ?>
					</tbody>
				</table>
				<div class="text-right">
					<button type="button" class="add-more-fee-types btn btn-success btn-sm"><?php esc_html_e('Add More', WL_MIM_DOMAIN); ?></button>
				</div>
			</div>
		</div>
		<hr>



		<div class="row">
			<div class="form-group col-sm-6 ">
				<label for="wlim-student-created_at" class="col-form-label"><?php esc_html_e('Registration Date', WL_MIM_DOMAIN); ?>:</label>
				<input name="created_at" type="text" class="form-control wlim-date_of_birth" id="wlim-student-created_at" placeholder="<?php esc_html_e('Registration Date', WL_MIM_DOMAIN); ?>">
			</div>

			<div class="form-group col-sm-6">
				<label for="wlim-student-expired_at" class="col-form-label"><?php esc_html_e('Registration Expiry Date', WL_MIM_DOMAIN); ?>:</label>
				<input name="expire_at" type="text" class="form-control wlim-date_of_birth" id="wlim-student-expired_at" placeholder="<?php esc_html_e('Registration Expiry Date', WL_MIM_DOMAIN); ?>">
			</div>
		</div>

		<?php if ($general_enable_roll_number) { ?>
			<div class="form-group">
				<label for="wlim-student-roll_number" class="col-form-label"><?php esc_html_e('Roll Number', WL_MIM_DOMAIN); ?>:</label>
				<input name="roll_number" type="text" class="form-control" id="wlim-student-roll_number" placeholder="<?php esc_html_e('Roll Number', WL_MIM_DOMAIN); ?>">
			</div>
		<?php } ?>


		<?php

	$sources     = WL_MIM_Helper::get_sources();
	$wlim_teachers     = WL_MIM_Helper::get_staff_teachers();

	 ?>
	<div class="row">
		<div class="form-group col-md-4">
			<label for="wlim-student-source" class="col-form-label"><?php esc_html_e('Source', WL_MIM_DOMAIN); ?>:</label>
			<select name="source" id="wlim-source" class="form-control">
			<option value="">Select Source</option>
				<?php foreach ($sources as $source): ?>
					<option value="<?php echo $source->source; ?>"><?php echo esc_html($source->source); ?></option>
				<?php endforeach ?>
			</select>
		</div>

		<div class="form-group col-md-4">
			<label for="wlim-teacher" class="col-form-label"><?php esc_html_e('Teacher', WL_MIM_DOMAIN); ?>:</label>
			<select name="teacher" id="wlim-teacher" class="form-control">
				<option value="">Select Teacher</option>
				<?php foreach ($wlim_teachers as $teacher): ?>
					<option value="<?php echo $teacher->first_name; ?>"><?php echo esc_html($teacher->first_name. ' '. $teacher->last_name); ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>

		<hr>
		<div class="form-check pl-0">
			<input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-student-is_active" checked>
			<label class="form-check-label" for="wlim-student-is_active">
				<?php esc_html_e('Is Active?', WL_MIM_DOMAIN); ?>
			</label>
		</div>
		<hr>
		<div class="form-group">
			<label for="wlim-enquiry-student-status" class="col-form-label"> <?php esc_html_e( "Status", WL_MIM_DOMAIN ); ?>:</label>
			<select name="student_status" class="form-control " id="wlim-enquiry-student-status">
				<option value="processing">Processing</option>
				<option value="approved">Approved</option>
			</select>
		</div>
		<div class="form-check pl-0">
			<input name="allow_login" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-student-allow_login" checked>
			<label class="form-check-label" for="wlim-student-allow_login">
				<strong class="text-primary"><?php esc_html_e('Allow Student to Login?', WL_MIM_DOMAIN); ?></strong>
			</label>
		</div>
		<div class="wlim-allow-login-fields">
			<hr>
			<div class="form-group">
				<label for="wlim-student-username" class="col-form-label"><?php esc_html_e('Username', WL_MIM_DOMAIN); ?>:</label>
				<input name="username" type="text" class="form-control" id="wlim-student-username" placeholder="<?php esc_html_e("Username", WL_MIM_DOMAIN); ?>">
			</div>
			<div class="form-group">
				<label for="wlim-student-password" class="col-form-label"><?php esc_html_e('Password', WL_MIM_DOMAIN); ?>:</label>
				<input name="password" type="password" class="form-control" id="wlim-student-password" placeholder="<?php esc_html_e("Password", WL_MIM_DOMAIN); ?>">
			</div>
			<div class="form-group">
				<label for="wlim-student-password_confirm" class="col-form-label"><?php esc_html_e('Confirm Password', WL_MIM_DOMAIN); ?>:</label>
				<input name="password_confirm" type="password" class="form-control" id="wlim-student-password_confirm" placeholder="<?php esc_html_e("Confirm Password", WL_MIM_DOMAIN); ?>">
			</div>
		</div>
		<div class="form-group mt-3 pl-0 pt-3 border-top enquiry_action">
			<label><?php esc_html_e('After Adding Student', WL_MIM_DOMAIN); ?>:</label><br>
			<div class="row">
				<div class="col">
					<label class="radio-inline"><input checked type="radio" name="enquiry_action" value="mark_enquiry_inactive" id="wlim-student-mark_enquiry_inactive"><?php esc_html_e('Mark Enquiry As Inactive', WL_MIM_DOMAIN); ?>
					</label>
				</div>
				<div class="col">
					<label class="radio-inline"><input type="radio" name="enquiry_action" value="delete_enquiry" id="wlim-student-delete_enquiry"><?php esc_html_e('Delete Enquiry', WL_MIM_DOMAIN); ?>
					</label>
				</div>
			</div>
		</div>
	<?php
			$html               = ob_get_clean();
			$wlim_date_selector = '.wlim-date_of_birth';

			$json = json_encode(array(
				'wlim_date_selector'  => esc_attr($wlim_date_selector),
				'date_of_birth_exist' => boolval($row->date_of_birth),
				'date_of_birth'       => esc_attr($row->date_of_birth),
				'course_id'           => esc_attr($row->course_id),
				'gender'              => esc_attr($row->gender)
			));
			wp_send_json_success(array('html' => $html, 'json' => $json));
		}
		die();
	}

	/* Fetch student fees payable */
	public static function fetch_fees_payable()
	{
		self::check_permission();
		if (!wp_verify_nonce($_REQUEST['security'], 'wl-ima')) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval(sanitize_text_field($_POST['id']));
		$row = $wpdb->get_row("SELECT fees, period, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND id = $id AND institute_id = $institute_id");
		$course_fees = $row->fees;

		if ('monthly' === $row->period) {
			$duration_in_month = WL_MIM_Helper::get_course_months_count($row->duration, $row->duration_in);
			$course_fees = number_format($course_fees / $duration_in_month, 2, '.', '');
		} else {
			$course_fees = number_format($course_fees, 2, '.', '');
		}

		ob_start();
		if ($row) {
		?>

		<div id="wlim-add-student-fetch-fees-calculation">
				<div class="fee_types_box">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th><?php esc_html_e('Total Course Fee', WL_MIM_DOMAIN); ?></th>
								<th><?php esc_html_e('Discount Amount', WL_MIM_DOMAIN); ?></th>
								<th><?php esc_html_e('Amount Payable', WL_MIM_DOMAIN); ?></th>
								<th><?php esc_html_e('Installment Count', WL_MIM_DOMAIN); ?></th></th>
							</tr>
						</thead>
						<tbody class="course_fee_types_rows course_fee_types_table">
							<tr>
								<td>
									<input type="text" name="total_course_fee" id="course_fee" class="form-control" id="course_fee" placeholder="<?php esc_html_e('Course Fee', WL_MIM_DOMAIN); ?>" readonly value="<?php echo esc_attr($course_fees); ?>">
								</td>
								<td>
									<input type="number" min="0" step="any" name="course_discount" class="form-control" id="course_discount" placeholder="<?php esc_html_e('Enter Discount', WL_MIM_DOMAIN); ?>">
								</td>
								<td>
									<input type="number" min="0" step="any" name="course_payable" id="course_payable" class="form-control" placeholder="<?php esc_html_e('Payable', WL_MIM_DOMAIN); ?>" readonly value="<?php echo esc_attr($course_fees); ?>">
								</td>
								<td>
								<select name="installment_count" class="form-control" id="wlim-installment-count">
									<option value=""> <?php esc_html_e("Select", WL_MIM_DOMAIN); ?></option>
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
									<option value="6">6</option>
									<option value="7">7</option>
									<option value="8">8</option>
									<option value="9">9</option>
									<option value="10">10</option>
								</select>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
		</div>
		<div id="wlim-add-student-fetch-installment">
		</div>

		<!-- <div class="fee_types_box">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th><?php esc_html_e('Installments', WL_MIM_DOMAIN); ?></th>
						<th><?php esc_html_e('Fee Period', WL_MIM_DOMAIN); ?></th>
						<th><?php esc_html_e('Amount Payable', WL_MIM_DOMAIN); ?></th>
						<th><?php esc_html_e('Amount Paid', WL_MIM_DOMAIN); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody class="fee_types_rows fee_types_table">
					<?php
					$fee_types = WL_MIM_Helper::get_active_fee_types();
					if (count($fee_types)) {
						foreach ($fee_types as $fee_type) { ?>
							<tr>
								<td>
									<input type="text" name="amount[type][]" class="form-control" placeholder="<?php esc_html_e('Fee Type', WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($fee_type->fee_type); ?>">
								</td>
								<td>
									<select name="period[]" class="form-control">
										<?php
										foreach (WL_MIM_Helper::get_period_in() as $key => $value) { ?>
											<option value="<?php echo esc_attr($key); ?>" <?php selected($key, esc_attr($fee_type->period), true); ?>><?php echo esc_html($value); ?></option>
										<?php
										} ?>
									</select>
								</td>
								<td>
									<input type="number" min="0" step="any" name="amount[payable][]" class="form-control" placeholder="<?php esc_html_e('Payable', WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($fee_type->amount); ?>">
								</td>
								<td>
									<input type="number" min="0" step="any" name="amount[paid][]" class="form-control" placeholder="<?php esc_html_e('Paid', WL_MIM_DOMAIN); ?>" value="0.00">
								</td>
								<td>
									<button class="remove_row btn btn-danger btn-sm" type="button">
										<i class="fa fa-remove" aria-hidden="true"></i>
									</button>
								</td>
							</tr>
						<?php
						} ?>
						<tr>
							<td>
								<input type="text" name="amount[type][]" class="form-control" placeholder="<?php esc_html_e('Fee Type', WL_MIM_DOMAIN); ?>" value="<?php esc_html_e('Course Fee', WL_MIM_DOMAIN); ?>">
							</td>
							<td>
								<select name="period[]" class="form-control">
									<?php
									foreach (WL_MIM_Helper::get_period_in() as $key => $value) { ?>
										<option value="<?php echo esc_attr($key); ?>" <?php selected($key, esc_attr($row->period), true); ?>><?php echo esc_html($value); ?></option>
									<?php
									} ?>
								</select>
							</td>
							<td>
								<input type="number" min="0" step="any" name="amount[payable][]" class="form-control" placeholder="<?php esc_html_e('Payable', WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($course_fees); ?>">
							</td>
							<td>
								<input type="number" min="0" step="any" name="amount[paid][]" class="form-control" placeholder="<?php esc_html_e('Paid', WL_MIM_DOMAIN); ?>" value="0.00">
							</td>
							<td>
								<button class="remove_row btn btn-danger btn-sm" type="button">
									<i class="fa fa-remove" aria-hidden="true"></i>
								</button>
							</td>
						</tr>
					<?php
					} else { ?>
						<tr>
							<td>
								<input type="text" name="amount[type][]" class="form-control" placeholder="<?php esc_html_e('Fee Type', WL_MIM_DOMAIN); ?>" value="<?php esc_html_e('Course Fee', WL_MIM_DOMAIN); ?>">
							</td>
							<td>
								<select name="period[]" class="form-control">
									<?php
									foreach (WL_MIM_Helper::get_period_in() as $key => $value) { ?>
										<option value="<?php echo esc_attr($key); ?>" <?php selected($key, esc_attr($row->period), true); ?>><?php echo esc_html($value); ?></option>
									<?php
									} ?>
								</select>
							</td>
							<td>
								<input type="number" min="0" step="any" name="amount[payable][]" class="form-control" placeholder="<?php esc_html_e('Payable', WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($course_fees); ?>">
							</td>
							<td>
								<input type="number" min="0" step="any" name="amount[paid][]" class="form-control" placeholder="<?php esc_html_e('Paid', WL_MIM_DOMAIN); ?>" value="0.00">
							</td>
							<td>
								<button class="remove_row btn btn-danger btn-sm" type="button">
									<i class="fa fa-remove" aria-hidden="true"></i>
								</button>
							</td>
						</tr>
					<?php
					} ?>
				</tbody>
			</table>
			<div class="text-right">
				<button type="button" class="add-more-fee-types btn btn-success btn-sm"><?php esc_html_e('Add More', WL_MIM_DOMAIN); ?></button>
			</div>
		</div>
	<?php
		} else { ?>
		<div class="fee_types_box">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th><?php esc_html_e('Fee Type', WL_MIM_DOMAIN); ?></th>
						<th><?php esc_html_e('Fee Period', WL_MIM_DOMAIN); ?></th>
						<th><?php esc_html_e('Amount Payable', WL_MIM_DOMAIN); ?></th>
						<th><?php esc_html_e('Amount Paid', WL_MIM_DOMAIN); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody class="fee_types_rows fee_types_table">
					<?php
					$fee_types = WL_MIM_Helper::get_active_fee_types();
					if (count($fee_types)) {
						foreach ($fee_types as $fee_type) { ?>
							<tr>
								<td>
									<input type="text" name="amount[type][]" class="form-control" placeholder="<?php esc_html_e('Fee Type', WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($fee_type->fee_type); ?>">
								</td>
								<td>
									<select name="period[]" class="form-control">
										<?php
										foreach (WL_MIM_Helper::get_period_in() as $key => $value) { ?>
											<option value="<?php echo esc_attr($key); ?>" <?php selected($key, esc_attr($fee_type->period), true); ?>><?php echo esc_html($value); ?></option>
										<?php
										} ?>
									</select>
								</td>
								<td>
									<input type="number" min="0" step="any" name="amount[payable][]" class="form-control" placeholder="<?php esc_html_e('Payable', WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($fee_type->amount); ?>">
								</td>
								<td>
									<input type="number" min="0" step="any" name="amount[paid][]" class="form-control" placeholder="<?php esc_html_e('Paid', WL_MIM_DOMAIN); ?>" value="0.00">
								</td>
								<td>
									<button class="remove_row btn btn-danger btn-sm" type="button">
										<i class="fa fa-remove" aria-hidden="true"></i>
									</button>
								</td>
							</tr>
						<?php
						}
					} else { ?>
						<tr>
							<td>
								<input type="text" name="amount[type][]" class="form-control" placeholder="<?php esc_html_e('Fee Type', WL_MIM_DOMAIN); ?>">
							</td>
							<td>
								<select name="period[]" class="form-control">
									<?php
									foreach (WL_MIM_Helper::get_period_in() as $key => $value) { ?>
										<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></option>
									<?php
									} ?>
								</select>
							</td>
							<td>
								<input type="number" min="0" step="any" name="amount[payable][]" class="form-control" placeholder="<?php esc_html_e('Payable', WL_MIM_DOMAIN); ?>">
							</td>
							<td>
								<input type="number" min="0" step="any" name="amount[paid][]" class="form-control" placeholder="<?php esc_html_e('Paid', WL_MIM_DOMAIN); ?>">
							</td>
							<td>
								<button class="remove_row btn btn-danger btn-sm" type="button">
									<i class="fa fa-remove" aria-hidden="true"></i>
								</button>
							</td>
						</tr>
					<?php
					} ?>
				</tbody>
			</table>
			<div class="text-right">
				<button type="button" class="add-more-fee-types btn btn-success btn-sm"><?php esc_html_e('Add More', WL_MIM_DOMAIN); ?></button>
			</div>
		</div> -->
	<?php
		}
		$html = ob_get_clean();
		wp_send_json_success(array('html' => $html));
	}

	/* Fetch student fees payable */
	public static function fetch_fees_installment() {
		self::check_permission();
		if (!wp_verify_nonce($_REQUEST['security'], 'wl-ima')) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval(sanitize_text_field($_POST['id']));
		$course_payable  = intval(sanitize_text_field($_POST['course_payable']));
		// var_dump($course_payable); die;
		// $row = $wpdb->get_row("SELECT fees, period, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND id = $id AND institute_id = $institute_id");
		// $course_fees = $row->fees;

		// if ('monthly' === $row->period) {
		// 	$duration_in_month = WL_MIM_Helper::get_course_months_count($row->duration, $row->duration_in);
		// 	$course_fees = number_format($course_fees / $duration_in_month, 2, '.', '');
		// } else {
		// 	$course_fees = number_format($course_fees, 2, '.', '');
		// }

		ob_start();
		?>
		<div class="fee_types_box">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th><?php esc_html_e('Title', WL_MIM_DOMAIN); ?></th>
						<th><?php esc_html_e('Amount Payable', WL_MIM_DOMAIN); ?></th>
						<th><?php esc_html_e('Due Date', WL_MIM_DOMAIN); ?></th>
						<th><?php esc_html_e('Due Date Amount', WL_MIM_DOMAIN); ?></th>
						<!-- <th></th> -->
					</tr>
				</thead>
				<tbody class="fee_types_rows fee_types_table">
						<?php for ($i=1; $i < $id+1 ; $i++) { ?>
						<tr>
							<td><input type="text" name="invoice_title[]" class="form-control" placeholder="Fee Type" value="<?php echo 'EMI '.$i; ?> "></td>
							<td><input type="text" name="payable_amount[]" class="form-control" placeholder="Fee Type" value="<?php echo round($course_payable / $id ); ?>"></td>
							<td><input type="text" name="due_date[]" class="form-control wlim-created_at" placeholder="Fee Type" value="<?php echo date('d-m-Y');?>"></td>
							<td><input type="text" name="due_date_amount[]" class="form-control" placeholder="Fee Type" value="<?php echo round($course_payable / $id ); ?>"></td>
						</tr>
						<?php } ?>

				</tbody>
			</table>
		</div>
	<?php
		$html = ob_get_clean();
		wp_send_json_success(array('html' => $html));
	}

	/* Get student registration form */
	public static function add_student_form()
	{
		$institute_id                     = WL_MIM_Helper::get_current_institute_id();
		$wlim_institute_active_categories = WL_MIM_Helper::get_active_categories_institute($institute_id);
		if (count($wlim_institute_active_categories) > 0) {
			$wlim_active_courses = array();
		} else {
			$wlim_active_courses = WL_MIM_Helper::get_active_courses();
		}
		global $wpdb;

		$general_enable_roll_number = WL_MIM_SettingHelper::get_general_enable_roll_number_settings($institute_id);
		$general_enable_enrollment_id = WL_MIM_SettingHelper::get_general_enable_enrollment_id_settings($institute_id);

		ob_start();
		if (count($wlim_institute_active_categories) > 0) { ?>
		<div class="form-group">
			<label for="wlim-student-category" class="col-form-label">* <?php esc_html_e("Category", WL_MIM_DOMAIN); ?>:</label>
			<select name="category" class="form-control" id="wlim-student-category">
				<option value="">-------- <?php esc_html_e("Select a Category", WL_MIM_DOMAIN); ?>--------</option>
				<?php
				foreach ($wlim_institute_active_categories as $active_category) { ?>
					<option value="<?php echo esc_attr($active_category->id); ?>"><?php echo esc_html($active_category->name); ?></option>
				<?php
				} ?>
			</select>
		</div>
		<div id="wlim-student-fetch-category-courses"></div>
	<?php
		} else { ?>
		<div class="form-group wlim-selectpicker">
			<label for="wlim-student-course" class="col-form-label">* <?php esc_html_e("Admission For", WL_MIM_DOMAIN); ?>:</label>
			<select name="course" class="form-control selectpicker" id="wlim-student-course">
				<option value="">-------- <?php esc_html_e("Select a Course", WL_MIM_DOMAIN); ?> --------</option>
				<?php
				if (count($wlim_active_courses) > 0) {
					foreach ($wlim_active_courses as $active_course) {
				?>
						<option value="<?php echo esc_attr($active_course->id); ?>">
							<?php echo esc_html("$active_course->course_name ($active_course->course_code) (" . __("Fees", WL_MIM_DOMAIN) . ": " . $active_course->fees . ")"); ?>
						</option>
				<?php
					}
				} ?>
			</select>
		</div>
	<?php
		} ?>
	<div id="wlim-add-student-course-batches"></div>
	<!-- Create a select input with options 1 to 10 for class selection, with a default selected value based on $row->class -->

	<div class="form-group">
		<label for="wlim-enquiry-class-student" class="col-form-label">* <?php esc_html_e( "Select Class", WL_MIM_DOMAIN ); ?>:</label>
		<select name="class" class="form-control selectpicker" id="wlim-enquiry-class-student">
			<option value=""> -------- <?php esc_html_e( "Select a Class", WL_MIM_DOMAIN ); ?> --------
			</option>
			<?php for($i = 1; $i <= 10; $i++): ?>
				<option value="<?php echo $i; ?>" ><?php esc_html_e( 'Class '.$i, WL_MIM_DOMAIN ); ?></option>
			<?php endfor; ?>
		</select>
	</div>

	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="wlim-student-enrollment_id" class="col-form-label"><?php esc_html_e('Enrollment ID', WL_MIM_DOMAIN); ?>:</label>
			<input name="enrollment_id" type="text" class="form-control" id="wlim-student-enrollment_id" placeholder="<?php esc_html_e('Enrollment ID', WL_MIM_DOMAIN); ?>">
			</div>
		<div class="col-sm-6 form-group">
			<label for="wlim-enquiry-note" class="col-form-label"><?php esc_html_e( 'Business manager', WL_MIM_DOMAIN ); ?>:</label>
			<input name="business_manager" type="text" class="form-control" id="wlim-enquiry-business_manager" placeholder="<?php esc_html_e( "Business manager", WL_MIM_DOMAIN ); ?>">
			</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="wlim-student-first_name" class="col-form-label">* <?php esc_html_e('First Name', WL_MIM_DOMAIN); ?>:</label>
			<input name="first_name" type="text" class="form-control" id="wlim-student-first_name" placeholder="<?php esc_html_e("First Name", WL_MIM_DOMAIN); ?>">
		</div>
		<div class="col-sm-6 form-group">
			<label for="wlim-student-last_name" class="col-form-label"><?php esc_html_e('Last Name', WL_MIM_DOMAIN); ?>:</label>
			<input name="last_name" type="text" class="form-control" id="wlim-student-last_name" placeholder="<?php esc_html_e("Last Name", WL_MIM_DOMAIN); ?>">
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label class="col-form-label">* <?php esc_html_e('Gender', WL_MIM_DOMAIN); ?>:</label><br>
			<div class="row mt-2">
				<div class="col-sm-12">
					<label class="radio-inline mr-3">
						<input checked type="radio" name="gender" class="mr-2" value="male" id="wlim-student-male"><?php esc_html_e('Male', WL_MIM_DOMAIN); ?>
					</label>
					<label class="radio-inline">
						<input type="radio" name="gender" class="mr-2" value="female" id="wlim-student-female"><?php esc_html_e('Female', WL_MIM_DOMAIN); ?>
					</label>
				</div>
			</div>
		</div>
		<div class="col-sm-6 form-group">
			<label for="wlim-student-date_of_birth" class="col-form-label">* <?php esc_html_e('Date of Birth', WL_MIM_DOMAIN); ?>:</label>
			<input name="date_of_birth" type="text" class="form-control wlim-date_of_birth" id="wlim-student-date_of_birth" placeholder="<?php esc_html_e("Date of Birth", WL_MIM_DOMAIN); ?>">
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="wlim-student-father_name" class="col-form-label"><?php esc_html_e("Father's Name", WL_MIM_DOMAIN); ?>:</label>
			<input name="father_name" type="text" class="form-control" id="wlim-student-father_name" placeholder="<?php esc_html_e("Father's Name", WL_MIM_DOMAIN); ?>">
		</div>
		<div class="col-sm-6 form-group">
			<label for="wlim-student-mother_name" class="col-form-label"><?php esc_html_e("Mother's Name", WL_MIM_DOMAIN); ?>:</label>
			<input name="mother_name" type="text" class="form-control" id="wlim-student-mother_name" placeholder="<?php esc_html_e("Mother's Name", WL_MIM_DOMAIN); ?>">
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="wlim-student-phone" class="col-form-label">* <?php esc_html_e('Father Phone', WL_MIM_DOMAIN); ?>:</label>
			<input name="phone" type="text" class="form-control" id="wlim-student-phone" placeholder="<?php esc_html_e("Phone", WL_MIM_DOMAIN); ?>">
		</div>
		<div class="col-sm-6 form-group">
			<label for="wlim-enquiry-phone_update" class="col-form-label">* <?php esc_html_e( 'Mother Phone', WL_MIM_DOMAIN ); ?>:</label>
			<input name="phone2" type="text" class="form-control" id="wlim-enquiry-phone_update" placeholder="<?php esc_html_e( "Mother Phone", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->phone2 ); ?>">
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="wlim-student-address" class="col-form-label"><?php esc_html_e('Address', WL_MIM_DOMAIN); ?>:</label>
			<textarea name="address" class="form-control" rows="4" id="wlim-student-address" placeholder="<?php esc_html_e("Address", WL_MIM_DOMAIN); ?>"></textarea>
		</div>
		<div class="col-sm-6 form-group">
			<div>
				<label for="wlim-student-city" class="col-form-label"><?php esc_html_e('City', WL_MIM_DOMAIN); ?>:</label>
				<input name="city" type="text" class="form-control" id="wlim-student-city" placeholder="<?php esc_html_e("City", WL_MIM_DOMAIN); ?>">
			</div>
			<div>
				<label for="wlim-student-zip" class="col-form-label"><?php esc_html_e('Zip Code', WL_MIM_DOMAIN); ?>:</label>
				<input name="zip" type="text" class="form-control" id="wlim-student-zip" placeholder="<?php esc_html_e("Zip Code", WL_MIM_DOMAIN); ?>">
			</div>
		</div>
	</div>
	<div class="row">
		<!-- <div class="col-sm-6 form-group">
			<label for="wlim-student-state" class="col-form-label"><?php esc_html_e('State', WL_MIM_DOMAIN); ?>:</label>
			<input name="state" type="text" class="form-control" id="wlim-student-state" placeholder="<?php esc_html_e("State", WL_MIM_DOMAIN); ?>">
		</div> -->
		<?php  	$wlim_states = WL_MIM_Helper::get_states(); ?>
		<div class="form-group col-md-6">
			<label for="wlim-state" class="col-form-label"><?php esc_html_e('State', WL_MIM_DOMAIN); ?>:</label>
			<select name="state" id="wlim-state" class="form-control">
				<option value="">Select State</option>
				<?php foreach ($wlim_states as $state): ?>
					<option value="<?php echo $state; ?>"><?php echo esc_html($state); ?></option>
				<?php endforeach ?>
			</select>
		</div>
		<div class="col-sm-6 form-group">
			<label for="wlim-student-nationality" class="col-form-label"><?php esc_html_e('Nationality', WL_MIM_DOMAIN); ?>:</label>
			<input name="nationality" type="text" class="form-control" id="wlim-student-nationality" placeholder="<?php esc_html_e("Nationality", WL_MIM_DOMAIN); ?>">
		</div>
	</div>
	<div class="row">
		<!-- <div class="col-sm-6 form-group">
			<label for="wlim-student-phone" class="col-form-label">* <?php esc_html_e('Phone', WL_MIM_DOMAIN); ?>:</label>
			<input name="phone" type="text" class="form-control" id="wlim-student-phone" placeholder="<?php esc_html_e("Phone", WL_MIM_DOMAIN); ?>">
		</div> -->
		<div class="col-sm-6 form-group">
			<label for="wlim-student-email" class="col-form-label"><?php esc_html_e('Email', WL_MIM_DOMAIN); ?>:</label>
			<input name="email" type="text" class="form-control" id="wlim-student-email" placeholder="<?php esc_html_e("Email", WL_MIM_DOMAIN); ?>">
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="wlim-student-qualification" class="col-form-label"><?php esc_html_e('Course', WL_MIM_DOMAIN); ?>:</label>
			<input name="qualification" type="text" class="form-control" id="wlim-student-qualification" placeholder="<?php esc_html_e("Course", WL_MIM_DOMAIN); ?>">
		</div>
		<div class="col-sm-6 form-group">
			<label for="wlim-student-id_proof" class="col-form-label"><?php esc_html_e('ID Proof', WL_MIM_DOMAIN); ?>:</label><br>
			<input name="id_proof" type="file" id="wlim-student-id_proof">
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="wlim-student-photo" class="col-form-label"><?php esc_html_e('Choose Photo', WL_MIM_DOMAIN); ?> :</label><br>
			<input name="photo" type="file" id="wlim-student-photo">
		</div>
		<div class="col-sm-6 form-group">
			<label for="wlim-student-signature" class="col-form-label"><?php esc_html_e('Choose Signature', WL_MIM_DOMAIN); ?>:</label><br>
			<input name="signature" type="file" id="wlim-student-signature">
		</div>
	</div>
	<?php
		$custom_fields = WL_MIM_Helper::get_active_custom_fields();
		if (count($custom_fields)) { ?>
		<!-- <div class="row">
			<?php
			foreach ($custom_fields as $key => $custom_field) { ?>
				<div class="col-sm-6 form-group">
					<label for="wlim-student-custom_fields_<?php echo esc_attr($key); ?>" class="col-form-label"><?php echo esc_html($custom_field->field_name); ?>:</label>
					<input type="hidden" name="custom_fields[name][]" value="<?php echo esc_attr($custom_field->field_name); ?>">
					<input name="custom_fields[value][]" type="text" class="form-control" id="wlim-student-custom_fields_<?php echo esc_attr($key); ?>" placeholder="<?php echo esc_attr($custom_field->field_name); ?>">
				</div>
			<?php
			} ?>
		</div> -->
	<?php
		} ?>

	<div id="wlim-add-student-fetch-fees-payable">
		<!-- <div class="fee_types_box">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th><?php esc_html_e('Fee Type', WL_MIM_DOMAIN); ?></th>
						<th><?php esc_html_e('Amount Payable', WL_MIM_DOMAIN); ?></th>
						<th><?php esc_html_e('Amount Paid', WL_MIM_DOMAIN); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody class="fee_types_rows fee_types_table">
					<?php
					$fee_types = WL_MIM_Helper::get_active_fee_types();
					if (count($fee_types)) {
						foreach ($fee_types as $fee_type) { ?>
							<tr>
								<td>
									<input type="text" name="course_fee" class="form-control" placeholder="<?php esc_html_e('Fee Type', WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($fee_type->fee_type); ?>">
								</td>
								<td>
									<input type="number" min="0" step="any" name="amount[payable][]" class="form-control" placeholder="<?php esc_html_e('Payable', WL_MIM_DOMAIN); ?>" value="<?php echo esc_attr($fee_type->amount); ?>">
								</td>
								<td>
									<input type="number" min="0" step="any" name="amount[paid][]" class="form-control" placeholder="<?php esc_html_e('Paid', WL_MIM_DOMAIN); ?>" value="0.00">
								</td>
								<td>
									<button class="remove_row btn btn-danger btn-sm" type="button">
										<i class="fa fa-remove" aria-hidden="true"></i>
									</button>
								</td>
							</tr>
						<?php
						}
					} else { ?>
						<tr>
							<td>
								<input type="text" name="amount[type][]" class="form-control" placeholder="<?php esc_html_e('Fee Type', WL_MIM_DOMAIN); ?>">
							</td>
							<td>
								<input type="number" min="0" step="any" name="amount[payable][]" class="form-control" placeholder="<?php esc_html_e('Payable', WL_MIM_DOMAIN); ?>">
							</td>
							<td>
								<input type="number" min="0" step="any" name="amount[paid][]" class="form-control" placeholder="<?php esc_html_e('Paid', WL_MIM_DOMAIN); ?>">
							</td>
							<td>
								<button class="remove_row btn btn-danger btn-sm" type="button">
									<i class="fa fa-remove" aria-hidden="true"></i>
								</button>
							</td>
						</tr>
					<?php
					} ?>
				</tbody>
			</table>
			<div class="text-right">
				<button type="button" class="add-more-fee-types btn btn-success btn-sm"><?php esc_html_e('Add More', WL_MIM_DOMAIN); ?></button>
			</div>
		</div> -->
	</div>

	<div id="wlim-add-student-fetch-installment">

	</div>

	<hr>
	<div class="row">
		<div class="form-group col-sm-6">
			<label for="wlim-student-created_at" class="col-form-label"><?php esc_html_e('Registration Date', WL_MIM_DOMAIN); ?>:</label>
			<input name="created_at" type="text" class="form-control wlim-date_of_birth" id="wlim-student-created_at" placeholder="<?php esc_html_e('Registration Date', WL_MIM_DOMAIN); ?>">
		</div>

		<div class="form-group col-sm-6">
				<label for="wlim-student-expired_at" class="col-form-label"><?php esc_html_e('Registration Expiry Date', WL_MIM_DOMAIN); ?>:</label>
				<input name="expire_at" type="text" class="form-control wlim-date_of_birth" id="wlim-student-expired_at" placeholder="<?php esc_html_e(' Registration Expiry Date', WL_MIM_DOMAIN); ?>">
		</div>
	</div>

	<?php if ($general_enable_roll_number) { ?>
		<div class="form-group">
			<label for="wlim-student-roll_number" class="col-form-label"><?php esc_html_e('Roll Number', WL_MIM_DOMAIN); ?>:</label>
			<input name="roll_number" type="text" class="form-control" id="wlim-student-roll_number" placeholder="<?php esc_html_e('Roll Number', WL_MIM_DOMAIN); ?>">
		</div>
	<?php } ?>

	<?php

	$sources     = WL_MIM_Helper::get_sources();
	$wlim_teachers     = WL_MIM_Helper::get_staff_teachers();

	 ?>
	<div class="row">
		<div class="form-group col-md-4">
			<label for="wlim-student-source" class="col-form-label"><?php esc_html_e('Source', WL_MIM_DOMAIN); ?>:</label>
			<select name="source" id="wlim-source" class="form-control">
			<option value="">Select Source</option>
				<?php foreach ($sources as $source): ?>
					<option value="<?php echo $source->source; ?>"><?php echo esc_html($source->source); ?></option>
				<?php endforeach ?>
			</select>
		</div>

		<div class="form-group col-md-4">
			<label for="wlim-teacher" class="col-form-label"><?php esc_html_e('Teacher', WL_MIM_DOMAIN); ?>:</label>
			<select name="teacher" id="wlim-teacher" class="form-control">
				<option value="">Select Teacher</option>
				<?php foreach ($wlim_teachers as $teacher): ?>
					<option value="<?php echo $teacher->first_name; ?>"><?php echo esc_html($teacher->first_name. ' '. $teacher->last_name); ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>

	<hr>
	<div class="form-check pl-0">
		<input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-student-is_active" checked>
		<label class="form-check-label" for="wlim-student-is_active">
			<?php esc_html_e('Is Active?', WL_MIM_DOMAIN); ?>
		</label>
	</div>
	<hr>
	<div class="form-check pl-0">
		<input name="allow_login" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-student-allow_login" checked>
		<label class="form-check-label" for="wlim-student-allow_login">
			<strong class="text-primary"><?php esc_html_e('Allow Student to Login?', WL_MIM_DOMAIN); ?></strong>
		</label>
	</div>
	<div class="wlim-allow-login-fields">
		<hr>
		<div class="form-group">
			<label for="wlim-student-username" class="col-form-label"><?php esc_html_e('Username', WL_MIM_DOMAIN); ?>:</label>
			<input name="username" type="text" class="form-control" id="wlim-student-username" placeholder="<?php esc_html_e("Username", WL_MIM_DOMAIN); ?>">
		</div>
		<div class="form-group">
			<label for="wlim-student-password" class="col-form-label"><?php esc_html_e('Password', WL_MIM_DOMAIN); ?>:</label>
			<input name="password" type="password" class="form-control" id="wlim-student-password" placeholder="<?php esc_html_e("Password", WL_MIM_DOMAIN); ?>">
		</div>
		<div class="form-group">
			<label for="wlim-student-password_confirm" class="col-form-label"><?php esc_html_e('Confirm Password', WL_MIM_DOMAIN); ?>:</label>
			<input name="password_confirm" type="password" class="form-control" id="wlim-student-password_confirm" placeholder="<?php esc_html_e("Confirm Password", WL_MIM_DOMAIN); ?>">
		</div>
	</div>
<?php
		$html               = ob_get_clean();
		$wlim_date_selector = '.wlim-date_of_birth';

		$json = json_encode(array(
			'wlim_date_selector' => esc_attr($wlim_date_selector),
		));
		wp_send_json_success(array('html' => $html, 'json' => $json));
	}

	/* Fetch category courses */
	public static function fetch_category_courses()
	{
		if (!wp_verify_nonce($_REQUEST['security'], 'wl-ima')) {
			die();
		}
		global $wpdb;
		$category_id = intval(sanitize_text_field($_POST['id']));

		$row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_course_categories WHERE id = $category_id AND is_active = '1'");
		if (!$row) {
			die();
		}

		$wlim_institute_active_courses = $wpdb->get_results("SELECT id, course_name, fees, course_code FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND course_category_id = $category_id ORDER BY course_name");
		ob_start();
?>
	<div class="form-group">
		<label for="wlim-student-course" class="col-form-label">* <?php esc_html_e("Admission For", WL_MIM_DOMAIN); ?>:</label>
		<select name="course" class="form-control" id="wlim-student-course">
			<option value="">-------- <?php esc_html_e("Select a Course", WL_MIM_DOMAIN); ?>-------- </option>
			<?php
			if (count($wlim_institute_active_courses) > 0) {
				foreach ($wlim_institute_active_courses as $active_course) { ?>
					<option value="<?php echo esc_attr($active_course->id); ?>"><?php echo esc_html("$active_course->course_name ($active_course->course_code)"); ?></option>
			<?php
				}
			} ?>
		</select>
	</div>
<?php $html = ob_get_clean();
		wp_send_json_success(array('html' => $html));
	}

	/* Fetch category courses update */
	public static function fetch_category_courses_update()
	{
		if (!wp_verify_nonce($_REQUEST['security'], 'wl-ima')) {
			die();
		}
		global $wpdb;
		$category_id = intval(sanitize_text_field($_POST['id']));

		$row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_course_categories WHERE id = $category_id AND is_active = '1'");
		if (!$row) {
			die();
		}

		$wlim_institute_active_courses = $wpdb->get_results("SELECT id, course_name, fees, course_code FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND course_category_id = $category_id ORDER BY course_name");
		ob_start();
?>
	<div class="form-group">
		<label for="wlim-student-course_update" class="col-form-label">* <?php esc_html_e("Admission For", WL_MIM_DOMAIN); ?>:</label>
		<select name="course" class="form-control" id="wlim-student-course_update">
			<option value="">-------- <?php esc_html_e("Select a Course", WL_MIM_DOMAIN); ?>--------</option>
			<?php
			if (count($wlim_institute_active_courses) > 0) {
				foreach ($wlim_institute_active_courses as $active_course) { ?>
					<option value="<?php echo esc_attr($active_course->id); ?>"><?php echo esc_html("$active_course->course_name ($active_course->course_code)"); ?></option>
			<?php
				}
			} ?>
		</select>
	</div>
<?php $html = ob_get_clean();
		wp_send_json_success(array('html' => $html));
	}

	/* Check permission to manage student */
	private static function check_permission()
	{
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if (!current_user_can('wl_min_manage_students') || !$institute_id) {
			die();
		}
	}
}
?>