<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once(WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php');
// WL_MIM_SettingHelper

    class WL_MIM_Homework {
        //Function to get batch and subjects on course change
        public static function getBatchSubject() {
            if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'wl-ima' ) ) {
                die();
            }
            global $wpdb;
            $data = [];
            $data['batchData'] = [];
            $data['subData']   = [];
            $courseID = isset( $_POST['courseID'] ) ? sanitize_text_field( $_POST['courseID'] ) : null;

            $errors = array();
            if ( empty( $courseID ) ) {
                $errors['courseID'] = esc_html__( 'Please select a course.', WL_MIM_DOMAIN );
            }
            $errors_count = count($errors);
            if( isset( $errors_count )  && $errors_count < 1) {
            
                $batchData = $wpdb->get_results("SELECT id, batch_code, batch_name FROM {$wpdb->prefix}wl_min_batches WHERE course_id = $courseID AND is_deleted='0'", ARRAY_A);

                // $savedBatch = $wpdb->get_row( "SELECT batch_id FROM {$wpdb->prefix}wl_min_timetable WHERE batch_date='$batch_date' AND start_time<='$startTime' AND end_time>='$endTime'" );
                // $savedTTbatchID = $savedBatch->batch_id;

                $subData   = $wpdb->get_results("SELECT id, subject_name FROM {$wpdb->prefix}wl_min_subjects AS sub WHERE courseId=$courseID AND is_active = 1", ARRAY_A);

                array_push($data['batchData'], "<option value=''>Select A Batch</option>");
                foreach( $batchData as $key => $value ) {
                        $bData = "<option value='" . $value['id'] . "'>" . $value['batch_name'] . "</option>";
                        array_push($data['batchData'], $bData);
                }
                array_push($data['subData'], "<option value=''>Select A Subject</option>");
                foreach( $subData as $key => $value ) {
                    $bData = "<option value='" . $value['id'] . "'>" . $value['subject_name'] . "</option>";
                    array_push($data['subData'], $bData);
                }
             } else {
                $results['data'] = [];
             }
             echo json_encode($data);
             die();
        }

        //save the homework
        public static function saveHomework() {
            // self::check_permission();
            if ( ! wp_verify_nonce( $_REQUEST['add-homework'], 'add-homework' ) ) {
                die();
            }
            global $wpdb;

            $instituteId    = isset( $_POST['instituteId'] ) ? intval( sanitize_text_field( $_POST['instituteId'] ) ) : null;
            $hw_title       = isset( $_POST['hw_title'] ) ? sanitize_text_field( $_POST['hw_title'] ) : null;
            $hw_description = isset( $_POST['hw_description'] ) ? sanitize_text_field( $_POST['hw_description'] ) : null;
            $hwcourseID     = isset( $_POST['hwcourseID'] ) ? sanitize_text_field( $_POST['hwcourseID'] ) : null;
            $hwbatchID      = isset( $_POST['hwbatchID'] ) ? sanitize_text_field( $_POST['hwbatchID'] ) : null;
            $hwsubID        = isset( $_POST['hwsubID'] ) ? sanitize_text_field( $_POST['hwsubID'] ) : null;
            $homework       = isset($_FILES['homework']) && is_array($_FILES['homework']) ? $_FILES['homework'] : null;
            $hw_sub_date    = isset( $_POST['hw_sub_date'] ) ? sanitize_text_field( $_POST['hw_sub_date'] ) : null;

            /* Validations */
            $errors = array();
            if ( empty( $hw_title ) ) {
                $errors['hw_title'] = esc_html__( 'Please enter a Title.', WL_MIM_DOMAIN );
            }

            // if (!current_user_can('administrator')) {
            //     // get current user id.
            //     $user_id = get_current_user_id();
            //     // get user staff data by user id.
            //     $user_staff_data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_staffs WHERE user_id = $user_id");
            //     // if user have batch_id then add batch_id in filter query.
            //    if ($user_staff_data->batch_id) {
            //     if ($user_staff_data->batch_id != $ttbatchID) {
            //         $errors['ttbatchID'] = esc_html__( 'Dont have permission for this batch.', WL_MIM_DOMAIN );
            //     }
            //    }
            // }

            if ( empty( $hwcourseID ) ) {
                $errors['hwcourseID'] = esc_html__( 'Please select a course.', WL_MIM_DOMAIN );
            }

            if ( empty( $hwbatchID ) ) {
                $errors['hwbatchID'] = esc_html__( 'Please select a batch.', WL_MIM_DOMAIN );
            }

            if ( empty( $hwsubID ) ) {
                $errors['hwsubID'] = esc_html__( 'Please select a subject.', WL_MIM_DOMAIN );
            }

            if ( empty( $hw_sub_date ) ) {
                $errors['hw_sub_date'] = esc_html__( 'Please select a submission date.', WL_MIM_DOMAIN );
            }

            $errors_count = count($errors);
            if( isset( $errors_count )  && $errors_count < 1) {
                try {
                    $wpdb->query('BEGIN;');
                    //data
                    $created_at    = current_time('Y-m-d H:i:s');
                    $homework_data = array(
                        'institute_id'      => $instituteId,
                        'title'             => $hw_title,
                        'description'       => $hw_description,
                        'courseId'          => $hwcourseID,
                        'batch_id'          => $hwbatchID,
                        'subject_id'        => $hwsubID,
                        'submission_date'   => $hw_sub_date,
                        'is_active'         => '1',
                        'is_delete'         => '0',
                        'created_at'        => $created_at,
                        'updated_at'        => ''
                    );

                    if (!empty($homework)) {
                        $homework = media_handle_upload('homework', 0);
                        if (is_wp_error($homework)) {
                            throw new Exception(esc_html__($homework->get_error_message(), WL_MIM_DOMAIN));
                        }
                        $homework_data['homework_id'] = $homework;
                    }

                    $success         = $wpdb->insert($wpdb->prefix . 'wl_min_homework', $homework_data);

                    // if ($hwbatchID) {
                    //     $institute_id = WL_MIM_Helper::get_current_institute_id();
                    //     $data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND batch_id = $hwbatchID AND is_active = 1 AND institute_id = $institute_id" );

                    //     foreach ($data as $student ) {

                    //        $phone = $student->phone;
                    //        $sms_template_student_time_table = WL_MIM_SettingHelper::sms_template_student_time_table($institute_id);
                    //        $sms = WL_MIM_SettingHelper::get_sms_settings($institute_id);

                    //        if ($sms_template_student_time_table['enable']) {
                    //            $sms_message = $sms_template_student_time_table['message'];
                    //            $template_id = $sms_template_student_time_table['template_id'];
                    //            $sms_message = str_replace('[FIRST_NAME]', $data->first_name, $sms_message);
                    //            $sms_message = str_replace('[LAST_NAME]', $data->last_name, $sms_message);
                    //            WL_MIM_SMSHelper::send_sms($sms, $institute_id, $sms_message, $phone, $template_id);
                    //        }
                    //     }
                    // }

                    $previousaddedID = $wpdb->insert_id;
                    if (false === $success) {
                        throw new Exception($wpdb->last_error);
                    }
                    $wpdb->query('COMMIT;');
                    wp_send_json_success( array('message'=>'Homework added') );
                } catch (Exception $exception) {
                    $wpdb->query('ROLLBACK;');
                    wp_send_json_error($exception->getMessage());
                }
            } else {
                wp_send_json_error($errors);
            }
        }

        //data table
        public static function fetch_homework() {
            // self::check_permission();
            if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
                die();
            }
            global $wpdb;
            $institute_id = WL_MIM_Helper::get_current_institute_id();
            if ( current_user_can( 'wl_min_view_homework' ) ) {

            } else {
                die();
            }
            //get current user
            $currentUserId   = get_current_user_id();
            $user            = wp_get_current_user();
            $currentUserRole = $user->roles[0];

           // if( $currentUserId == 1 ) {
            if( current_user_can( 'manage_options' ) ) {
                $result = $wpdb->get_results( "SELECT id, title, description, courseId, batch_id, subject_id, submission_date, homework_id, is_active, is_delete, created_at FROM {$wpdb->prefix}wl_min_homework WHERE institute_id=$institute_id" );
            } else {
                $filter_query = '';
                if (!current_user_can('administrator')) {
                    // get current user id.
                    $user_id = get_current_user_id();
                    // get user staff data by user id.
                    $user_staff_data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_staffs WHERE user_id = $user_id");
                    // if user have batch_id then add batch_id in filter query.
                    if ($user_staff_data->batch_id) {
                        $filter_query .= " AND batch_id = $user_staff_data->batch_id";
                    }
                }
                $result = $wpdb->get_results( "SELECT id, title, description, courseId, batch_id, subject_id, submission_date, homework_id, is_active, is_delete, created_at FROM {$wpdb->prefix}wl_min_homework WHERE staff_id = $currentUserId AND institute_id=$institute_id $filter_query" );
            }
            if(count($result) != 0) {
                $i = 1;
                foreach ($result as $row) {
                    $sno             = $i;
                    $id              = $row->id;
                    $title           = $row->title;
                    $description     = $row->description;
                    $courseID        = WL_MIM_Helper::get_course($row->courseId);
                    $courseName      = $courseID->course_name . " (" . $courseID->course_code . ")";
                    $batch_id        = WL_MIM_Helper::get_batch($row->batch_id);
                    $batch_name      = $batch_id->batch_name . " (" . $batch_id->batch_code . ")";
                    $subjectID       = WL_MIM_Helper::getSubjectName($row->subject_id);
                    $subjectName     = $subjectID->subject_name;
                    $submission_date = $row->submission_date; 
                    $is_acitve       = ($row->is_active == 1) ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
                    $added_on        = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
                    //$added_by      = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

                    if (  current_user_can( 'wl_min_manage_timetable' ) ) {
                        $results['data'][] = array(
                            $sno,
                            esc_html($title),
                            $description,
                            $courseName,
                            $batch_name,
                            $subjectName,
                            $submission_date,
                            // $is_acitve,
                            '<a class="mr-3" href="#update-homework" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> ' . '<a href="javascript:void(0)" delete-homework-security="' . wp_create_nonce( "delete-homework-$id" ) . '"delete-homework-id="' . $id . '" class="delete-homework"> <i class="fa fa-trash text-danger"></i></a>' 
                            // . ' <a class="mr-3" href="#view-homework" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-eye"></i></a> '
                        );
                    } else {
                        $results['data'][] = array(
                            $sno,
                            esc_html($title),
                            $description,
                            $courseName,
                            $batch_name,
                            $subjectName,
                            $submission_date,
                            // $is_acitve,
                        );
                    }

                    $i++;
                }
            } else {
                $results['data'] = array();
            }
            echo json_encode($results);
            die();
        }

        //fetch_homeworkmodal
        public static function fetch_homeworkmodal() {
            // self::check_permission();
            if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
                die();
            }
            global $wpdb;
            $institute_id   = WL_MIM_Helper::get_current_institute_id();
            $institute_name = WL_MIM_Helper::get_current_institute_name();
            $id = intval( sanitize_text_field( $_POST['id'] ) );
            $query = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_homework WHERE id=$id AND institute_id=$institute_id");
            // var_dump($query->title); die();
            // $courseId   = $query->courseId;
            // $subject_id = $query->subject_id;
            // $batch_id   = $query->batch_id;
            // $is_active  = $query->is_active;
            $title      = $query->title;

            // $query_subjects = "";
            // $query_topics   = "";
            ob_start();
            ?>
            <?php
                wp_nonce_field( 'update-homwork', 'update-homwork' );
            ?>
            <input type="hidden" name="homworkid" value="<?php echo $id; ?>" />
            <div class="row"></div>
            <div class="modal fade" id="add-timetable" tabindex="-1" role="dialog" aria-labelledby="add-timetable-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-topic-label"><?php esc_html_e('Add New Homework', WL_MIM_DOMAIN); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post" id="wim_add_homework">
                    <div class="modal-body pr-4 pl-4">
                        <?php
                            wp_nonce_field( 'add-homework', 'add-homework' );
                        ?>
                        <div class="row"></div>
                        <!-- Title & Description -->
                        <div class="row">
                            <div class="form-group col-6">
                                <label for="wlim_hw_title" class="col-form-label">
                                    <?php _e( 'Title', WL_MIM_DOMAIN ); ?>
                                </label>
                                <input type="text" class="form-control" name="hw_title" id="wlim_hw_title" placeholder="Enter Title" />
                            </div>

                            <div class="form-group col-6">
                                <label for="wlim_hw_description" class="col-form-label">
                                    <?php _e( 'Description', WL_MIM_DOMAIN ); ?>
                                </label>
                                <textarea class="form-control" name="hw_description" id="wlim_hw_description" placeholder="Enter Description" /></textarea>
                            </div>
                        </div>

                        <!-- Course & Batch -->
                        <div class="row">
                            <div class="form-group col-6">
                                <label for="hwcourseID" class="col-form-label">
                                    <?php _e( 'Course', WL_MIM_DOMAIN ); ?>
                                </label>
                                <?php
                                    $courses = WL_MIM_Helper::getCourses();
                                ?>
                                <select name="hwcourseID" id="hwcourseID" class="form-control">
                                    <option value=""><?php esc_html_e('Select a Course', WL_MIM_DOMAIN); ?></option>
                                    <?php
                                            foreach($courses as $key=>$value) {
                                            ?>
                                            <option value="<?php echo $value->id; ?>"><?php esc_html_e($value->course_name, WL_MIM_DOMAIN); ?></option>
                                            <?php
                                        }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group col-6">
                                <label for="hwbatchID" class="col-form-label">
                                    <?php _e( 'Batch', WL_MIM_DOMAIN ); ?>
                                </label>
                                <?php
                                    $batches = WL_MIM_Helper::getBatch();
                                ?>
                                <select name="hwbatchID" id="hwbatchID" class="form-control "></select>
                            </div>
                        </div>

                        <!-- Subjects & Homwwork -->
                        <div class="row">
                            <div class="form-group col-6">
                                <label for="hwsubID" class="col-form-label">
                                    <?php _e( 'Subject', WL_MIM_DOMAIN ); ?>
                                </label>
                                <select name="hwsubID" id="hwsubID" class="form-control"></select>
                            </div>

                            <div class="form-group col-6">
                                <label for="wlim-hw-doc" class="col-form-label"><?php esc_html_e('Home Work', WL_MIM_DOMAIN); ?> :</label><br>
                                <input name="homework" type="file" id="wlim-hw-doc">
                            </div>
                        </div>

                        <!-- Submission Date -->
                        <div class="row">
                            <div class="form-group col-6">
                                <label for="wlim_hw_sub_date" class="col-form-label">
                                    <?php _e( 'Submission Date', WL_MIM_DOMAIN ); ?>
                                </label>
                                <input type="date" class="form-control" name="hw_sub_date" id="wlim_hw_sub_date" />
                            </div>
                        </div>
                        
                        <div class="wlim-add-topic-form-fields">
                            <div class="row">
                                <div class="col-6 form-group">
                                    <!--<label for="wlim-institute-name" class="col-form-label">
                                        <?php //esc_html_e('Institute Name', WL_MIM_DOMAIN); ?>
                                    </label>
                                     <input type="text" class="form-control" for="wlin-institute-name" value="<?php echo $institute_name; ?>" disabled /> -->
                                    <input type="hidden" name="instituteId" id="instituteId" class="form-control" value="<?php echo $institute_id; ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e('Cancel', WL_MIM_DOMAIN); ?></button>
                        <button type="submit" class="btn btn-primary add-topic-submit"><?php esc_html_e('Add New Homework', WL_MIM_DOMAIN); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div><!-- end - add new studio modal -->
            <div class="wlim-add-topic-form-fields">
                <div class="row">
                    <!-- <div class="col-6 form-group"> -->
                        <!-- <label for="wlim-institute-name" class="col-form-label">
                            <?php //esc_html_e('Institute Name', WL_MIM_DOMAIN); ?>
                        </label> -->
                        <!-- <input type="text" class="form-control" for="wlin-institute-name" value="<?php echo $institute_name; ?>" disabled /> -->
                        <input type="hidden" name="instituteId" id="instituteId" class="form-control" value="<?php echo $institute_id; ?>" />
                    <!-- </div> -->
                </div>
                <div class="row">
                    <div class="form-group col-6">
                        <label for="wlim_hw_title" class="col-form-label">
                            <?php _e( 'Title', WL_MIM_DOMAIN ); ?>
                        </label>
                        <input type="text" class="form-control" name="hw_title" id="wlim_hw_title" placeholder="Enter Title" />
                    </div>
                    <div class="form-group wlim-selectpicker col-6">
                        <label for="wlim-timetableName" class="col-form-label">
                                <?php esc_html_e('Time Table Name', WL_MIM_DOMAIN); ?>
                        </label>
                        <input type="text" name="wlim-timetableName" id="wlim-timetableName" class="form-control" value="<?php echo  $title; ?>"/>
                    </div>
                </div>
                

            </div>
            <?php
		    $html = ob_get_clean();
            wp_send_json_success( array( 'html' => $html ) );
        }

        /* update */
        public static function update_homwork() {
            // self::check_permission();
            if ( ! wp_verify_nonce( $_POST['update-homwork'], 'update-homwork' ) ) {
                die();
            }
            global $wpdb;
            $institute_id = WL_MIM_Helper::get_current_institute_id();
            global $wpdb;
            $timetableid   = isset( $_POST['timetableid'] ) ? intval( sanitize_text_field( $_POST['timetableid'] ) ) : null;
            $instituteID   = isset( $_POST['instituteId'] ) ? intval( sanitize_text_field( $_POST['instituteId'] ) ) : null;
            $timeTableName = isset( $_POST['wlim-timetableName'] ) ? sanitize_text_field( $_POST['wlim-timetableName'] ) : null;
            $ttcourseID    = isset( $_POST['ttcourseID'] ) ? sanitize_text_field( $_POST['ttcourseID'] ) : null;
            $ttbatchID     = isset( $_POST['ttbatchID'] ) ? sanitize_text_field( $_POST['ttbatchID'] ) : null;
            $ttsubID       = isset( $_POST['ttsubID'] ) ? sanitize_text_field( $_POST['ttsubID'] ) : null;
            $tttopicID     = isset( $_POST['tttopicID'] ) ? sanitize_text_field( $_POST['tttopicID'] ) : null;
            $ttteacherID   = isset( $_POST['ttteacherID'] ) ? sanitize_text_field( $_POST['ttteacherID'] ) : null;
            $wlim_tt_class_date      = isset( $_POST['wlim_tt_class_date'] ) ? sanitize_text_field( $_POST['wlim_tt_class_date'] ) : null;
            $wlim_tt_class_startTime = isset( $_POST['wlim_tt_class_startTime'] ) ? sanitize_text_field( $_POST['wlim_tt_class_startTime'] ) : null;
            $wlim_tt_class_endTime   = isset( $_POST['wlim_tt_class_endTime'] ) ? sanitize_text_field( $_POST['wlim_tt_class_endTime'] ) : null;
            $is_active   = isset( $_POST['is_active'] ) ? sanitize_text_field( $_POST['is_active'] ) : null;
            // if is_active is on the store 1 else 0
            if( $is_active == 'on' ) {
                $is_active = 1;
            } else {
                $is_active = 0;
            }

            /* Validations */
            $errors = array();
            // if ( empty( $timeTableName ) ) {
            //     $errors['timeTableName'] = esc_html__( 'Please select a .', WL_MIM_DOMAIN );
            // }

            // if ( empty( $ttcourseID ) ) {
            //     $errors['ttcourseID'] = esc_html__( 'Please select a .', WL_MIM_DOMAIN );
            // }

            // if ( empty( $ttbatchID ) ) {
            //     $errors['ttbatchID'] = esc_html__( 'Please select a .', WL_MIM_DOMAIN );
            // }

            // if ( empty( $ttsubID ) ) {
            //     $errors['ttsubID'] = esc_html__( 'Please select a .', WL_MIM_DOMAIN );
            // }

            // if ( empty( $tttopicID ) ) {
            //     $errors['tttopicID'] = esc_html__( 'Please select a .', WL_MIM_DOMAIN );
            // }

            // if ( empty( $ttteacherID ) ) {
            //     $errors['ttteacherID'] = esc_html__( 'Please select a .', WL_MIM_DOMAIN );
            // }

            // if ( empty( $wlim_tt_class_date ) ) {
            //     $errors['wlim_tt_class_date'] = esc_html__( 'Please select a .', WL_MIM_DOMAIN );
            // }

            // if ( empty( $wlim_tt_class_startTime ) ) {
            //     $errors['wlim_tt_class_startTime'] = esc_html__( 'Please select a .', WL_MIM_DOMAIN );
            // }

            // if ( empty( $wlim_tt_class_endTime ) ) {
            //     $errors['wlim_tt_class_endTime'] = esc_html__( 'Please select a .', WL_MIM_DOMAIN );
            // }


            $errors_count = count($errors);
            if( isset( $errors_count )  && $errors_count < 1) {
                try {
                    $wpdb->query('BEGIN;');
                    //data
                    $created_at   = current_time('Y-m-d H:i:s');
                    $timeTable_data = array(
                        'institute_id'  => $instituteID,
                        'timeTableName' => $timeTableName,
                        'courseId'    => $ttcourseID,
                        'batch_id'    => $ttbatchID,
                        'subject_id'  => $ttsubID,
                        'topic_id'    => $tttopicID,
                        'staff_id'    => $ttteacherID,
                        'room_id'     => '',
                        'batch_date'  => $wlim_tt_class_date,
                        'start_time'  => $wlim_tt_class_startTime,
                        'end_time'    => $wlim_tt_class_endTime,
                        'is_active'   => $is_active,
                        'updated_at'  => $created_at
                    );

                    $success = $wpdb->update( "{$wpdb->prefix}wl_min_homwork", $homwork_data, array(
                        'id' => $timetableid,
                    ) );
                    // $previousaddedID = $wpdb->insert_id;
                    if (false === $success) {
                        throw new Exception($wpdb->last_error);
                    }
                    $wpdb->query('COMMIT;');

                    if ($ttbatchID) {
                        $institute_id = WL_MIM_Helper::get_current_institute_id();
                        $data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND batch_id = $ttbatchID AND is_active = 1 AND institute_id = $institute_id" );

                        foreach ($data as $student ) {

                           $phone = $student->phone;
                           $sms_template_student_class_cancel = WL_MIM_SettingHelper::sms_template_student_class_cancel($institute_id);
                           $sms = WL_MIM_SettingHelper::get_sms_settings($institute_id);

                           if ($sms_template_student_class_cancel['enable']) {
                               $sms_message = $sms_template_student_class_cancel['message'];
                               $template_id = $sms_template_student_class_cancel['template_id'];

                               $sms_message = str_replace('[FIRST_NAME]', $data->first_name, $sms_message);
                               $sms_message = str_replace('[LAST_NAME]', $data->last_name, $sms_message);
                               WL_MIM_SMSHelper::send_sms($sms, $institute_id, $sms_message, $phone, $template_id);
                           }
                        }
                    }

                    wp_send_json_success( array('message'=>'Time Table Updated') );
                } catch (Exception $exception) {
                    $wpdb->query('ROLLBACK;');
                    wp_send_json_error($exception->getMessage());
                }
            } else {
                wp_send_json_error($errors);
            }
        }

        /* Check permission to manage course */
        // private static function check_permission() {
        //     $institute_id = WL_MIM_Helper::get_current_institute_id();
        //     if ( ! current_user_can( 'wl_min_view_homework' ) || ! $institute_id ) {
        //         die();
        //     }
        //     global $wpdb;
        //     $institute_id = WL_MIM_Helper::get_current_institute_id();
        //     if ( current_user_can( WL_MIM_Helper::get_multi_institute_capability() ) ) {
        //         $can_delete_subject = true;
        //     }
        // }
    }