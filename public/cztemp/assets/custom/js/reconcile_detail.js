"use strict";

var startDate = "";
var endDate = "";
var selectedChannel = "";
var regex = /\/reconcile\/detail\/([^\/]+)/;

var KTDatatablesServerSideBO = (function () {
    var dt;
    var token = getTokenFromUrl(regex);
    
    var url = `${baseUrl}/reconcile/detail/data/${token}`;

    var initDatatable = function () {
        dt = $("#bank_statement_detail_table").DataTable({
            searchDelay: 200,
            processing: true,
            serverSide: true,
            order: [[0, "asc"]],
            stateSave: true,
            autoWidth: true,
            select: {
                style: "os",
                selector: "td:first-child",
                className: "row-selected",
            },
            ajax: {
                url,
                data: function (d) {
                    d.startDate = startDate;
                    d.endDate = endDate;
                    d.channel = selectedChannel;
                },
            },
            columns: [
                { data: "id" },
                { data: "transfer_date" },
                { data: "mid" },
                { data: "description2" },
                { data: "description1" },
                { data: "amount_debit" },
                { data: "amount_credit" },
            ],
            columnDefs: [
                {
                    targets: 0,
                    orderable: true,
                    className: "text-start w-10px",
                    width: "10px",
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    },
                },
                {
                    targets: 1,
                    orderable: true,
                    className: "text-start w-100px",
                    width: "100px",
                    render: function (data, type, row, meta) {
                        return ` 
                            <div class="text-bold fs-7">${to_date(data)}</div>
                        `;
                    },
                },
                {
                    targets: 2,
                    orderable: true,
                    searchable: false,
                    className: "text-start w-50px",
                    width: "50px",
                    render: function (data, type, row) {
                        return ` 
                            <div class="text-bold fs-7">${data}</div>
                        `;
                    },
                },
                {
                    targets: 3,
                    orderable: true,
                    className: "text-start w-200px",
                    width: "200px",
                    render: function (data, type, row) {
                        return ` 
                            <div class="text-bold fs-7">${data}</div>
                        `;
                    },
                },
                {
                    targets: 4,
                    orderable: true,
                    className: "text-start w-200px",
                    width: "200px",
                    render: function (data, type, row) {
                        return ` 
                            <div class="text-bold fs-7">${data}</div>
                        `;
                    },
                },
                {
                    targets: 5,
                    orderable: true,
                    className: "text-start w-100px",
                    width: "100px",
                    render: function (data, type, row) {
                        return `
                            <div class="text-bold fs-7 text-uppercase">${!data ? '0' : to_rupiah(parseInt(data))}</div>
                        `;
                    },
                },
                {
                    targets: 6,
                    orderable: true,
                    className: "text-start w-100px",
                    width: "100px",
                    render: function (data, type, row) {
                        return `
                            <div class="text-bold fs-7 text-uppercase">${!data ? '0' : to_rupiah(parseInt(data))}</div>
                        `;
                    },
                },
            ],

            createdRow: function (row, data, dataIndex) {
                $(row).find("td:eq(4)").attr("data-filter", data.id);
            },
        });

        dt.on("draw", function () {
            KTMenu.createInstances();
        });
    };
    
    var reloadDatatable = function () {
        dt.ajax.reload();
    };

    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector(
            '[data-kt-docs-table-filter="searchBo"]'
        );
        filterSearch.addEventListener("keyup", function (e) {
            dt.search(e.target.value).draw();
        });
    };
    var handleChannelSelect = function () {
        const channelSelect = document.getElementById("channelSearch");
        channelSelect.addEventListener("change", function (e) {
            selectedChannel = e.target.value;
            reloadDatatable();
        });
    };

    var initDateRangePicker = function () {
        $('#kt_daterangepicker_2').daterangepicker({
            opens: 'left',
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month')
        }, function(start, end, label) {
            startDate = start.format('YYYY-MM-DD');
            endDate = end.format('YYYY-MM-DD');
            reloadDatatable();
        });
    };

    var clearFilter = function () {
        const clear = document.getElementById("clearBoSearch");
        clear.addEventListener("click", function (e) {
            const filterSearch = document.querySelector('[data-kt-docs-table-filter="searchBo"]');
            filterSearch.value = '';
            dt.search('').draw();

            const channelSelect = document.getElementById("channelSearch");
            channelSelect.value = '';
            selectedChannel = ''
            
            $('#kt_daterangepicker_2').data('daterangepicker').setStartDate(moment().startOf('month'));
            $('#kt_daterangepicker_2').data('daterangepicker').setEndDate(moment().endOf('month'));
            startDate = '';
            endDate = '';
            reloadDatatable();
        });
    };

    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
            handleChannelSelect();
            initDateRangePicker();
            clearFilter();
        },
        reload: function () {
            reloadDatatable();
        },
        setDates: function (start, end) {
            startDate = start;
            endDate = end;
        },
    };
})();

