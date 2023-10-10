<?php
defined( 'ABSPATH' ) || die();

require_once 'WL_MIM_Menu.php';
require_once 'inc/controllers/WL_MIM_Setting.php';
require_once 'inc/controllers/WL_MIM_Administrator.php';
require_once 'inc/controllers/WL_MIM_Institute.php';
require_once 'inc/controllers/WL_MIM_Course.php';
require_once 'inc/controllers/WL_MIM_Batch.php';
require_once 'inc/controllers/WL_MIM_Enquiry.php';
require_once 'inc/controllers/WL_MIM_Student.php';
require_once 'inc/controllers/WL_MIM_Note.php';
require_once 'inc/controllers/WL_MIM_Fee.php';
require_once 'inc/controllers/WL_MIM_Invoice.php';
require_once 'inc/controllers/WL_MIM_Reminder.php';
require_once 'inc/controllers/WL_MIM_Custom_Field.php';
require_once 'inc/controllers/WL_MIM_Result.php';
require_once 'inc/controllers/WL_MIM_Expense.php';
require_once 'inc/controllers/WL_MIM_Report.php';
require_once 'inc/controllers/WL_MIM_Notification.php';
require_once 'inc/controllers/WL_MIM_Notice.php';
require_once 'inc/controllers/WL_MIM_Payment.php';
require_once 'inc/controllers/WL_MIM_Attendance.php';
require_once 'inc/controllers/WL_MIM_Reset.php';
require_once 'inc/controllers/WL_MIM_Subject.php';
require_once 'inc/controllers/WL_MIM_Topic.php';
require_once 'inc/controllers/WL_MIM_Room.php';
require_once 'inc/controllers/WL_MIM_TimeTable.php';
// require_once( 'inc/controllers/bulk_action.php' );

/* Action for creating institute management menu pages */
add_action( 'admin_menu', array( 'WL_MIM_Menu', 'create_menu' ) );

/* Action for general setttings */
add_action( 'wp_ajax_wl-mim-save-general-settings', array( 'WL_MIM_Setting', 'save_general_settings' ) );

/* Action for payment setttings */
add_action( 'wp_ajax_wl-mim-save-payment-settings', array( 'WL_MIM_Setting', 'save_payment_settings' ) );

/* Action for email setttings */
add_action( 'wp_ajax_wl-mim-save-email-settings', array( 'WL_MIM_Setting', 'save_email_settings' ) );
add_action( 'wp_ajax_wl-mim-save-email-template', array( 'WL_MIM_Setting', 'save_email_template' ) );

/* Action for sms setttings */
add_action( 'wp_ajax_wl-mim-save-sms-settings', array( 'WL_MIM_Setting', 'save_sms_settings' ) );

/* Action for admit card setttings */
add_action( 'wp_ajax_wl-mim-save-admit-card-settings', array( 'WL_MIM_Setting', 'save_admit_card_settings' ) );

/* Action for id card setttings */
add_action( 'wp_ajax_wl-mim-save-id-card-settings', array( 'WL_MIM_Setting', 'save_id_card_settings' ) );

/* Action for certificate setttings */
add_action( 'wp_ajax_wl-mim-save-certificate-settings', array( 'WL_MIM_Setting', 'save_certificate_settings' ) );

add_action( 'wp_ajax_wl-mim-save-certificate', array( 'WL_MIM_Setting', 'save_certificate' ) );

/* On admin init */
add_action( 'admin_init', array( 'WL_MIM_Setting', 'register_settings' ) );

/* Actions for multi institute administrator */
add_action( 'wp_ajax_wl-mim-get-user-administrator-data', array( 'WL_MIM_Administrator', 'get_user_administrator_data' ) );
add_action( 'wp_ajax_wl-mim-add-user-administrator', array( 'WL_MIM_Administrator', 'add_user_administrator' ) );
add_action( 'wp_ajax_wl-mim-fetch-user-administrator', array( 'WL_MIM_Administrator', 'fetch_user_administrator' ) );
add_action( 'wp_ajax_wl-mim-update-user-administrator', array( 'WL_MIM_Administrator', 'update_user_administrator' ) );

