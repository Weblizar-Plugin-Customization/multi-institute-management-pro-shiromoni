(function ($) {
    "use strict";
    jQuery(document).ready(function () {
        var urlParams = new URLSearchParams(window.location.search);
        var year = urlParams.get('year');
        var month = urlParams.get('month');
        var status = urlParams.get('status');
        var batch_id = urlParams.get('batch_id');
        var course_id = urlParams.get('course_id');

        // for student_id
        var student_id = urlParams.get('student_id');

        var filters = '&filter_by_year=' + year + '&filter_by_month=' + month + '&status=' + status + '&course_id=' + course_id + '&batch_id=' + batch_id + '&student_id=' + student_id;

        /* Get data to display on table with export options */
        function initializeDatatable(table, action) {

            var role = sessionStorage.getItem("role");
            var user_can = sessionStorage.getItem("user_can_export");


            if (user_can == 1 && role == 'staff') {
                var epppp = 'lBfrtip';
            } else {
                epppp = 'lfrtip';
            }
            if (role == 'admin') {
                var epppp = 'lBfrtip';
            }


            var table = jQuery(table).DataTable({
                aaSorting: [],
                responsive: true,
                ajax: {
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=' + action + filters,
                    dataSrc: 'data'
                },
                language: {
                    "loadingRecords": "Loading..."
                },
                lengthChange: false,
                dom: epppp,
                columnDefs: [
                    { orderable: false, targets: 0 }
                  ],
                select: true,
                buttons: [
                    'pageLength',
                    {
                        extend: 'excel',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                selected: null
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                selected: null
                            }
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                selected: null
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: 'Print all',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                selected: null
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: 'Print selected',
                        exportOptions: {
                            columns: ':visible',
                        }
                    },
                    'colvis'
                ]
            });
        }

        /* Actions for student */
        initializeDatatable('#student-table', 'wl-mim-get-student-data');

        initializeDatatable('#student-invoice-table', 'wl-mim-get-student-invoice-data');

        initializeDatatable('#reminder-table', 'wl-mim-get-reminder-data');
    });
})(jQuery);