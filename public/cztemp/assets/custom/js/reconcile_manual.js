"use strict";

var KTDatatablesServerSide = (function () {
    var dt;
    var startDate = "";
    var endDate = "";
    var selectedBank = "";

    var url = `settlement/bank/data`;

    var initDatatable = function () {
        dt = $("#bank_settlement_table").DataTable({
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
                    d.bank = selectedBank;
                },
            },
            columns: [
                { data: "transfer_date" },
                { data: "merchant_name" },
                { data: "header.processor" },
                { data: "mid" },
                { data: "amount_credit" },
                { data: "id" },
            ],
            columnDefs: [
                {
                    targets: -1,
                    orderable: true,
                    className: "text-start",
                    width: "150px",
                    render: function (data, type, row, meta) {
                        // return meta.row + 1;
                        return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input onclick="checkBank(
                                    ${row.id}, 
                                    '${to_date(row.transfer_date)}', 
                                    '${row.header.processor}', 
                                    '${row.mid}', '${row.amount_credit}'
                                )" id="checkbox_bank_${row.id}" 
                                class="form-check-input" name="bo_check[]" type="checkbox" 
                                value="1" data-kt-check="true" data-kt-check-target=".widget-9-check" 
                                ${row.is_reconcile ? 'disabled' : ''}/>
                            </div>
                        `;
                    },
                },
                {
                    targets: 0,
                    orderable: true,
                    searchable: false,
                    className: "text-start",
                    width: "800px",
                    render: function (data, type, row) {
                        return to_date(data);
                    },
                },
                {
                    targets: 4,
                    orderable: true,
                    className: "text-end",
                    width: "250px",
                    render: function (data, type, row) {
                        return to_rupiah(parseInt(data));
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
            '[data-kt-docs-table-filter="searchBank"]'
        );
        filterSearch.addEventListener("keyup", function (e) {
            dt.search(e.target.value).draw();
        });
    };

    var handleBankSelection = function () {
        const bankSelect = document.getElementById("bankSettlementSearch");
        bankSelect.addEventListener("change", function (e) {
            selectedBank = e.target.value;
            reloadDatatable();
        });
    };

    var initDateRangePicker = function () {
        $('#kt_daterangepicker_1').daterangepicker({
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
        const clear = document.getElementById("clearBankSearch");
        clear.addEventListener("click", function (e) {
            const filterSearch = document.querySelector('[data-kt-docs-table-filter="searchBank"]');
            filterSearch.value = '';
            dt.search('').draw();

            const bankSelect = document.getElementById("bankSettlementSearch");
            bankSelect.value = '';
            selectedBank = ''
            
            $('#kt_daterangepicker_1').data('daterangepicker').setStartDate(moment().startOf('month'));
            $('#kt_daterangepicker_1').data('daterangepicker').setEndDate(moment().endOf('month'));
            startDate = moment().startOf('month').format('YYYY-MM-DD');
            endDate = moment().endOf('month').format('YYYY-MM-DD');;
            reloadDatatable();
        });
    };

    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
            handleBankSelection();
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

var KTDatatablesServerSideBO = (function () {
    var dt;
    var startDate = "";
    var endDate = "";
    var selectedBank = "";
    var url = `/settlement/bo/data`;

    var initDatatable = function () {
        dt = $("#bo_settlement_table").DataTable({
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
                    d.bank = selectedBank;
                },
            },
            columns: [
                { data: "settlement_date" },
                { data: "batch_fk" },
                { data: "merchant.name" },
                { data: "header.processor" },
                { data: "header.mid" },
                { data: "bank_payment" },
                { data: "id" },
            ],
            columnDefs: [
                {
                    targets: -1,
                    orderable: true,
                    className: "text-start",
                    width: "150px",
                    render: function (data, type, row, meta) {
                        // return meta.row + 1;
                        return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input onclick="checkBo(${row.id}, '${row.settlement_date}', '${row.batch_fk}', '${row.header.processor}', '${row.header.mid}', '${row.bank_payment}')" id="checkbox_bo_${row.id}" class="form-check-input" name="bo_check[]" type="checkbox" value="1" data-kt-check="true" data-kt-check-target=".widget-9-check" />
                            </div>
                        `;
                    },
                },
                {
                    targets: 0,
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
                    width: "30px",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    targets: 4,
                    orderable: true,
                    className: "text-center",
                    width: "50px",
                    render: function (data, type, row) {
                        return `
                            <div class="d-flex justify-content-center mb-1">
                            ${data}
                            </div>
                        `;
                    },
                },
                {
                    targets: 5,
                    orderable: true,
                    className: "text-end",
                    width: "200px",
                    render: function (data, type, row) {
                        return to_rupiah(parseInt(data));
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
    var handleBankSelection = function () {
        const bankSelect = document.getElementById("bankSettlementBoSearch");
        bankSelect.addEventListener("change", function (e) {
            selectedBank = e.target.value;
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

            const bankSelect = document.getElementById("bankSettlementBoSearch");
            bankSelect.value = '';
            selectedBank = ''
            
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
            handleBankSelection();
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

$("#refreshButton").on("click", function () {
    KTDatatablesServerSide.reload();
});

var totalBankSettlement = 0;
function checkBank(id, settlementDate, bankType, mid, bankSettlement) {
    var checkbox = document.getElementById(`checkbox_bank_${id}`);
    var tbody = document.querySelector("#bank_selected_items tbody");
    var tfoot = document.querySelector("#bank_selected_items tfoot");
    if (checkbox.checked) {
        // Clear existing rows
        // tbody.innerHTML = "";

        var row = document.createElement("tr");
        row.setAttribute("id", `bank_detail_${id}`);
        row.innerHTML = `
            <td>${settlementDate}</td>
            <td>${bankType}</td>
            <td>${mid}</td>
            <td class="text-end">${to_rupiah(parseInt(bankSettlement))}</td>
        `;
        totalBankSettlement = totalBankSettlement + parseInt(bankSettlement);
        tbody.appendChild(row);
        tfoot.innerHTML = `
            <td colspan="2" class="text-start">Total</td>
            <td colspan="2" class="text-end">${to_rupiah(
                totalBankSettlement
            )}</td>
        `;
    } else {
        totalBankSettlement = totalBankSettlement - parseInt(bankSettlement);
        tfoot.innerHTML = "";
        tfoot.innerHTML = `
            <td colspan="2" class="text-start">Total</td>
            <td colspan="2" class="text-end">${to_rupiah(totalBankSettlement)}</td>
        `;
        var row = document.getElementById(`bank_detail_${id}`);
        row.remove();
    }
}

var totalBankPayment = 0;
function checkBo(id, settlementDate, batch, bankType, mid, bankPayment) {
    console.log(`check bo ${id}`);
    var checkbox = document.getElementById(`checkbox_bo_${id}`);
    var tbody = document.querySelector("#bo_selected_items tbody");
    var tfoot = document.querySelector("#bo_selected_items tfoot");
    if (checkbox.checked) {
        // Clear existing rows
        // tbody.innerHTML = "";

        var row = document.createElement("tr");
        row.setAttribute("id", `bo_detail_${id}`);
        row.innerHTML = `
            <td>${to_date(settlementDate)}</td>
            <td>${batch}</td>
            <td>${bankType}</td>
            <td>${mid}</td>
            <td class="text-end">${to_rupiah(bankPayment)}</td>
        `;
        totalBankPayment = totalBankPayment + parseInt(bankPayment);
        tbody.appendChild(row);
        tfoot.innerHTML = `
            <td colspan="3" class="text-start">Total</td>
            <td colspan="2" class="text-end">${to_rupiah(totalBankPayment)}</td>
        `;
    } else {
        totalBankPayment = totalBankPayment - parseInt(bankPayment);
        tfoot.innerHTML = "";
        tfoot.innerHTML = `
            <td colspan="3" class="text-start">Total</td>
            <td colspan="2" class="text-end">${to_rupiah(totalBankPayment)}</td>
        `;
        var row = document.getElementById(`bo_detail_${id}`);
        row.remove();
    }
}

$("#kt_daterangepicker_1").daterangepicker();
$("#kt_daterangepicker_2").daterangepicker();

KTUtil.onDOMContentLoaded(function () {
    KTDatatablesServerSide.init();
    KTDatatablesServerSideBO.init();
});