/* Actions for institute administrator */
add_action( 'wp_ajax_wl-mim-get-administrator-data', array( 'WL_MIM_Administrator', 'get_administrator_data' ) );
add_action( 'wp_ajax_wl-mim-get-staff-data', array( 'WL_MIM_Administrator', 'get_staff_data' ) );
add_action( 'wp_ajax_wl-mim-add-administrator', array( 'WL_MIM_Administrator', 'add_administrator' ) );
add_action( 'wp_ajax_wl-mim-fetch-administrator', array( 'WL_MIM_Administrator', 'fetch_administrator' ) );
add_action( 'wp_ajax_wl-mim-update-administrator', array( 'WL_MIM_Administrator', 'update_administrator' ) );

/* Actions for institute */
add_action( 'wp_ajax_wl-mim-set-institute', array( 'WL_MIM_Institute', 'set_institute' ) );
add_action( 'wp_ajax_wl-mim-get-institute-data', array( 'WL_MIM_Institute', 'get_institute_data' ) );
add_action( 'wp_ajax_wl-mim-add-institute', array( 'WL_MIM_Institute', 'add_institute' ) );
add_action( 'wp_ajax_wl-mim-fetch-institute', array( 'WL_MIM_Institute', 'fetch_institute' ) );
add_action( 'wp_ajax_wl-mim-update-institute', array( 'WL_MIM_Institute', 'update_institute' ) );
add_action( 'wp_ajax_wl-mim-delete-institute', array( 'WL_MIM_Institute', 'delete_institute' ) );

/* Actions for main course */
add_action( 'wp_ajax_wl-mim-get-main-course-data', array( 'WL_MIM_Institute', 'get_course_data' ) );
add_action( 'wp_ajax_wl-mim-add-main-course', array( 'WL_MIM_Institute', 'add_course' ) );
add_action( 'wp_ajax_wl-mim-fetch-main-course', array( 'WL_MIM_Institute', 'fetch_course' ) );
add_action( 'wp_ajax_wl-mim-update-main-course', array( 'WL_MIM_Institute', 'update_course' ) );
add_action( 'wp_ajax_wl-mim-delete-main-course', array( 'WL_MIM_Institute', 'delete_course' ) );

/* Actions for category */
add_action( 'wp_ajax_wl-mim-get-category-data', array( 'WL_MIM_Course', 'get_category_data' ) );
add_action( 'wp_ajax_wl-mim-add-category', array( 'WL_MIM_Course', 'add_category' ) );
add_action( 'wp_ajax_wl-mim-fetch-category', array( 'WL_MIM_Course', 'fetch_category' ) );
add_action( 'wp_ajax_wl-mim-update-category', array( 'WL_MIM_Course', 'update_category' ) );
add_action( 'wp_ajax_wl-mim-delete-category', array( 'WL_MIM_Course', 'delete_category' ) );

/* Actions for course */
add_action( 'wp_ajax_wl-mim-get-course-data', array( 'WL_MIM_Course', 'get_course_data' ) );
add_action( 'wp_ajax_wl-mim-add-course', array( 'WL_MIM_Course', 'add_course' ) );
add_action( 'wp_ajax_wl-mim-fetch-course', array( 'WL_MIM_Course', 'fetch_course' ) );
add_action( 'wp_ajax_wl-mim-update-course', array( 'WL_MIM_Course', 'update_course' ) );
add_action( 'wp_ajax_wl-mim-delete-course', array( 'WL_MIM_Course', 'delete_course' ) );

