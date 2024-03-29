<?php
defined( 'ABSPATH' ) || die();

require_once( 'WL_MIM_Language.php' );
require_once( 'WL_MIM_Widget.php' );
require_once( 'WL_MIM_Shortcode.php' );
require_once( 'inc/controllers/WL_MIM_Enquiry_Front.php' );
require_once( 'inc/controllers/WL_MIM_Result_Front.php' );
require_once( 'inc/controllers/WL_MIM_Payment_Front.php' );
require_once( 'inc/controllers/WL_MIM_ID_Card.php' );
require_once( 'inc/controllers/WL_MIM_Certificate.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

/* Load Translation */
add_action( 'plugins_loaded', array( 'WL_MIM_Language', 'load_translation' ) );

/* Register Widgets */
add_action( 'widgets_init', array( 'WL_MIM_Widget', 'register_widgets' ) );

/* Shortcode Assets */
add_action( 'wp_enqueue_scripts', array( 'WL_MIM_Shortcode', 'shortcode_assets' ) );

/* Admission Form Shortcode */
add_shortcode( 'institute_admission_enquiry_form', array( 'WL_MIM_Shortcode', 'create_enquiry_form' ) );

/* ID Card Form Shortcode */
add_shortcode( 'institute_id_card', array( 'WL_MIM_Shortcode', 'create_id_card_form' ) );

/* Admit Card Form Shortcode */
add_shortcode( 'institute_admit_card', array( 'WL_MIM_Shortcode', 'create_admit_card_form' ) );

/* Certificate Form Shortcode */
add_shortcode( 'institute_certificate', array( 'WL_MIM_Shortcode', 'create_certificate_form' ) );

/* Result Form Shortcode */
add_shortcode( 'institute_exam_result', array( 'WL_MIM_Shortcode', 'create_result_form' ) );

/* Results By Name Form Shortcode */
add_shortcode( 'institute_exam_results_by_name', array( 'WL_MIM_Shortcode', 'create_results_by_name_form' ) );

/* noticebord Shortcode */
add_shortcode( 'institute_noticeboard', array( 'WL_MIM_Shortcode', 'noticeboard' ) );

/* certificate verify */
add_shortcode( 'verify_certificate', array( 'WL_MIM_Shortcode', 'certificate_verify_page' ) );

/* Actions to fetch institute categories */
add_action( 'wp_ajax_wl-mim-fetch-institute-categories', array(
	'WL_MIM_Enquiry_Front',
	'fetch_institute_categories'
) );
add_action( 'wp_ajax_nopriv_wl-mim-fetch-institute-categories', array(
	'WL_MIM_Enquiry_Front',
	'fetch_institute_categories'
) );

/* Actions to fetch category courses */
add_action( 'wp_ajax_wl-mim-fetch-category-courses', array( 'WL_MIM_Enquiry_Front', 'fetch_category_courses' ) );
add_action( 'wp_ajax_nopriv_wl-mim-fetch-category-courses', array(
	'WL_MIM_Enquiry_Front',
	'fetch_category_courses'
) );

/* Actions to fetch institute custom fields */
add_action( 'wp_ajax_wl-mim-fetch-institute-custom-fields', array(
	'WL_MIM_Enquiry_Front',
	'fetch_institute_custom_fields'
) );
add_action( 'wp_ajax_nopriv_wl-mim-fetch-institute-custom-fields', array(
	'WL_MIM_Enquiry_Front',
	'fetch_institute_custom_fields'
) );

/* Actions to add enquiry */
add_action( 'wp_ajax_wl-mim-add-enquiry-front', array( 'WL_MIM_Enquiry_Front', 'add_enquiry' ) );
add_action( 'wp_ajax_nopriv_wl-mim-add-enquiry-front', array( 'WL_MIM_Enquiry_Front', 'add_enquiry' ) );

/* Actions to fetch institute exams */
add_action( 'wp_ajax_wl-mim-fetch-institute-exams', array( 'WL_MIM_Result_Front', 'fetch_institute_exams' ) );
add_action( 'wp_ajax_nopriv_wl-mim-fetch-institute-exams', array( 'WL_MIM_Result_Front', 'fetch_institute_exams' ) );

/* Actions to fetch institute dob for id card if required */
add_action( 'wp_ajax_wl-mim-fetch-institute-dob', array( 'WL_MIM_ID_Card', 'fetch_institute_dob' ) );
add_action( 'wp_ajax_nopriv_wl-mim-fetch-institute-dob', array( 'WL_MIM_ID_Card', 'fetch_institute_dob' ) );

/* Actions to fetch institute dob for certifcate if required */
add_action( 'wp_ajax_wl-mim-fetch-institute-dob-certificate', array( 'WL_MIM_Certificate', 'fetch_institute_dob' ) );
add_action( 'wp_ajax_nopriv_wl-mim-fetch-institute-dob-certificate', array( 'WL_MIM_Certificate', 'fetch_institute_dob' ) );

/* Actions to get id card*/
add_action( 'wp_ajax_wl-mim-get-id-card', array( 'WL_MIM_ID_Card', 'get_id_card' ) );
add_action( 'wp_ajax_nopriv_wl-mim-get-id-card', array( 'WL_MIM_ID_Card', 'get_id_card' ) );

/* Actions to get admit card */
add_action( 'wp_ajax_wl-mim-get-admit-card', array( 'WL_MIM_Result_Front', 'get_admit_card' ) );
add_action( 'wp_ajax_nopriv_wl-mim-get-admit-card', array( 'WL_MIM_Result_Front', 'get_admit_card' ) );

/* Actions to get certificate*/
add_action( 'wp_ajax_wl-mim-get-certificate', array( 'WL_MIM_Certificate', 'get_certificate' ) );
add_action( 'wp_ajax_nopriv_wl-mim-get-certificate', array( 'WL_MIM_Certificate', 'get_certificate' ) );

/* Actions to get exam result */
add_action( 'wp_ajax_wl-mim-get-exam-result', array( 'WL_MIM_Result_Front', 'get_result' ) );
add_action( 'wp_ajax_nopriv_wl-mim-get-exam-result', array( 'WL_MIM_Result_Front', 'get_result' ) );

/* Actions to get exam results by name */
add_action( 'wp_ajax_wl-mim-get-exam-results', array( 'WL_MIM_Result_Front', 'get_results_by_name' ) );
add_action( 'wp_ajax_nopriv_wl-mim-get-exam-results', array( 'WL_MIM_Result_Front', 'get_results_by_name' ) );

/* Actions for paypal transaction status */
add_action( 'wp_ajax_wl-mim-paypal-payments', array( 'WL_MIM_Payment_Front', 'paypal_payments' ) );
add_action( 'wp_ajax_nopriv_wl-mim-paypal-payments', array( 'WL_MIM_Payment_Front', 'paypal_payments' ) );

add_action( 'wp_ajax_nopriv_wl-mim-send-email-otp', array( 'WL_MIM_Helper', 'send_email_otp' ) );
add_action( 'wp_ajax_nopriv_wl-mim-verify-email-otp', array( 'WL_MIM_Helper', 'verify_email_otp' ) );

/* Send birthday messages */
if ( ! wp_next_scheduled( 'wl_mim_send_birthday_messages' ) ) {
	wp_schedule_event( time(), 'daily', 'wl_mim_send_birthday_messages' );
}
add_action( 'wl_mim_send_birthday_messages', array( 'WL_MIM_Helper', 'send_birthday_messages' ) );


if ( ! wp_next_scheduled( 'wl_mim_send_emi_two_day_reminder' ) ) {
	wp_schedule_event( time(), 'daily', 'wl_mim_send_emi_two_day_reminder' );
}
add_action( 'wl_mim_send_emi_two_day_reminder', array( 'WL_MIM_Helper', 'send_emi_two_day_reminder' ) );

if ( ! wp_next_scheduled( 'wl_mim_send_emi_three_days_reminder' ) ) {
	wp_schedule_event( time(), 'daily', 'wl_mim_send_emi_three_days_reminder' );
}
add_action( 'wl_mim_send_emi_three_days_reminder', array( 'WL_MIM_Helper', 'send_emi_three_days_reminder' ) );

// function login_redirect( ) {
//     // Check if the user ID exists in the students table
//     global $wpdb;
// 	$user_id = get_current_user_id();
//     $student_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}wl_min_students WHERE user_id = %d", $user_id ) );
// // var_dump($student_id); die;

//     // Redirect based on the existence of the user ID
// 	if (is_user_logged_in() ) {
// 		if ( $student_id ) {
// 			return home_url( 'wp-admin/admin.php?page=multi-institute-management-student-dashboard' );
// 		} else {
// 			return home_url( 'wp-admin/index.php' );
// 		}
// 	}

// }
// add_filter( 'login_redirect', 'login_redirect' , 10, 3);

//  i want to login_redirect if user is not admin and user_id exists in student table.
function login_redirect( $redirect_to, $request, $user ){

	global $wpdb;
     // Redirect based on the existence of the user ID
		// if (is_user_logged_in() ) {
		// 	$user_id = $user->ID;
		// 	$student_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}wl_min_students WHERE user_id = %d", $user_id ) );
		// 	if ( $student_id ) {
		// 		return home_url( 'wp-admin/admin.php?page=multi-institute-management-student-dashboard' );
		// 	} else {
				return home_url( 'wp-admin/admin.php?page=welcome' );
		// 	}
		// }
}
add_filter( 'login_redirect', 'login_redirect', 10, 3 );