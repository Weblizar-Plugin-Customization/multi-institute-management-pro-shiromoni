<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

class WL_MIM_Subject {
    //Add new batch
    public static function add_subject() {
         self::check_permission();
        if ( ! wp_verify_nonce( $_POST['add-subject'], 'add-subject' ) ) {
			die();
		}
        global $wpdb;
        $institute_id = WL_MIM_Helper::get_current_institute_id();
        if ( current_user_can( WL_MIM_Helper::get_multi_institute_capability() ) ) {
			$can_delete_subject = true;
		} else {
			$institute = $wpdb->get_row( "SELECT can_delete_subject FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id" );
			$can_delete_subject = false;
			if($institute) {
				$can_delete_subject = (bool) $institute->can_delete_subject;
			}
		}
        //$staffID = [];
        $instituteID  = isset( $_POST['instituteId'] ) ? intval( sanitize_text_field( $_POST['instituteId'] ) ) : null;
        $courseID     = isset( $_POST['wlim-course-name'] ) ? sanitize_text_field( $_POST['wlim-course-name'] ) : null;       
        $staffID      = isset( $_POST['wlim-staff'] ) ? $_POST['wlim-staff'] : null;
        $subjectName  = isset( $_POST['wlim-subject-name'] ) ? sanitize_text_field( $_POST['wlim-subject-name'] ) : '';
        $staff_serialized = serialize($staffID);
        /* Validations */
		$errors = array();
		if ( empty( $courseID ) ) {
			$errors['wlim-course-name'] = esc_html__( 'Please select a course.', WL_MIM_DOMAIN );
		}
        
        if ( empty( $subjectName ) ) {
			$errors['wlim-subject-name'] = esc_html__( 'Please provide subject name.', WL_MIM_DOMAIN );
		}
        $errors_count = count($errors);
            if( isset( $errors_count )  && $errors_count < 1) { 
                try {
                $wpdb->query('BEGIN;');
                //data
                $created_at   = current_time('Y-m-d H:i:s');
                $subject_data = array(
                    'instituteId'  => $instituteID,
                    'courseId'     => $courseID,
                    'staffId'      => $staff_serialized,
                    'subject_name' => $subjectName,
                    'subject_desc' => '',
                    'is_active'    => '1',
                    'created_at'   => $created_at                        
                );
                $success         = $wpdb->insert($wpdb->prefix . 'wl_min_subjects', $subject_data);
                $previousaddedID = $wpdb->insert_id;                
                if (false === $success) {
                    throw new Exception($wpdb->last_error);
                }
                $wpdb->query('COMMIT;');
                wp_send_json_success( array('message'=>'Subject added') );
            } catch (Exception $exception) {
                $wpdb->query('ROLLBACK;');
                wp_send_json_error($exception->getMessage());
            }
        } else {
            wp_send_json_error($errors);
        }
    }