/* Actions for batch */
add_action( 'wp_ajax_wl-mim-get-batch-data', array( 'WL_MIM_Batch', 'get_batch_data' ) );
add_action( 'wp_ajax_wl-mim-add-batch', array( 'WL_MIM_Batch', 'add_batch' ) );
add_action( 'wp_ajax_wl-mim-fetch-batch', array( 'WL_MIM_Batch', 'fetch_batch' ) );
add_action( 'wp_ajax_wl-mim-update-batch', array( 'WL_MIM_Batch', 'update_batch' ) );
add_action( 'wp_ajax_wl-mim-delete-batch', array( 'WL_MIM_Batch', 'delete_batch' ) );

/* Actions for enquiry */
add_action( 'wp_ajax_wl-mim-get-enquiry-data', array( 'WL_MIM_Enquiry', 'get_enquiry_data' ) );
add_action( 'wp_ajax_wl-mim-add-enquiry', array( 'WL_MIM_Enquiry', 'add_enquiry' ) );
add_action( 'wp_ajax_wl-mim-fetch-enquiry', array( 'WL_MIM_Enquiry', 'fetch_enquiry' ) );
add_action( 'wp_ajax_wl-mim-update-enquiry', array( 'WL_MIM_Enquiry', 'update_enquiry' ) );
add_action( 'wp_ajax_wl-mim-delete-enquiry', array( 'WL_MIM_Enquiry', 'delete_enquiry' ) );

/* Actions to fetch category courses */
add_action( 'wp_ajax_wl-mim-fetch-category-courses', array( 'WL_MIM_Enquiry', 'fetch_category_courses' ) );
add_action( 'wp_ajax_wl-mim-fetch-category-courses-update', array( 'WL_MIM_Enquiry', 'fetch_category_courses_update' ) );
add_action( 'wp_ajax_wl-mim-student-fetch-category-courses', array( 'WL_MIM_Student', 'fetch_category_courses' ) );
add_action( 'wp_ajax_wl-mim-student-fetch-category-courses-update', array( 'WL_MIM_Student', 'fetch_category_courses_update' ) );

/* Actions for student */
add_action( 'wp_ajax_wlim_get_batches', array( 'WL_MIM_Student', 'wlim_get_batches' ) );

add_action( 'wp_ajax_wl-mim-get-student-fees-report-data', array( 'WL_MIM_Student', 'get_student_fees_report_data' ) );
add_action( 'wp_ajax_wl-mim-get-student-fees-report-data-dash', array( 'WL_MIM_Student', 'get_student_fees_report_data_dash' ) );
add_action( 'wp_ajax_wl-mim-get-student-data', array( 'WL_MIM_Student', 'get_student_data' ) );
add_action( 'wp_ajax_wl-mim-add-student', array( 'WL_MIM_Student', 'add_student' ) );
add_action( 'wp_ajax_wl-mim-fetch-student', array( 'WL_MIM_Student', 'fetch_student' ) );
add_action( 'wp_ajax_wl-mim-update-student', array( 'WL_MIM_Student', 'update_student' ) );
add_action( 'wp_ajax_wl-mim-delete-student', array( 'WL_MIM_Student', 'delete_student' ) );
add_action( 'wp_ajax_wl-mim-add-student-fetch-course-batches', array( 'WL_MIM_Student', 'fetch_course_batches' ) );
add_action(
	'wp_ajax_wl-mim-add-student-fetch-course-update-batches',
	array(
		'WL_MIM_Student',
		'fetch_course_update_batches',
	)
);
add_action( 'wp_ajax_wl-mim-add-student-fetch-enquiries', array( 'WL_MIM_Student', 'fetch_enquiries' ) );
add_action( 'wp_ajax_wl-mim-add-student-fetch-enquiry', array( 'WL_MIM_Student', 'fetch_enquiry' ) );
add_action( 'wp_ajax_wl-mim-add-student-fetch-fees-payable', array( 'WL_MIM_Student', 'fetch_fees_payable' ) );
add_action( 'wp_ajax_wl-mim-add-student-fetch-installment', array( 'WL_MIM_Student', 'fetch_fees_installment' ) );
add_action( 'wp_ajax_wl-mim-add-student-form', array( 'WL_MIM_Student', 'add_student_form' ) );

