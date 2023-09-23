<?php 
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

if( !class_exists('WL_MIM_Room') ){
    class WL_MIM_Room {
        public static function add_room() {
            if ( ! wp_verify_nonce( $_POST['add-room'], 'add-room' ) ) {
                die();
            }
            global $wpdb;
            $instituteID  = isset( $_POST['instituteId'] ) ? intval( sanitize_text_field( $_POST['instituteId'] ) ) : null;
            $wlim_room    = isset( $_POST['wlim-room'] ) ? sanitize_text_field( $_POST['wlim-room'] ) : null;
            $wlim_roomno  = isset( $_POST['wlim-roomno'] ) ? sanitize_text_field( $_POST['wlim-roomno'] ) : null;

            /* Validations */
            $errors = array();
            if ( empty( $instituteID ) ) {
                $errors['wlim-course-name'] = esc_html__( 'Please select a institute.', WL_MIM_DOMAIN );
            }
            if ( empty( $wlim_room ) ) {
                $errors['wlim-subject'] = esc_html__( 'Please enter the room name.', WL_MIM_DOMAIN );
            }
            if ( empty( $wlim_roomno ) ) {
                $errors['wlim-topic-name'] = esc_html__( 'Please provide room number.', WL_MIM_DOMAIN );
            }

            $errors_count = count($errors);
            if( isset( $errors_count )  && $errors_count < 1) { 
                try {
                    $wpdb->query('BEGIN;');
                    //data
                    $created_at   = current_time('Y-m-d H:i:s');
                    $room_data = array(
                        'institute_id' => $instituteID,
                        'room_name'    => $wlim_room,
                        'room_desc'    => $wlim_roomno,
                        'is_active'    => '1',
                        'created_at'   => $created_at
                    );
                    $success = $wpdb->insert($wpdb->prefix . 'wl_min_room', $room_data);
                    $previousaddedID = $wpdb->insert_id;                   
                    if (false === $success) {
                        throw new Exception($wpdb->last_error);
                    }
                    $wpdb->query('COMMIT;');
                    wp_send_json_success( array('message'=>'Room added') );
                } catch (Exception $exception) {
                    $wpdb->query('ROLLBACK;');
                    wp_send_json_error($exception->getMessage());
                } 
            }else {
                wp_send_json_error($errors);
            }
        } //add room function end

        /* fetch room into data table */
        public static function RoomList() {
            if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
                die();
            }
            global $wpdb;
            $institute_id = WL_MIM_Helper::get_current_institute_id();
            // $result = $wpdb->get_results("SELECT tt.id as tid, tt.subject_id AS subid, tt.topic_name AS tname, st.`id` AS subID, st.subject_name AS subName FROM {$wpdb->prefix}wl_min_topics AS tt JOIN {$wpdb->prefix}wl_min_subjects AS st ON tt.subject_id=st.id");
            $result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_room WHERE is_active=1");
            if(count($result) != 0) {
                $i = 1;
                foreach ($result as $row) { 
                    $sno         = $i;
                    $id         = $row->id;
                    $roomName    = $row->room_name;
                    $room_desc   = $row->room_desc;
                    $status      = $row->is_active;
                    $regDate     = $row->created_at;                    
                    $results['data'][] = array(
                        $sno,
                        esc_html($roomName),
                        esc_html($room_desc),
                        $status,
                        $regDate,                        
                        '<a class="mr-3" href="#update-room" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> ' . '<a href="javascript:void(0)" delete-room-security="' . wp_create_nonce( "delete-room-$id" ) . '"delete-room-id="' . $id . '" class="delete-room"> <i class="fa fa-trash text-danger"></i></a>'
                    );
                    $i++;
                }
            } else {
                $results['data'] = array();   
            }
            echo json_encode($results);
            die();
        }

        // show on modal
        public static function fetchRoomModal() {
            self::check_permission();
            if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
                die();
            }            
            global $wpdb;
            $institute_id   = WL_MIM_Helper::get_current_institute_id();
            $institute_name = WL_MIM_Helper::get_current_institute_name();
            $id             = intval( sanitize_text_field( $_POST['id'] ) );
            $row            = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_room WHERE id = $id AND institute_id = $institute_id" );
            // var_dump($row);
            $room           = $row->room_name;
            $roomno         = $row->room_desc;
            if( !$row ) {
                die();
            }
            ob_start(); ?>
            <input type="hidden" name="roomid" value="<?php echo $id; ?>">
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
                                <?php esc_html_e('Room Name', WL_MIM_DOMAIN); ?>
                        </label>
                        <input type="text" name="wlim-room" id="wlim-room" class="form-control" value="<?php echo $room; ?>" />                                    
                    </div>
                </div>
                
                <div class="row">
                    <div class="form-group col">                                
                        <label for="wlim-topic-name" class="col-form-label">
                            <?php _e( 'Room No', WL_MIM_DOMAIN ); ?>
                        </label>                               
                        <input type="text" name="wlim-roomno" id="wlim-roomno" class="form-control" value="<?php echo $roomno; ?>"/>
                    </div>
                </div>

            </div>
            <?php
            $html = ob_get_clean();
            wp_send_json_success( array( 'html' => $html ) );
        }

        //update the room
        public static function update_room() {
            self::check_permission();
            if ( ! wp_verify_nonce( $_POST['update-room'], 'update-room' ) ) {
                die();
            }
            global $wpdb;
            $institute_id = WL_MIM_Helper::get_current_institute_id();        
            $instituteID  = isset( $_POST['instituteId'] ) ? intval( sanitize_text_field( $_POST['instituteId'] ) ) : null;
            $id           = isset( $_POST['roomid'] ) ? intval( sanitize_text_field( $_POST['roomid'] ) ) : null;
            $wlim_room    = isset( $_POST['wlim-room'] ) ? sanitize_text_field( $_POST['wlim-room'] ) : null;
            $wlim_roomno  = isset( $_POST['wlim-roomno'] ) ? sanitize_text_field( $_POST['wlim-roomno'] ) : null;

            /* Validations */
            $errors = array();
            if ( empty( $instituteID ) ) {
                $errors['wlim-course-name'] = esc_html__( 'Please select a institute.', WL_MIM_DOMAIN );
            }
            if ( empty( $wlim_room ) ) {
                $errors['wlim-subject'] = esc_html__( 'Please enter the room name.', WL_MIM_DOMAIN );
            }
            if ( empty( $wlim_roomno ) ) {
                $errors['wlim-topic-name'] = esc_html__( 'Please provide room number.', WL_MIM_DOMAIN );
            }

            $errors_count = count($errors);
            if( isset( $errors_count )  && $errors_count < 1) { 
                try {
                    $wpdb->query('BEGIN;');
                    //data
                    $created_at   = current_time('Y-m-d H:i:s');
                    $room_data = array(                       
                        'room_name'    => $wlim_room,
                        'room_desc'    => $wlim_roomno,
                    );
                    //$success = $wpdb->update($wpdb->prefix . 'wl_min_room', $room_data);
                    $success = $wpdb->update( "{$wpdb->prefix}wl_min_room", $room_data, array(
                        'id' => $id,
                    ) );
                    // $previousaddedID = $wpdb->insert_id;                   
                    if (false === $success) {
                        throw new Exception($wpdb->last_error);
                    }
                    $wpdb->query('COMMIT;');
                    wp_send_json_success( array('message'=>'Room updated') );
                } catch (Exception $exception) {
                    $wpdb->query('ROLLBACK;');
                    wp_send_json_error($exception->getMessage());
                } 
            }else {
                wp_send_json_error($errors);
            }
        }

        //delete room/studio
        public static function delete_room() {
            self::check_permission();
            $id = intval( sanitize_text_field( $_POST['id'] ) );
            if ( ! wp_verify_nonce( $_POST["delete-room-$id"], "delete-room-$id" ) ) {
                die();
            }
            global $wpdb;
            $institute_id = WL_MIM_Helper::get_current_institute_id();

            try {
                $wpdb->query( 'BEGIN;' );
    
                /*$success = $wpdb->update( "{$wpdb->prefix}wl_min_room",
                    array(
                        'is_deleted' => 1,
                        'deleted_at' => date( 'Y-m-d H:i:s' )
                    ), array( 'is_deleted' => 0, 'id' => $id, 'institute_id' => $institute_id )
                );*/
                $success = $wpdb->delete( "{$wpdb->prefix}wl_min_room", array(
                    'id'           => $id,
                    'institute_id' => $institute_id
                ) );
                if ( ! $success ) {
                    throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
                }
    
                $wpdb->query( 'COMMIT;' );
                wp_send_json_success( array( 'message' => esc_html__( 'Room removed successfully.', WL_MIM_DOMAIN ) ) );
            } catch ( Exception $exception ) {
                $wpdb->query( 'ROLLBACK;' );
                wp_send_json_error( esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ) );
            }
        }

        /* Check permission to manage course */
        private static function check_permission() {
            $institute_id = WL_MIM_Helper::get_current_institute_id();
            if ( ! current_user_can( 'wl_min_manage_studios' ) || ! $institute_id ) {
                die();
            }
        }
    }
}