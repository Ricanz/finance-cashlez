"use strict";

var KTDatatablesServerSide = (function () {
    var dt;

    var initDatatable = function () {
        dt = $("#kt_datatable_example_1").DataTable({
            searchDelay: 200,
            processing: true,
            serverSide: true,
            order: [[3, "desc"]],
            stateSave: true,
            select: {
                style: "os",
                selector: "td:first-child",
                className: "row-selected",
            },
            ajax: {
                url: "/settlement/data",
            },
            columns: [
                { data: "id" },
                { data: "created_at" },
                { data: "processor" },
                { data: "url" },
                { data: "credit_total" },
                { data: "credit_sum" },
                { data: "debit_total" },
                { data: "debit_sum" },
                { data: "created_by" },
                { data: "token_applicant" },
            ],
            columnDefs: [
                {
                    targets: 0,
                    orderable: true,
                    className: "text-start",
                    width: "50px",
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    },
                },
                {
                    targets: 1,
                    orderable: true,
                    className: "text-start",
                    width: "120px",
                    render: function (data, type, row) {
                        return to_date(data);
                    },
                },
                {
                    targets: 4,
                    orderable: true,
                    className: "text-center",
                    width: "30px",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    targets: 5,
                    orderable: true,
                    className: "text-start",
                    width: "150px",
                    render: function (data, type, row) {
                        return to_rupiah(data);
                    },
                },
                {
                    targets: 6,
                    orderable: true,
                    className: "text-center",
                    width: "30px",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    targets: 7,
                    orderable: true,
                    className: "text-start",
                    width: "150px",
                    render: function (data, type, row) {
                        return to_rupiah(data);
                    },
                },
                {
                    targets: 3,
                    orderable: true,
                    className: "text-center",
                    width: "100px",
                    render: function (data, type, row) {
                        return `<a href='${data}'>Dowload File</a>`;
                    },
                },
                {
                    targets: -1,
                    orderable: false,
                    className: "text-center",
                    width: "200px",
                    render: function (data, type, row) {
                        if (!row.is_reconcile) {
                            return `
                                <a href="javascript:void(0)" id="reconcile_${data}" onclick="reconcile('${data}')" class="btn btn-primary btn-active-light-primary btn-sm rounded-sm">
                                    Reconcile
                                </a>
                            `;
                        }
                        return `
                            <div class="d-flex">
                                <a href="/reconcile/${data}/show" class="btn btn-success btn-active-light-success btn-sm rounded-sm">
                                    View
                                </a>
                                <a href="/reconcile/${data}/download" class="btn btn-light-warning btn-sm rounded-sm mx-2">
                                    Download
                                </a>
                            </div>
                        `;
                    },
                },
            ],

            createdRow: function (row, data, dataIndex) {
                $(row)
                    .find("td:eq(4)")
                    .attr("data-filter", data.name);
            },
        }).withIndexColumn();

        dt.on("draw", function () {
            KTMenu.createInstances();
        });
    };

    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector(
            '[data-kt-docs-table-filter="search"]'
        );
        filterSearch.addEventListener("keyup", function (e) {
            dt.search(e.target.value).draw();
        });
    };

    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
        },
    };
})();

function reconcile(token) {
    Swal.fire({
        text: "Are you sure you want to reconcile this data?",
        icon: "warning",
        showCancelButton: true,
        buttonsStyling: false,
        confirmButtonText: "Yes, reconcile!",
        cancelButtonText: "No, cancel",
        customClass: {
            confirmButton: "btn fw-bold btn-primary rounded-sm",
            cancelButton: "btn fw-bold btn-active-light-primary rounded-sm",
        },
    }).then(function (result) {
        if (result.value) {
            $.ajax({
                url: "/reconcile/" + token + '/proceed',
                type: "GET",
                success: function (response) {
                    Swal.fire({
                        text: "You have reconcile the data!",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary rounded-sm",
                        },
                    }).then(function () {
                        window.location.reload();
                    });
                },
                error: function (xhr, status, error) {
                    Swal.fire({
                        text: "Failed to reconcile the record.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary rounded-sm",
                        },
                    });
                },
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire({
                text: "Reconcile is canceled.",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn fw-bold btn-primary rounded-sm",
                },
            });
        }
    });
}

var uploadedFile = null;
var fileUrl = null;

var myDropzone = new Dropzone("#kt_dropzonejs_example_1", {
    url: "/api/file/check",
    paramName: "file",
    maxFiles: 1,
    maxFilesize: 10,
    addRemoveLinks: true,
    accept: function(file, done) {
        uploadedFile = file; // Store the uploaded file
        done();
    },
    success: function(file, response) {
        if (response.success) {
            fileUrl = response.data
            console.log("File berhasil diunggah:", file);
        } else {
            console.error("Gagal mengunggah file:", response.message);
        }
    }
});


$("#store_settlement_form").on("submit", function(event) {
    event.preventDefault();
    var token = $('meta[name="csrf-token"]').attr('content');
    var formData = new FormData(this);
    formData.append('file', uploadedFile);  
    formData.append('url', fileUrl);  
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': token
        },
        type: 'POST',
        data: formData,
        url: '/settlement',
        dataType: 'JSON',
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function() {
            swal.showLoading();
        },
        success: function(data) {
            if (data.status === true) {
                swal.hideLoading();
                swal.fire({
                    text: data.message,
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn font-weight-bold btn-light-primary"
                    }
                }).then(function() {
                    location.href = "/settlement";
                });
            } else {
                var values = '';
                jQuery.each(data.message, function(key, value) {
                    values += value + "<br>";
                });

                swal.fire({
                    text: data.message,
                    html: values,
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn font-weight-bold btn-light-primary"
                    }
                }).then(function() {});
            }
        }
    });
}); 

KTUtil.onDOMContentLoaded(function () {
    KTDatatablesServerSide.init();
});