// BULK IMPORT
add_action( 'wp_ajax_wl-mim-import-student', array( 'WL_MIM_Student', 'import_students' ) );

/* Actions for note */
add_action( 'wp_ajax_wl-mim-get-note-data', array( 'WL_MIM_Note', 'get_note_data' ) );
add_action( 'wp_ajax_wl-mim-add-note', array( 'WL_MIM_Note', 'add_note' ) );
add_action( 'wp_ajax_wl-mim-fetch-note', array( 'WL_MIM_Note', 'fetch_note' ) );
add_action( 'wp_ajax_wl-mim-update-note', array( 'WL_MIM_Note', 'update_note' ) );
add_action( 'wp_ajax_wl-mim-delete-note', array( 'WL_MIM_Note', 'delete_note' ) );
add_action( 'wp_ajax_wl-mim-view-student-note', array( 'WL_MIM_Note', 'view_student_note' ) );

/* Actions for fee installment */
add_action( 'wp_ajax_wl-mim-get-installment-data', array( 'WL_MIM_Fee', 'get_installment_data' ) );
add_action( 'wp_ajax_wl-mim-add-installment', array( 'WL_MIM_Fee', 'add_installment' ) );
add_action( 'wp_ajax_wl-mim-fetch-installment', array( 'WL_MIM_Fee', 'fetch_installment' ) );
add_action( 'wp_ajax_wl-mim-update-installment', array( 'WL_MIM_Fee', 'update_installment' ) );
add_action( 'wp_ajax_wl-mim-delete-installment', array( 'WL_MIM_Fee', 'delete_installment' ) );
add_action( 'wp_ajax_wl-mim-add-installment-fetch-fees', array( 'WL_MIM_Fee', 'fetch_fees' ) );
add_action( 'wp_ajax_wl-mim-print-installment-fee-receipt', array( 'WL_MIM_Fee', 'print_installment_fee_receipt' ) );
add_action( 'wp_ajax_wl-mim-fetch-invoice-amount', array( 'WL_MIM_Fee', 'fetch_invoice_amount' ) );


// student invoice data
add_action( 'wp_ajax_wl-mim-get-student-invoice-data', array( 'WL_MIM_Invoice', 'get_student_invoice_data' ) );

add_action( 'wp_ajax_wl-mim-get-inactive-invoice-data', array( 'WL_MIM_Invoice', 'get_inactive_invoice_data' ) );

/* Actions for invoice */
add_action( 'wp_ajax_wl-mim-get-invoice-data', array( 'WL_MIM_Invoice', 'get_invoice_data' ) );
add_action( 'wp_ajax_wl-mim-add-invoice', array( 'WL_MIM_Invoice', 'add_invoice' ) );
add_action( 'wp_ajax_wl-mim-fetch-invoice', array( 'WL_MIM_Invoice', 'fetch_invoice' ) );
add_action( 'wp_ajax_wl-mim-update-invoice', array( 'WL_MIM_Invoice', 'update_invoice' ) );
add_action( 'wp_ajax_wl-mim-delete-invoice', array( 'WL_MIM_Invoice', 'delete_invoice' ) );
add_action( 'wp_ajax_wl-mim-add-invoice-fetch-fees', array( 'WL_MIM_Invoice', 'fetch_fees' ) );
add_action( 'wp_ajax_wl-mim-print-invoice-fee-invoice', array( 'WL_MIM_Invoice', 'print_invoice_fee_invoice' ) );

//  student reminders
add_action( 'wp_ajax_wl-mim-get-reminder-data', array( 'WL_MIM_Reminder', 'get_reminder_data' ) );
add_action( 'wp_ajax_wl-mim-add-reminder', array( 'WL_MIM_Reminder', 'add_reminder' ) );
add_action( 'wp_ajax_wl-mim-fetch-reminder', array( 'WL_MIM_Reminder', 'fetch_reminder' ) );
add_action( 'wp_ajax_wl-mim-update-reminder', array( 'WL_MIM_Reminder', 'add_reminder' ) );
add_action( 'wp_ajax_wl-mim-delete-reminder', array( 'WL_MIM_Reminder', 'delete_reminder' ) );

