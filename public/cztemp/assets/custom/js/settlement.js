"use strict";
$("#kt_daterangepicker_1").daterangepicker();
$("#kt_daterangepicker_2").daterangepicker();
$("#kt_daterangepicker_3").daterangepicker();

var KTDatatablesServerSide = (function () {
    var dt;

    var initDatatable = function () {
        dt = $("#kt_datatable_example_1").DataTable({
            searchDelay: 200,
            processing: true,
            serverSide: true,
            order: [[1, "desc"]],
            stateSave: true,
            select: {
                style: "os",
                selector: "td:first-child",
                className: "row-selected",
            },
            ajax: {
                url: `${baseUrl}/settlement/data`,
            },
            columns: [
                { data: "id" },
                { data: "created_at" },
                { data: "start_date" },
                { data: "channel.channel" },
                { data: "url" },
                { data: "debit_total" },
                { data: "debit_sum" },
                { data: "credit_total" },
                { data: "credit_sum" },
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
                        return ` 
                            <div class="text-bold fs-7 text-uppercase">${to_date(data)}</div>
                            <div class="text-bold fs-7">Upload By : ${row.created_by}</div>
                        `;
                    },
                },
                {
                    targets: 2,
                    orderable: true,
                    className: "text-start",
                    width: "300px",
                    render: function (data, type, row) {
                        return ` 
                            <div class="text-bold fs-7">Start : ${to_date(data)}</div>
                            <div class="text-bold fs-7">End : ${to_date(row.end_date)}</div>
                        `;
                    },
                },
                {
                    targets: 3,
                    orderable: true,
                    className: "text-center",
                    width: "100px",
                    render: function (data, type, row) {
                        return ` 
                            <div class="text-bold fs-7">${data}</div>
                        `;
                    },
                },
                {
                    targets: 4,
                    orderable: true,
                    className: "text-center",
                    width: "100px",
                    render: function (data, type, row) {
                        return `<a href='${data}' class="text-bold fs-7">Dowload File</a>`;
                    },
                },
                {
                    targets: 5,
                    orderable: true,
                    className: "text-center",
                    width: "30px",
                    render: function (data, type, row) {
                        return ` 
                            <div class="text-bold fs-7">${data}</div>
                        `;
                    },
                },
                {
                    targets: 6,
                    orderable: true,
                    className: "text-start",
                    width: "150px",
                    render: function (data, type, row) {
                        return ` 
                            <div class="text-bold fs-7">${to_rupiah(Math.round(data))}</div>
                        `;
                    },
                },
                {
                    targets: 7,
                    orderable: true,
                    className: "text-center",
                    width: "30px",
                    render: function (data, type, row) {
                        return ` 
                            <div class="text-bold fs-7">${data}</div>
                        `;
                    },
                },
                {
                    targets: 8,
                    orderable: true,
                    className: "text-start",
                    width: "150px",
                    render: function (data, type, row) {
                        return ` 
                            <div class="text-bold fs-7">${to_rupiah(Math.round(data))}</div>
                        `;
                    },
                },
                {
                    targets: -1,
                    orderable: false,
                    className: "text-center",
                    width: "200px",
                    render: function (data, type, row) {
                        return `
                            <div class="d-flex">
                                <a href="${baseUrl}/reconcile/detail/${data}" class="btn btn-success btn-active-light-success btn-sm rounded-sm">
                                    View Detail
                                </a>
                            </div>
                        `;
                    },
                },
                // {
                //     targets: -1,
                //     orderable: false,
                //     className: "text-center",
                //     width: "200px",
                //     render: function (data, type, row) {
                //         if (!privCreate || privCreate == "0") {
                //             return `
                //                 <a href="javascript:void(0)"  class="btn btn-secondary btn-sm rounded-sm disabled">
                //                     Access Disabled
                //                 </a>
                //             `;
                //         }
                //         if (row.is_reconcile == "0" || !row.is_reconcile) {
                //             return `
                //                 <a href="javascript:void(0)" id="reconcile_${data}" onclick="reconcile('${data}')" class="btn btn-primary btn-active-light-primary btn-sm rounded-sm">
                //                     Reconcile
                //                 </a>
                //             `;
                //         }
                //         return `
                //             <div class="d-flex">
                //                 <a href="/reconcile/result?token=${data}" class="btn btn-success btn-active-light-success btn-sm rounded-sm">
                //                     View
                //                 </a>
                //                 <a href="/reconcile/download?token=${data}" class="btn btn-light-warning btn-sm rounded-sm mx-2">
                //                     Download
                //                 </a>
                //             </div>
                //         `;
                //     },
                // },
            ],

            createdRow: function (row, data, dataIndex) {
                $(row)
                    .find("td:eq(4)")
                    .attr("data-filter", data.name);
            },
        })

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
        swal.showLoading();
        if (result.value) {
            $.ajax({
                url: baseUrl + "/reconcile/" + token + '/proceed',
                type: "GET",
                beforeSend: function() {
                    swal.showLoading();
                },
                success: function (response) {
                    swal.hideLoading();
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
                    swal.hideLoading();
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

var uploadedFilePartner = null;
var fileUrlPartner = null;

var myDropzone = new Dropzone("#kt_dropzonejs_example_1", {
    url: baseUrl + "/api/file/check",
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

var myDropzone = new Dropzone("#kt_dropzonejs_example_2", {
    url: baseUrl + "/api/file/check",
    paramName: "file",
    maxFiles: 1,
    maxFilesize: 10,
    addRemoveLinks: true,
    accept: function(file, done) {
        uploadedFilePartner = file; // Store the uploaded file
        done();
    },
    success: function(file, response) {
        if (response.success) {
            fileUrlPartner = response.data
            console.log("File berhasil diunggah:", file);
        } else {
            console.error("Gagal mengunggah file:", response.message);
        }
    }
});


function addPartnerReport() {
    var checkbox = document.getElementById(`partnerReport`);
    var dropzonePartner = document.getElementById(`dropzonePartnerReport`);
    if (checkbox.checked) {
        dropzonePartner.style.display = 'block';
    } else {
        dropzonePartner.style.display = 'none';
        uploadedFilePartner = null;
        fileUrlPartner = null;
    }
}

$("#store_settlement_form").on("submit", function(event) {
    event.preventDefault();
    var token = $('meta[name="csrf-token"]').attr('content');
    var formData = new FormData(this);
    formData.append('file', uploadedFile);  
    formData.append('url', fileUrl);  
    formData.append('filePartner', uploadedFilePartner);  
    formData.append('urlPartner', fileUrlPartner);  
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': token
        },
        type: 'POST',
        data: formData,
        url: `${baseUrl}/settlement`,
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
                    location.href = baseUrl + "/settlement";
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

$("#store_reconcile_form").on("submit", function(event) {
    event.preventDefault();
    var token = $('meta[name="csrf-token"]').attr('content');
    var formData = new FormData(this);
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': token
        },
        type: 'POST',
        data: formData,
        url: `${baseUrl}/reconcile`,
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
                    location.href = baseUrl + "/reconcile/result";
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