<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
?>

<div class="row">
		<div class="card col">
			<div class="card-header bg-primary text-white">
				<!-- card header content -->
				<div class="row">
                    <div class="col-md-9 col-xs-12">
						<div class="h4"><?php esc_html_e( 'Time Table', WL_MIM_DOMAIN ); ?></div>
					</div>
				</div>
				<!-- end - card header content -->
			</div>
			<div class="card-body">
				<!-- card body content -->
				<div class="row">
					<div class="col">
                        <table class="table table-hover table-striped table-bordered" id="student_timetableList">
                            <?php
                            $entity = 'timetable';
                            // require(WL_MIM_PLUGIN_DIR_PATH. 'inc/controllers/bulk_action.php' );
                           // require(WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/controllers/bulk_action.php'); ?>
                            <thead>
                                <tr>
                                    <th><input type="checkbox" name="select_all" id="wl-mim-select-all" value="1"></th>
                                    <th scope="col"><?php esc_html_e('Time Table Name', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Studio Number', WL_MIM_DOMAIN); ?></th>                                    
                                    <th scope="col"><?php esc_html_e('Course', WL_MIM_DOMAIN); ?></th>                                    
                                    <th scope="col"><?php esc_html_e('Batch', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Subject', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Topic', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e('Is Active', WL_MIM_DOMAIN); ?></th>                                    
                                    <th scope="col"><?php esc_html_e('Date', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Start Time', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('End Time', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Remark', WL_MIM_DOMAIN); ?></th>                                    
                                </tr>
                            </thead>
                        </table>
					</div>
				</div>
				<!-- end - card body content -->
			</div>
		</div>
	</div>