add_action( 'wp_ajax_wl-mim-get-reminder-filter-data', array( 'WL_MIM_Reminder', 'get_reminder_data' ) );

/* Actions for fee type */
add_action( 'wp_ajax_wl-mim-get-fee-type-data', array( 'WL_MIM_Fee', 'get_fee_type_data' ) );
add_action( 'wp_ajax_wl-mim-add-fee-type', array( 'WL_MIM_Fee', 'add_fee_type' ) );
add_action( 'wp_ajax_wl-mim-fetch-fee-type', array( 'WL_MIM_Fee', 'fetch_fee_type' ) );
add_action( 'wp_ajax_wl-mim-update-fee-type', array( 'WL_MIM_Fee', 'update_fee_type' ) );
add_action( 'wp_ajax_wl-mim-delete-fee-type', array( 'WL_MIM_Fee', 'delete_fee_type' ) );

/* Actions for custom field */
add_action( 'wp_ajax_wl-mim-get-custom-field-data', array( 'WL_MIM_Custom_Field', 'get_custom_field_data' ) );
add_action( 'wp_ajax_wl-mim-add-custom-field', array( 'WL_MIM_Custom_Field', 'add_custom_field' ) );
add_action( 'wp_ajax_wl-mim-fetch-custom-field', array( 'WL_MIM_Custom_Field', 'fetch_custom_field' ) );
add_action( 'wp_ajax_wl-mim-update-custom-field', array( 'WL_MIM_Custom_Field', 'update_custom_field' ) );
add_action( 'wp_ajax_wl-mim-delete-custom-field', array( 'WL_MIM_Custom_Field', 'delete_custom_field' ) );

add_action( 'wp_ajax_wl-mim-add-source', array( 'WL_MIM_Custom_Field', 'add_source' ) );
add_action( 'wp_ajax_wl-mim-get-source', array( 'WL_MIM_Custom_Field', 'get_source' ) );
add_action( 'wp_ajax_wl-mim-delete-source', array( 'WL_MIM_Custom_Field', 'delete_source' ) );
/* Actions for exam */
add_action( 'wp_ajax_wl-mim-get-exam-data', array( 'WL_MIM_Result', 'get_exam_data' ) );
add_action( 'wp_ajax_wl-mim-add-exam', array( 'WL_MIM_Result', 'add_exam' ) );
add_action( 'wp_ajax_wl-mim-fetch-exam', array( 'WL_MIM_Result', 'fetch_exam' ) );
add_action( 'wp_ajax_wl-mim-update-exam', array( 'WL_MIM_Result', 'update_exam' ) );
add_action( 'wp_ajax_wl-mim-delete-exam', array( 'WL_MIM_Result', 'delete_exam' ) );

/* Actions for result */
add_action( 'wp_ajax_wl-mim-add-result-fetch-course-batches', array( 'WL_MIM_Result', 'fetch_course_batches' ) );
add_action( 'wp_ajax_wl-mim-add-result-fetch-batch-students', array( 'WL_MIM_Result', 'fetch_batch_students' ) );
add_action( 'wp_ajax_wl-mim-save-result', array( 'WL_MIM_Result', 'save_result' ) );
add_action( 'wp_ajax_wl-mim-get-exam-results', array( 'WL_MIM_Result', 'get_exam_results' ) );
add_action( 'wp_ajax_wl-mim-get-result-data', array( 'WL_MIM_Result', 'get_result_data' ) );

add_action( 'wp_ajax_wl-mim-save-exam-results', array( 'WL_MIM_Result', 'save_exam_results' ) );

add_action( 'wp_ajax_wl-mim-print-exam-result', array( 'WL_MIM_Result', 'print_exam_result' ) );

