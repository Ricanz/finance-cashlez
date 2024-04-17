"use strict";

var KTDatatablesServerSide = (function () {
    var dt;
    var url = window.location.href;
    var uuid = '';
    const pattern = /\/reconcile\/([0-9a-fA-F-]+)\/show/;
    const match = url.match(pattern);
    if (match && match[1]) {
        uuid = match[1];
        console.log(uuid);
    } else {
        console.error('UUID not found in URL');
    }

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
                url: `/reconcile/${uuid}/data`,
            },
            columns: [
                { data: "id" },
                { data: "settlement_date", width: '20%'},
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
            // columns: [{ width: '20%' }, null, null, null, null],
            columnDefs: [
                {
                    targets: 0,
                    orderable: true,
                    className: "text-start w-30px",
                    // width: "50px",
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    },
                },
                {
                    targets: 1,
                    orderable: true,
                    className: "text-start w-200px",
                    width: "200px",
                    render: function (data, type, row) {
                        return to_date_time(data);
                    },
                },
                {
                    targets: 4,
                    orderable: true,
                    className: "text-center",
                    // width: "50px",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    targets: 6,
                    orderable: true,
                    className: "text-center",
                    // width: "30px",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    targets: 7,
                    orderable: true,
                    className: "text-start",
                    // width: "150px",
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

KTUtil.onDOMContentLoaded(function () {
    KTDatatablesServerSide.init();
});