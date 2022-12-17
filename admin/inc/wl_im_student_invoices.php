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
						<div class="h4"><?php esc_html_e( 'Installments', WL_MIM_DOMAIN ); ?></div>
					</div>
				</div>
				<!-- end - card header content -->
			</div>
			<div class="card-body">
				<!-- card body content -->
				<div class="row">
					<div class="col">
						<table class="table table-hover table-striped table-bordered" id="student-invoice-table">
							<thead>
								<tr>
						        	<!-- <th scope="col"><?php esc_html_e( 'Installment No.', WL_MIM_DOMAIN ); ?></th> -->
						        	<th scope="col"><?php esc_html_e( 'Installment Title', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Amount Payable', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Due Date', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Due Date Amount', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Status', WL_MIM_DOMAIN ); ?></th>
						        	<!-- <th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th> -->
						        	<th scope="col"><?php esc_html_e( 'Added On', WL_MIM_DOMAIN ); ?></th>
						        	<!-- <th scope="col"><?php esc_html_e( 'Edit', WL_MIM_DOMAIN ); ?></th> -->
								</tr>
							</thead>
						</table>
					</div>
				</div>
				<!-- end - card body content -->
			</div>
		</div>
	</div>