add_action( 'wp_ajax_wl-mim-add-result', array( 'WL_MIM_Result', 'add_result' ) );
add_action( 'wp_ajax_wl-mim-fetch-result', array( 'WL_MIM_Result', 'fetch_result' ) );
add_action( 'wp_ajax_wl-mim-update-result', array( 'WL_MIM_Result', 'update_result' ) );
add_action( 'wp_ajax_wl-mim-delete-result', array( 'WL_MIM_Result', 'delete_result' ) );

/* Actions for expense */
add_action( 'wp_ajax_wl-mim-get-expense-data', array( 'WL_MIM_Expense', 'get_expense_data' ) );
add_action( 'wp_ajax_wl-mim-add-expense', array( 'WL_MIM_Expense', 'add_expense' ) );
add_action( 'wp_ajax_wl-mim-fetch-expense', array( 'WL_MIM_Expense', 'fetch_expense' ) );
add_action( 'wp_ajax_wl-mim-update-expense', array( 'WL_MIM_Expense', 'update_expense' ) );
add_action( 'wp_ajax_wl-mim-delete-expense', array( 'WL_MIM_Expense', 'delete_expense' ) );

/* Actions for report */
add_action( 'wp_ajax_wl-mim-view-report', array( 'WL_MIM_Report', 'view_report' ) );
add_action( 'wp_ajax_wl-mim-print-student', array( 'WL_MIM_Report', 'print_student' ) );
add_action( 'wp_ajax_wl-mim-print-student-admission-detail', array( 'WL_MIM_Report', 'print_student_admission_detail', ) );
add_action( 'wp_ajax_wl-mim-print-student-fees-report', array( 'WL_MIM_Report', 'print_student_fees_report' ) );
add_action( 'wp_ajax_wl-mim-print-student-pending-fees', array( 'WL_MIM_Report', 'print_student_pending_fees' ) );
add_action( 'wp_ajax_wl-mim-print-student-certificate', array( 'WL_MIM_Report', 'print_student_certificate' ) );
add_action( 'wp_ajax_wl-mim-overall-report-selection', array( 'WL_MIM_Report', 'overall_report_selection' ) );
add_action( 'wp_ajax_wl-mim-view-overall-report', array( 'WL_MIM_Report', 'view_overall_report' ) );

/* Actions for notifications */
add_action( 'wp_ajax_wl-mim-notification-configure', array( 'WL_MIM_Notification', 'notification_configure' ) );
add_action( 'wp_ajax_wl-mim-send-notification', array( 'WL_MIM_Notification', 'send_notification' ) );

/* Actions for noticeboard */
add_action( 'wp_ajax_wl-mim-get-notice-data', array( 'WL_MIM_Notice', 'get_notice_data' ) );
add_action( 'wp_ajax_wl-mim-add-notice', array( 'WL_MIM_Notice', 'add_notice' ) );
add_action( 'wp_ajax_wl-mim-fetch-notice', array( 'WL_MIM_Notice', 'fetch_notice' ) );
add_action( 'wp_ajax_wl-mim-update-notice', array( 'WL_MIM_Notice', 'update_notice' ) );
add_action( 'wp_ajax_wl-mim-delete-notice', array( 'WL_MIM_Notice', 'delete_notice' ) );

/* Actions for payments */
add_action( 'wp_ajax_wl-mim-pay-fees', array( 'WL_MIM_Payment', 'pay_fees' ) );
add_action( 'wp_ajax_wl-mim-pay-razorpay', array( 'WL_MIM_Payment', 'process_razorpay' ) );
add_action( 'wp_ajax_wl-mim-pay-paystack', array( 'WL_MIM_Payment', 'process_paystack' ) );
add_action( 'wp_ajax_wl-mim-pay-stripe', array( 'WL_MIM_Payment', 'process_stripe' ) );
add_action( 'wp_ajax_wl-mim-pay-instamojo', array( 'WL_MIM_Payment', 'pay_instamojo' ) );
// add_action( 'wp_ajax_wl-mim-pay-instamojo', array( 'WL_MIM_Payment', 'process_instamojo' ) );