    //subject list datatable
    public static function subList() {
        if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
            die();
        }
        global $wpdb;
        $institute_id = WL_MIM_Helper::get_current_institute_id();
        $result = $wpdb->get_results("SELECT sub.id as suid, sub.courseId AS courseID, sub.staffId AS staffID, sub.subject_name AS subName, cours.course_name AS courseName FROM {$wpdb->prefix}wl_min_subjects AS sub JOIN {$wpdb->prefix}wl_min_courses AS cours ON sub.courseID=cours.id WHERE sub.instituteId=$institute_id");
        if(count($result) != 0) {
            $i = 1;
            foreach ($result as $row) { 
                $sno          = $i;                    
                $id           = $row->suid;
                $subject_id   = $row->subid;
                $courseID     = $row->courseID;
                $courseName   = $row->courseName;
                $subject_name = $row->subName;
                $is_acitve    = ($row->is_active == 1) ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
				$added_on     = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
				$added_by     = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';
                // $edit         = "<a href='#' data-subid=$subject_id>Edit</a>";
                $results['data'][] = array(
                    $sno,
                    esc_html($subject_name),
                    esc_html($courseName), 
                    esc_html( $is_acitve ),					
					esc_html( $added_by ),
                    esc_html( $added_on ),
                    '<a class="mr-3" href="#update-subject" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> ' . '<a href="javascript:void(0)" delete-subject-security="' . wp_create_nonce( "delete-subject-$id" ) . '"delete-subject-id="' . $id . '" class="delete-subject"> <i class="fa fa-trash text-danger"></i></a>'
                );
                $i++;
            }
        } else {
            $results['data'] = array();   
        }
        echo json_encode($results);
        die();
    }

    //fetch subject to modal for edit
    public static function fetch_subject() {
        self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id   = WL_MIM_Helper::get_current_institute_id();
        $institute_name = WL_MIM_Helper::get_current_institute_name();
        $staff          = WL_MIM_Helper::get_staff($institute_id);
        $id      = intval( sanitize_text_field( $_POST['id'] ) );
        $courses = WL_MIM_Helper::get_courses($institute_id);        
        $row     = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_subjects WHERE id = $id" );
        //get staff from other table using subject id, $id
       // $staffIDs = $wpdb->get_results( "SELECT first_name, last_name FROM {$wpdb->prefix}wl_min_staffs WHERE id=$staff_id" );

        $staff_id = unserialize($row->staffId);
        //$staffD = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}wl_min_subjectstaff WHERE subjectID=$id" );
        // var_dump($staff_id);
        //$staffarr = [];
        // for($i=0;$i<count($staffD); $i++) { 
        //     $staffarr[] = $staffD[$i]->id;
        // }     
        //var_dump($staffarr);
        if( !$row ) {
            die();
        }
        ob_start(); ?>             
                <input type="hidden" name="subID" value="<?php echo $id; ?>" />
                <div class="wlim-update-subject-form-fields">                            
                    <div class="row">
                        <div class="col form-group">
                            <label for="wlim-course-name" class="col-form-label">
                                <?php esc_html_e('Institute Name', WL_MIM_DOMAIN); ?>
                            </label>                                
                            <input type="text" class="form-control" name="institute name" value="<?php echo $institute_name; ?>" disabled />
                            <input type="hidden" name="instituteId" id="instituteId" class="form-control" value="<?php echo $institute_id; ?>" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group wlim-selectpicker col">
                            <label for="wlim-course-name" class="col-form-label">
                                    <?php esc_html_e('Course Name', WL_MIM_DOMAIN); ?>
                            </label>                                
                            <select name="wlim-course-name" id="wlim-course-name" class="form-control selectpicker">
                                <option value="">-------- <?php esc_html_e("Select a Course", WL_MIM_DOMAIN); ?> --------</option>
                                <?php 
                                    foreach($courses AS $key ) {
                                        $id          = $key->id;
                                        $course      = $key->course_name;
                                        $course_code = $key->course_code;
                                        ?>
                                        <option value="<?php echo $id; ?>" <?php selected( $id, esc_attr( $row->courseId ), true ); ?>><?php echo $course . "(" . $course_code .")"; ?></option>
                                        <?php
                                    }
                                ?>
                            </select>                                
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">                                
                            <label for="wlim-staff" class="col-form-label">
                                <?php _e( 'Staff', WL_MIM_DOMAIN ); ?>
                            </label>                               
                            <select name="wlim-staff[]" id="wlim-update-staff" class="form-control selectpicker" data-live-search="true" multiple>
                                <option value="">-------- <?php esc_html_e("Select Staff", WL_MIM_DOMAIN); ?> --------</option>
                                <?php 
                                    foreach( $staff as $key => $value) {                                         
                                ?>                                
                                <option value="<?php echo $value->user_id; ?>" <?php echo ( in_array($value->user_id, $staff_id) ? 'selected' : '' ); ?>>
                                    <?php _e( $value->first_name . '&nbsp;' .$value->last_name, WL_MIM_DOMAIN ); ?>
                                </option>
                                <?php
                                        }
                                  
                                ?>
                            </select>                               
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">                                
                            <label for="wlim-subject-name" class="col-form-label">
                                <?php _e( 'Subject', WL_MIM_DOMAIN ); ?>
                            </label>                               
                            <input type="text" name="wlim-subject-name" id="wlim-subject-name" class="form-control" value="<?php echo $row->subject_name; ?>" />
                        </div>
                    </div>
                </div>           
        <?php
		$html = ob_get_clean();
        wp_send_json_success( array( 'html' => $html ) );
    }

    //update the subjects
    public static function update_subject() {
        self::check_permission();
        if ( ! wp_verify_nonce( $_POST['update-subjects'], 'update-subjects' ) ) {
			die();
		}
        global $wpdb;
        $institute_id = WL_MIM_Helper::get_current_institute_id();        
        $instituteID  = isset( $_POST['instituteId'] ) ? intval( sanitize_text_field( $_POST['instituteId'] ) ) : null;
        $courseID     = isset( $_POST['wlim-course-name'] ) ? sanitize_text_field( $_POST['wlim-course-name'] ) : null;
        // $staffID      = isset( $_POST['wlim-staff'] ) ? sanitize_text_field( $_POST['wlim-staff'] ) : null;
        $staffID      = isset( $_POST['wlim-staff'] ) ? $_POST['wlim-staff'] : null;
        $subjectName  = isset( $_POST['wlim-subject-name'] ) ? sanitize_text_field( $_POST['wlim-subject-name'] ) : '';
        $id           = isset( $_POST['subID'] ) ? sanitize_text_field( $_POST['subID'] ) : '';
        $errors = array();
		if ( empty( $courseID ) ) {
			$errors['wlim-course-name'] = esc_html__( 'Please select a course.', WL_MIM_DOMAIN );
		}
        // if ( empty( $staffID ) ) {
		// 	$errors['wlim-staff'] = esc_html__( 'Please provide Staff.', WL_MIM_DOMAIN );
		// }
        $staff_serialized = serialize($staffID);
        if ( empty( $subjectName ) ) {
			$errors['wlim-subject-name'] = esc_html__( 'Please provide subject name.', WL_MIM_DOMAIN );
		}
        $errors_count = count($errors);
            if( isset( $errors_count )  && $errors_count < 1) {
        try {
                $wpdb->query( 'BEGIN;' );

                $data = array(                
                    'courseId'     => $courseID,
                    'staffId'      => $staff_serialized,
                    'subject_name' => $subjectName,
                    'updated_at'   => date( 'Y-m-d H:i:s' )
                );

                $success = $wpdb->update( "{$wpdb->prefix}wl_min_subjects", $data, array(
                    'id' => $id,
                ) );
                
                if ( $success === false ) {
                    throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
                }

                $wpdb->query( 'COMMIT;' );
                wp_send_json_success( array( 'message' => esc_html__( 'Subject updated successfully.', WL_MIM_DOMAIN ) ) );
            } catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
        }
    }

    // delete_subject
    public static function delete_subject() {
        self::check_permission();
        $id = intval( sanitize_text_field( $_POST['id'] ) );
        if ( ! wp_verify_nonce( $_POST["delete-subject-$id"], "delete-subject-$id" ) ) {
            die();
        }
        global $wpdb;
        $institute_id = WL_MIM_Helper::get_current_institute_id();

        try {
            $wpdb->query( 'BEGIN;' );

            /*$success = $wpdb->update( "{$wpdb->prefix}wl_min_topic",
                array(
                    'is_deleted' => 1,
                    'deleted_at' => date( 'Y-m-d H:i:s' )
                ), array( 'is_deleted' => 0, 'id' => $id, 'institute_id' => $institute_id )
            );*/
            $success = $wpdb->delete( "{$wpdb->prefix}wl_min_subjects", array(
                'id'           => $id,
                'instituteId' => $institute_id
            ) );
            if ( ! $success ) {
                throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
            }

            $wpdb->query( 'COMMIT;' );
            wp_send_json_success( array( 'message' => esc_html__( 'Subject removed successfully.', WL_MIM_DOMAIN ) ) );
        } catch ( Exception $exception ) {
            $wpdb->query( 'ROLLBACK;' );
            wp_send_json_error( esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ) );
        }
    }

    /* Check permission to manage course */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_students' ) || ! $institute_id ) {
			die();
		}
	}
}