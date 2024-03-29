(function ($) {
    "use strict";


    jQuery(document).ready(function () {
        /* Loading */
        jQuery(document).ajaxStart(function () {
            jQuery('button[type="submit"]').prop('disabled', true);
        }).ajaxStop(function () {
            jQuery('button[type="submit"]').prop('disabled', false);
        });
        jQuery('.selectpicker').selectpicker();

        /* Serialize object */
        (function ($, undefined) {
            '$:nomunge';
            $.fn.serializeObject = function () {
                var obj = {};
                $.each(this.serializeArray(), function (i, o) {
                    var n = o.name,
                        v = o.value;
                    obj[n] = obj[n] === undefined ? v
                        : $.isArray(obj[n]) ? obj[n].concat(v)
                            : [obj[n], v];
                });
                return obj;
            };
        })(jQuery);


        /* Get data to display on table */
        function initializeDatatable(table, action, data = {}) {
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
            // console.log(epppp);


            jQuery(table).DataTable({
                aaSorting: [],
                responsive: true,
                ajax: {
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=' + action,
                    dataSrc: 'data',
                    data: data
                },
                language: {
                    "loadingRecords": "Loading..."
                },
                lengthChange: false,
                dom: epppp,
                columnDefs: [
                    { orderable: false, targets: 0 }
                ],
                // select: true,
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




        /* Add or update record */
        function save(selector, action, form = null, modal = null, reloadTables = []) {
            jQuery(document).on('click', selector, function (event) {
                var data = {
                    action: action
                };
                var formData = {};
                if (form) {
                    formData = jQuery(form).serializeObject();
                }
                console.log(formData);
                jQuery(selector).prop('disabled', true);
                jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: jQuery.extend(formData, data),
                    success: function (response) {
                        jQuery(selector).prop('disabled', false);
                        if (response.success) {
                            toastr.success(response.data.message);
                            if (response.data.hasOwnProperty('reload') && response.data.reload) {
                                location.reload();
                            } else {
                                jQuery(form)[0].reset();
                                if (modal) {
                                    jQuery(modal).modal('hide');
                                }
                                reloadTables.forEach(function (table) {
                                    jQuery(table).DataTable().ajax.reload(null, false);
                                });
                            }
                        } else {
                            jQuery('span.text-danger').remove();
                            if (response.data && jQuery.isPlainObject(response.data)) {
                                jQuery(form + ' :input').each(function () {
                                    var input = this;
                                    if (response.data[input.name]) {
                                        var errorSpan = '<span class="text-danger">' + response.data[input.name] + '</span>';
                                        jQuery(errorSpan).insertAfter(this);
                                    }
                                });
                            } else {
                                var errorSpan = '<span class="text-danger ml-3 mt-3">' + response.data + '<hr></span>';
                                jQuery(errorSpan).insertBefore(form);
                                toastr.error(response.data);
                            }
                        }
                    },
                    error: function (response) {
                        jQuery(selector).prop('disabled', false);
                        toastr.error(response.statusText);
                    },
                    dataType: 'json'
                });
            });
        }

        /* Fetch record to update */
        function fetch(modal, action, target) {
            jQuery(document).on('show.bs.modal', modal, function (e) {
                var id = jQuery(e.relatedTarget).data('id');
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=' + action,
                    data: 'id=' + id,
                    success: function (data) {
                        jQuery(target).html(data);
                    }
                });
            });
        }

        /* Delete record */
        function remove(selector, id_attribute, nonce_attribute, nonce_name, action, reloadTables = []) {
            jQuery(document).on("click", selector, function (event) {
                var id = jQuery(this).attr(id_attribute);
                var nonce = jQuery(this).attr(nonce_attribute);
                jQuery.confirm({
                    title: 'Confirm!',
                    type: 'red',
                    content: 'Please confirm!',
                    buttons: {
                        confirm: function () {
                            jQuery.ajax({
                                data: "id=" + id + "&" + nonce_name + "-" + id + "=" + nonce + "&action=" + action,
                                url: ajaxurl,
                                type: "POST",
                                success: function (response) {
                                    if (response.success) {
                                        toastr.success(response.data.message);
                                        if (response.data.hasOwnProperty('reload') && response.data.reload) {
                                            location.reload();
                                        } else {
                                            reloadTables.forEach(function (table) {
                                                jQuery(table).DataTable().ajax.reload(null, false);
                                            });
                                        }
                                    } else {
                                        toastr.error(response.data);
                                    }
                                },
                                error: function (response) {
                                    toastr.error(response.statusText);
                                }
                            });
                        },
                        cancel: function () {
                        }
                    }
                });
            });
        }

        /* Fetch records */
        function fetchRecords(action, target, data = null) {
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=' + action,
                data: data,
                success: function (data) {
                    jQuery(target).html(data);
                }
            });
        }

        /* Add or update record with files */
        function saveWithFiles(selector, form = null, modal = null, reloadTables = [], reset = true) {
            jQuery(form).ajaxForm({
                success: function (response) {
                    jQuery(selector).prop('disabled', false);
                    if (response.success) {
                        jQuery('span.text-danger').remove();
                        jQuery(".is-invalid").removeClass("is-invalid");
                        toastr.success(response.data.message);
                        if (response.data.hasOwnProperty('reload') && response.data.reload) {
                            if (response.data.hasOwnProperty('url') && response.data.url) {
                                window.location.href = response.data.url;
                            } else {
                                location.reload();
                            }
                        } else {
                            if (reset) {
                                jQuery(form)[0].reset();
                            }
                            if (modal) {
                                jQuery(modal).modal('hide');
                            }
                            reloadTables.forEach(function (table) {
                                jQuery(table).DataTable().ajax.reload(null, false);
                            });
                        }
                    } else {
                        jQuery('span.text-danger').remove();
                        if (response.data && jQuery.isPlainObject(response.data)) {
                            jQuery(form + ' :input').each(function () {
                                var input = this;
                                jQuery(input).removeClass('is-invalid');
                                if (response.data[input.name]) {
                                    var errorSpan = '<span class="text-danger">' + response.data[input.name] + '</span>';
                                    jQuery(input).addClass('is-invalid');
                                    jQuery(errorSpan).insertAfter(input);
                                }
                            });
                        } else {
                            var errorSpan = '<span class="text-danger ml-3 mt-3">' + response.data + '<hr></span>';
                            jQuery(errorSpan).insertBefore(form);
                            toastr.error(response.data);
                        }
                    }
                },
                error: function (response) {
                    jQuery(selector).prop('disabled', false);
                    toastr.error(response.statusText);
                },
                uploadProgress(event, progress, total, percentComplete) {
                    jQuery('#wlim-progress').text(percentComplete);
                }
            });
        }

          // when id wlim-institute-select-course is changed then get the batches of that course
          jQuery(document).on('change', '#wlim-institute-select-course', function () {
            var course_id = jQuery(this).val();
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wlim_get_batches',
                    course_id: course_id
                },
                success: function (response) {
                    jQuery('#wlim-institute-select-batch').html('');
                    response.data.forEach(function (batch) {
                        jQuery('#wlim-institute-select-batch').append('<option value="' + batch.id + '">' + batch.batch_name + '</option>');
                    });
                    jQuery('#wlim-institute-select-batch').selectpicker('refresh');
                }
            });
        });

        // when fees-report-btn is clicked get data from form id fees-report-form update the text of span id total-student.
        jQuery(document).ready(function() {
            $('#fees-report-btn').click(function() {
                var data = $('#fees-report-form').serialize();
                $.ajax({
                    url: ajaxurl + '?action=wl-mim-get-student-fees-report-data-dash',
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        $('#total-students-count').text(response.total_students);
                        $('#total-students-amount').text(response.total_payable_amount);
                        $('#paid-students-count').text(response.paid_students);
                        $('#paid-students-amount').text(response.paid_amount);
                        $('#unpaid-students-count').text(response.unpaid_students);
                        $('#unpaid-students-amount').text(response.total_unpaid_amount);
                    }
                });
            });
        });

        /* Switch institute on click */
        jQuery(document).on('click', '.wlmim-institute-switch', function () {
            var institute_id = jQuery(this).data('id');
            var security = jQuery(this).data('security');
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action': 'wl-mim-set-institute',
                    'institute': institute_id,
                    'set-institute': security
                },
                success: function (response) {
                    if (response.success) {
                        toastr.success(response.data.message);
                        if (response.data.hasOwnProperty('reload') && response.data.reload) {
                            if (response.data.hasOwnProperty('url') && response.data.url) {
                                window.location.href = response.data.url;
                            } else {
                                location.reload();
                            }
                        }
                    }
                },
                error: function (response) {
                    toastr.error(response.statusText);
                },
                dataType: 'json'
            });
        });

        /* Actions for multi institute administrator */
        initializeDatatable('#user-administrator-table', 'wl-mim-get-user-administrator-data');
        saveWithFiles('.add-user-administrator-submit', '#wlim-add-user-administrator-form', '#add-user-administrator', ['#user-administrator-table']);
        jQuery(document).on('show.bs.modal', '#update-user-administrator', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-user-administrator',
                data: 'id=' + id,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#fetch_user_administrator').html(response.data.html);
                    jQuery('span.text-danger').remove();
                    jQuery('.is-valid').removeClass('is-valid');
                    jQuery('.is-invalid').removeClass('is-invalid');

                    /* Select single option */
                    jQuery('#wlim-administrator-institute_update').selectpicker({
                        liveSearch: true
                    });
                    if (data.manage_multi_institute) {
                        jQuery('.wlim-manage-single-institute').hide();
                    }
                    jQuery.each(data.permissions, function (key, capability) {
                        jQuery(capability).prop('checked', true);
                    });
                }
            });
        });
        saveWithFiles('.update-user-administrator-submit', '#wlim-update-user-administrator-form', '#update-user-administrator', ['#user-administrator-table', '#staff-table']);

        /* Actions for institute administrator */
        initializeDatatable('#administrator-table', 'wl-mim-get-administrator-data');
        initializeDatatable('#staff-table', 'wl-mim-get-staff-data');
        saveWithFiles('.add-administrator-submit', '#wlim-add-administrator-form', '#add-administrator', ['#administrator-table', '#staff-table']);
        jQuery(document).on('show.bs.modal', '#update-administrator', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-administrator',
                data: 'id=' + id,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#fetch_administrator').html(response.data.html);
                    // Show date time picker inside modal
                    var minDate = new Date();
                    minDate.setFullYear(minDate.getFullYear() - 100);
                    jQuery(data.wlim_date_selector).datetimepicker({
                        format: 'DD-MM-YYYY',
                        showClear: true,
                        showClose: true,
                        minDate: minDate
                    });
                    jQuery('span.text-danger').remove();
                    jQuery('.is-valid').removeClass('is-valid');
                    jQuery('.is-invalid').removeClass('is-invalid');
                    jQuery('.wlim-selectpicker').load(location.href + " " + '.wlim-selectpicker', function () {
                        /* Select single option */
                        try {
                            jQuery('.selectpicker').selectpicker({
                                liveSearch: true
                            });
                        } catch (error) {
                        }
                    });
                    jQuery.each(data.permissions, function (key, capability) {
                        jQuery(capability).prop('checked', true);
                    });
                    if (!data.staff_exist) {
                        jQuery('.wlim-staff-record-fields').hide();
                    }
                    if (data.date_of_birth_exist) {
                        /* Select date of birth */
                        jQuery(data.wlim_date_selector).data("DateTimePicker").date(moment(data.date_of_birth));
                    }
                }
            });
        });
        saveWithFiles('.update-administrator-submit', '#wlim-update-administrator-form', '#update-administrator', ['#administrator-table', '#staff-table']);


        jQuery(document).on('click', '#fees-report-btn', function (event) {
            event.preventDefault();
            var course_id = jQuery('#wlim-institute-select-course').val();
            var batch_id = jQuery('#wlim-institute-select-batch').val();
            jQuery('#student-fees-report-table').DataTable().destroy();
            initializeDatatable('#student-fees-report-table', 'wl-mim-get-student-fees-report-data', { 'course_id': course_id, 'batch_id': batch_id });
        });

        jQuery(document).on('click', '#wlim-invoice-filter', function (event) {
            event.preventDefault();
            var start_date = jQuery('#wlim-invoice_start').val();
            var end_date = jQuery('#wlim-invoice_end').val();
            jQuery('#invoice-table').DataTable().destroy();
            initializeDatatable('#invoice-table', 'wl-mim-get-invoice-data', { 'start_date': start_date, 'end_date': end_date });
        });

        initializeDatatable('#student-invoice-table', 'wl-mim-get-student-invoice-data');
        initializeDatatable('#student_timetableList', 'wl-mim-get-student-timetable');

        initializeDatatable('#inactive-invoice-table', 'wl-mim-get-inactive-invoice-data');

        /* Actions for fee invoice */
        initializeDatatable('#invoice-table', 'wl-mim-get-invoice-data');
        save('.add-invoice-submit', 'wl-mim-add-invoice', '#wlim-add-invoice-form', '#add-invoice', ['#invoice-table']);
        jQuery(document).on('show.bs.modal', '#update-invoice', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-invoice',
                data: 'id=' + id,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#fetch_invoice').html(response.data.html);
                    jQuery(data.wlim_created_at_selector).datetimepicker({
                        format: 'DD-MM-YYYY',
                        showClear: true,
                        showClose: true
                    });

                    if (data.created_at_exist) {
                        /* Select date of registration */
                        jQuery(data.wlim_created_at_selector).data("DateTimePicker").date(moment(data.created_at));
                    }
                }
            });
        });
        save('.update-invoice-submit', 'wl-mim-update-invoice', '#wlim-update-invoice-form', '#update-invoice', ['#invoice-table']);
        remove('.delete-invoice', 'delete-invoice-id', 'delete-invoice-security', 'delete-invoice', 'wl-mim-delete-invoice', ['#invoice-table']);
        fetch('#print-invoice-fee-invoice', 'wl-mim-print-invoice-fee-invoice', '#print_invoice_fee_invoice');

        jQuery('#wl-min-reminder').datetimepicker({
            format: 'DD-MM-YYYY',
            showClear: true,
            showClose: true
        });


        jQuery(document).on('click', '#wlim-reminder-filter', function(event) {
			event.preventDefault();
            var start_date = jQuery('#wlim-reminder_start').val();
            var end_date = jQuery('#wlim-reminder_end').val();
            jQuery('#reminder-table').DataTable().destroy();
            initializeDatatable('#reminder-table', 'wl-mim-get-reminder-data', {'start_date' : start_date, 'end_date': end_date});
        });


        /* Actions for fee reminder */

        save('.add-reminder-submit', 'wl-mim-add-reminder', '#wlim-add-reminder-form', '#add-reminder', ['#reminder-table']);
        jQuery(document).on('show.bs.modal', '#update-reminder', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            var urlParams = new URLSearchParams(window.location.search);
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-reminder',
                data: 'id=' + id,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#fetch_reminder').html(response.data.html);
                    jQuery(data.wlim_created_at_selector).datetimepicker({
                        format: 'DD-MM-YYYY',
                        showClear: true,
                        showClose: true
                    });

                    if (data.created_at_exist) {
                        /* Select date of registration */
                        jQuery(data.wlim_created_at_selector).data("DateTimePicker").date(moment(data.created_at));
                    }
                }
            });
        });
        save('.update-reminder-submit', 'wl-mim-update-reminder', '#wlim-update-reminder-form', '#update-reminder', ['#reminder-table']);
        remove('.delete-reminder', 'delete-reminder-id', 'delete-reminder-security', 'delete-reminder', 'wl-mim-delete-reminder', ['#reminder-table']);

        /* Action to fetch invoice amount */
        jQuery(document).on('change', '#wlim-invoice-id', function() {
            var data = null;
            if(this.value) {
                data = 'id='+ this.value;
                data += '&student_id='+ jQuery(this).data('student_id');
                jQuery.ajax({
                    type : 'post',
                    url : ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-invoice-amount',
                    data :  data,
                    success : function(response) {
                        var sum = 0;
                        jQuery.each(response, function( idx, val ) {
                            var invoice_title = response.invoice_title;
                            var payable_amount = response.payable_amount;
                            var due_date_amount = response.due_date_amount;
                            var due_date = response.due_date;
                            var created_at = response.created_at;

                            $('#wlim-invoice-title').val(invoice_title);
                            $('#wlim-invoice-payable').val(payable_amount);
                            $('#wlim-invoice-due-date').val(due_date);
                            $('#wlim-invoice-due-date-amount').val(due_date_amount);
                            $('#wlim-installment-created_at').val(created_at);
                        });

                        jQuery('.wlima-amount-payable-total').html(sum.toFixed(2));
                    }
                });
            } else {
                jQuery('.wlima-invoice-amount-payable').prop('disabled', false).val("0.00");
            }
        });

        /* Actions for institute */
        initializeDatatable('#institute-table', 'wl-mim-get-institute-data');
        saveWithFiles('.add-institute-submit', '#wlim-add-institute-form', '#add-institute', ['#institute-table']);
        jQuery(document).on('show.bs.modal', '#update-institute', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-institute',
                data: 'id=' + id,
                success: function (response) {
                    jQuery('#fetch_institute').html(response.data.html);
                    /* Select multiple option */
                    jQuery('#wlim-institute-update-course').selectpicker({
                        liveSearch: true,
                        actionsBox: true
                    });

                    jQuery('#wlim-institute-add-course').selectpicker({
                        liveSearch: true,
                        actionsBox: true
                    });
                }
            });
        });
        saveWithFiles('.update-institute-submit', '#wlim-update-institute-form', '#update-institute', ['#institute-table']);
        remove('.delete-institute', 'delete-institute-id', 'delete-institute-security', 'delete-institute', 'wl-mim-delete-institute', ['#institute-table']);
        saveWithFiles('.set-institute-submit', '#wlim-set-institute-form');

        /* Actions for main course */
        initializeDatatable('#main-course-table', 'wl-mim-get-main-course-data');
        save('.add-main-course-submit', 'wl-mim-add-main-course', '#wlim-add-main-course-form', '#add-main-course', ['#main-course-table']);
        jQuery(document).on('show.bs.modal', '#update-main-course', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-main-course',
                data: 'id=' + id,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#fetch_main_course').html(response.data.html);
                    jQuery("#wlim-main-course-duration_in_update").val(data.duration_in);
                }
            });
        });
        save('.update-main-course-submit', 'wl-mim-update-main-course', '#wlim-update-main-course-form', '#update-main-course', ['#main-course-table']);
        remove('.delete-main-course', 'delete-main-course-id', 'delete-main-course-security', 'delete-main-course', 'wl-mim-delete-main-course', ['#main-course-table']);

        /* Actions for course */
        initializeDatatable('#course-table', 'wl-mim-get-course-data');
        save('.add-course-submit', 'wl-mim-add-course', '#wlim-add-course-form', '#add-course', ['#course-table', '#category-table']);
        jQuery(document).on('show.bs.modal', '#update-course', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-course',
                data: 'id=' + id,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#fetch_course').html(response.data.html);
                    /* Select single option */
                    jQuery('#wlim-course-category_update').selectpicker({
                        liveSearch: true
                    });
                    jQuery('#wlim-course-category_update').selectpicker('val', data.course_category_id);
                    jQuery("#wlim-course-duration_in_update").val(data.duration_in);
                }
            });
        });
        save('.update-course-submit', 'wl-mim-update-course', '#wlim-update-course-form', '#update-course', ['#course-table', '#category-table']);
        remove('.delete-course', 'delete-course-id', 'delete-course-security', 'delete-course', 'wl-mim-delete-course', ['#course-table', '#category-table']);

        /* Actions for category */
        initializeDatatable('#category-table', 'wl-mim-get-category-data');
        save('.add-category-submit', 'wl-mim-add-category', '#wlim-add-category-form', '#add-category', ['#category-table']);
        jQuery(document).on('show.bs.modal', '#update-category', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-category',
                data: 'id=' + id,
                success: function (response) {
                    jQuery('#fetch_category').html(response.data.html);
                }
            });
        });
        save('.update-category-submit', 'wl-mim-update-category', '#wlim-update-category-form', '#update-category', ['#category-table']);
        remove('.delete-category', 'delete-category-id', 'delete-category-security', 'delete-category', 'wl-mim-delete-category', ['#category-table']);

        /* Actions for batch */
        initializeDatatable('#batch-table', 'wl-mim-get-batch-data');
        save('.add-batch-submit', 'wl-mim-add-batch', '#wlim-add-batch-form', '#add-batch', ['#batch-table']);
        jQuery(document).on('show.bs.modal', '#update-batch', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-batch',
                data: 'id=' + id,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#fetch_batch').html(response.data.html);
                    jQuery(data.wlim_start_date_selector).datetimepicker({
                        format: 'DD-MM-YYYY',
                        showClear: true,
                        showClose: true,
                    });
                    jQuery(data.wlim_end_date_selector).datetimepicker({
                        format: 'DD-MM-YYYY',
                        showClear: true,
                        showClose: true,
                    });
                    jQuery(data.wlim_time_from_selector).datetimepicker({
                        format: 'h:mm A',
                        showClear: true,
                        showClose: true,
                    });
                    jQuery(data.wlim_time_to_selector).datetimepicker({
                        format: 'h:mm A',
                        showClear: true,
                        showClose: true,
                    });
                    jQuery('span.text-danger').remove();
                    jQuery('.is-valid').removeClass('is-valid');
                    jQuery('.is-invalid').removeClass('is-invalid');
                    /* Select single option */
                    jQuery('#wlim-batch-course_update').selectpicker({
                        liveSearch: true
                    });
                    jQuery('#wlim-batch-course_update').selectpicker('val', data.course_id);
                    /* Select start date */
                    jQuery(data.wlim_start_date_selector).data("DateTimePicker").date(moment(data.start_date));
                    /* Select end date */
                    jQuery(data.wlim_end_date_selector).data("DateTimePicker").date(moment(data.end_date));
                    /* Select time from */
                    jQuery(data.wlim_time_from_selector).data("DateTimePicker");
                    /* Select time to */
                    jQuery(data.wlim_time_to_selector).data("DateTimePicker");
                }
            });
        });
        save('.update-batch-submit', 'wl-mim-update-batch', '#wlim-update-batch-form', '#update-batch', ['#batch-table']);
        remove('.delete-batch', 'delete-batch-id', 'delete-batch-security', 'delete-batch', 'wl-mim-delete-batch', ['#batch-table']);

        /* Actions for enquiry */
        initializeDatatable('#enquiry-table', 'wl-mim-get-enquiry-data', { 'follow_up': $('#enquiry-table').data('follow-up') });
        saveWithFiles('.add-enquiry-submit', '#wlim-add-enquiry-form', '#add-enquiry', ['#enquiry-table']);
        jQuery(document).on('show.bs.modal', '#update-enquiry', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-enquiry',
                data: 'id=' + id,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#fetch_enquiry').html(response.data.html);
                    // Show date time picker inside modal
                    var minDate = new Date();
                    minDate.setFullYear(minDate.getFullYear() - 100);
                    jQuery(data.wlim_date_selector).datetimepicker({
                        format: 'DD-MM-YYYY',
                        showClear: true,
                        showClose: true,
                        minDate: minDate
                    });
                    jQuery(data.wlim_follow_selector).datetimepicker({
                        format: 'YYYY-MM-DD',
                        showClear: true,
                        showClose: true,
                    });
                    jQuery('span.text-danger').remove();
                    jQuery('.is-valid').removeClass('is-valid');
                    jQuery('.is-invalid').removeClass('is-invalid');
                    jQuery('.wlim-selectpicker').load(location.href + " " + '.wlim-selectpicker', function () {
                        /* Select single option */
                        try {
                            jQuery('.selectpicker').selectpicker({
                                liveSearch: true
                            });
                        } catch (error) {
                        }
                    });
                    /* Select single option */
                    jQuery('#wlim-enquiry-course_update').selectpicker({
                        liveSearch: true
                    });
                    jQuery('#wlim-enquiry-course_update').selectpicker('val', data.course_id);
                    /* Select gender */
                    jQuery("input[name=gender][value=" + data.gender + "]").prop('checked', true);
                    if (data.date_of_birth_exist) {
                        /* Select date of birth */
                        jQuery(data.wlim_date_selector).data("DateTimePicker").date(moment(data.date_of_birth));
                    }
                }
            });
        });
        saveWithFiles('.update-enquiry-submit', '#wlim-update-enquiry-form', '#update-enquiry', ['#enquiry-table']);
        remove('.delete-enquiry', 'delete-enquiry-id', 'delete-enquiry-security', 'delete-enquiry', 'wl-mim-delete-enquiry', ['#enquiry-table']);

        /* Action to fetch category courses */
        jQuery(document).on('change', '#wlim-enquiry-category', function () {
            jQuery('span.text-danger').remove();
            if (this.value) {
                var data = 'id=' + this.value;
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-category-courses',
                    data: data,
                    success: function (response) {
                        jQuery('#wlim-fetch-category-courses').html(response.data.html);
                        jQuery('#wlim-enquiry-course').selectpicker({
                            liveSearch: true
                        });
                    }
                });
            } else {
                jQuery('#wlim-fetch-category-courses').html("");
            }
        });
        jQuery(document).on('change', '#wlim-enquiry-category_update', function () {
            jQuery('span.text-danger').remove();
            if (this.value) {
                var data = 'id=' + this.value;
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-category-courses-update',
                    data: data,
                    success: function (response) {
                        jQuery('#wlim-fetch-category-courses_update').html(response.data.html);
                        jQuery('#wlim-enquiry-course_update').selectpicker({
                            liveSearch: true
                        });
                    }
                });
            } else {
                jQuery('#wlim-fetch-category-courses_update').html("");
            }
        });
        jQuery(document).on('change', '#wlim-student-category', function () {
            jQuery('span.text-danger').remove();
            if (this.value) {
                var data = 'id=' + this.value;
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-student-fetch-category-courses',
                    data: data,
                    success: function (response) {
                        jQuery('#wlim-student-fetch-category-courses').html(response.data.html);
                        jQuery('#wlim-student-course').selectpicker({
                            liveSearch: true
                        });
                    }
                });
            } else {
                jQuery('#wlim-student-fetch-category-courses').html("");
            }
        });
        jQuery(document).on('change', '#wlim-student-category_update', function () {
            jQuery('span.text-danger').remove();
            if (this.value) {
                var data = 'id=' + this.value;
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-student-fetch-category-courses-update',
                    data: data,
                    success: function (response) {
                        jQuery('#wlim-student-fetch-category-courses_update').html(response.data.html);
                        jQuery('#wlim-student-course_update').selectpicker({
                            liveSearch: true
                        });
                    }
                });
            } else {
                jQuery('#wlim-student-fetch-category-courses_update').html("");
            }
        });

        /* Actions for student */
        saveWithFiles('.import-student-submit', '#wlim-import-student-form', '#import-student', ['#student-table']);

        /* Actions for student */
        saveWithFiles('.add-student-submit', '#wlim-add-student-form', '#add-student', ['#student-table']);
        jQuery(document).on('show.bs.modal', '#update-student', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-student',
                data: 'id=' + id,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#fetch_student').html(response.data.html);
                    // Show date time picker inside modal
                    jQuery(data.wlim_date_selector).datetimepicker({
                        format: 'DD-MM-YYYY',
                        showClear: true,
                        showClose: true
                    });
                    jQuery(data.wlim_created_at_selector).datetimepicker({
                        format: 'DD-MM-YYYY',
                        showClear: true,
                        showClose: true
                    });
                    jQuery('span.text-danger').remove();
                    jQuery('.is-valid').removeClass('is-valid');
                    jQuery('.is-invalid').removeClass('is-invalid');
                    jQuery('.wlim-selectpicker').load(location.href + " " + '.wlim-selectpicker', function () {
                        /* Select single option */
                        try {
                            jQuery('.selectpicker').selectpicker({
                                liveSearch: true
                            });
                        } catch (error) {
                        }
                    });
                    // Select single option
                    jQuery('#wlim-student-course_update').selectpicker({
                        liveSearch: true
                    });
                    jQuery('#wlim-student-course_update').selectpicker('val', data.course_id);

                    // Select single option
                    jQuery('#wlim-student-batch_update').selectpicker({
                        liveSearch: true
                    });
                    jQuery('#wlim-student-batch_update').selectpicker('val', data.batch_id);

                    /* Select gender */
                    jQuery("input[name=gender][value=" + data.gender + "]").prop('checked', true);

                    if (data.date_of_birth_exist) {
                        /* Select date of birth */
                        jQuery(data.wlim_date_selector).data("DateTimePicker").date(moment(data.date_of_birth));
                    }

                    if (data.created_at_exist) {
                        /* Select date of registration */
                        jQuery(data.wlim_created_at_selector).data("DateTimePicker").date(moment(data.created_at));
                    }

                    /* Allow student to login checkbox */
                    if (!data.allow_login) {
                        jQuery('.wlim-allow-login-fields').hide();
                    }
                    jQuery(document).on('change', '#wlim-student-allow_login_update', function () {
                        if (this.checked) {
                            jQuery('.wlim-allow-login-fields').fadeIn();
                        } else {
                            jQuery('.wlim-allow-login-fields').fadeOut();
                        }
                    });
                }
            });
        });

        saveWithFiles('.update-student-submit', '#wlim-update-student-form', '#update-student', ['#student-table']);
        remove('.delete-student', 'delete-student-id', 'delete-student-security', 'delete-student', 'wl-mim-delete-student', ['#student-table']);

        /* Actions for note */
        initializeDatatable('#note-table', 'wl-mim-get-note-data');
        saveWithFiles('.add-note-submit', '#wlim-add-note-form', '#add-note', ['#note-table']);
        jQuery(document).on('show.bs.modal', '#update-note', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-note',
                data: 'id=' + id,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#fetch_note').html(response.data.html);
                    // Show date time picker inside modal
                    jQuery(data.wlim_date_selector).datetimepicker({
                        format: 'DD-MM-YYYY',
                        showClear: true,
                        showClose: true,
                    });
                    jQuery('span.text-danger').remove();
                    jQuery('.is-valid').removeClass('is-valid');
                    jQuery('.is-invalid').removeClass('is-invalid');
                    jQuery('.wlim-selectpicker').load(location.href + " " + '.wlim-selectpicker', function () {
                        /* Select single option */
                        try {
                            jQuery('.selectpicker').selectpicker({
                                liveSearch: true
                            });
                        } catch (error) {
                        }
                    });
                    /* Select single option */
                    jQuery('#wlim-note-batch_update').selectpicker({
                        liveSearch: true
                    });
                    jQuery('#wlim-note-batch_update').selectpicker('val', data.batch_id);

                    if (data.notes_date_exist) {
                        /* Select date of birth */
                        jQuery(data.wlim_date_selector).data("DateTimePicker").date(moment(data.notes_date));
                    }
                }
            });
        });
        saveWithFiles('.update-note-submit', '#wlim-update-note-form', '#update-note', ['#note-table']);
        remove('.delete-note', 'delete-note-id', 'delete-note-security', 'delete-note', 'wl-mim-delete-note', ['#note-table']);
        jQuery(document).on('show.bs.modal', '#view-student-note', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-view-student-note',
                data: 'id=' + id,
                success: function (response) {
                    jQuery('#view_student_note').html(response.data.html);
                }
            });
        });

        /* Action for settings */
        saveWithFiles('.save-general-settings-submit', '#wlim-general-settings-form', null, [], false);
        saveWithFiles('.save-payment-settings-submit', '#wlim-payment-settings-form', null, [], false);
        saveWithFiles('.save-email-settings-submit', '#wlim-email-settings-form', null, [], false);
        saveWithFiles('.save-template-settings-submit', '#wlim-email-template-form', null, [], false);
        saveWithFiles('.save-sms-settings-submit', '#wlim-sms-settings-form', null, [], false);
        saveWithFiles('.save-admit-card-settings-submit', '#wlim-admit-card-settings-form', null, [], false);
        saveWithFiles('.save-id-card-settings-submit', '#wlim-id-card-settings-form', null, [], false);
        saveWithFiles('.save-certificate-settings-submit', '#wlim-certificate-settings-form', null, [], false);

        /* Fetch student enquiries */
        jQuery(document).on('change', '#wlim-student-from_enquiry', function () {
            jQuery('span.text-danger').remove();
            if (this.checked) {
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-add-student-fetch-enquiries',
                    data: [],
                    success: function (response) {
                        var data = JSON.parse(response.data.json);
                        jQuery('#wlim-add-student-from-enquiries').html(response.data.html);
                        if (data.element) {
                            /* Select single option */
                            jQuery(data.element).selectpicker({
                                liveSearch: true
                            });
                        }
                    }
                });
            } else {
                jQuery('#wlim-add-student-from-enquiries').html("");
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-add-student-form',
                    data: [],
                    success: function (response) {
                        var data = JSON.parse(response.data.json);
                        jQuery('#wlim-add-student-form-fields').html(response.data.html);
                        // Show date time picker inside modal
                        var minDate = new Date();
                        minDate.setFullYear(minDate.getFullYear() - 100);
                        jQuery(data.wlim_date_selector).datetimepicker({
                            format: 'DD-MM-YYYY',
                            showClear: true,
                            showClose: true,
                            minDate: minDate
                        });
                        jQuery('span.text-danger').remove();
                        jQuery('.is-valid').removeClass('is-valid');
                        jQuery('.is-invalid').removeClass('is-invalid');
                        jQuery('.wlim-selectpicker').load(location.href + " " + '.wlim-selectpicker', function () {
                            /* Select single option */
                            try {
                                jQuery('.selectpicker').selectpicker({
                                    liveSearch: true
                                });
                            } catch (error) {
                            }
                        });
                        /* Select single option */
                        try {
                            jQuery('.selectpicker').selectpicker({
                                liveSearch: true
                            });
                        } catch (error) {
                        }
                    }
                });
            }
        });

        /* Fetch student course batches */
        jQuery(document).on('change', '#wlim-student-course', function () {
            jQuery('span.text-danger').remove();
            if (this.value) {
                var data = 'id=' + this.value;
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-add-student-fetch-course-batches',
                    data: data,
                    success: function (response) {
                        var data = JSON.parse(response.data.json);
                        jQuery('#wlim-add-student-course-batches').html(response.data.html);
                        if (data.element) {
                            /* Select single option */
                            jQuery(data.element).selectpicker({
                                liveSearch: true
                            });
                        }
                    }
                });
            } else {
                jQuery('#wlim-add-student-course-batches').html("");
            }
        });
        jQuery(document).on('change', '#wlim-student-course_update', function () {
            jQuery('span.text-danger').remove();
            if (this.value) {
                var batch_id = jQuery(this).data('batch_id');
                var data = 'id=' + this.value + '&batch_id=' + batch_id;
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-add-student-fetch-course-update-batches',
                    data: data,
                    success: function (response) {
                        var data = JSON.parse(response.data.json);
                        jQuery('#wlim-add-student-course-update-batches').html(response.data.html);
                        if (data.element) {
                            /* Select single option */
                            jQuery(data.element).selectpicker({
                                liveSearch: true
                            });
                        }
                        if (data.batch_id) {
                            jQuery('#wlim-student-batch_update').selectpicker('val', data.batch_id);
                        }
                    }
                });
            } else {
                jQuery('#wlim-add-student-course-update-batches').html("");
            }
        });

        /* Fetch add student form on open modal */
        jQuery(document).on('shown.bs.modal', '#add-student', function () {
            var form = '#wlim-add-student-form';
            jQuery(form)[0].reset();
            jQuery(form + ' span.text-danger').remove();
            jQuery(form + ' .is-valid').removeClass('is-valid');
            jQuery(form + ' .is-invalid').removeClass('is-invalid');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-add-student-form',
                data: [],
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#wlim-add-student-form-fields').html(response.data.html);
                    // Show date time picker inside modal
                    var minDate = new Date();
                    minDate.setFullYear(minDate.getFullYear() - 100);
                    jQuery(data.wlim_date_selector).datetimepicker({
                        format: 'DD-MM-YYYY',
                        showClear: true,
                        showClose: true,
                        minDate: minDate
                    });
                    jQuery('span.text-danger').remove();
                    jQuery('.is-valid').removeClass('is-valid');
                    jQuery('.is-invalid').removeClass('is-invalid');
                    /* Select single option */
                    try {
                        jQuery('.selectpicker').selectpicker({
                            liveSearch: true
                        });
                    } catch (error) {
                    }
                }
            });
            jQuery('#wlim-add-student-from-enquiries').html('');
        });

        /* Fetch student enquiry */
        jQuery(document).on('change', '#wlim-student-enquiry', function () {
            var data = null;
            if (this.value) {
                data = 'id=' + this.value;
            }
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-add-student-fetch-enquiry',
                data: data,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#wlim-add-student-form-fields').html(response.data.html);
                    // Show date time picker inside modal
                    var minDate = new Date();
                    minDate.setFullYear(minDate.getFullYear() - 100);
                    jQuery(data.wlim_date_selector).datetimepicker({
                        format: 'DD-MM-YYYY',
                        showClear: true,
                        showClose: true,
                        minDate: minDate
                    });
                    jQuery('span.text-danger').remove();
                    jQuery('.is-valid').removeClass('is-valid');
                    jQuery('.is-invalid').removeClass('is-invalid');
                    jQuery('.wlim-selectpicker').load(location.href + " " + '.wlim-selectpicker', function () {
                        /* Select single option */
                        try {
                            jQuery('.selectpicker').selectpicker({
                                liveSearch: true
                            });
                        } catch (error) {
                        }
                    });
                    /* Select single option */
                    jQuery('#wlim-student-course').selectpicker({
                        liveSearch: true
                    });
                    jQuery('#wlim-student-course').selectpicker('val', data.course_id);

                    /* Select single option */
                    jQuery('#wlim-student-batch').selectpicker({
                        liveSearch: true
                    });

                    /* Select gender */
                    jQuery("input[name=gender][value=" + data.gender + "]").prop('checked', true);

                    if (data.date_of_birth_exist) {
                        /* Select date of birth */
                        jQuery(data.wlim_date_selector).data("DateTimePicker").date(moment(data.date_of_birth));
                    }
                }
            });
        });

        /* Fetch student fees */
        jQuery(document).on('change', '#wlim-student-course', function () {
            var data = null;
            if (this.value) {
                data = 'id=' + this.value;
            }
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-add-student-fetch-fees-payable',
                data: data,
                success: function (response) {
                    jQuery('#wlim-add-student-fetch-fees-payable').html(response.data.html);
                }
            });
        });

         /* Fetch student fees */
         jQuery(document).on('change', '#course_discount', function () {
            var discount   = $('#course_discount').val();
            var course_fee = $('#course_fee').val();
            var payable_amount = course_fee - discount;
            $('#course_payable').val(payable_amount);
        });

        // installment fetch

        /* Fetch student fees */
        jQuery(document).on('change', '#wlim-installment-count', function () {
            var data = null;
            var course_payable = $('#course_payable').val()
            if (this.value) {
                data = 'id='+ this.value + '&course_payable=' + course_payable;
            }
            console.log();
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-add-student-fetch-installment',
                data: data,
                success: function (response) {
                    jQuery('#wlim-add-student-fetch-installment').html(response.data.html);
                }
            });
        });

        /* Fetch student fees for invoice */
        jQuery(document).on('change', '#wlim-invoice-student', function() {
            var data = null;
            if (this.value) {
                data = 'id=' + this.value;
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-add-invoice-fetch-fees',
                    data: data,
                    success: function (response) {
                        var data = JSON.parse(response.data.json);
                        jQuery('#wlim_add_invoice_fetch_fees').html(response.data.html);
                        jQuery(data.wlim_created_at_selector).datetimepicker({
                            format: 'DD-MM-YYYY',
                            showClear: true,
                            showClose: true
                        });
                        if (data.created_at_exist) {
                            /* Select date of registration */
                            jQuery(data.wlim_created_at_selector).data("DateTimePicker").date(moment(data.created_at));
                        }
                    }
                });
            } else {
                jQuery('#wlim_add_invoice_fetch_fees').html('');
            }
        });

        /* Actions for fee installment */
        initializeDatatable('#installment-table', 'wl-mim-get-installment-data');
        // save('.add-installment-submit', 'wl-mim-add-installment', '#wlim-add-installment-form', '#add-installments', ['#installment-table']);
        // saveWithFiles('#add-installment-submit', '#wlim-add-installment-form', '#add-installments', ['installment-table']);

        saveWithFiles('#add-installment-submit', '#wlim-add-installment-form', '#add-installments', ['#installment-table']);

        jQuery(document).on('show.bs.modal', '#update-installment', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-installment',
                data: 'id=' + id,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#fetch_installment').html(response.data.html);
                    jQuery(data.wlim_created_at_selector).datetimepicker({
                        format: 'DD-MM-YYYY',
                        showClear: true,
                        showClose: true
                    });
                    var updateInstallmentSubmit = jQuery(".update-installment-submit");
                    if ( data.invoice ) {
                        updateInstallmentSubmit.siblings().html(data.close);
                        updateInstallmentSubmit.hide();
                    } else {
                        updateInstallmentSubmit.siblings().html(data.cancel);
                        updateInstallmentSubmit.html(data.update);
                        updateInstallmentSubmit.show();
                    }

                    if (data.created_at_exist) {
                        /* Select date of registration */
                        jQuery(data.wlim_created_at_selector).data("DateTimePicker").date(moment(data.created_at));
                    }
                }
            });
        });
        save('.update-installment-submit', 'wl-mim-update-installment', '#wlim-update-installment-form', '#update-installment', ['#installment-table']);
        remove('.delete-installment', 'delete-installment-id', 'delete-installment-security', 'delete-installment', 'wl-mim-delete-installment', ['#installment-table']);
        fetch('#print-installment-fee-receipt', 'wl-mim-print-installment-fee-receipt', '#print_installment_fee_receipt');

        /* Actions for fee type */
        initializeDatatable('#fee-type-table', 'wl-mim-get-fee-type-data');
        save('.add-fee-type-submit', 'wl-mim-add-fee-type', '#wlim-add-fee-type-form', '#add-fee-type', ['#fee-type-table']);
        fetch('#update-fee-type', 'wl-mim-fetch-fee-type', '#fetch_fee-type');
        save('.update-fee-type-submit', 'wl-mim-update-fee-type', '#wlim-update-fee-type-form', '#update-fee-type', ['#fee-type-table']);
        remove('.delete-fee-type', 'delete-fee-type-id', 'delete-fee-type-security', 'delete-fee-type', 'wl-mim-delete-fee-type', ['#fee-type-table']);

        /* Actions for custom field */
        initializeDatatable('#sources-table', 'wl-mim-get-source');
        save('.add-source-submit', 'wl-mim-add-source', '#wlim-add-source-form', '#add-source', ['#source-table']);
        remove('.delete-source', 'delete-source-id', 'delete-source-security', 'delete-source', 'wl-mim-delete-source', ['#source-table']);

        initializeDatatable('#custom-field-table', 'wl-mim-get-custom-field-data');
        save('.add-custom-field-submit', 'wl-mim-add-custom-field', '#wlim-add-custom-field-form', '#add-custom-field', ['#custom-field-table']);
        fetch('#update-custom-field', 'wl-mim-fetch-custom-field', '#fetch_custom-field');
        save('.update-custom-field-submit', 'wl-mim-update-custom-field', '#wlim-update-custom-field-form', '#update-custom-field', ['#custom-field-table']);
        remove('.delete-custom-field', 'delete-custom-field-id', 'delete-custom-field-security', 'delete-custom-field', 'wl-mim-delete-custom-field', ['#custom-field-table']);

        /* Fetch student fees */
        jQuery(document).on('change', '#wlim-installment-student', function () {
            var data = null;
            if (this.value) {
                data = 'id=' + this.value;
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-add-installment-fetch-fees',
                    data: data,
                    success: function (response) {
                        var data = JSON.parse(response.data.json);
                        jQuery('#wlim_add_installment_fetch_fees').html(response.data.html);
                        jQuery(data.wlim_created_at_selector).datetimepicker({
                            format: 'DD-MM-YYYY',
                            showClear: true,
                            showClose: true
                        });
                        if (data.created_at_exist) {
                            /* Select date of registration */
                            jQuery(data.wlim_created_at_selector).data("DateTimePicker").date(moment(data.created_at));
                        }
                        if ( data.is_invoice_available ) {
                            jQuery('#wlim-invoice-id').selectpicker({
                                liveSearch: true
                            });
                        }
                    }
                });
            } else {
                jQuery('#wlim_add_installment_fetch_fees').html('');
            }
        });

        /* Actions for exam */
        initializeDatatable('#exam-table', 'wl-mim-get-exam-data');
        saveWithFiles('.add-exam-submit', '#wlim-add-exam-form', '#add-exam', ['#exam-table']);
        jQuery(document).on('show.bs.modal', '#update-exam', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-exam',
                data: 'id=' + id,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#fetch_exam').html(response.data.html);
                    /* Select exam date */
                    jQuery('.wlim-exam-exam_date_update').datetimepicker({
                        format: 'DD-MM-YYYY',
                        showClear: true,
                        showClose: true,
                    });
                    if (data.exam_date_exist) {
                        jQuery('.wlim-exam-exam_date_update').data("DateTimePicker").date(moment(data.exam_date));
                    }
                }
            });
        });
        saveWithFiles('.update-exam-submit', '#wlim-update-exam-form', '#update-exam', ['#exam-table']);
        remove('.delete-exam', 'delete-exam-id', 'delete-exam-security', 'delete-exam', 'wl-mim-delete-exam', ['#exam-table']);

        /* Actions for result */
        saveWithFiles('.save-result-submit', '#wlim-save-result-form', '#save-result', [], false);
        fetch('#update-result', 'wl-mim-fetch-result', '#fetch_result');

        fetch('#print-exam-result', 'wl-mim-print-exam-result', '#print_exam_result');

        remove('.delete-result', 'delete-result-id', 'delete-result-security', 'delete-result', 'wl-mim-delete-result', ['#result-table']);
        /* Fetch result course batches */
        jQuery(document).on('change', '#wlim-result-course', function () {
            jQuery('span.text-danger').remove();
            if (this.value) {
                var data = 'id=' + this.value;
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-add-result-fetch-course-batches',
                    data: data,
                    success: function (response) {
                        var data = JSON.parse(response.data.json);
                        jQuery('#wlim-add-result-course-batches').html(response.data.html);
                        /* Select single option */
                        jQuery('#wlim-result-batch').selectpicker({
                            liveSearch: true
                        });
                    }
                });
            } else {
                jQuery('#wlim-add-result-course-batches').html("");
            }
        });
        /* Fetch result batch students */
        jQuery(document).on('change', '#wlim-result-batch', function () {
            jQuery('span.text-danger').remove();
            if (this.value) {
                var data = 'id=' + this.value;
                data += '&exam_id=' + jQuery('#wlim-result-exam').val();
                fetchRecords('wl-mim-add-result-fetch-batch-students', '#wlim-add-result-batch-students', data);
            } else {
                jQuery('#wlim-add-result-batch-students').html("");
            }
        });
        /* Fetch exam results */
        jQuery(document).on('submit', '#wlim-get-exam-results-form', function (e) {
            e.preventDefault();
            jQuery('span.text-danger').remove();
            var data = jQuery('#wlim-get-exam-results-form').serialize();
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-get-exam-results',
                data: data,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#wlim-get-exam-results').html(response.data.html);
                    /* Select multiple option */
                    jQuery('#wlim-students').selectpicker({
                        liveSearch: true,
                        actionsBox: true
                    });

                    jQuery('#update-result').appendTo("body");

                    jQuery('#result-table').DataTable({
                        aaSorting: [],
                        responsive: true,
                        ajax: {
                            url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-get-result-data&exam=' + data.exam_id,
                            dataSrc: 'data'
                        },
                        language: {
                            "loadingRecords": "Loading..."
                        }
                    });

                    /* Actions for result */
                    saveWithFiles('.add-result-submit', '#wlim-add-result-form', '#add-result', ['#result-table']);
                    saveWithFiles('.update-result-submit', '#wlim-update-result-form', '#update-result', ['#result-table']);
                }
            });
        });

         /* Fetch exam results */
 jQuery(document).on('submit', '#wlim-save-exam-results-form', function (e) {
    e.preventDefault();
    jQuery('span.text-danger').remove();
    var data = jQuery('#wlim-save-exam-results-form').serialize();
    jQuery.ajax({
        type: 'post',
        url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-save-exam-results',
        data: data,
        success: function (response) {
            var data = JSON.parse(response.data.json);
            jQuery('#wlim-save-exam-results').html(response.data.html);
            /* Select multiple option */
            jQuery('#wlim-students').selectpicker({
                liveSearch: true,
                actionsBox: true
            });

            jQuery('#update-result').appendTo("body");

            jQuery('#result-table').DataTable({
                aaSorting: [],
                responsive: true,
                ajax: {
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-get-result-data&exam=' + data.exam_id,
                    dataSrc: 'data'
                },
                language: {
                    "loadingRecords": "Loading..."
                }
            });

            /* Actions for result */
            saveWithFiles('.add-result-submit', '#wlim-add-result-form', '#add-result', ['#result-table']);
            saveWithFiles('.update-result-submit', '#wlim-update-result-form', '#update-result', ['#result-table']);
        }
    });
});

        /* Actions for expense */
        initializeDatatable('#expense-table', 'wl-mim-get-expense-data');
        saveWithFiles('.add-expense-submit', '#wlim-add-expense-form', '#add-expense', ['#expense-table']);
        jQuery(document).on('show.bs.modal', '#update-expense', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-expense',
                data: 'id=' + id,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#fetch_expense').html(response.data.html);
                    // Show date time picker inside modal
                    jQuery(data.wlim_date_selector).datetimepicker({
                        format: 'DD-MM-YYYY',
                        showClear: true,
                        showClose: true
                    });
                    jQuery('span.text-danger').remove();
                    jQuery('.is-valid').removeClass('is-valid');
                    jQuery('.is-invalid').removeClass('is-invalid');

                    if (data.consumption_date_exist) {
                        /* Select consumption date */
                        jQuery(data.wlim_date_selector).data("DateTimePicker").date(moment(data.consumption_date));
                    }
                }
            });
        });
        saveWithFiles('.update-expense-submit', '#wlim-update-expense-form', '#update-expense', ['#expense-table']);
        remove('.delete-expense', 'delete-expense-id', 'delete-expense-security', 'delete-expense', 'wl-mim-delete-expense', ['#expense-table']);

        /* Actions for report */
        jQuery(document).on('submit', '#wlim-view-report-form', function (e) {
            e.preventDefault();
            var data = jQuery('#wlim-view-report-form').serialize();
            fetchRecords('wl-mim-view-report', '#wlim-view-report', data);
        });
        fetch('#print-student', 'wl-mim-print-student', '#print_student');
        fetch('#print-student-admission-detail', 'wl-mim-print-student-admission-detail', '#print_student_admission_detail');
        fetch('#print-student-fees-report', 'wl-mim-print-student-fees-report', '#print_student_fees_report');
        fetch('#print-student-pending-fees', 'wl-mim-print-student-pending-fees', '#print_student_pending_fees');
        fetch('#print-student-certificate', 'wl-mim-print-student-certificate', '#print_student_certificate');
        jQuery(document).on('change', '#wlim-overall-report', function () {
            jQuery('span.text-danger').remove();
            var data = 'report_by=' + jQuery('#wlim-overall-report').val();
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-overall-report-selection',
                data: data,
                success: function (response) {
                    jQuery('#wlim-overall-report-selection').html(response.data.html);
                    if (response.data) {
                        var data = JSON.parse(response.data.json);
                        if (data.element) {
                            /* Select single option */
                            jQuery(data.element).selectpicker({
                                liveSearch: true
                            });
                        }
                    }
                }
            });
        });

     /* add subject */
     jQuery('#wim_add_subject').on('submit', function(e){
        e.preventDefault();
        jQuery.ajax({
            url: ajaxurl+"?action=wl_mim-add-subject",
            type: "post",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response){
                if( response.success == true ) {
                    toastr.success(response.data.message);
                    jQuery('#add-subject'). modal('hide');
                   // jQuery('#addTopic')[0].reset();
                    jQuery("#subject-table").DataTable().ajax.reload();
                    window.location.reload();
                }
            }
        })
     });
     remove('.delete-subject', 'delete-subject-id', 'delete-subject-security', 'delete-subject', 'wl-mim-delete-subject', ['#subject-table']);

     //data table for subject
     initializeDatatable('#subject-table', 'wl-mim-subject-data');

     //open modal to edit the subjects
     jQuery(document).on('show.bs.modal', '#update-subject', function (e) {
        var id = jQuery(e.relatedTarget).data('id');
        jQuery.ajax({
            type: 'post',
            url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-subject',
            data: 'id=' + id,
            dataType: 'json',
            success: function (response) {
                // console.table(response.data.html);
                // var data = JSON.parse(response.data.json);
                jQuery('#fetch_subject').html(response.data.html);
                jQuery('.selectpicker').selectpicker();
            }
        });
    });

    //update the subject
    jQuery('#wim_update_subject').on('submit', function(e){
        e.preventDefault();
        jQuery.ajax({
            url: ajaxurl+"?action=wl-mim-update-subject",
            type: "post",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response){
                if( response.success == true ) {
                    toastr.success(response.data.message);
                   // jQuery('#addTopic')[0].reset();
                   jQuery('#update-subject'). modal('hide');
                   jQuery("#subject-table").DataTable().ajax.reload();
                }
            }
        })
     });

     // Get the batches on change of course
     jQuery("#courseID").on("changed.bs.select",
            function(e, clickedIndex, newValue, oldValue) {
                    var data = jQuery('#wlim-notification-configure-form').serialize();
                    var formData = new FormData();
                    var batches = $('#batchID');
                    var course_id = this.value;
                    formData.append('course_id', newValue);
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-get-batches&course_id='+course_id,
                data: data,
                dataType: 'json',
                success: function (response) {o
                    var data = (response.data);
                    batches.html('');
                    // cada array del parametro tiene un elemento index(concepto) y un elemento value(el  valor de concepto)
                    batches.append('<option value="' + 0 + '">' +'Select Batch'+ '</option>');
                    $.each(data, function(index, value) {
                      // darle un option con los valores asignados a la variable select
                      batches.append('<option value="' + value.id + '">' + value.batch_name +' ['+value.batch_code +']'+ '</option>');
                    });
                    batches.selectpicker("refresh");
                }
            });
        });

     // get the subject on change of the course
     jQuery('#wlim-course-name').on('change', function(e) {
        e.preventDefault();
        let courseId = jQuery(this).val();
        let instituteId = jQuery('#instituteId').val();
        //alert(`${courseId} and ${instituteId}`);
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'wl_mim-get-subject',
                nonce: WLIMAjax.security,
                instituteId: instituteId,
                courseId: courseId,
            },
            success: function(response){
                // console.table(response);
                jQuery('#wlim-subject').html(response).selectpicker('refresh');
            }
        });
    });

     //add topic
     initializeDatatable('#topicList', 'wl-mim-topic-data');
     jQuery("#wim_add_topic").on('submit', function(e) {
        e.preventDefault();
        var form = jQuery(this);
        jQuery.ajax({
            url: ajaxurl+"?action=wl_mim-add-topic",
            type: "post",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response){
                if( response.success == true ) {
                    toastr.success(response.data.message);
                    jQuery('#add-topic'). modal('hide');
                    jQuery('#wim_add_topic')[0].reset();
                    jQuery("#topicList").DataTable().ajax.reload();
                }
                else if( response.success == false ){
                    jQuery.each( response.data, function( key, value ) {
                        //console.log( key + ": " + value );
                        var msg = '<label class="text-danger" for="'+key+'">'+value+'</label>';
                        jQuery('input[name="' + key + '"], select[name="' + key + '"]').addClass('is-invalid').after(msg);
                    });
                    var keys = Object.keys(response);
                    jQuery('input[name="'+keys[0]+'"]').focus();
                }
            }
        })
    });

    //fetch topic data to modal for edit it
    jQuery(document).on('show.bs.modal', '#update-topic', function (e) {
        var id = jQuery(e.relatedTarget).data('id');
        jQuery.ajax({
            type: 'post',
            url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-topic',
            data: 'id=' + id,
            dataType: 'json',
            success: function (response) {
                // console.table(response.data.html);
                // var data = JSON.parse(response.data.json);
                jQuery('#fetch_topic').html(response.data.html);
                jQuery('.selectpicker').selectpicker();
            }
        });
    });
     //update the topic
    // wl-mim-update-topic
    jQuery("#wim_update_topic").on('submit', function(e) {
        e.preventDefault();
        var form = jQuery(this);
        jQuery.ajax({
            url: ajaxurl+"?action=wl-mim-update-topic",
            type: "post",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response){
                if( response.success == true ) {
                    toastr.success(response.data.message);
                    jQuery('#update-topic'). modal('hide');
                    // jQuery('#wim_update_topic')[0].reset();
                    jQuery("#topicList").DataTable().ajax.reload();
                }
                else if( response.success == false ){
                    jQuery.each( response.data, function( key, value ) {
                        //console.log( key + ": " + value );
                        var msg = '<label class="text-danger" for="'+key+'">'+value+'</label>';
                        jQuery('input[name="' + key + '"], select[name="' + key + '"]').addClass('is-invalid').after(msg);
                    });
                    var keys = Object.keys(response);
                    jQuery('input[name="'+keys[0]+'"]').focus();
                }
            }
        })
    });

    //Delete the topic
    remove('.delete-topic', 'delete-topic-id', 'delete-topic-security', 'delete-topic', 'wl-mim-delete-topic', ['#topicList']);

    /* studio/room */
    remove('.delete-room', 'delete-room-id', 'delete-room-security', 'delete-room', 'wl-mim-delete-room', ['#roomList']);
    initializeDatatable('#roomList', 'wl-mim-room-data');
    jQuery("#wim_add_room").on('submit', function(e) {
        e.preventDefault();
        var form = jQuery(this);
        jQuery.ajax({
            url: ajaxurl+"?action=wl_mim-add-room",
            type: "post",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response){
                if( response.success == true ) {
                    toastr.success(response.data.message);
                    jQuery('#add-studio').modal('hide');
                    jQuery('#wim_add_room')[0].reset();
                    jQuery("#roomList").DataTable().ajax.reload();
                }
                else if( response.success == false ){
                    jQuery.each( response.data, function( key, value ) {
                        //console.log( key + ": " + value );
                        var msg = '<label class="text-danger" for="'+key+'">'+value+'</label>';
                        jQuery('input[name="' + key + '"], select[name="' + key + '"]').addClass('is-invalid').after(msg);
                    });
                    var keys = Object.keys(response);
                    jQuery('input[name="'+keys[0]+'"]').focus();
                }
            }
        })
    });
    //fetch room data on modal to edit it
    jQuery(document).on('show.bs.modal', '#update-room', function (e) {
        var id = jQuery(e.relatedTarget).data('id');
        jQuery.ajax({
            type: 'post',
            url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-room',
            data: 'id=' + id,
            dataType: 'json',
            success: function (response) {
                // console.table(response.data.html);
                // var data = JSON.parse(response.data.json);
                jQuery('#fetch_room').html(response.data.html);
                jQuery('.selectpicker').selectpicker();
            }
        });
    });
    //update room
    jQuery('#wim_update_room').on('submit', function(e){
        e.preventDefault();
        jQuery.ajax({
            url: ajaxurl+"?action=wl-mim-update-room",
            type: "post",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response){
                if( response.success == true ) {
                    toastr.success(response.data.message);
                   // jQuery('#addTopic')[0].reset();
                   jQuery('#update-room'). modal('hide');
                   jQuery("#roomList").DataTable().ajax.reload();
                }
            }
        })
     });

    jQuery('.selectpicker').selectpicker();
    jQuery('#ttcourseID').on('change', function(e) {
        e.preventDefault();
        var courseID  = jQuery(this).val();
        let startTime = jQuery('#wlim_tt_class_startTime').val();
        let endTime   = jQuery('#wlim_tt_class_endTime').val();
        let batch_date   = jQuery('#wlim_tt_class_date').val();

        let batchhtml = jQuery('#ttbatchID');
        let subhtml   = jQuery('#ttsubID');
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'wl-mim-dataforTT',
                nonce: WLIMAjax.security,
                courseID: courseID,
                startTime: startTime,
                endTime: endTime,
                batch_date: batch_date
            },
            success: function(response){
                let data     = jQuery.parseJSON(response);
                let batchD   = data.batchData;
                let subD     = data.subData;
                let batch    = '';
                let subjects = '';

                jQuery('#ttbatchID').html(data.batchData);
                jQuery('#ttsubID').html(data.subData);

                // batchD.forEach(function(item){
                //     batch += batchhtml.append('<option value="' + item.batchid + '">' + item.batchName + '</option>');
                // });
                //batchhtml.selectpicker("refresh");

                // subD.forEach(function(item){
                //     subjects += subhtml.append('<option value="' + item.subid + '">' + item.subName + '</option>');
                // });
                //subhtml.selectpicker("refresh");

            }
        });
    });

    //ajax call to get the topic and teacher
    //ajax call to get the topic and teacher
    jQuery('#ttsubID').on('change', function(e){
        e.preventDefault();
        var subID       = jQuery(this).val();
        let endtime     = jQuery('#wlim_tt_class_endTime').val();
        let starttime   = jQuery('#wlim_tt_class_startTime').val();
        let classDate   = jQuery('#wlim_tt_class_date').val();
        let topichtml   = jQuery('#tttopicID');
        let teacherhtml = jQuery('#ttteacherID');

        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'wl-mim-topicTeacher',
                nonce: WLIMAjax.security,
                subID: subID,
                classDate: classDate,
                starttime: starttime,
                endtime: endtime
            },
            success: function(response){
                // console.table(response);
                let data     = jQuery.parseJSON(response);
                let topicD   = data.topicData;
                let teacherD = data.teacherNames;
                let topic    = '';
                let teacher  = '';
                console.log( data );
                jQuery('#tttopicID').html(data.topics);
                jQuery('#ttteacherID').html(data.teacherNames);
            }
        });
    });


    initializeDatatable('#timetableList', 'wl-mim-fetch-timetable');
    // initializeDatatable('#viewtimetableList', 'wl-mim-view-timetable');
    $('#myTable').DataTable({
    buttons: [
      { text: 'PDF', extend: 'pdf' },
      { text: 'Print', extend: 'print' },
      { text: 'CSV', extend: 'csv' }
    ],
    dom: 'Bfrtip'
  });
        //add time table:- wl-mim-timetable
        jQuery("#wim_add_timetable").on('submit', function(e) {
            e.preventDefault();
            var form = jQuery(this);
            jQuery.ajax({
                url: ajaxurl+"?action=wl-mim-timetable",
                type: "post",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(response){
                    if( response.success == true ) {
                        toastr.success(response.data.message);
                        jQuery('#add-timetable'). modal('hide');
                        jQuery('#wim_add_timetable')[0].reset();
                        jQuery("#timetableList").DataTable().ajax.reload();
                    }
                    else if( response.success == false ){
                        jQuery.each( response.data, function( key, value ) {
                            //console.log( key + ": " + value );
                            var msg = '<label class="text-danger" for="'+key+'">'+value+'</label>';
                            jQuery('input[name="' + key + '"], select[name="' + key + '"]').addClass('is-invalid').after(msg);
                        });
                        var keys = Object.keys(response);
                        jQuery('input[name="'+keys[0]+'"]').focus();
                    }
                    jQuery('#wim_add_timetable')[0].reset();
                }
            })
        });

        //time table modal wl-mim-fetch-timetablemodal
        jQuery(document).on('show.bs.modal', '#update-timetable', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-timetablemodal',
                data: 'id=' + id,
                dataType: 'json',
                success: function (response) {
                    // console.table(response.data.html);
                    // var data = JSON.parse(response.data.json);
                    jQuery('#fetch_timetable').html(response.data.html);
                    // jQuery('.selectpicker').selectpicker();
                }
            });
        });

        /* Time table show on modal*/
        jQuery(document).on('show.bs.modal', '#view-timetable', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-view-timetable',
                data: 'id=' + id,
                dataType: 'json',
                success: function (response) {
                    let tableData = response.data;
                    // var data = JSON.parse(response.data.json);
                    tableData.forEach((element) => {
                        // jQuery('#viewtimetableList tbody').append(tableData);
                        jQuery('#viewtimetableList').find('tbody').html(tableData);
                      });
                }
            });
        });

        //Get the room on date and time change
        jQuery('#wlim_tt_class_endTime').on('change', function(e){
            e.preventDefault();
            let endtime   = jQuery(this).val();
            let starttime = jQuery('#wlim_tt_class_startTime').val();
            let classDate = jQuery('#wlim_tt_class_date').val();
            // console.log(endtime);
            console.log(`end time ${endtime} start time ${starttime}`);
            let roomhtml   = jQuery('#ttroomID');
            // roomhtml.selectpicker();
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'wl-mim-getRoom-timetable',
                    nonce: WLIMAjax.security,
                    classDate: classDate,
                    starttime: starttime,
                    endtime: endtime,
                },
                success: function(response){
                    // console.log(response);
                    roomhtml.empty();
                    let data = jQuery.parseJSON(response);
                    let roomD = data.roomlist;
                    let rooms = '';
                    // console.log(roomD);
                    rooms += roomhtml.append(`<option value="">Select Studio</option>`);
                    roomD.forEach(function(item){
                        // console.log(item);
                        rooms += roomhtml.append(`<option value="${item.id}">${item.room_name}( ${item.room_desc} )</option>`);
                    });
                }
            });
        });

    //update time table
    jQuery('#wim_update_timetable').on('submit', function(e){
        e.preventDefault();
        jQuery.ajax({
            url: ajaxurl+"?action=wl-mim-update-timetable",
            type: "post",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response){
                if( response.success == true ) {
                    toastr.success(response.data.message);
                   // jQuery('#addTopic')[0].reset();
                   jQuery('#update-timetable'). modal('hide');
                   jQuery("#timetableList").DataTable().ajax.reload();
                }
            }
        })
     });

      //save the remark
      jQuery(document).on('submit', '#teacherRemark', function(e){
        e.preventDefault();
        let teacherRemark = jQuery( '#teacherRemark' ).val();
        let timeTableID   = jQuery( '#timeTableID' ).val();
        // console.log( `the teachers remark is ${teacherRemark} and the time table id is ${timeTableID}` );
        jQuery.ajax({
            // url: ajaxurl+'?action=wl-mim-update-teacherRemark'+ '&security='+WLIMAjax.security,
            url: ajaxurl + '?action=wl-mim-update-teacherRemark',
            type: "post",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response){
                if( response.success == true ) {
                    toastr.success(response.data.message);
                   // jQuery('#addTopic')[0].reset();
                   jQuery('#update-timetable'). modal('hide');
                   jQuery("#timetableList").DataTable().ajax.reload();
                }
            }
        });
     });

     //Save the student remark
     //wl-mim-student-timetableRemark
     jQuery(document).on('submit', '#studentRemarkPost', function(e){
        e.preventDefault();
        // console.log( `the teachers remark is ${teacherRemark} and the time table id is ${timeTableID}` );
        jQuery.ajax({
            url: ajaxurl + '?action=wl-mim-student-timetableRemark',
            type: "post",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response){
                if( response.success == true ) {
                    toastr.success(response.data.message);
                   // jQuery('#addTopic')[0].reset();
                   jQuery("#student_timetableList").DataTable().ajax.reload();
                }
            }
        });
     });

     remove('.delete-timetable', 'delete-timetable-id', 'delete-timetable-security', 'delete-timetable', 'wl-mim-delete-timetable', ['#timetableList']);

        jQuery(document).on('submit','#wlim-view-overall-report-form', function(e) {
            e.preventDefault();
            var data = jQuery('#wlim-view-overall-report-form').serialize();
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-view-overall-report',
                data: data,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#wlim-view-overall-report').html(response.data.html);
                    if (data.element) {
                        if ('#current-students-table-report' === data.element) {
                            jQuery(data.element).DataTable({
                                aaSorting: [],
                                responsive: true,
                                lengthChange: false,
                                dom: 'lBfrtip',
                                select: true,
                                buttons: [
                                    'pageLength',
                                    {
                                        extend: 'excel',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'csv',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'pdf',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print all',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print selected',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                        }
                                    },
                                    'colvis'
                                ]
                            });

                        }else if ('#pending-fees-by-batch-table-report' === data.element) {
                            jQuery(data.element).DataTable({
                                aaSorting: [],
                                responsive: true,
                                lengthChange: false,
                                dom: 'lBfrtip',
                                select: true,
                                buttons: [
                                    'pageLength',
                                    {
                                        extend: 'excel',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'csv',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'pdf',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print all',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print selected',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                        }
                                    },
                                    'colvis'
                                ]
                            });

                        }else if ('#expense-table-report' === data.element) {
                            jQuery(data.element).DataTable({
                                aaSorting: [],
                                responsive: true,
                                lengthChange: false,
                                dom: 'lBfrtip',
                                select: true,
                                buttons: [
                                    'pageLength',
                                    {
                                        extend: 'excel',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'csv',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'pdf',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print all',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print selected',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4],
                                        }
                                    },
                                    'colvis'
                                ]
                            });

                        } else if ('#attendance-by-batch-table-report' === data.element) {
                            jQuery(data.element).DataTable({
                                aaSorting: [],
                                responsive: true,
                                lengthChange: false,
                                dom: 'lBfrtip',
                                select: true,
                                buttons: [
                                    'pageLength',
                                    {
                                        extend: 'excel',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'csv',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'pdf',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print all',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print selected',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
                                        }
                                    },
                                    'colvis'
                                ]
                            });

                        }else if ('#enquiries-table-report' === data.element) {
                            jQuery(data.element).DataTable({
                                aaSorting: [],
                                responsive: true,
                                lengthChange: false,
                                dom: 'lBfrtip',
                                select: true,
                                buttons: [
                                    'pageLength',
                                    {
                                        extend: 'excel',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'csv',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'pdf',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print all',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print selected',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                                        }
                                    },
                                    'colvis'
                                ]
                            });

                        }else if ('#fees-collection-table-report' === data.element) {
                            jQuery(data.element).DataTable({
                                aaSorting: [],
                                responsive: true,
                                lengthChange: false,
                                dom: 'lBfrtip',
                                select: true,
                                buttons: [
                                    'pageLength',
                                    {
                                        extend: 'excel',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'csv',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'pdf',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print all',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print selected',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7],
                                        }
                                    },
                                    'colvis'
                                ]
                            });

                        }else if ('#outstanding-fees-table-report' === data.element) {
                            jQuery(data.element).DataTable({
                                aaSorting: [],
                                responsive: true,
                                lengthChange: false,
                                dom: 'lBfrtip',
                                select: true,
                                buttons: [
                                    'pageLength',
                                    {
                                        extend: 'excel',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'csv',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'pdf',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print all',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print selected',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5],
                                        }
                                    },
                                    'colvis'
                                ]
                            });

                        }
                        else if ('#students-drop-out-table-report' === data.element) {
                            jQuery(data.element).DataTable({
                                aaSorting: [],
                                responsive: true,
                                lengthChange: false,
                                dom: 'lBfrtip',
                                select: true,
                                buttons: [
                                    'pageLength',
                                    {
                                        extend: 'excel',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'csv',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'pdf',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print all',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print selected',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                        }
                                    },
                                    'colvis'
                                ]
                            });

                        } else if ('#student-registrations-table-report' === data.element) {
                            jQuery(data.element).DataTable({
                                aaSorting: [],
                                responsive: true,
                                lengthChange: false,
                                dom: 'lBfrtip',
                                select: true,
                                buttons: [
                                    'pageLength',
                                    {
                                        extend: 'excel',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'csv',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'pdf',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print all',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                            modifier: {
                                                selected: null
                                            }
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Print selected',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                        }
                                    },
                                    'colvis'
                                ]
                            });

                        } else {
                            jQuery(data.element).DataTable({
                                aaSorting: [],
                                responsive: true
                            });
                        }

                        if (data.element == '#pending-fees-by-batch-table-report') {
                            jQuery('#print-id-cards').appendTo("body");
                        }
                    }
                }
            });
        });

        // Staff: Distribute certificate.
		var distributeCertificateFormId = '#wl-mim-distribute-certificate-form';
		var distributeCertificateForm = $(distributeCertificateFormId);
		var distributeCertificateBtn = $('#wl-mim-distribute-certificate-btn');
		distributeCertificateForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(distributeCertificateBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, distributeCertificateFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reset') && response.data.reset) {
						distributeCertificateForm[0].reset();
					}
					window.location.href = response.data.url;
				} else {
					wlsmDisplayFormErrors(response, distributeCertificateFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, distributeCertificateFormId, distributeCertificateBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(distributeCertificateBtn);
			}
        });




        /* Select stuadents */
        jQuery("#wl_mim_batch").on("changed.bs.select",
            function(e, clickedIndex, newValue, oldValue) {
                    var data = jQuery('#wlim-notification-configure-form').serialize();
                    var formData = new FormData();
                    var students = $('#wl_min_student');
                    var batch_id = this.value;
                    formData.append('batch_id', newValue);
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-get-students&batch_id='+ batch_id,
                data: data,
                dataType: 'json',
                success: function (response) {
                    var data = (response.data);
                    students.html('');
                    console.log(response.data);
                    // cada array del parametro tiene un elemento index(concepto) y un elemento value(el  valor de concepto)
                    $.each(data, function(index, value) {
                      // darle un option con los valores asignados a la variable select
                      students.append('<option value="' + value.id + '">' + value.first_name + '</option>');
                    });
                    students.selectpicker("refresh");
                }
            });
        });

        jQuery("#wl_mim_course").on("changed.bs.select",
            function(e, clickedIndex, newValue, oldValue) {
                    var data = jQuery('#wlim-notification-configure-form').serialize();
                    var formData = new FormData();
                    var batches = $('#wl_mim_batch');
                    var course_id = this.value;
                    formData.append('course_id', newValue);
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-get-batches&course_id='+course_id,
                data: data,
                dataType: 'json',
                success: function (response) {
                    var data = (response.data);
                    batches.html('');
                    // cada array del parametro tiene un elemento index(concepto) y un elemento value(el  valor de concepto)
                    batches.append('<option value="' + 0 + '">' +'Select Batch'+ '</option>');
                    $.each(data, function(index, value) {
                      // darle un option con los valores asignados a la variable select
                      batches.append('<option value="' + value.id + '">' + value.batch_name +' ['+value.batch_code +']'+ '</option>');
                    });
                    batches.selectpicker("refresh");
                }
            });
        });

        /* Actions for notifications */
        jQuery(document).on('change', '#wlim-notification_by', function () {
            jQuery('span.text-danger').remove();
            var data = jQuery('#wlim-notification-configure-form').serialize();
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-notification-configure',
                data: data,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#wlim-notification-configure').html(response.data.html);
                    var notification_by = data.notification_by;
                    if (notification_by == 'by-batch') {
                        if (data.element) {
                            /* Select single option */
                            jQuery(data.element).selectpicker({
                                liveSearch: true
                            });
                        }
                    } else if (notification_by == 'by-course') {
                        if (data.element) {
                            /* Select single option */
                            jQuery(data.element).selectpicker({
                                liveSearch: true
                            });
                        }
                    } else if (notification_by == 'by-pending-fees') {
                        if (data.element) {
                            /* Select multiple option */
                            jQuery(data.element).selectpicker({
                                liveSearch: true,
                                actionsBox: true
                            });
                        }
                        jQuery('#wlim-students').selectpicker('selectAll');
                    } else if (notification_by == 'by-active-students') {
                        if (data.element) {
                            /* Select multiple option */
                            jQuery(data.element).selectpicker({
                                liveSearch: true,
                                actionsBox: true
                            });
                        }
                    } else if (notification_by == 'by-inactive-students') {
                        if (data.element) {
                            /* Select multiple option */
                            jQuery(data.element).selectpicker({
                                liveSearch: true,
                                actionsBox: true
                            });
                            jQuery('#wlim-students').selectpicker('selectAll');
                        }
                    } else if (notification_by == 'by-individual-students') {
                        if (data.element) {
                            /* Select multiple option */
                            jQuery(data.element).selectpicker({
                                liveSearch: true,
                                actionsBox: true
                            });
                        }
                    }
                }
            });
        });
        jQuery('.send-notification-submit').mousedown(function () {
            tinyMCE.triggerSave();
        });
        saveWithFiles('.send-notification-submit', '#send-notification-form', null, [], false);


        // Fetch cetificate table
        initializeDatatable('#wl-mim-certificates-table', 'wl-mim-fetch-certificates');

        const certificateData = {
            'certificate': $('#wl-mim-certificates-distributed-table').data('certificate')
        }

        initializeDatatable('#wl-mim-certificates-distributed-table', 'wl-mim-fetch-certificates-distributed', certificateData);
        // var cert_id = jQuery('#wl-mim-certificates-distributed-table').data('certificate');


        // var certificatesDistributedTable = $('#wl-mim-certificates-distributed-table');
        // var certificate = certificatesDistributedTable.data('certificate');
		// var nonce = certificatesDistributedTable.data('nonce');
		// if ( certificate && nonce ) {
		// 	var data = {'action': 'wl-mim-fetch-certificates-distributed', 'certificate': certificate };
        //     data['certificate-' + certificate] = nonce;

		// 	initializeDatatable(certificatesDistributedTable, data);
		// }


        /* Actions for noticeboard */
        initializeDatatable('#notice-table', 'wl-mim-get-notice-data');
        saveWithFiles('.add-notice-submit', '#wlim-add-notice-form', '#add-notice', ['#notice-table']);
        jQuery(document).on('show.bs.modal', '#update-notice', function (e) {
            var id = jQuery(e.relatedTarget).data('id');
            jQuery.ajax({
                type: 'post',
                url: ajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-notice',
                data: 'id=' + id,
                success: function (response) {
                    var data = JSON.parse(response.data.json);
                    jQuery('#fetch_notice').html(response.data.html);
                    if (data.link_to_url) {
                        jQuery('.wlim-notice-attachment').hide();
                        jQuery('.wlim-notice-url').show();
                        jQuery("input[name=notice_link_to][value='url']").prop("checked", true);
                    } else {
                        jQuery('.wlim-notice-url').hide();
                        jQuery('.wlim-notice-attachment').show();
                        jQuery("input[name=notice_link_to][value='attachment']").prop("checked", true);
                    }
                }
            });
        });
        saveWithFiles('.update-notice-submit', '#wlim-update-notice-form', '#update-notice', ['#notice-table']);
        remove('.delete-notice', 'delete-notice-id', 'delete-notice-security', 'delete-notice', 'wl-mim-delete-notice', ['#notice-table']);

        /* Actions for payments */
        jQuery('#wlim-pay-fees').ajaxForm({
            success: function (response) {
                jQuery('.pay-fees-submit').prop('disabled', false);
                if (response.success) {
                    var data = JSON.parse(response.data.json);
                    jQuery('span.text-danger').remove();
                    jQuery(".is-valid").removeClass("is-valid");
                    jQuery(".is-invalid").removeClass("is-invalid");
                    jQuery('#wlim-pay-fees')[0].reset();
                    jQuery('.wlim-pay-fees-now').html(response.data.html);
                    if (data.payment_method == 'razorpay') {
                        var amount_paid = JSON.parse(data.amount_paid);
                        var options = {
                            'key': data.razorpay_key,
                            'amount': data.amount_in_paisa,
                            'currency': data.currency,
                            'name': data.institute_name,
                            'description': data.description,
                            'image': data.institute_logo,
                            'handler': function (response) {
                                var razorpayData = {
                                    action: 'wl-mim-pay-razorpay',
                                    security: data.security,
                                    payment_id: response.razorpay_payment_id,
                                    amount: data.amount_in_paisa,
                                    invoice_id: data.invoice_id
                                };
                                jQuery.ajax({
                                    type: 'POST',
                                    url: ajaxurl,
                                    data: razorpayData,
                                    success: function (response) {
                                        if (response.success) {
                                            toastr.success(response.data.message);
                                            location.reload();
                                        } else {
                                            toastr.error(response.data);
                                        }
                                    },
                                    error: function (response) {
                                        toastr.error(response.statusText);
                                    },
                                    dataType: 'json'
                                });
                            },
                            'prefill': {
                                'name': data.name,
                                'email': data.email
                            },
                            'notes': {
                                'address': data.address,
                                'student_id': data.student_id,
                                // data.amount_paid
                            },
                            'theme': {
                                'color': '#F37254'
                            }
                        };
                        // jQuery.each(amount_paid, function (key, value) {
                        //     options.notes['fee_' + (key + 1)] = value;
                        // });
                        var rzp1 = new Razorpay(options);
                        document.getElementById('rzp-button1').onclick = function (e) {
                            rzp1.open();
                            e.preventDefault();
                        }
                    } else if (data.payment_method == 'instamojo') {
                        var amount_paid = JSON.parse(data.amount_paid);
                    }
                    else if (data.payment_method == 'paystack') {
                        var amount_paid = JSON.parse(data.amount_paid);

                        var custom_fields_obj = {
                            display_name: data.institute_name,
                            student_id: data.student_id,
                            amount: parseFloat(data.amount_x_100)
                        };

                        var ptk = PaystackPop.setup({
                            key: data.paystack_key,
                            email: data.email,
                            amount: data.amount_x_100,
                            currency: data.currency,
                            metadata: {
                                custom_fields: [
                                    custom_fields_obj
                                ]
                            },
                            callback: function(response) {
                                var paystackData = {
                                    'action': 'wl-mim-pay-paystack',
                                    'security': data.security,
                                    'student_id': data.student_id,
                                    'amount': parseFloat(data.amount_x_100),
                                    'reference': response.reference
                                };

                                jQuery.each(amount_paid, function (key, value) {
                                    paystackData['fee_' + (key + 1)] = value;
                                });

                                jQuery.ajax({
                                    type: 'POST',
                                    url: ajaxurl,
                                    data: paystackData,
                                    success: function (response) {
                                        if (response.success) {
                                            toastr.success(response.data.message);
                                            location.reload();
                                        } else {
                                            toastr.error(response.data);
                                        }
                                    },
                                    error: function (response) {
                                        toastr.error(response.statusText);
                                    },
                                    dataType: 'json'
                                });
                            },
                            onClose: function() {
                            }
                        });

                        // Open Paystack payment window.
                        $(document).on('click', '#paystack-btn', function(e) {
                            ptk.openIframe();
                            e.preventDefault();
                        });

                    } else if (data.payment_method == 'stripe') {
                        var amount_paid = JSON.parse(data.amount_paid);
                        var handler = StripeCheckout.configure({
                            key: data.stripe_key,
                            image: data.institute_logo,
                            token: function (token) {
                                var stripeToken = token.id;
                                var stripeEmail = token.email;
                                var stripeData = {
                                    action: 'wl-mim-pay-stripe',
                                    security: data.security,
                                    stripeToken: stripeToken,
                                    stripeEmail: stripeEmail,
                                    stripeInvoiceId: data.invoice_id,
                                };
                                jQuery.each(amount_paid, function (key, value) {
                                    stripeData['fee_' + (key + 1)] = value;
                                });
                                jQuery.ajax({
                                    type: 'POST',
                                    url: ajaxurl,
                                    data: stripeData,
                                    success: function (response) {
                                        if (response.success) {
                                            toastr.success(response.data.message);
                                            location.reload();
                                        } else {
                                            toastr.error(response.data);
                                        }
                                    },
                                    error: function (response) {
                                        toastr.error(response.statusText);
                                    },
                                    dataType: 'json'
                                });
                            }
                        });
                        jQuery('#stripe-button').on('click', function (e) {
                            // Open Checkout with further options
                            handler.open({
                                name: data.name,
                                description: data.description,
                                currency: data.currency,
                                amount: data.amount_in_cents
                            });
                            e.preventDefault();
                        });
                        // Close Checkout on page navigation
                        jQuery(window).on('popstate', function () {
                            handler.close();
                        });
                    }
                } else {
                    jQuery('span.text-danger').remove();
                    if (response.data && jQuery.isPlainObject(response.data)) {
                        jQuery('#wlim-pay-fees :input').each(function () {
                            var input = this;
                            jQuery(input).removeClass('is-valid');
                            jQuery(input).removeClass('is-invalid');
                            if (response.data[input.name]) {
                                var errorSpan = '<span class="text-danger">' + response.data[input.name] + '</span>';
                                jQuery(input).addClass('is-invalid');
                                jQuery(errorSpan).insertAfter(input);
                            } else {
                                jQuery(input).addClass('is-valid');
                            }
                        });
                    } else {
                        var errorSpan = '<span class="text-danger ml-3 mt-3">' + response.data + '<hr></span>';
                        jQuery(errorSpan).insertBefore('#wlim-pay-fees');
                        toastr.error(response.data);
                    }
                }
            },
            error: function (response) {
                jQuery('.pay-fees-submit').prop('disabled', false);
                toastr.error(response.statusText);
            },
            uploadProgress(event, progress, total, percentComplete) {
                jQuery('#wlim-progress').text(percentComplete);
            }
        });

        /* Action to get student attendance */
        jQuery(document).on('submit', '#pay-with-instamojo', function (e) {
            e.preventDefault();
            var selector = '#instamojo-pay-btn';
            var form = '#pay-with-instamojo';
            var formData = {};
            if (form) {
                formData = jQuery(form).serializeObject();
            }

            jQuery(selector).prop('disabled', true);
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: formData,
                success: function (response) {
                    console.log(response);
                    jQuery(selector).prop('disabled', false);
                    if (response.success) {
                        // Simulate an HTTP redirect:
                        if (response.data.status == 'Pending') {
                            var Instamojo_response = Instamojo.open(response.data.longurl);
                        }
                    }
                },
                error: function (response) {
                    // console.log(response);
                    jQuery(selector).prop('disabled', false);
                    toastr.error(response.statusText);
                },
                dataType: 'json'
            });


            // save('#instamojo-pay-btn', 'wl-mim-pay-instamojo', '#pay-with-instamojo');
            // // Staff: Save certificate.
            // var payInstamojoFormId = '#pay-with-instamojo';
            // var payInstamojoForm = $(payInstamojoFormId);
            // var payInstamojoBtn = $('#instamojo-pay-btn');
            // payInstamojoForm.ajaxForm({
            //     beforeSubmit: function(arr, $form, options) {
            //         return wlsmBeforeSubmit(payInstamojoBtn);
            //     },
            //     success: function(response) {
            //         if(response.success) {
            //             wlsmShowSuccessAlert(response.data.message, payInstamojoFormId);
            //             toastr.success(response.data.message);
            //             if(response.data.hasOwnProperty('reset') && response.data.reset) {
            //                 payInstamojoForm[0].reset();
            //             }
            //             window.location.href = response.data.url;
            //         } else {
            //             wlsmDisplayFormErrors(response, payInstamojoFormId);
            //         }
            //     },
            //     error: function(response) {
            //         wlsmDisplayFormError(response, payInstamojoFormId, payInstamojoBtn);
            //     },
            //     complete: function(event, xhr, settings) {
            //         wlsmComplete(payInstamojoBtn);
            //     }
            // });

        });


        /* Actions for payments */
        jQuery('#pay-with-instamojo').ajaxForm({
            success: function (response) {
                jQuery('.pay-fees-submit').prop('disabled', false);
                if (response.success) {
                    var data = JSON.parse(response.data.json);
                    console.log(response);
                    return;

                    jQuery('span.text-danger').remove();
                    jQuery(".is-valid").removeClass("is-valid");
                    jQuery(".is-invalid").removeClass("is-invalid");
                    jQuery('#wlim-pay-fees')[0].reset();
                    jQuery('.wlim-pay-fees-now').html(response.data.html);
                    if (data.payment_method == 'razorpay') {
                        var amount_paid = JSON.parse(data.amount_paid);
                        var options = {
                            'key': data.razorpay_key,
                            'amount': data.amount_in_paisa,
                            'currency': data.currency,
                            'name': data.institute_name,
                            'description': data.description,
                            'image': data.institute_logo,
                            'handler': function (response) {
                                var razorpayData = {
                                    action: 'wl-mim-pay-razorpay',
                                    security: data.security,
                                    payment_id: response.razorpay_payment_id,
                                    amount: data.amount_in_paisa
                                };
                                jQuery.ajax({
                                    type: 'POST',
                                    url: ajaxurl,
                                    data: razorpayData,
                                    success: function (response) {
                                        if (response.success) {
                                            toastr.success(response.data.message);
                                            location.reload();
                                        } else {
                                            toastr.error(response.data);
                                        }
                                    },
                                    error: function (response) {
                                        toastr.error(response.statusText);
                                    },
                                    dataType: 'json'
                                });
                            },
                            'prefill': {
                                'name': data.name,
                                'email': data.email
                            },
                            'notes': {
                                'address': data.address,
                                'student_id': data.student_id,
                                // data.amount_paid
                            },
                            'theme': {
                                'color': '#F37254'
                            }
                        };
                        jQuery.each(amount_paid, function (key, value) {
                            options.notes['fee_' + (key + 1)] = value;
                        });
                        var rzp1 = new Razorpay(options);
                        document.getElementById('rzp-button1').onclick = function (e) {
                            rzp1.open();
                            e.preventDefault();
                        }
                    } else if (data.payment_method == 'instamojo') {
                        var amount_paid = JSON.parse(data.amount_paid);
                        console.log(data);
                        var options = {
                            'key': data.instamojo_key,
                            'amount': data.amount_in_paisa,
                            'currency': data.currency,
                            'name': data.institute_name,
                            'description': data.description,
                            'image': data.institute_logo,
                            'handler': function (response) {
                                var instamojoData = {
                                    action: 'wl-mim-pay-instamojo',
                                    security: data.security,
                                    payment_id: response.instamojo_payment_id,
                                    amount: data.amount_in_paisa
                                };
                                jQuery.ajax({
                                    type: 'POST',
                                    url: ajaxurl,
                                    data: instamojoData,
                                    success: function (response) {
                                        if (response.success) {
                                            toastr.success(response.data.message);
                                            location.reload();
                                        } else {
                                            toastr.error(response.data);
                                        }
                                    },
                                    error: function (response) {
                                        toastr.error(response.statusText);
                                    },
                                    dataType: 'json'
                                });
                            },
                            'prefill': {
                                'name': data.name,
                                'email': data.email
                            },
                            'notes': {
                                'address': data.address,
                                'student_id': data.student_id,
                                // data.amount_paid
                            },
                            'theme': {
                                'color': '#F37254'
                            }
                        };
                        jQuery.each(amount_paid, function (key, value) {
                            options.notes['fee_' + (key + 1)] = value;
                        });
                        var rzp1 = new Razorpay(options);
                        document.getElementById('rzp-button1').onclick = function (e) {
                            rzp1.open();
                            e.preventDefault();
                        }
                    }
                    else if (data.payment_method == 'paystack') {
                        var amount_paid = JSON.parse(data.amount_paid);

                        var custom_fields_obj = {
                            display_name: data.institute_name,
                            student_id: data.student_id,
                            amount: parseFloat(data.amount_x_100)
                        };

                        var ptk = PaystackPop.setup({
                            key: data.paystack_key,
                            email: data.email,
                            amount: data.amount_x_100,
                            currency: data.currency,
                            metadata: {
                                custom_fields: [
                                    custom_fields_obj
                                ]
                            },
                            callback: function(response) {
                                var paystackData = {
                                    'action': 'wl-mim-pay-paystack',
                                    'security': data.security,
                                    'student_id': data.student_id,
                                    'amount': parseFloat(data.amount_x_100),
                                    'reference': response.reference
                                };

                                jQuery.each(amount_paid, function (key, value) {
                                    paystackData['fee_' + (key + 1)] = value;
                                });

                                jQuery.ajax({
                                    type: 'POST',
                                    url: ajaxurl,
                                    data: paystackData,
                                    success: function (response) {
                                        if (response.success) {
                                            toastr.success(response.data.message);
                                            location.reload();
                                        } else {
                                            toastr.error(response.data);
                                        }
                                    },
                                    error: function (response) {
                                        toastr.error(response.statusText);
                                    },
                                    dataType: 'json'
                                });
                            },
                            onClose: function() {
                            }
                        });

                        // Open Paystack payment window.
                        $(document).on('click', '#paystack-btn', function(e) {
                            ptk.openIframe();
                            e.preventDefault();
                        });

                    } else if (data.payment_method == 'stripe') {
                        var amount_paid = JSON.parse(data.amount_paid);
                        var handler = StripeCheckout.configure({
                            key: data.stripe_key,
                            image: data.institute_logo,
                            token: function (token) {
                                var stripeToken = token.id;
                                var stripeEmail = token.email;
                                var stripeData = {
                                    action: 'wl-mim-pay-stripe',
                                    security: data.security,
                                    stripeToken: stripeToken,
                                    stripeEmail: stripeEmail,
                                    // data.amount_paid
                                };
                                jQuery.each(amount_paid, function (key, value) {
                                    stripeData['fee_' + (key + 1)] = value;
                                });
                                jQuery.ajax({
                                    type: 'POST',
                                    url: ajaxurl,
                                    data: stripeData,
                                    success: function (response) {
                                        if (response.success) {
                                            toastr.success(response.data.message);
                                            location.reload();
                                        } else {
                                            toastr.error(response.data);
                                        }
                                    },
                                    error: function (response) {
                                        toastr.error(response.statusText);
                                    },
                                    dataType: 'json'
                                });
                            }
                        });
                        jQuery('#stripe-button').on('click', function (e) {
                            // Open Checkout with further options
                            handler.open({
                                name: data.name,
                                description: data.description,
                                currency: data.currency,
                                amount: data.amount_in_cents
                            });
                            e.preventDefault();
                        });
                        // Close Checkout on page navigation
                        jQuery(window).on('popstate', function () {
                            handler.close();
                        });
                    }
                } else {
                    jQuery('span.text-danger').remove();
                    if (response.data && jQuery.isPlainObject(response.data)) {
                        jQuery('#wlim-pay-fees :input').each(function () {
                            var input = this;
                            jQuery(input).removeClass('is-valid');
                            jQuery(input).removeClass('is-invalid');
                            if (response.data[input.name]) {
                                var errorSpan = '<span class="text-danger">' + response.data[input.name] + '</span>';
                                jQuery(input).addClass('is-invalid');
                                jQuery(errorSpan).insertAfter(input);
                            } else {
                                jQuery(input).addClass('is-valid');
                            }
                        });
                    } else {
                        var errorSpan = '<span class="text-danger ml-3 mt-3">' + response.data + '<hr></span>';
                        jQuery(errorSpan).insertBefore('#wlim-pay-fees');
                        toastr.error(response.data);
                    }
                }
            },
            error: function (response) {
                jQuery('.pay-fees-submit').prop('disabled', false);
                toastr.error(response.statusText);
            },
            uploadProgress(event, progress, total, percentComplete) {
                jQuery('#wlim-progress').text(percentComplete);
            }
        });

        /* Action to get student attendance */
        jQuery(document).on('submit', '#wlim-student-attendance-form', function (e) {
            e.preventDefault();
            var data = jQuery('#wlim-student-attendance-form').serialize();
            fetchRecords('wl-mim-get-student-attendance', '#wlim-get-student-attendance', data);
        });

        /* Action to get student admit card */
        jQuery(document).on('submit', '#wlim-student-admit-card-form', function (e) {
            e.preventDefault();
            var data = jQuery('#wlim-student-admit-card-form').serialize();
            fetchRecords('wl-mim-get-student-admit-card', '#wlim-get-student-admit-card', data);
        });

        /* Action to get student exam result */
        jQuery(document).on('submit', '#wlim-student-exam-result-form', function (e) {
            e.preventDefault();
            var data = jQuery('#wlim-student-exam-result-form').serialize();
            fetchRecords('wl-mim-get-student-exam-result', '#wlim-get-student-exam-result', data);
        });

        /* Action to view admit card */
        jQuery(document).on('click', '.view-admit-card-submit', function () {
            jQuery('span.text-danger').remove();
            var data = jQuery('#wlim-view-admit-card-form').serialize();
            fetchRecords('wl-mim-view-admit-card', '#wlim-view-admit-card', data);
        });

        /* Action to get batch students for attendance */
        jQuery(document).on('click', '#wlmim-get-students-attendance', function () {
            jQuery('span.text-danger').remove();
            var data = jQuery('#wlim-add-attendance-form').serialize();
            var data = data.split('add-attendance')[0];
            fetchRecords('wl-mim-attendance-batch-students', '#wlim-attendance-batch-students', data);
        });

        /* Action to save attendance */
        saveWithFiles('.add-attendance-submit', '#wlim-add-attendance-form', null, [], false);

        /* Reset plugin */
        var loaderContainer = jQuery('<span/>', {
            'class': 'wlim-loader ml-2'
        });
        var loader = jQuery('<img/>', {
            'src': WL_MIM_ADMIN_URL + 'images/spinner.gif',
            'class': 'wlim-loader-image mb-1'
        });
        jQuery('#wlim-reset-plugin-form').ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                var message = jQuery('.wlim-reset-plugin-button').data('message');
                if(confirm(message)) {
                    /* Disable submit button */
                    jQuery('.wlim-reset-plugin-button').prop('disabled', true);
                    /* Show loading spinner */
                    loaderContainer.insertAfter(jQuery('.wlim-reset-plugin-button'));
                    loader.appendTo(loaderContainer);
                    return true;
                }
                return false;
            },
            success: function(response) {
                toastr.success(response.data.message);
            },
            error: function(response) {
                toastr.error(response.statusText);
            },
            complete: function(event, xhr, settings) {
                /* Enable submit button */
                jQuery('.wlim-reset-plugin-button').prop('disabled', false);
                /* Hide loading spinner */
                loaderContainer.remove();
            }
        });
    });
   // Loading icon variables.
		var loaderContainer = $('<span/>', {
			'class': 'wlsm-loader ml-2'
		});
		var loader = $('<img/>', {
			'src': 'images/spinner.gif',
			'class': 'wlsm-loader-image mb-1'
		});

    // Function: Before Submit.
		function wlsmBeforeSubmit(button) {
			$('div.text-danger').remove();
			$(".is-invalid").removeClass("is-invalid");
			$('.wlsm .alert-dismissible').remove();
			button.prop('disabled', true);
			loaderContainer.insertAfter(button);
			loader.appendTo(loaderContainer);
			return true;
        }

        	// Function: Show Success Alert.
		function wlsmShowSuccessAlert(message, formId) {
			var alertBox = '<div class="mt-2 alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="wlsm-font-bold"><i class="fa fa-check"></i> &nbsp;' + message + '</span></div>';
			$(alertBox).insertBefore(formId);
        }

        // Function: Complete.
		function wlsmComplete(button) {
			button.prop('disabled', false);
			loaderContainer.remove();
		}



    // Function: Display Form Erros.
    function wlsmDisplayFormErrors(response, formId) {
        if(response.data && $.isPlainObject(response.data)) {
            $(formId + ' :input').each(function() {
                var input = this;
                $(input).removeClass('is-invalid');
                if(response.data[input.name]) {
                    var errorSpan = '<div class="text-danger mt-1">' + response.data[input.name] + '</div>';
                    $(input).addClass('is-invalid');
                    $(errorSpan).insertAfter(input);
                }
            });
        } else {
            var errorSpan = '<div class="text-danger mt-3">' + response.data + '<hr></div>';
            $(errorSpan).insertBefore(formId);
            toastr.error(response.data);
        }
    }

    var subHeader = '.wlsm-sub-header-left';
    // Function: Action.
		function wlsmAction(event, element, data, performActions, color = 'red', showLoadingIcon = false) {
			event.preventDefault();
			$('.wlsm .alert-dismissible').remove();
			var button = $(element);
			var title = button.data('message-title');
			var content = button.data('message-content');
			var cancel = button.data('cancel');
			var submit = button.data('submit');
			$.confirm({
				title: title,
				content: content,
				type: color,
				useBootstrap: false,
				buttons: {
					formSubmit: {
						text: submit,
           				btnClass: 'btn-' + color,
						action: function () {
							$.ajax({
								data: data,
								url: ajaxurl,
								type: 'POST',
								beforeSend: function(xhr) {
									$('.wlsm .alert-dismissible').remove();
									if(showLoadingIcon) {
										return wlsmBeforeSubmit(button);
									}
								},
								success: function(response) {
									if(response.success) {
										var alertBox = '<div class="alert alert-success alert-dismissible clearfix" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-check"></i> &nbsp;' + response.data.message + '</strong></div>';
										$(alertBox).insertBefore(subHeader);
										toastr.success(
											response.data.message,
											'',
											{
												timeOut: 600,
												fadeOut: 600,
												closeButton: true,
												progressBar: true,
												onHidden: function() {
													performActions(response);
												}
											}
										);
									} else {
										toastr.error(response.data);
										var errorSpan = '<div class="alert alert-danger alert-dismissible clearfix" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>' + response.data + '</strong></div>';
										$(errorSpan).insertBefore(subHeader);
									}
								},
								error: function(response) {
									toastr.error(response.status + ': ' + response.statusText);
									var errorSpan = '<div class="alert alert-danger alert-dismissible clearfix" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>' + response.status + '</strong>: ' + response.statusText + '</div>';
									$(errorSpan).insertBefore(subHeader);
								},
								complete: function(event, xhr, settings) {
									if(showLoadingIcon) {
										wlsmComplete(button);
									}
								},
							});
						}
					},
					cancel: {
						text: cancel,
						action: function () {
							$('.wlsm .alert-dismissible').remove();
						}
					}
				}
			});
        }

        $(document).on('change', '#wl-mim-select-all', function() {
			if($(this).is(':checked')) {
				$('.wl-mim-select-single').prop('checked', true);
			} else {
				$('.wl-mim-select-single').prop('checked', false);
			}
		});

        // Bulk Action.
		$(document).on('click', '.bulk-action-btn', function(event) {
			var button = $(this);
			var nonce = button.data('nonce');
			var tableId = '#' + button.parent().parent().parent().attr('id');
			var bulkActionSelect = $('.bulk-action-select');
			var bulkAction = bulkActionSelect.val();

			var entity = bulkActionSelect.data('entity');

			var bulkValues = $("input[name='bulk_data[]']:checked")
				.map(function() {
					return $(this).val();
				}).get();

			var data = {
				'bulk_action': bulkAction,
				'bulk_values': bulkValues,
				'action': 'wl-mim-bulk-action',
				'entity': entity,
				'nonce': nonce
			};

			var performActions = function() {
				$(tableId).DataTable().ajax.reload(null, false);
			}

			wlsmAction(event, this, data, performActions, 'red', true);
		});

    /// Staff: Delete certificate.
		$(document).on('click', '.wl-mim-delete-certificate', function(event) {
			var certificateId = $(this).data('certificate');
			var nonce = $(this).data('nonce');
			var data = "certificate_id=" + certificateId + "&delete-certificate-" + certificateId + "=" + nonce + "&action=wl-mim-delete-certificate";
			var performActions = function() {
				certificatesTable.DataTable().ajax.reload(null, false);
			}
			wlsmAction(event, this, data, performActions);
        });


           // Staff: Delete certificate distributed.
		$(document).on('click', '.wlsm-delete-certificate-distributed', function(event) {
			event.preventDefault();
			var certificateDistributedId = $(this).data('certificate-distributed');
			var nonce = $(this).data('nonce');
			var data = "certificate_student_id=" + certificateDistributedId + "&delete-certificate-distributed-" + certificateDistributedId + "=" + nonce + "&action=wl-mim-delete-certificate-distributed";
			var performActions = function() {
				certificatesDistributedTable.DataTable().ajax.reload(null, false);
			}
			wlsmAction(event, this, data, performActions);
		});




    // Staff: Save certificate.
		var saveCertificateFormId = '#wl-mim-save-certificate-form';
		var saveCertificateForm = $(saveCertificateFormId);
		var saveCertificateBtn = $('#wl-mim-save-certificate-btn');
		saveCertificateForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveCertificateBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveCertificateFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reset') && response.data.reset) {
						saveCertificateForm[0].reset();
					}
					window.location.href = response.data.url;
				} else {
					wlsmDisplayFormErrors(response, saveCertificateFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveCertificateFormId, saveCertificateBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveCertificateBtn);
			}
		});
})(jQuery);