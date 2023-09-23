<?php 
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

if( !class_exists('WL_MIM_Topic')) {
    class WL_MIM_Topic {
        public static function add_Topic() {
            if ( ! wp_verify_nonce( $_POST['add-topic'], 'add-topic' ) ) {
                die();
            }
            global $wpdb;
            $instituteID  = isset( $_POST['instituteId'] ) ? intval( sanitize_text_field( $_POST['instituteId'] ) ) : null;
            $courseID     = isset( $_POST['wlim-course-name'] ) ? sanitize_text_field( $_POST['wlim-course-name'] ) : null;
            $subjectID    = isset( $_POST['wlim-subject'] ) ? sanitize_text_field( $_POST['wlim-subject'] ) : null;
            $topic        = isset( $_POST['wlim-topic-name'] ) ? sanitize_text_field( $_POST['wlim-topic-name'] ) : null;           

            /* Validations */
            $errors = array();
            if ( empty( $courseID ) ) {
                $errors['wlim-course-name'] = esc_html__( 'Please select a course.', WL_MIM_DOMAIN );
            }
            if ( empty( $subjectID ) ) {
                $errors['wlim-subject'] = esc_html__( 'Please select subject.', WL_MIM_DOMAIN );
            }
            if ( empty( $topic ) ) {
                $errors['wlim-topic-name'] = esc_html__( 'Please provide topic name.', WL_MIM_DOMAIN );
            }
            $errors_count = count($errors);
            if( isset( $errors_count )  && $errors_count < 1) {  
                try {
                    $wpdb->query('BEGIN;');
                    //data
                    $created_at   = current_time('Y-m-d H:i:s');
                    $subject_data = array(
                        'courseId'   => $courseID,
                        'subject_id' => $subjectID,
                        'topic_name' => $topic,
                        'topic_desc' => '',
                        'is_active'  => '1',
                        'created_at' => $created_at
                    );
                    $success = $wpdb->insert($wpdb->prefix . 'wl_min_topics', $subject_data);
                    $previousaddedID = $wpdb->insert_id;                   
                    if (false === $success) {
                        throw new Exception($wpdb->last_error);
                    }
                    $wpdb->query('COMMIT;');
                    wp_send_json_success( array('message'=>'Topic added') );
                } catch (Exception $exception) {
                    $wpdb->query('ROLLBACK;');
                    wp_send_json_error($exception->getMessage());
                }
            } else {
                wp_send_json_error($errors);
            }
        }

        /* Topic list to data table */
        public static function TopicList() {
            self::check_permission();
            if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
                die();
            }
            global $wpdb;
            $institute_id = WL_MIM_Helper::get_current_institute_id();
            $result = $wpdb->get_results("SELECT tt.id as tid, tt.subject_id AS subid, tt.topic_name AS tname, tt.is_active AS tisActive, st.`id` AS subID, st.subject_name AS subName FROM {$wpdb->prefix}wl_min_topics AS tt JOIN {$wpdb->prefix}wl_min_subjects AS st ON tt.subject_id=st.id");
            if(count($result) != 0) {
                $i = 1;
                foreach ($result as $row) { 
                    $sno          = $i;                    
                    $id           = $row->tid;
                    $subject_id   = $row->subid;
                    $subject_name = $row->subName;
                    $topic_name   = $row->tname;
                    $is_acitve    = ($row->tisActive == 1) ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
                    $added_on     = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
                    $added_by     = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';
                    $edit         = "<a href='#' data-tid=$id>Edit</a>";
                    $results['data'][] = array(
                        $sno,
                        esc_html($topic_name),
                        esc_html($subject_name),
                        //'',
                        $is_acitve,
                        //'',
                       // $added_by,
                        $added_on,
                        '<a class="mr-3" href="#update-topic" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> ' . '<a href="javascript:void(0)" delete-topic-security="' . wp_create_nonce( "delete-topic-$id" ) . '"delete-topic-id="' . $id . '" class="delete-topic"> <i class="fa fa-trash text-danger"></i></a>'
                    );
                    $i++;
                }
            } else {
                $results['data'] = array();   
            }
            echo json_encode($results);
            die();
        }

        //fetch the topic to modal
        public static function fetch_topic() {
            self::check_permission();
            if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
                die();
            }
            global $wpdb;
            $institute_id   = WL_MIM_Helper::get_current_institute_id();
            $institute_name = WL_MIM_Helper::get_current_institute_name();
            $id             = intval( sanitize_text_field( $_POST['id'] ) );
            $courses        = WL_MIM_Helper::get_courses($institute_id);
            $row            = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_topics WHERE id = $id" );
            $courseIdSaved  = $row->courseId;
            $subIdSaved     = $row->subject_id;
            $row2           = $wpdb->get_results( "SELECT id, subject_name FROM {$wpdb->prefix}wl_min_subjects WHERE courseId = $courseIdSaved" );
            
            if( !$row ) {
                die();
            }
            ob_start(); ?>
                <input type="hidden" name="topicID" value="<?php echo $id; ?>" />
                <div class="wlim-add-topic-form-fields">                            
                    <div class="row">
                        <div class="col form-group">
                            <label for="wlim-institute-name" class="col-form-label">
                                <?php esc_html_e('Institute Name', WL_MIM_DOMAIN); ?>
                            </label>                                
                            <input type="text" class="form-control" for="wlin-institute-name" value="<?php echo $institute_name; ?>" disabled />
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
                                        <option value="<?php echo $id; ?>" <?php selected( $id, esc_attr( $courseIdSaved ), true ); ?>><?php echo $course . "(" . $course_code .")"; ?></option>
                                        <?php
                                    }
                                ?>
                            </select>                                
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">                                
                            <label for="wlim-subject" class="col-form-label">
                                <?php _e( 'Subject', WL_MIM_DOMAIN ); ?>
                            </label>                               
                            <select name="wlim-subject" id="wlim-subject" class="form-control selectpicker" data-live-search="true">
                                <option value="">-------- <?php esc_html_e("Select Subject", WL_MIM_DOMAIN); ?> --------</option>
                                <?php 
                                    foreach( $row2 AS $key1 ) {
                                        ?>
                                        <option value="<?php echo $key1->id; ?>" <?php selected( $key1->id, esc_attr( $subIdSaved ), true ); ?>><?php esc_html_e($key1->subject_name, WL_MIM_DOMAIN); ?></option>
                                        <?php
                                    }
                                ?>
                            </select>                               
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label for="wlim-topic-name" class="col-form-label">
                                <?php _e( 'Topic', WL_MIM_DOMAIN ); ?>
                            </label>                               
                            <input type="text" name="wlim-topic-name" id="wlim-topic-name" class="form-control" value="<?php echo $row->topic_name; ?>" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group wlim-selectpicker col">
                            <label for="wlim-topic-status" class="col-form-label">
                                    <?php esc_html_e('Topic Status', WL_MIM_DOMAIN); ?>
                            </label>                                
                            <select name="wlim-topic-status" id="wlim-topic-status" class="form-control selectpicker">
                                <option value="">-------- <?php esc_html_e("Select a status", WL_MIM_DOMAIN); ?> --------</option>
                                
                                <option value="1" <?php selected( 1, esc_attr( $row->is_active ), true ); ?>><?php esc_html_e("Active", WL_MIM_DOMAIN); ?></option> 
                                <option value="0" <?php selected( 0, esc_attr( $row->is_active ), true ); ?>><?php esc_html_e("No", WL_MIM_DOMAIN); ?></option>                                   
                            </select>                                
                        </div>
                    </div>
                </div>           
            <?php
            $html = ob_get_clean();
            wp_send_json_success( array( 'html' => $html ) );
        }

        //update the topic
        public static function update_topic() {
            self::check_permission();
            if ( ! wp_verify_nonce( $_POST['update-topics'], 'update-topics' ) ) {
                die();
            }
            global $wpdb;
            $institute_id = WL_MIM_Helper::get_current_institute_id();
            global $wpdb;
            $instituteID  = isset( $_POST['instituteId'] ) ? intval( sanitize_text_field( $_POST['instituteId'] ) ) : null;
            $topicId      = isset( $_POST['topicID'] ) ? intval( sanitize_text_field( $_POST['topicID'] ) ) : null;
            $courseID     = isset( $_POST['wlim-course-name'] ) ? sanitize_text_field( $_POST['wlim-course-name'] ) : null;
            $subjectID    = isset( $_POST['wlim-subject'] ) ? sanitize_text_field( $_POST['wlim-subject'] ) : null;
            $topic        = isset( $_POST['wlim-topic-name'] ) ? sanitize_text_field( $_POST['wlim-topic-name'] ) : null;           

            /* Validations */
            $errors = array();
            if ( empty( $courseID ) ) {
                $errors['wlim-course-name'] = esc_html__( 'Please select a course.', WL_MIM_DOMAIN );
            }
            if ( empty( $subjectID ) ) {
                $errors['wlim-subject'] = esc_html__( 'Please select subject.', WL_MIM_DOMAIN );
            }
            if ( empty( $topic ) ) {
                $errors['wlim-topic-name'] = esc_html__( 'Please provide topic name.', WL_MIM_DOMAIN );
            }
            $errors_count = count($errors);
            if( isset( $errors_count )  && $errors_count < 1) {  
                try {
                    $wpdb->query('BEGIN;');
                    //data
                    $created_at   = current_time('Y-m-d H:i:s');
                    $topic_data = array(
                        'courseId'   => $courseID,
                        'subject_id' => $subjectID,
                        'topic_name' => $topic,
                        'topic_desc' => '',
                        'is_active'  => '1',
                        'created_at' => $created_at
                    );
                    
                    $success = $wpdb->update( "{$wpdb->prefix}wl_min_topics", $topic_data, array(
                        'id' => $topicId,
                    ) );
                    // $previousaddedID = $wpdb->insert_id;                   
                    if (false === $success) {
                        throw new Exception($wpdb->last_error);
                    }                   
                    $wpdb->query('COMMIT;');
                    
                    wp_send_json_success( array('message'=>'Topic added') );
                } catch (Exception $exception) {
                    $wpdb->query('ROLLBACK;');
                    wp_send_json_error($exception->getMessage());
                }
            } else {
                wp_send_json_error($errors);
            }            
        }
        //delete room/studio
        public static function delete_topic() {
            self::check_permission();
            $id = intval( sanitize_text_field( $_POST['id'] ) );
            if ( ! wp_verify_nonce( $_POST["delete-topic-$id"], "delete-topic-$id" ) ) {
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
                $success = $wpdb->delete( "{$wpdb->prefix}wl_min_topics", array(
                    'id'           => $id,
                ) );
                if ( ! $success ) {
                    throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
                }
    
                $wpdb->query( 'COMMIT;' );
                wp_send_json_success( array( 'message' => esc_html__( 'Topic removed successfully.', WL_MIM_DOMAIN ) ) );
            } catch ( Exception $exception ) {
                $wpdb->query( 'ROLLBACK;' );
                wp_send_json_error( esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ) );
            }
        }

        /* Check permission to manage course */
        private static function check_permission() {
            $institute_id = WL_MIM_Helper::get_current_institute_id();
            if ( ! current_user_can( 'wl_min_manage_topics' ) || ! $institute_id ) {
                die();
            }            
        }
    }
}