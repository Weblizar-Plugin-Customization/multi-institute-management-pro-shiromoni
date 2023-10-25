<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once(WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php');
// WL_MIM_SettingHelper

    class WL_MIM_TimeTable {
        //Function to get batch and subjects on class change
        public static function getBatchSub() {
            if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'wl-ima' ) ) {
                die();
            }
            global $wpdb;
            $data = [];
            $data['batchData'] = [];
            $data['subData']   = [];
            $courseID = isset( $_POST['courseID'] ) ? sanitize_text_field( $_POST['courseID'] ) : null;

            $startTime  = isset( $_POST['startTime'] ) ? sanitize_text_field( $_POST['startTime'] ) : null;
            $endTime    = isset( $_POST['endTime'] ) ? sanitize_text_field( $_POST['endTime'] ) : null;
            $batch_date = isset( $_POST['batch_date'] ) ? sanitize_text_field( $_POST['batch_date'] ) : null;
            $errors = array();
            if ( empty( $courseID ) ) {
                $errors['courseID'] = esc_html__( 'Please select a course.', WL_MIM_DOMAIN );
            }
            $errors_count = count($errors);
            if( isset( $errors_count )  && $errors_count < 1) {
            //$batchData = $wpdb->get_results("SELECT bh.id as batchid, bh.batch_code AS batchCode, bh.batch_name AS batchName FROM {$wpdb->prefix}wl_min_batches AS bh WHERE bh.course_id = $courseID");
            //$subData   = $wpdb->get_results("SELECT sub.id as subid, sub.subject_name AS subName FROM {$wpdb->prefix}wl_min_subjects AS sub WHERE sub.courseId=$courseID AND sub.is_active = 1");
                $batchData = $wpdb->get_results("SELECT id, batch_code, batch_name FROM {$wpdb->prefix}wl_min_batches WHERE course_id = $courseID AND is_deleted='0'", ARRAY_A);

                $savedBatch = $wpdb->get_row( "SELECT batch_id FROM {$wpdb->prefix}wl_min_timetable WHERE batch_date='$batch_date' AND start_time<='$startTime' AND end_time>='$endTime'" );
                $savedTTbatchID = $savedBatch->batch_id;

                $subData   = $wpdb->get_results("SELECT id, subject_name FROM {$wpdb->prefix}wl_min_subjects AS sub WHERE courseId=$courseID AND is_active = 1", ARRAY_A);

                array_push($data['batchData'], "<option value=''>Select A Batch</option>");
                foreach( $batchData as $key => $value ) {
                    if( $savedTTbatchID != $value['id'] ) {
                        $bData = "<option value='" . $value['id'] . "'>" . $value['batch_name'] . "</option>";
                        array_push($data['batchData'], $bData);
                    }
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

        //Get the topic and teacher on subject selection
        public static function getTopicTeacher() {
            if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'wl-ima' ) ) {
                die();
            }
            global $wpdb;
            $data = [];
            $data['teacherNames'] = [];
            $data['topics']       = [];
            $subID     = isset( $_POST['subID'] ) ? sanitize_text_field( $_POST['subID'] ) : null;
            $classDate = isset( $_POST['classDate'] ) ? sanitize_text_field( $_POST['classDate'] ) : null;
            $starttime = isset( $_POST['starttime'] ) ? sanitize_text_field( $_POST['starttime'] ) : null;
            $endtime   = isset( $_POST['endtime'] ) ? sanitize_text_field( $_POST['endtime'] ) : null;

            $errors = array();
            if ( empty( $subID ) ) {
                $errors['subID'] = esc_html__( 'Please select a subject.', WL_MIM_DOMAIN );
            }
            $errors_count = count($errors);
            if( isset( $errors_count )  && $errors_count < 1) {
                $topic      = $wpdb->get_results( "SELECT id, topic_name FROM {$wpdb->prefix}wl_min_topics WHERE subject_id = $subID", ARRAY_A );
                array_push($data['topics'], "<option value=''>Select A Topic</option>");
                foreach( $topic as $key => $value ) {
                    $topicData = "<option value='" . $value['id'] . "'>" . $value['topic_name'] . "</option>";
                    array_push($data['topics'], $topicData);
                }
                $teacher    = $wpdb->get_row( "SELECT staffId FROM {$wpdb->prefix}wl_min_subjects WHERE id = $subID" );
                $teacherArr = unserialize($teacher->staffId);
                //get the teacher from the time table Table for the sub id and given date, start and end time
                $ttsid =  $wpdb->get_row( "SELECT staff_id FROM {$wpdb->prefix}wl_min_timetable WHERE subject_id='$subID' AND batch_date = '$classDate' AND start_time<='$starttime' AND end_time>='$endtime'" );
                $savedTeacheridTimeTable = $ttsid->staff_id;
                // foreach( $teacherArr as $teacherId ) {
                array_push($data['teacherNames'], "<option value=''>Select Teacher</option>");
                for( $ti=0; $ti<count($teacherArr); $ti++ ) {
                    $tid = $teacherArr[$ti];
                    if( $savedTeacheridTimeTable !== $tid ){
                     $getTeacherNames = $wpdb->get_row( "SELECT id, first_name, last_name, user_id FROM {$wpdb->prefix}wl_min_staffs WHERE user_id=$tid");
                     $teacherData = "<option value='" . $getTeacherNames->user_id . "'>" . $getTeacherNames->first_name . "</option>";
                     array_push($data['teacherNames'], $teacherData);
                    }
                }
                // $data['topicData'] = $topic;
            } else {
                $results['data'] = [];
            }
            echo json_encode($data);
            die();
        }

        //Get the topic
        public static function getTopic() {
            if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'wl-ima' ) ) {
                die();
            }
            global $wpdb;
            $data = [];
            $ttsubID = isset( $_POST['ttsubID'] ) ? sanitize_text_field( $_POST['ttsubID'] ) : null;
            $errors = array();
            if ( empty( $ttsubID ) ) {
                $errors['ttsubID'] = esc_html__( 'Please select a subject.', WL_MIM_DOMAIN );
            }
            $errors_count = count($errors);
            if( isset( $errors_count )  && $errors_count < 1) {
                $topic = $wpdb->get_results( "SELECT t.id AS topicId, t.topic_name AS topicName FROM {$wpdb->prefix}wl_min_topics AS t WHERE t.subject_id = $ttsubID" );

                $data['topicData'] = $topic;
            } else {
                $results['data'] = [];
            }
            echo json_encode($data);
            die();
        }

        //save the time table
        public static function saveTimeTable() {
            self::check_permission();
            if ( ! wp_verify_nonce( $_REQUEST['add-timetable'], 'add-timetable' ) ) {
                die();
            }
            global $wpdb;

            $instituteID   = isset( $_POST['instituteId'] ) ? intval( sanitize_text_field( $_POST['instituteId'] ) ) : null;
            $timeTableName = isset( $_POST['wlim-timetableName'] ) ? sanitize_text_field( $_POST['wlim-timetableName'] ) : null;
            $ttcourseID    = isset( $_POST['ttcourseID'] ) ? sanitize_text_field( $_POST['ttcourseID'] ) : null;
            $ttbatchID     = isset( $_POST['ttbatchID'] ) ? sanitize_text_field( $_POST['ttbatchID'] ) : null;
            $ttsubID       = isset( $_POST['ttsubID'] ) ? sanitize_text_field( $_POST['ttsubID'] ) : null;
            $tttopicID     = isset( $_POST['tttopicID'] ) ? sanitize_text_field( $_POST['tttopicID'] ) : null;
            $ttroomID      = isset( $_POST['ttroomID'] ) ? sanitize_text_field( $_POST['ttroomID'] ) : null;
            $ttteacherID   = isset( $_POST['ttteacherID'] ) ? sanitize_text_field( $_POST['ttteacherID'] ) : null;
            $wlim_tt_class_date      = isset( $_POST['wlim_tt_class_date'] ) ? sanitize_text_field( $_POST['wlim_tt_class_date'] ) : null;
            $wlim_tt_class_startTime = isset( $_POST['wlim_tt_class_startTime'] ) ? sanitize_text_field( $_POST['wlim_tt_class_startTime'] ) : null;
            $wlim_tt_class_endTime   = isset( $_POST['wlim_tt_class_endTime'] ) ? sanitize_text_field( $_POST['wlim_tt_class_endTime'] ) : null;

            /* Validations */
            $errors = array();
            if ( empty( $timeTableName ) ) {
                $errors['timeTableName'] = esc_html__( 'Please enter a Time table name.', WL_MIM_DOMAIN );
            }

            if ( empty( $ttcourseID ) ) {
                $errors['ttcourseID'] = esc_html__( 'Please select a Course.', WL_MIM_DOMAIN );
            }

            if ( empty( $ttroomID ) ) {
                $errors['ttroomID'] = esc_html__( 'Please select a room.', WL_MIM_DOMAIN );
            }

            if ( empty( $ttbatchID ) ) {
                $errors['ttbatchID'] = esc_html__( 'Please select a batch.', WL_MIM_DOMAIN );
            }

            // Batch Validations
            if ($ttbatchID) {
                global $wpdb;
                $result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_timetable WHERE batch_id = $ttbatchID AND batch_date = '$wlim_tt_class_date' AND start_time <= '$wlim_tt_class_startTime' AND end_time >= '$wlim_tt_class_endTime'");

                if (!empty($result)) {
                    $errors['ttbatchID'] = esc_html__( 'This Batch already exists for given date and time', WL_MIM_DOMAIN );
                }
            }


            if ($ttteacherID) {
                global $wpdb;
                $result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_timetable WHERE staff_id = $ttteacherID AND batch_date = '$wlim_tt_class_date' AND start_time <='$wlim_tt_class_startTime' AND end_time >= '$wlim_tt_class_endTime'");

                if (!empty($result)) {
                    $errors['ttteacherID'] = esc_html__( 'This Teacher already exists for given date and time', WL_MIM_DOMAIN );
                }
            }

            if ( empty( $ttsubID ) ) {
                $errors['ttsubID'] = esc_html__( 'Please select a subject.', WL_MIM_DOMAIN );
            }

            if ( empty( $tttopicID ) ) {
                $errors['tttopicID'] = esc_html__( 'Please select a topic.', WL_MIM_DOMAIN );
            }

            if ( empty( $ttteacherID ) ) {
                $errors['ttteacherID'] = esc_html__( 'Please select a teacher.', WL_MIM_DOMAIN );
            }

            if ( empty( $wlim_tt_class_date ) ) {
                $errors['wlim_tt_class_date'] = esc_html__( 'Please select a class date.', WL_MIM_DOMAIN );
            }

            if ( empty( $wlim_tt_class_startTime ) ) {
                $errors['wlim_tt_class_startTime'] = esc_html__( 'Please select a start time.', WL_MIM_DOMAIN );
            }

            if ( empty( $wlim_tt_class_endTime ) ) {
                $errors['wlim_tt_class_endTime'] = esc_html__( 'Please select a end time.', WL_MIM_DOMAIN );
            }

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
                        'room_id'     => $ttroomID,
                        'batch_date'  => $wlim_tt_class_date,
                        'start_time'  => $wlim_tt_class_startTime,
                        'end_time'    => $wlim_tt_class_endTime,
                        'is_active'   => '1',
                        'created_at'  => $created_at,
                        'updated_at'  => ''
                    );
                    $success         = $wpdb->insert($wpdb->prefix . 'wl_min_timetable', $timeTable_data);

                    if ($ttbatchID) {
                        $institute_id = WL_MIM_Helper::get_current_institute_id();
                        $data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND batch_id = $ttbatchID AND is_active = 1 AND institute_id = $institute_id" );

                        foreach ($data as $student ) {

                           $phone = $student->phone;
                           $sms_template_student_time_table = WL_MIM_SettingHelper::sms_template_student_time_table($institute_id);
                           $sms = WL_MIM_SettingHelper::get_sms_settings($institute_id);

                           if ($sms_template_student_time_table['enable']) {
                               $sms_message = $sms_template_student_time_table['message'];
                               $template_id = $sms_template_student_time_table['template_id'];
                               $sms_message = str_replace('[FIRST_NAME]', $data->first_name, $sms_message);
                               $sms_message = str_replace('[LAST_NAME]', $data->last_name, $sms_message);
                               WL_MIM_SMSHelper::send_sms($sms, $institute_id, $sms_message, $phone, $template_id);
                           }
                        }
                    }

                    $previousaddedID = $wpdb->insert_id;
                    if (false === $success) {
                        throw new Exception($wpdb->last_error);
                    }
                    $wpdb->query('COMMIT;');
                    wp_send_json_success( array('message'=>'Time Table added') );
                } catch (Exception $exception) {
                    $wpdb->query('ROLLBACK;');
                    wp_send_json_error($exception->getMessage());
                }
            } else {
                wp_send_json_error($errors);
            }
        }

        //data table
        public static function fetch_timetable() {
            // self::check_permission();
            if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
                die();
            }
            global $wpdb;
            $institute_id = WL_MIM_Helper::get_current_institute_id();
            if ( current_user_can( 'wl_min_view_timetable' ) ) {

            } else {
                die();
            }
            //get current user
            $currentUserId = get_current_user_id();
            $user            = wp_get_current_user();
            $currentUserRole = $user->roles[0];


            $result1       = $wpdb->get_results("SELECT tt.id as tid, tt.subject_id AS subid, tt.topic_name AS tname, tt.is_active AS tisActive, st.`id` AS subID, st.subject_name AS subName FROM {$wpdb->prefix}wl_min_topics AS tt JOIN {$wpdb->prefix}wl_min_subjects AS st ON tt.subject_id=st.id");
           // if( $currentUserId == 1 ) {
            if( current_user_can( 'manage_options' ) ) {
                $result = $wpdb->get_results( "SELECT id, batch_id, courseId,subject_id, topic_id, room_id, timeTableName, is_active, created_at, batch_date, start_time, end_time, staff_id, remark FROM {$wpdb->prefix}wl_min_timetable WHERE institute_id=$institute_id" );
            } else {
                $result = $wpdb->get_results( "SELECT id, batch_id, courseId,subject_id, topic_id, room_id, timeTableName, is_active, created_at, batch_date, start_time, end_time, staff_id, remark FROM {$wpdb->prefix}wl_min_timetable WHERE staff_id = $currentUserId AND institute_id=$institute_id" );
            }
            if(count($result) != 0) {
                $i = 1;
                foreach ($result as $row) {
                    $sno           = $i;
                    $id            = $row->id;
                    $courseID      = WL_MIM_Helper::get_course($row->courseId);
                    $courseName    = $courseID->course_name . " (" . $courseID->course_code . ")";
                    $batch_id      = WL_MIM_Helper::get_batch($row->batch_id);
                    $batch_name    = $batch_id->batch_name . " (" . $batch_id->batch_code . ")";
                    $subjectID     = WL_MIM_Helper::getSubjectName($row->subject_id);
                    $subjectName   = $subjectID->subject_name;
                    $timetablename = $row->timeTableName;
                    $staff_nameR   = WL_MIM_Helper::get_staffName_by_staffID($row->staff_id);
                    $staff_name    = $staff_nameR->first_name . " " . $staff_nameR->last_name;
                    // $date          = $row->batch_date;
                    $date          = date_format( date_create( $row->batch_date ), "d-m-Y" );
                    $topic_id      = WL_MIM_Helper::getTopicName($row->topic_id);
                    $topic_name    = $topic_id->topic_name;
                    $room_id       = WL_MIM_Helper::getRoomName($row->room_id);
                    $end_time      = $row->end_time;
                    $start_time    = $row->start_time;
                    $savedRemark   = $row->remark;
                    $is_acitve     = ($row->is_active == 1) ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
                    $added_on      = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
                    //$added_by     = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';
                    if( !empty($savedRemark) ) {
                        $remark = $savedRemark;
                    } else {
                        $remark = '';
                    }

                    $nonce         = wp_create_nonce( "tRemark" );

                    $remarkControl = WL_MIM_Helper::controlRemark($id)->MATCHED;
                    if($remarkControl == 1) {
                        $remark = "<form id='teacherRemark'><input type='hidden' name='tRemark' value='" . $nonce ."'/><input type='text' class='form-control' name='teacherRemark' id='teacherRemark' value='" . $remark . "' /><input type='hidden' name='timeTableID' id='timeTableID' value='".$id ."' /><button class='btn btn-success' type='submit' id='saveRemark'>Save</button></form>";
                    } elseif($remarkControl == 0) {
                        $remark = "<input type='text' name='remark' id='remark' value='" . $remark ."' disabled />";
                    }
                    $nonce = wp_create_nonce( "sRemark" );
                    if (  current_user_can( 'wl_min_manage_timetable' ) ) {
                        $results['data'][] = array(
                            $sno,
                            esc_html($timetablename),
                            $room_id->room_name,
                            $courseName,
                            $batch_name,
                            $subjectName,
                            $topic_name,
                            $staff_name,
                            $is_acitve,
                            $date,
                            $start_time,
                            $end_time,
                            $remark,
                            '<a class="mr-3" href="#update-timetable" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> ' . '<a href="javascript:void(0)" delete-timetable-security="' . wp_create_nonce( "delete-timetable-$id" ) . '"delete-timetable-id="' . $id . '" class="delete-timetable"> <i class="fa fa-trash text-danger"></i></a>' . ' <a class="mr-3" href="#view-timetable" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-eye"></i></a> '
                        );
                    } else {
                        $results['data'][] = array(
                            $sno,
                            esc_html($timetablename),
                            $room_id->room_name,
                            $courseName,
                            $batch_name,
                            $subjectName,
                            $topic_name,
                            $staff_name,
                            $is_acitve,
                            $date,
                            $start_time,
                            $end_time,
                            $remark,
                            ''
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

        //Add or update the teacher remark
        public static function teacherRemark() {
            self::check_permission();
            if ( ! wp_verify_nonce( $_REQUEST['tRemark'], 'tRemark' ) ) {
                die();
            }
            global $wpdb;
            $institute_id  = WL_MIM_Helper::get_current_institute_id();
            $teacherRemark = isset( $_POST['teacherRemark'] ) ? sanitize_text_field( $_POST['teacherRemark'] ) : null;
            $timeTableID   = isset( $_POST['timeTableID'] ) ? sanitize_text_field( $_POST['timeTableID'] ) : null;
            //UPDATE `wp_wl_min_timetable` SET remark='' WHERE `id`
            $errors = array();
            if ( empty( $teacherRemark ) ) {
                $errors['teacherRemark'] = esc_html__( 'Please enter remark .', WL_MIM_DOMAIN );
            }
            $errors_count = count($errors);
            if( isset( $errors_count )  && $errors_count < 1) {
                try {
                    $wpdb->query('BEGIN;');
                    //data
                    $created_at   = current_time('Y-m-d H:i:s');
                    $timeTable_data = array(
                        'remark'    => $teacherRemark,
                        'updated_at'  => $created_at
                    );

                    $success = $wpdb->update( "{$wpdb->prefix}wl_min_timetable", $timeTable_data, array(
                        'id' => $timeTableID,
                    ) );
                    // $previousaddedID = $wpdb->insert_id;
                    if (false === $success) {
                        throw new Exception($wpdb->last_error);
                    }
                    $wpdb->query('COMMIT;');

                    wp_send_json_success( array('message'=>'Time Table Updated') );
                } catch (Exception $exception) {
                    $wpdb->query('ROLLBACK;');
                    wp_send_json_error($exception->getMessage());
                }
            } else {
                wp_send_json_error($errors);
            }

            die();
        }



        //add the student remark
        public static function post_student_timetableRemark() {
            if ( ! wp_verify_nonce( $_REQUEST['sRemark'], 'sRemark' ) ) {
                die();
            }
            global $wpdb;
            $institute_id  = WL_MIM_Helper::get_current_institute_id();
            $studentRemark = isset( $_POST['studentRemark'] ) ? sanitize_text_field( $_POST['studentRemark'] ) : null;
            $timeTableID   = isset( $_POST['timeTableID'] ) ? sanitize_text_field( $_POST['timeTableID'] ) : null;
            $batch_id      = isset( $_POST['batch_id'] ) ? sanitize_text_field( $_POST['batch_id'] ) : null;
            $subjectID     = isset( $_POST['subjectID'] ) ? sanitize_text_field( $_POST['subjectID'] ) : null;
            $topicID       = isset( $_POST['topicID'] ) ? sanitize_text_field( $_POST['topicID'] ) : null;
            $batch_date    = isset( $_POST['batch_date'] ) ? sanitize_text_field( $_POST['batch_date'] ) : null;
            $currentUserId = get_current_user_id();
            $errors = array();
            if ( empty( $studentRemark ) ) {
                $errors['studentRemark'] = esc_html__( 'Please enter remark .', WL_MIM_DOMAIN );
            }
            $errors_count = count($errors);
            $checkSavedRecord = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_studentremark WHERE `batch_id`='$batch_id' AND `batch_date`='$batch_date' AND `subject_id`='$subjectID' AND `student_id`='$currentUserId'" );

            if( isset( $errors_count )  && $errors_count < 1) {
                if( $checkSavedRecord ) {
                    $savedTTIDData = $wpdb->get_row("SELECT id FROM {$wpdb->prefix}wl_min_studentremark WHERE `batch_id`='$batch_id' AND `batch_date`='$batch_date' AND `subject_id`='$subjectID' AND `student_id`='$currentUserId'");
                    $studentTTID = $savedTTIDData->id;
                    try {
                        $wpdb->query('BEGIN;');
                        //data
                        $created_at   = current_time('Y-m-d H:i:s');
                        $timeTable_data = array(
                            'remark'    => $studentRemark,
                            'updated_at'  => $created_at
                        );

                        $success = $wpdb->update( "{$wpdb->prefix}wl_min_studentremark", $timeTable_data, array(
                            'id' => $studentTTID, 'student_id' => $currentUserId,
                        ) );
                        // $previousaddedID = $wpdb->insert_id;
                        if (false === $success) {
                            throw new Exception($wpdb->last_error);
                        }
                        $wpdb->query('COMMIT;');

                        wp_send_json_success( array('message'=>'Time Table Updated') );
                    } catch (Exception $exception) {
                        $wpdb->query('ROLLBACK;');
                        wp_send_json_error($exception->getMessage());
                    }
                } else {
                    try {
                        $wpdb->query('BEGIN;');
                        //data
                        $created_at   = current_time('Y-m-d H:i:s');
                        $timeTable_data = array(
                            'institute_id' => $institute_id,
                            'student_id'   => $currentUserId,
                            'timeTableId'  => $timeTableID,
                            'batch_id'     => $batch_id,
                            'subject_id'   => $subjectID,
                            'topic_id'     => $topicID,
                            'remark'       => $studentRemark,
                            'batch_date'   => $batch_date,
                            'is_active'    => '1',
                            'created_at'   => $created_at,
                            'updated_at'   => ''
                        );
                        $success         = $wpdb->insert($wpdb->prefix . 'wl_min_studentremark', $timeTable_data);
                        //$previousaddedID = $wpdb->insert_id;
                        if (false === $success) {
                            throw new Exception($wpdb->last_error);
                        }
                        $wpdb->query('COMMIT;');
                        wp_send_json_success( array('message'=>'Remark added') );
                    } catch (Exception $exception) {
                        $wpdb->query('ROLLBACK;');
                        wp_send_json_error($exception->getMessage());
                    }
                }

            } else {
                wp_send_json_error($errors);
            }

        }


        // public static function fetch_timetable() {
        //     self::check_permission();
        //     if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
        //         die();
        //     }
        //     global $wpdb;
        //     $institute_id = WL_MIM_Helper::get_current_institute_id();
        //     $result1       = $wpdb->get_results("SELECT tt.id as tid, tt.subject_id AS subid, tt.topic_name AS tname, tt.is_active AS tisActive, st.`id` AS subID, st.subject_name AS subName FROM {$wpdb->prefix}wl_min_topics AS tt JOIN {$wpdb->prefix}wl_min_subjects AS st ON tt.subject_id=st.id");
        //     $result = $wpdb->get_results( "SELECT id, courseId, topic_id, timeTableName, is_active, created_at, batch_date FROM {$wpdb->prefix}wl_min_timetable WHERE institute_id=$institute_id" );
        //     if(count($result) != 0) {
        //         $i = 1;
        //         foreach ($result as $row) {
        //             $sno           = $i;
        //             $id            = $row->id;
        //             $courseID      = $row->courseId;
        //             $timetablename = $row->timeTableName;
        //             $date          = $row->batch_date;
        //             $topic_name    = $row->topic_id;
        //             $is_acitve     = ($row->is_active == 1) ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
        //             $added_on      = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
        //             //$added_by      = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';
        //             $results['data'][] = array(
        //                 $sno,
        //                 esc_html($timetablename),
        //                 '',
        //                 $is_acitve,
        //                 $added_on,
        //                 '<a class="mr-3" href="#update-timetable" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> ' . '<a href="javascript:void(0)" delete-timetable-security="' . wp_create_nonce( "delete-timetable-$id" ) . '"delete-timetable-id="' . $id . '" class="delete-timetable"> <i class="fa fa-trash text-danger"></i></a>' . ' <a class="mr-3" href="#view-timetable" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-eye"></i></a> '
        //             );
        //             $i++;
        //         }
        //     } else {
        //         $results['data'] = array();
        //     }
        //     echo json_encode($results);
        //     die();
        // }

        //fetch_timetablemodal
        public static function fetch_timetablemodal() {
            self::check_permission();
            if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
                die();
            }
            global $wpdb;
            $institute_id   = WL_MIM_Helper::get_current_institute_id();
            $institute_name = WL_MIM_Helper::get_current_institute_name();
            $id = intval( sanitize_text_field( $_POST['id'] ) );
            $query = $wpdb->get_row("SELECT timeTableName, courseId, batch_date, start_time, end_time, created_at, batch_id, subject_id, topic_id, staff_id, room_id, is_active FROM {$wpdb->prefix}wl_min_timetable WHERE id=$id AND institute_id=$institute_id");
            $courseId   = $query->courseId;
            $subject_id = $query->subject_id;
            $room_id    = $query->room_id;
            $topic_id   = $query->topic_id;
            $staff_id   = $query->staff_id;
            $batch_id   = $query->batch_id;
            $batch_date = $query->batch_id;
            $start_time = $query->start_time;
            $end_time   = $query->end_time;
            $is_active   = $query->is_active;

            $query_subjects = "";
            $query_topics   = "";
            ob_start();
            ?>
            <?php
                wp_nonce_field( 'update-timetable', 'update-timetable' );
            ?>
            <input type="hidden" name="timetableid" value="<?php echo $id; ?>" />
            <div class="row">
                </div>
            <div class="wlim-add-topic-form-fields">
                <div class="row">
                    <div class="col-6 form-group">
                        <label for="wlim-institute-name" class="col-form-label">
                            <?php esc_html_e('Institute Name', WL_MIM_DOMAIN); ?>
                        </label>
                        <input type="text" class="form-control" for="wlin-institute-name" value="<?php echo $institute_name; ?>" disabled />
                        <input type="hidden" name="instituteId" id="instituteId" class="form-control" value="<?php echo $institute_id; ?>" />
                    </div>
                </div>
                <div class="row">
                    <div class="form-group wlim-selectpicker col-6">
                        <label for="wlim-timetableName" class="col-form-label">
                                <?php esc_html_e('Time Table Name', WL_MIM_DOMAIN); ?>
                        </label>
                        <input type="text" name="wlim-timetableName" id="wlim-timetableName" class="form-control" value="<?php echo  $query->timeTableName; ?>"/>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col">
                        <label for="ttcourseID" class="col-form-label">
                            <?php _e( 'Course', WL_MIM_DOMAIN ); ?>
                        </label>
                        <?php
                            $courses = WL_MIM_Helper::getCourses();
                        ?>
                        <select name="ttcourseID" id="ttcourseID" class="form-control">
                            <option value=""><?php esc_html_e('Select a Course', WL_MIM_DOMAIN); ?></option>
                            <?php
                                    foreach($courses as $key=>$value) {
                                    ?>
                                    <option value="<?php echo $value->id; ?>" <?php selected(  $courseId, $value->id ); ?>><?php esc_html_e($value->course_name, WL_MIM_DOMAIN); echo $courseID; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col">
                        <label for="ttbatchID" class="col-form-label">
                            <?php _e( 'Batch', WL_MIM_DOMAIN ); ?>
                        </label>
                        <?php
                            $batches = WL_MIM_Helper::get_batches_by_course_id($courseId);
                        ?>
                        <select name="ttbatchID" id="ttbatchID" class="form-control ">
                            <option value="">-------- <?php esc_html_e("Select Batch", WL_MIM_DOMAIN); ?> --------</option>
                            <?php
                                foreach( $batches AS $value ) {
                                    ?>
                                        <option value="<?php $value->id; ?>" <?php selected( $batch_id, $value->id ); ?>><?php echo $value->batch_name; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col">
                        <label for="ttsubID" class="col-form-label">
                            <?php _e( 'Subjects', WL_MIM_DOMAIN ); ?>
                        </label>
                        <?php
                             $subjectsvalues = WL_MIM_Helper::get_subjects_by_courseID( $courseId );
                        ?>
                        <select name="ttsubID" id="ttsubID" class="form-control">
                            <option value="">-------- <?php esc_html_e("Select Subject", WL_MIM_DOMAIN); ?> --------</option>
                            <?php
                                foreach( $subjectsvalues as $subvalue ) {
                                    ?>
                                    <option value="<?php echo $subvalue->id; ?>" <?php selected( $subject_id, $subvalue->id ); ?>>
                                        <?php echo $subvalue->subject_name; ?>
                                    </option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col">
                        <label for="tttopicID" class="col-form-label">
                            <?php _e( 'Topic', WL_MIM_DOMAIN ); ?>
                        </label>
                        <?php
                            $topicSubject = WL_MIM_Helper::get_topics_by_subjectID( $subject_id );
                        ?>
                        <select name="tttopicID" id="tttopicID" class="form-control">
                            <option value="">-------- <?php esc_html_e("Select Topic", WL_MIM_DOMAIN); ?> --------</option>
                            <?php
                                foreach( $topicSubject as $topicvalue ) {
                                    ?>
                                        <option value="<?php echo $topicvalue->id; ?>" <?php selected( $topic_id, $topicvalue->id ); ?>><?php echo $topicvalue->topic_name; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col">
                        <label for="ttteacherID" class="col-form-label">
                            <?php _e( 'Teacher', WL_MIM_DOMAIN ); ?>
                        </label>
                        <?php
                            $staffSubject = WL_MIM_Helper::get_staff_by_subjectID( $subject_id );
                            $staffid_Arr = unserialize($staffSubject->staffId);
                        ?>
                        <select name="ttteacherID" id="ttteacherID" class="form-control">
                            <option value="">-------- <?php esc_html_e('Select a Teacher', WL_MIM_DOMAIN); ?> --------</option>
                            <?php
                                for( $i=0; $i<count($staffid_Arr); $i++ ) {
                                    $staff_data = WL_MIM_Helper::get_staffName_by_staffID( $staffid_Arr[$i] );
                                    ?>
                                        <option value="<?php echo $staffid_Arr[$i]; ?>" <?php selected( $staff_id, $staffid_Arr[$i] ); ?>>
                                        <?php
                                           echo $staff_data->first_name . " " . $staff_data->last_name;
                                        ?>
                                        </option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-4">
                        <label for="wlim_tt_class_date" class="col-form-label">
                            <?php _e( 'Date', WL_MIM_DOMAIN ); ?>
                        </label>
                        <input type="date" class="form-control" name="wlim_tt_class_date" id="wlim_tt_class_date" value="<?php echo $batch_date; ?>" />
                    </div>
                    <div class="form-group col-4">
                        <label for="wlim_tt_class_startTime" class="col-form-label">
                            <?php _e( 'Start Time', WL_MIM_DOMAIN ); ?>
                        </label>
                        <input type="time" class="form-control" name="wlim_tt_class_startTime" id="wlim_tt_class_startTime" value="<?php echo $start_time; ?>" />
                    </div>
                    <div class="form-group col-4">
                        <label for="wlim_tt_class_endTime" class="col-form-label">
                            <?php _e( 'End Time', WL_MIM_DOMAIN ); ?>
                        </label>
                        <input type="time" class="form-control" name="wlim_tt_class_endTime" id="wlim_tt_class_endTime" value="<?php echo $end_time; ?>" />
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-4">
                        <!-- create input for active and inactive -->
                        <label  for="is_active" class=" ">
                            <?php _e( 'Active', WL_MIM_DOMAIN ); ?>
                        </label>
                        <input type="checkbox" class="form-control" name="is_active" id="is_active" <?php
                        // check if $is_active is 1.
                        if( $is_active == 1 ) {
                            echo "checked";
                        }

                        ?>  />
                    </div>
                </div>

            </div>
            <?php
		    $html = ob_get_clean();
            wp_send_json_success( array( 'html' => $html ) );
        }

        /* update */
        public static function update_timeTable() {
            self::check_permission();
            if ( ! wp_verify_nonce( $_POST['update-timetable'], 'update-timetable' ) ) {
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

                    $success = $wpdb->update( "{$wpdb->prefix}wl_min_timetable", $timeTable_data, array(
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

        //delete time table
        public static function delete_timetable() {
            self::check_permission();
            $id = intval( sanitize_text_field( $_POST['id'] ) );
            if ( ! wp_verify_nonce( $_POST["delete-timetable-$id"], "delete-timetable-$id" ) ) {
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
                $success = $wpdb->delete( "{$wpdb->prefix}wl_min_timetable", array(
                    'id' => $id,
                ) );
                if ( ! $success ) {
                    throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
                }

                $wpdb->query( 'COMMIT;' );
                wp_send_json_success( array( 'message' => esc_html__( 'Time Table removed successfully.', WL_MIM_DOMAIN ) ) );
            } catch ( Exception $exception ) {
                $wpdb->query( 'ROLLBACK;' );
                wp_send_json_error( esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ) );
            }
        }

        /* View time table for staff */
        public static function view_timetable() {
            self::check_permission();
            if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
                die();
            }
            global $wpdb;
            $institute_id   = WL_MIM_Helper::get_current_institute_id();
            $institute_name = WL_MIM_Helper::get_current_institute_name();
            // $id             = intval( sanitize_text_field( $_POST['id'] ) );
            $id             = get_current_user_id();
            $currentDate    = date('Y-m-d');
            if( $id != 1 ) {
                $query = $wpdb->get_results("SELECT timeTableName, courseId, batch_date, start_time, end_time, created_at, batch_id, subject_id, topic_id, staff_id, room_id FROM {$wpdb->prefix}wl_min_timetable WHERE staff_id=$id AND batch_date >= $currentDate AND institute_id=$institute_id");
            } else {
                $query = $wpdb->get_results("SELECT timeTableName, courseId, batch_date, start_time, end_time, created_at, batch_id, subject_id, topic_id, staff_id, room_id FROM {$wpdb->prefix}wl_min_timetable WHERE batch_date >= $currentDate AND institute_id=$institute_id");
            }
            $query = $wpdb->get_results("SELECT timeTableName, courseId, batch_date, start_time, end_time, created_at, batch_id, subject_id, topic_id, staff_id, room_id FROM {$wpdb->prefix}wl_min_timetable WHERE staff_id=$id AND batch_date >= $currentDate AND institute_id=$institute_id");
            /*$timeTableName = $query->timeTableName;
            $courseId      = $query->courseId;
            $subject_id    = $query->subject_id;
            $room_id       = $query->room_id;
            $topic_id      = $query->topic_id;
            $staff_id      = $query->staff_id;
            $batch_id      = $query->batch_id;
            $batch_date    = $query->batch_id;
            $start_time    = $query->start_time;
            $end_time      = $query->end_time;*/
            if(count($query) != 0) {
                $i = 1;
                $tableData = [];
                foreach ($query as $row) {
                    $sno           = $i;
                    $id            = $row->id;
                    $courseID      = $row->courseId;
                    $timetablename = $row->timeTableName;
                    $date          = $row->batch_date;
                    $topic_name    = $row->topic_id;
                    $start_time    = $row->start_time;
                    $is_acitve     = ($row->is_active == 1) ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
                    $tableData     = "<tr><td>$sno</td><td>$timetablename</td><td>$date</td><td>$start_time</td></tr>";
                    // $results['data'][] = array(
                    //     $sno,
                    //     esc_html($timetablename),
                    //     '',
                    //     $date,
                    //     $start_time
                    // );
                    $results['data'][] = $tableData;
                    $i++;
                }
            } else {
                $results['data'] = array();
            }
            echo json_encode($results);
            die();
        }

        public static function get_room() {
            self::check_permission();
            if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'wl-ima' ) ) {
                die();
            }
            global $wpdb;
            $institute_id   = WL_MIM_Helper::get_current_institute_id();
            $institute_name = WL_MIM_Helper::get_current_institute_name();
            $data = [];
            $classDate  = isset($_POST['classDate']) ? sanitize_text_field($_POST['classDate']) : '';
            $starttimet = isset($_POST['starttime']) ? sanitize_text_field($_POST['starttime']) : '';
            $endtimet   = isset($_POST['endtime']) ? sanitize_text_field($_POST['endtime']) : '';
            $starttime  = date("H:i:s", strtotime($starttimet));
            $endtime    = date("H:i:s", strtotime($endtimet));
            $errors = array();
            if ( empty( $classDate ) ) {
                $errors['wlim_tt_class_date'] = esc_html__( 'Please select the class date.', WL_MIM_DOMAIN );
            }
            if ( empty( $starttime ) ) {
                $errors['wlim_tt_class_startTime'] = esc_html__( 'Please select the start time.', WL_MIM_DOMAIN );
            }
            if ( empty( $endtime ) ) {
                $errors['wlim_tt_class_endTime'] = esc_html__( 'Please select the end time.', WL_MIM_DOMAIN );
            }
            $errors_count = count($errors);
            if( isset( $errors_count )  && $errors_count < 1) {
                $query = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_room WHERE id NOT IN (SELECT room_id FROM {$wpdb->prefix}wl_min_timetable WHERE batch_date = '$classDate' AND start_time BETWEEN '$starttime' AND '$endtime')");
                $data['roomlist']   = $query;
            } else {
                $results['data'] = [];
             }
             echo json_encode($data);
             die();
        }

        // get student time table
       // get student time table
       public static function get_student_timetable_data() {
        // self::check_permission();
        if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
            die();
        }
        global $wpdb;
        $institute_id              = WL_MIM_Helper::get_current_institute_id();
        $general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

        $user_id = get_current_user_id();

        $student_id = $wpdb->get_row("SELECT id FROM {$wpdb->prefix}wl_min_students WHERE `user_id`=$user_id;");
        $student_id = $student_id->id;

        $batch_idD = $wpdb->get_row( "SELECT batch_id FROM {$wpdb->prefix}wl_min_students WHERE id=$student_id" );
        $batch_idStudent = $batch_idD->batch_id;

        $get_timeTableD = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_timetable WHERE batch_id=$batch_idStudent" );
        if(count($get_timeTableD) != 0) {
            $i = 1;
            foreach ($get_timeTableD as $row) {
                $sno           = $i;
                $id            = $row->id;
                $courseID      = WL_MIM_Helper::get_course($row->courseId);
                $courseName    = $courseID->course_name . " (" . $courseID->course_code . ")";
                $batch_id      = WL_MIM_Helper::get_batch($row->batch_id);
                // $batchID       = $batch_id->id;
                $batchID       = $batch_idStudent;
                $batch_name    = $batch_id->batch_name . " (" . $batch_id->batch_code . ")";
                $subjectID     = WL_MIM_Helper::getSubjectName($row->subject_id);
                $subjectName   = $subjectID->subject_name;
                $subID         = $subjectID->id;
                $staff_nameR   = WL_MIM_Helper::get_staffName_by_staffID($row->staff_id);
                $staff_name    = $staff_nameR->first_name . " " . $staff_nameR->last_name;
                $timetablename = $row->timeTableName;
                $date          = date_format( date_create( $row->batch_date ), "d-m-Y" );
                $topic_id      = WL_MIM_Helper::getTopicName($row->topic_id);
                $topicID       = $topic_id->id;
                $topic_name    = $topic_id->topic_name;
                $room_id       = WL_MIM_Helper::getRoomName($row->room_id);
                $end_time      = $row->end_time;
                $start_time    = $row->start_time;
                $savedremark   = WL_MIM_TimeTable::getStudentTimeTableremark(  $row->batch_date, $user_id, $id, $topicID, $batchID);
                $is_acitve     = ($row->is_active == 1) ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
                $added_on      = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
                //$added_by     = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';
                $remark        = '';
                $savedRemark   = $row->remark;
                $remarkControl = WL_MIM_Helper::controlRemark($id)->MATCHED;
                $nonce         = wp_create_nonce( "sRemark" );
                if($remarkControl == 1) {
                    $remark = "<form id='studentRemarkPost' method='post'><input type='text' class='form-control' name='studentRemark' id='studentRemark' value='" . $savedremark . "' /><input  type='hidden' value='". $nonce ."' name='sRemark' /><input type='hidden' name='timeTableID' id='timeTableID' value='".$id ."' /><input type='hidden' name='batch_id' id='batch_id' value='".$batchID ."' /><input type='hidden' name='subjectID' id='subjectID' value='".$subID ."' /><input type='hidden' name='topicID' id='topicID' value='".$topicID ."' /><input type='hidden' name='batch_date' id='batch_date' value='".$row->batch_date ."' /><button class='btn btn-success' id='saveRemark'>Save</button></form>";
                } elseif($remarkControl == 0) {
                    $remark = "<input type='text' name='remark' id='remark' value='" . $savedremark . "' disabled />";
                }

                $results['data'][] = array(
                    $sno,
                    esc_html($timetablename),
                    $room_id->room_name,
                    $courseName,
                    $batch_name,
                    $subjectName,
                    $topic_name,
                    $staff_name,
                    $is_acitve,
                    $date,
                    $start_time,
                    $end_time,
                    $remark
                );
                $i++;
            }
        } else {
            $results['data'] = array();
        }
        echo json_encode($results);
        die();
    }

    public static function getStudentTimeTableremark( $batch_date, $student_id, $time_table_id, $topicID, $batchID) {
        global $wpdb;
        $institute_id              = WL_MIM_Helper::get_current_institute_id();
        $general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

        $user_id = get_current_user_id();
        $row = $wpdb->get_row("SELECT remark FROM {$wpdb->prefix}wl_min_studentremark WHERE batch_date='$batch_date' AND `batch_id`='$batchID' AND `topic_id`='$topicID' AND `student_id`='$student_id' AND `timeTableId`='$time_table_id'");

		if (!$row) {
			return null;
		}

		return $row->remark;
    }

        /* Check permission to manage course */
        private static function check_permission() {
            $institute_id = WL_MIM_Helper::get_current_institute_id();
            if ( ! current_user_can( 'wl_min_view_timetable' ) || ! $institute_id ) {
                die();
            }
            global $wpdb;
            $institute_id = WL_MIM_Helper::get_current_institute_id();
            if ( current_user_can( WL_MIM_Helper::get_multi_institute_capability() ) ) {
                $can_delete_subject = true;
            // } else {
            //     $institute = $wpdb->get_row( "SELECT can_delete_timetable FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id" );
            //     $can_delete_subject = false;
            //     if($institute) {
            //         $can_delete_timetable = (bool) $institute->can_timetable;
            //     }
            }
        }
    }