"use strict";
$("#kt_daterangepicker_1").daterangepicker();
$("#kt_daterangepicker_2").daterangepicker();

var KTDatatablesServerSide = (function () {
    var dt;
    var uuid = "";
    var url = "";
    var status = "";
    var startDate = "";
    var endDate = "";
    const queryParams = new URLSearchParams(window.location.search);

    var parUuid = queryParams.get("token");
    var parUstatus = queryParams.get("status");

    if (parUuid) {
        uuid = `token=${parUuid}`;
    }

    if (parUstatus) {
        status = `status=${parUstatus}`;
    }

    url = `reconcile/data?${uuid}&${status}`;

    var initDatatable = function () {
        dt = $("#kt_datatable_example_1").DataTable({
            searchDelay: 200,
            processing: true,
            serverSide: true,
            order: [[1, "desc"]],
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
                },
            },
            columns: [
                { data: "id" },
                { data: "settlement_date" },
                // { data: "batch_fk" },
                { data: "processor_payment" },
                { data: "mid" },
                { data: "merchant.name" },
                { data: "status" },
                { data: "internal_payment" },
                { data: "bank_settlement_amount" },
                { data: "dispute_amount" },
                { data: "transfer_amount" },
                { data: "bank_account.account_number" },
                { data: "bank_account.bank_code" },
                { data: "bank_account.bank_name" },
                { data: "bank_account.account_holder" },
            ],
            columnDefs: [
                {
                    targets: 0,
                    orderable: true,
                    className: "text-start",
                    width: "150px",
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    },
                },
                {
                    targets: 1,
                    orderable: true,
                    searchable: false,
                    className: "text-start",
                    width: "800px",
                    render: function (data, type, row) {
                        return to_date(data);
                    },
                },
                {
                    targets: 3,
                    orderable: true,
                    className: "text-center",
                    width: "50px",
                    render: function (data, type, row) {
                        return `
                            <div class="d-flex justify-content-center mb-1">
                            ${data}
                            </div>
                            <div class="d-flex justify-content-end">
                                <a href="#" class="btn btn-sm btn-light-primary me-3 rounded-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_new_target" onclick="mrcDetail('${row.token_applicant}')">${row.merchant.reference_code}</a>
                            </div>
                        `;
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
                    className: "text-center",
                    width: "50px",
                    render: function (data, type, row) {
                        var status = "";
                        var badge = "";
                        if (data == "MATCH") {
                            status = "match";
                            badge = "badge-light-success";
                        } else if (data == "NOT_MATCH" || data == "NOT_FOUND") {
                            status = "dispute";
                            badge = "badge-light-danger";
                        } else {
                            status = "on hold";
                            badge = "badge-light-warning";
                        }
                        return `
                            <span class="badge ${badge}">${status}</span>
                        `;
                    },
                },
                {
                    targets: 6,
                    orderable: true,
                    className: "text-start",
                    width: "150px",
                    render: function (data, type, row) {
                        return to_rupiah(data);
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
                    targets: 8,
                    orderable: true,
                    className: "text-start",
                    width: "150px",
                    render: function (data, type, row) {
                        return to_rupiah(data);
                    },
                },
                {
                    targets: 9,
                    orderable: true,
                    className: "text-start",
                    width: "150px",
                    render: function (data, type, row) {
                        return to_rupiah(data);
                    },
                },
            ],

            createdRow: function (row, data, dataIndex) {
                $(row).find("td:eq(4)").attr("data-filter", data.name);
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
            '[data-kt-docs-table-filter="search"]'
        );
        filterSearch.addEventListener("keyup", function (e) {
            dt.search(e.target.value).draw();
        });
    };

    var initDateRangePicker = function () {
        $("#kt_daterangepicker_1").daterangepicker(
            {
                opens: "left",
                startDate: moment().startOf("month"),
                endDate: moment().endOf("month"),
            },
            function (start, end, label) {
                startDate = start.format("YYYY-MM-DD");
                endDate = end.format("YYYY-MM-DD");
                reloadDatatable();
            }
        );
    };

    var handleRefreshTable = function () {
        const refreshButton = document.getElementById("refreshButton");
        const searchTable = document.getElementById("searchTable");
        refreshButton.addEventListener("click", function (e) {
            searchTable.value = "";
            dt.search("").draw();
            reloadDatatable();
        });
    };

    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
            reloadDatatable();
            initDateRangePicker();
            handleRefreshTable();
        },
    };
})();

$("#download_reconcile_form").on("submit", function (event) {
    event.preventDefault();

    var bank = document.getElementById(`bankInput`).value;
    var status = document.getElementById(`statusInput`).value;
    var dateRange = document.getElementById(`kt_daterangepicker_2`).value;

    var dates = dateRange.split(" - ");

    var startDateString = dates[0];
    var startDateParts = startDateString.split("/");
    var formattedStartDate = startDateParts[2] + "-" + startDateParts[0].padStart(2, '0') + "-" + startDateParts[1].padStart(2, '0');
    
    // Parsing tanggal akhir
    var endDateString = dates[1];
    var endDateParts = endDateString.split("/");
    var formattedEndDate = endDateParts[2] + "-" + endDateParts[0].padStart(2, '0') + "-" + endDateParts[1].padStart(2, '0');

    return window.location.href = `/reconcile/download?bank=${bank}&status=${status}&startDate=${formattedStartDate}&endDate=${formattedEndDate}`

});

function mrcDetail(tokenApplicant) {
    $.ajax({
        url: "/mrc/" + tokenApplicant + "/detail",
        type: "GET",
        success: function (response) {
            console.log(response);
            var data = response.data;
            document.getElementById("settlementDate").innerHTML = to_date_time(
                data.settlement_date
            );
            document.getElementById("batch").innerHTML = data.batch_fk;
            document.getElementById("bankType").innerHTML = "-";
            document.getElementById("mrc").innerHTML =
                data.merchant.reference_code;
            document.getElementById("merchantName").innerHTML =
                data.merchant.name;
            document.getElementById("grossTrf").innerHTML = "-";
            document.getElementById("bankAdmin").innerHTML = "-";
            document.getElementById("netTransfer").innerHTML = `${to_rupiah(
                data.transfer_amount
            )} `;
            document.getElementById("accountNumber").innerHTML =
                data.bank_account.account_number;
            document.getElementById("bankCode").innerHTML =
                data.bank_account.bank_code;
            document.getElementById("bankName").innerHTML =
                data.bank_account.bank_name;
            document.getElementById("accounttHolder").innerHTML =
                data.bank_account.account_holder;
            document.getElementById("accountEmail").innerHTML =
                data.merchant.email;
        },
        error: function (xhr, status, error) {
            Swal.fire({
                text: "Failed to delete the record.",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn fw-bold btn-primary",
                },
            });
        },
    });
}

$(document).ready(function () {
    $("#mrcDetail").on("click", function (event) {
        console.log(event);
    });
});

KTUtil.onDOMContentLoaded(function () {
    KTDatatablesServerSide.init();
});
