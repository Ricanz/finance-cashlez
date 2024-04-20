"use strict";

var KTDatatablesServerSide = (function () {
    var dt;
    var url = window.location.href;
    var uuid = '';
    var status = '';
    const uuidPattern = /\/reconcile\/([0-9a-fA-F-]+)\/show/;
    const statusPattern = /[?&]status=([^&#]*)/;
    const uuidMatch = url.match(uuidPattern);
    const statusMatch = url.match(statusPattern);

    if (uuidMatch && uuidMatch[1]) {
        uuid = uuidMatch[1];
    } else {
        console.error('UUID not found in URL');
    }

    if (statusMatch && statusMatch[1]) {
        status = `?status=${statusMatch[1]}`
    } else {
        status = ''
    }


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
                url: `/reconcile/${uuid}/data${status}`,
            },
            columns: [
                { data: "id" },
                { data: "settlement_date" },
                { data: "batch_fk" },
                { data: "processor_payment" },
                { data: "mid" },
                { data: "merchant.reference_code" },
                { data: "merchant.name" },
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
                        return to_date_time(data);
                    },
                },
                {
                    targets: 4,
                    orderable: true,
                    className: "text-center",
                    width: "50px",
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
                        return `
                            <div class="d-flex justify-content-end">
                                <a href="#" class="btn btn-light-primary me-3 rounded-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_new_target" onclick="mrcDetail('${row.token_applicant}')">${data}</a>
                            </div>
                        `;
                    }
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
                {
                    targets: 10,
                    orderable: true,
                    className: "text-start",
                    width: "150px",
                    render: function (data, type, row) {
                        return to_rupiah(data);
                    },
                },
            ],

            createdRow: function (row, data, dataIndex) {
                $(row)
                    .find("td:eq(4)")
                    .attr("data-filter", data.name);
            },
        });

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

function mrcDetail(tokenApplicant) {
    $.ajax({
        url: "/mrc/" + tokenApplicant + "/detail",
        type: "GET",
        success: function (response) {
            console.log(response);
            var data = response.data
            document.getElementById("settlementDate").innerHTML = to_date_time(data.settlement_date);
            document.getElementById("batch").innerHTML = data.batch_fk;
            document.getElementById("bankType").innerHTML = '-';
            document.getElementById("mrc").innerHTML = data.merchant.reference_code;
            document.getElementById("merchantName").innerHTML = data.merchant.name;
            document.getElementById("grossTrf").innerHTML = '-';
            document.getElementById("bankAdmin").innerHTML = '-';
            document.getElementById("netTransfer").innerHTML = `Rp. ${to_rupiah(data.transfer_amount)} `;
            document.getElementById("accountNumber").innerHTML = data.bank_account.account_number;
            document.getElementById("bankCode").innerHTML = data.bank_account.bank_code;
            document.getElementById("bankName").innerHTML = data.bank_account.bank_name;
            document.getElementById("accounttHolder").innerHTML = data.bank_account.account_holder;
            document.getElementById("accountEmail").innerHTML = data.merchant.email;
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

$( document ).ready(function() {
    
    $("#mrcDetail").on("click", function (event) {
        console.log(event);
    })
});

KTUtil.onDOMContentLoaded(function () {
    KTDatatablesServerSide.init();
});