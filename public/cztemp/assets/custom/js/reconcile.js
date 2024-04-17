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
                            <a href="javascript:void(0)" id="mrcDetail_${data}" onclick="mrcDetail('${data}')" class="btn btn-light-primary btn-active-primary btn-sm rounded-sm">
                                ${data}
                            </a>
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

KTUtil.onDOMContentLoaded(function () {
    KTDatatablesServerSide.init();
});