var totalBankSettlement = 0;
var selectedBanks = [];

var totalBankPayment = 0;
var selectedBo = [];

$("#refreshButton").on("click", function () {
    var tbodyBank = document.querySelector("#bank_selected_items tbody");
    var tfootBank = document.querySelector("#bank_selected_items tfoot");
    var tbodyBo = document.querySelector("#bo_selected_items tbody");
    var tfootBo = document.querySelector("#bo_selected_items tfoot");

    tbodyBank.innerHTML = "";
    tfootBank.innerHTML = "";
    tbodyBo.innerHTML = "";
    tfootBo.innerHTML = "";

    $('input[type="checkbox"]').prop('checked', false);
    totalBankPayment = 0;
    totalBankSettlement = 0;
});


$("#store_reconcile_form").on("submit", function(event) {
    event.preventDefault();
    var token = $('meta[name="csrf-token"]').attr('content');
    var tokenApplicant = getTokenFromUrl(regex);

    var formData = new FormData(this);
    formData.append('token_applicant', tokenApplicant)
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': token
        },
        type: 'POST',
        data: formData,
        url: baseUrl + '/reconcile/channel',
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

// $("#proceedReconcile").on("submit", function (event) {
//     event.preventDefault();
//     var token = $('meta[name="csrf-token"]').attr('content');
//     var tokenApplicant = getTokenFromUrl(regex);

//     var formData = new FormData(this);
//     formData.append("startDate", startDate)
//     formData.append("endDate", endDate)
//     formData.append("selectedChannel", selectedChannel)
//     formData.append("tokenApplicant", tokenApplicant)
//     $.ajax({
//         headers: { 'X-CSRF-TOKEN': token },
//         type : 'POST',
//         data: formData,
//         url  : '/reconcile/channel',
//         dataType: 'JSON',
//         cache: false,
//         contentType: false,
//         processData: false,
//         beforeSend: function() {
//             swal.showLoading();
//         },
//         success: function(data){
//             if(data.status === true) {
//                 swal.hideLoading();
//                 swal.fire({
//                     text: data.message,
//                     icon: "success",
//                     buttonsStyling: false,
//                     confirmButtonText: "Ok, got it!",
//                     customClass: {
//                         confirmButton: "btn font-weight-bold btn-light-primary"
//                     }
//                 }).then(function() {
//                     location.href = "/reconcile/result";
//                 });
//             }else {
//                 var values = '';
//                 jQuery.each(data.message, function (key, value) {
//                     values += value+"<br>";
//                 });

//                 swal.fire({
//                     text: data.message,
//                     html: values,
//                     icon: "error",
//                     buttonsStyling: false,
//                     confirmButtonText: "Ok, got it!",
//                     customClass: {
//                         confirmButton: "btn font-weight-bold btn-light-primary"
//                     }
//                 }).then(function() { });
//             }
//         }
//     });
// });

$("#kt_daterangepicker_1").daterangepicker();
$("#kt_daterangepicker_2").daterangepicker();
$("#kt_daterangepicker_3").daterangepicker();
$("#kt_daterangepicker_4").daterangepicker();

KTUtil.onDOMContentLoaded(function () {
    KTDatatablesServerSideBO.init();
});