// Process pay_instamojo.
add_action( 'admin_init', array( 'WL_MIM_Payment', 'process_instamojo' ) );
/* Actions to get student attendance */
add_action( 'wp_ajax_wl-mim-get-student-attendance', array( 'WL_MIM_Attendance', 'get_student_attendance' ) );

/* Actions to get student admit card */
add_action( 'wp_ajax_wl-mim-get-student-admit-card', array( 'WL_MIM_Result', 'get_admit_card' ) );

/* Actions to get student exam result */
add_action( 'wp_ajax_wl-mim-get-student-exam-result', array( 'WL_MIM_Result', 'get_student_result' ) );

/* Action to get batch students for attendance */
add_action( 'wp_ajax_wl-mim-attendance-batch-students', array( 'WL_MIM_Attendance', 'get_batch_students' ) );

/* Action to save attendance */
add_action( 'wp_ajax_wl-mim-view-admit-card', array( 'WL_MIM_Result', 'view_admit_card' ) );

/* Action to save attendance */
add_action( 'wp_ajax_wl-mim-add-attendance', array( 'WL_MIM_Attendance', 'save_attendance' ) );

/* Action for reset */
add_action( 'wp_ajax_wl-mim-reset-plugin', array( 'WL_MIM_Reset', 'perform_reset' ) );

add_action( 'wp_ajax_wl-mim-save-certificate', array( 'WL_MIM_Setting', 'save_certificate' ) );
add_action( 'wp_ajax_wl-mim-fetch-certificates', array( 'WL_MIM_Setting', 'fetch_certificates' ) );
add_action( 'wp_ajax_wl-mim-delete-certificate', array( 'WL_MIM_Setting', 'delete_certificate' ) );
add_action( 'wp_ajax_wl-mim-distribute-certificate', array( 'WL_MIM_Setting', 'distribute_certificate' ) );
add_action( 'wp_ajax_wl-mim-fetch-certificates-distributed', array( 'WL_MIM_Setting', 'fetch_certificates_distributed' ) );
add_action( 'wp_ajax_wl-mim-delete-certificate-distributed', array( 'WL_MIM_Setting', 'delete_certificate_distributed' ) );


add_action( 'wp_ajax_wl-mim-get-students', array( 'WL_MIM_Setting', 'get_students' ) );
add_action( 'wp_ajax_wl-mim-get-batches', array( 'WL_MIM_Setting', 'get_batches' ) );
add_action( 'wp_ajax_wl-mim-fetch-student', array( 'WL_MIM_Student', 'fetch_student' ) );


add_action( 'wp_ajax_wl-mim-bulk-action', array( 'WL_MIM_Student', 'bulk_action' ) );

//action to add subject
add_action( 'wp_ajax_wl_mim-add-subject', array( 'WL_MIM_Subject', 'add_subject'));
add_action( 'wp_ajax_wl-mim-subject-data', array( 'WL_MIM_Subject', 'subList' ));
add_action( 'wp_ajax_wl-mim-fetch-subject', array( 'WL_MIM_Subject', 'fetch_subject' ) );
add_action( 'wp_ajax_wl-mim-update-subject', array( 'WL_MIM_Subject', 'update_subject' ) );
add_action( 'wp_ajax_wl-mim-delete-subject', array( 'WL_MIM_Subject', 'delete_subject' ) );


// action to get the subject on change course
add_action( 'wp_ajax_wl_mim-get-subject', array( 'WL_MIM_Helper', 'get_subject' ));

