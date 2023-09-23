<?php 
defined('ABSPATH') || die();
require_once(WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php');
require_once(WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_SettingHelper.php');

$institute_id      = WL_MIM_Helper::get_current_institute_id();

$general_institute = WL_MIM_SettingHelper::get_general_institute_settings($institute_id);

$courses           = WL_MIM_Helper::get_courses($institute_id);

if (empty($general_institute['institute_name'])) {
    $institute_name = WL_MIM_Helper::get_current_institute_name();
} else {
    $institute_name = $general_institute['institute_name'];
}
?>
<div class="container-fluid wl_im_container">
    <!-- row 1 -->
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <h1 class="text-center wlim-institute-dashboard-title">
                <span class="border-bottom"><i class="fa fa-tachometer"></i> <?php echo esc_html($institute_name); ?></span>
            </h1>
            <h2 class="text-center font-weight-normal">
                <span class="border-bottom"><i class="fa fa-users"></i> <?php esc_html_e('Topics', WL_MIM_DOMAIN); ?></span>
            </h2>
            <?php
            $institute_active = WL_MIM_Helper::get_current_institute_status();
            if (!$institute_active) {
                require_once(WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php');
                die();
            } ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
                <?php esc_html_e('Here, you can either add a new topics or edit existing topics.', WL_MIM_DOMAIN); ?>
            </div>
            <!-- end main header content -->
        </div>
    </div>
    <!-- end - row 1 -->

    <!-- row 2 -->
    <div class="row">
        <div class="card col">
            <div class="card-header bg-primary text-white">
                <!-- card header content -->
                <div class="row">
                    <div class="col-md-6 col-xs-12">
                        <div class="h4"><?php esc_html_e('Manage Topics', WL_MIM_DOMAIN); ?></div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <button type="button" class="btn btn-outline-light float-right add-student ml-2" data-toggle="modal" data-target="#add-topic" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-plus"></i> <?php esc_html_e('Add New Topic', WL_MIM_DOMAIN); ?>
                        </button>
                        <!-- <button type="button" class="btn btn-outline-light float-right import-student" data-toggle="modal" data-target="#import-student" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-cloud-upload"></i> <?php esc_html_e('Bulk Import Topic', WL_MIM_DOMAIN); ?>
                        </button> -->
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col">
                        <div class="float-left">
                            <?php
                            if (isset($_GET['year']) && !empty($_GET['year'])) {
                                $year        = esc_attr($_GET['year']);
                                $date_format = esc_html__("Year", WL_MIM_DOMAIN) . ': ' . $year;
                                if (isset($_GET['month']) && !empty($_GET['month'])) {
                                    $date        = DateTime::createFromFormat('!m', esc_attr($_GET['month']));
                                    $month       = $date->format('F');
                                    $date_format = "$month, $year";
                                }
                            ?>
                                <span class="text-secondary"><?php esc_html_e('Showing Records For', WL_MIM_DOMAIN); ?>&nbsp;
                                    <strong><?php echo "$date_format"; ?></strong>
                                </span>
                                <a class="ml-1 text-primary" href="<?php echo admin_url('admin.php?page=multi-institute-management-topics'); ?>"><?php esc_html_e('Show All', WL_MIM_DOMAIN); ?></a>
                            <?php
                            } else { ?>
                                <span class="text-secondary"><?php esc_html_e('Showing All Records', WL_MIM_DOMAIN); ?></span>
                                <?php
                                //if ($filters_applied) { ?>
                                    <!-- <a class="ml-1 text-primary" href="<?php echo admin_url('admin.php?page=multi-institute-management-topics'); ?>"><?php esc_html_e('Clear Filters', WL_MIM_DOMAIN); ?></a> -->
                            <?php
                                // }
                            } ?>
                            <div class="row">
                                <div class="col">
                                    <ul>
                                        <?php
                                        if (isset($status_output)) { ?>
                                            <li class="font-weight-bold mt-1"><?php echo esc_html($status_output) . ' ' . esc_html__('Topic Id', WL_MIM_DOMAIN); ?></li>
                                        <?php
                                        }
                                        if (isset($course_output)) { ?>
                                            <li>
                                                <span class="font-weight-bold"><?php esc_html_e('Topic', WL_MIM_DOMAIN); ?>:&nbsp;</span>
                                                <span><?php echo esc_html($course_output); ?></span>
                                                <?php
                                                if (isset($batch_output)) { ?>
                                                    <span class="ml-3">
                                                        <span class="font-weight-bold"><?php esc_html_e('Batch', WL_MIM_DOMAIN); ?>:&nbsp;</span>
                                                        <span><?php echo esc_html($batch_output); ?></span>
                                                    </span>
                                                <?php
                                                } ?>
                                            </li>
                                        <?php
                                        } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="wlim-filter float-right mb-3">
                            <form method="get" class="form-inline">
                                <?php
                                foreach ($_GET as $name => $value) {
                                    $name  = esc_attr($name);
                                    $value = esc_attr($value);
                                    echo '<input type="hidden" name="' . $name . '" value="' . $value . '">';
                                } ?>
                                <input type="hidden" name="page" value="multi-institute-management-students">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold" for="wlim-student-filter_by_year">
                                        <?php esc_html_e('Year', WL_MIM_DOMAIN); ?>:&nbsp;
                                    </label>
                                    <input type="text" name="year" class="form-control wlim-year" id="wlim-student-filter_by_year" placeholder="<?php esc_html_e('Year', WL_MIM_DOMAIN); ?>">
                                </div>&nbsp;
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold" for="wlim-student-filter_by_month">
                                        <?php esc_html_e('Month', WL_MIM_DOMAIN); ?>:&nbsp;
                                    </label>
                                    <input type="text" name="month" class="form-control wlim-month" id="wlim-student-filter_by_month" placeholder="<?php esc_html_e('Month', WL_MIM_DOMAIN); ?>">
                                </div>&nbsp;
                                <button type="submit" class="btn btn-success"><?php esc_html_e('Apply Filter', WL_MIM_DOMAIN); ?></button>
                            </form>
                        </div>



                        <table class="table table-hover table-striped table-bordered" id="topicList">
                            <?php
                            $entity = 'students';
                            // require(WL_MIM_PLUGIN_DIR_PATH. 'inc/controllers/bulk_action.php' );
                            require(WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/controllers/bulk_action.php'); ?>
                            <thead>
                                <tr>
                                    <th><input type="checkbox" name="select_all" id="wl-mim-select-all" value="1"></th>
                                    <th scope="col"><?php esc_html_e('Topic Name', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Subject', WL_MIM_DOMAIN); ?></th>                                    
                                    <!-- <th scope="col"><?php esc_html_e('Batch', WL_MIM_DOMAIN); ?></th> -->                                    
                                    <th scope="col"><?php esc_html_e('Is Active', WL_MIM_DOMAIN); ?></th>
                                    <!-- <th scope="col"><?php esc_html_e('Added By', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Follow Up History', WL_MIM_DOMAIN); ?></th> -->
                                    <th scope="col"><?php esc_html_e('Registration Date', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Edit', WL_MIM_DOMAIN); ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <!-- end - card body content -->
            </div>
        </div>
    </div>
    <!-- end - row 2 -->
    <!-- add new Topic modal -->
    <div class="modal fade" id="add-topic" tabindex="-1" role="dialog" aria-labelledby="add-topic-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-topic-label"><?php esc_html_e('Add New topic', WL_MIM_DOMAIN); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post" id="wim_add_topic">
                    <div class="modal-body pr-4 pl-4">                        
                        <?php 
                            wp_nonce_field( 'add-topic', 'add-topic' );
                        ?>                        
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
                                                <option value="<?php echo $id; ?>"><?php echo $course . "(" . $course_code .")"; ?></option>
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
                                    </select>                               
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col">                                
                                    <label for="wlim-topic-name" class="col-form-label">
                                        <?php _e( 'Topic', WL_MIM_DOMAIN ); ?>
                                    </label>                               
                                    <input type="text" name="wlim-topic-name" id="wlim-topic-name" class="form-control" />
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e('Cancel', WL_MIM_DOMAIN); ?></button>
                        <button type="submit" class="btn btn-primary add-topic-submit"><?php esc_html_e('Add New Topic', WL_MIM_DOMAIN); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div><!-- end - add new topic modal -->
     <!-- update topic modal -->
     <div class="modal fade" id="update-topic" tabindex="-1" role="dialog" aria-labelledby="update-topic-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="update-topic-label"><?php esc_html_e( 'Update topic', WL_MIM_DOMAIN ); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post" id="wim_update_topic">             
                <?php                     
                    wp_nonce_field( 'update-topics', 'update-topics' );
                ?>
                <div class="modal-body pr-4 pl-4" id="fetch_topic"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary update-main-topic-submit"><?php esc_html_e( 'Update Topic', WL_MIM_DOMAIN ); ?></button>
                </div>
                </form>
            </div>
        </div>
    </div><!-- end - update course modal -->
</div>