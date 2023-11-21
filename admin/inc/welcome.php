<?php
defined( 'ABSPATH' ) || die();
// session_start();
require_once WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php';
require_once WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_StudentHelper.php';
require_once WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_PaymentHelper.php';
require_once WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php';

?>

<div class="container-fluid wl_im_container">
	<!-- row 1 -->
	<div class="row">
		<div class="col">
			<!-- main header content -->
			<h1 class="display-4 text-center">
				<span class=""><?php esc_html_e( 'Shiromani Institute Private Limited', WL_MIM_DOMAIN ); ?></span>
			</h1>
			<!-- <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can find your details.', WL_MIM_DOMAIN ); ?>
			</div> -->
			<!-- end main header content -->
		</div>
	</div>
	<!-- end - row 1 -->

	<!-- row 2 -->
	<div class="row">
		<div class="card col">
			<div class="card-header">
				<!-- card header content -->
				<div class="row">
					<div class="col-md-12 wlim-noticboard-background pt-2 pb-2">
						<div class="wlim-student-heading text-center display-4">
							<span class="text-white"><?php esc_html_e( 'Welcome', WL_MIM_DOMAIN ); ?>
								<!-- <span class="wlim-student-name-heading"><?php echo esc_html( $student->first_name ); ?></span> !</span> -->
						</div>
					</div>
				</div>
				<!-- end - card header content -->
			</div>

		<!-- create to ANCHOR button  -->

		<div class="row">
			<div class="col-md-6">
				<div class="wlim-student-heading text-center display-4">
					<a href="<?php echo esc_url( home_url( '/wp-admin/admin.php?page=multi-institute-management-student-dashboard' ) ); ?>" class="btn btn-success btn-lg btn-block" role="button" aria-pressed="true"><?php esc_html_e( 'Student Dashboard', WL_MIM_DOMAIN ); ?></a>
				</div>
			</div>
			<div class="col-md-6">
				<div class="wlim-student-heading text-center display-4">
					<a href="<?php echo esc_url( home_url( '/wp-admin/admin.php?page=multi-institute-management' ) ); ?>" class="btn btn-warning btn-lg btn-block" role="button" aria-pressed="true"><?php esc_html_e( 'Staff Dashboard', WL_MIM_DOMAIN ); ?></a>
				</div>
			</div>

		</div>
	</div>
	<!-- end - row 2 -->

</div>