//action to add the topic
add_action( 'wp_ajax_wl_mim-add-topic', array( 'WL_MIM_Topic', 'add_Topic' ));
add_action( 'wp_ajax_wl-mim-topic-data', array( 'WL_MIM_Topic', 'TopicList' ));
add_action( 'wp_ajax_wl-mim-fetch-topic', array( 'WL_MIM_Topic', 'fetch_topic' ) );
add_action( 'wp_ajax_wl-mim-update-topic', array( 'WL_MIM_Topic', 'update_topic' ) );
add_action( 'wp_ajax_wl-mim-delete-topic', array( 'WL_MIM_Topic', 'delete_topic' ) );

//action to add the room/studio
add_action( 'wp_ajax_wl_mim-add-room', array( 'WL_MIM_Room', 'add_room' ));
add_action( 'wp_ajax_wl-mim-room-data', array( 'WL_MIM_Room', 'RoomList' ));
add_action( 'wp_ajax_wl-mim-fetch-room', array( 'WL_MIM_Room', 'fetchRoomModal' ) );
add_action( 'wp_ajax_wl-mim-update-room', array( 'WL_MIM_Room', 'update_room' ) );
add_action( 'wp_ajax_wl-mim-delete-room', array( 'WL_MIM_Room', 'delete_room' ) );

//Time Table
//action to get the batches/subject and teachers according to the course
add_action( 'wp_ajax_wl-mim-dataforTT', array( 'WL_MIM_TimeTable', 'getBatchSub' ));
add_action( 'wp_ajax_wl-mim-topicforTT', ['WL_MIM_TimeTable', 'getTopic'] );
//action to get the topic and teacher after selecting the subject
add_action( 'wp_ajax_wl-mim-topicTeacher', array( 'WL_MIM_TimeTable', 'getTopicTeacher' ));

//Time Table
add_action( 'wp_ajax_wl-mim-timetable', array( 'WL_MIM_TimeTable', 'saveTimeTable' ));
add_action( 'wp_ajax_wl-mim-fetch-timetable', array( 'WL_MIM_TimeTable', 'fetch_timetable' ) );
// add_action( 'wp_ajax_wl-mim-fetch-timetableview', array( 'WL_MIM_TimeTable', 'fetch_timetableview' ) );
add_action( 'wp_ajax_wl-mim-fetch-timetablemodal', array( 'WL_MIM_TimeTable', 'fetch_timetablemodal' ) );
add_action( 'wp_ajax_wl-mim-update-timetable', array( 'WL_MIM_TimeTable', 'update_timeTable' ) );
add_action( 'wp_ajax_wl-mim-delete-timetable', array( 'WL_MIM_TimeTable', 'delete_timetable' ) );
add_action( 'wp_ajax_wl-mim-view-timetable', array( 'WL_MIM_TimeTable', 'view_timetable' ) );
add_action( 'wp_ajax_wl-mim-getRoom-timetable', array( 'WL_MIM_TimeTable', 'get_room' ) );
add_action( 'wp_ajax_wl-mim-update-teacherRemark', array( 'WL_MIM_TimeTable', 'teacherRemark' ) );

// student time table data
add_action( 'wp_ajax_wl-mim-get-student-timetable', array( 'WL_MIM_TimeTable', 'get_student_timetable_data' ) );
add_action( 'wp_ajax_wl-mim-student-timetableRemark', array( 'WL_MIM_TimeTable', 'post_student_timetableRemark' ) );

// student time table data
add_action( 'wp_ajax_wl-mim-get-student-timetable', array( 'WL_MIM_TimeTable', 'get_student_timetable_data' ) );


// Change user theme
function change_user_theme() {
    // Get all subscriber users
    $subscriber_users = get_users( array(
        'role' => 'subscriber',
    ) );

    // Loop through each subscriber user
    foreach ( $subscriber_users as $user ) {
        // Update user meta to set the theme
        update_user_meta( $user->ID, 'admin_color', 'sunrise' );
    }
}
add_action( 'init', 'change_user_theme' );

function wpse_remove_collapse() {
    echo '<style type="text/css">#collapse-menu { display: none; visibility: hidden; }</style>';
 }
 add_action('admin_head', 'wpse_remove_collapse');

