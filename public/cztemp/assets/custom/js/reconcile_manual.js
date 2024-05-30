"use strict";

var KTDatatablesServerSide = (function () {
    var dt;
    var startDate = "";
    var endDate = "";
    var selectedBank = "";

    var url = `${baseUrl}/settlement/bank/data`;

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
                { data: "description2" },
                { data: "mid" },
                { data: "amount_credit" },
                { data: "id" },
            ],
            columnDefs: [
                {
                    targets: -1,
                    orderable: true,
                    className: "text-end",
                    width: "150px",
                    render: function (data, type, row, meta) {
                        // return meta.row + 1;
                        console.log(row.is_reconcile);
                        if (row.is_reconcile == "0" || !row.is_reconcile) {
                            return `
                                <div class="form-check form-check-sm form-check-custom form-check-solid text-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Tooltip on top">
                                    <input onclick="checkBank(
                                        ${row.id}, 
                                        '${to_date(row.transfer_date)}', 
                                        '${row.header.processor}', 
                                        '${row.mid}', '${row.amount_credit}'
                                    )" id="checkbox_bank_${row.id}" 
                                    class="form-check-input boCheckbox" name="bo_check[]" type="checkbox" 
                                    value="1" data-kt-check="true" data-kt-check-target=".widget-9-check" />
                                </div>
                            `;
                        } else{
                            return `
                                <div class="form-check form-check-sm form-check-custom form-check-solid text-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Tooltip on top">
                                    <input
                                    class="form-check-input boCheckbox" name="bo_check[]" type="checkbox" 
                                    value="1" data-kt-check="true" data-kt-check-target=".widget-9-check" 
                                    disabled/>
                                </div>
                            `;
                        }
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
    var url = `${baseUrl}/settlement/bo/data`;

    var initDatatable = function () {
        dt = $("#bo_settlement_table").DataTable({
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
                    d.bank = selectedBank;
                },
            },
            columns: [
                { data: "created_at" },
                // { data: "batch_fk" },
                { data: "merchant.name" },
                { data: "processor" },
                { data: "mid" },
                { data: "bank_transfer" },
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
                                <input onclick="checkBo(${row.id}, '${row.created_at}', '${row.processor}', '${row.mid}', '${row.bank_transfer}')" id="checkbox_bo_${row.id}" class="form-check-input" name="bo_check[]" type="checkbox" value="1" data-kt-check="true" data-kt-check-target=".widget-9-check" />
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
                    targets: 2,
                    orderable: true,
                    className: "text-center",
                    width: "30px",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    targets: 3,
                    orderable: true,
                    className: "text-center w-100",
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
                    targets: 4,
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

$("#singleReconcile").on("submit", function (event) {
    event.preventDefault();
    var token = $('meta[name="csrf-token"]').attr('content');
    var formData = new FormData(this);
    formData.append("selectedBo", selectedBo)
    formData.append("selectedBank", selectedBanks)
    $.ajax({
        headers: { 'X-CSRF-TOKEN': token },
        type : 'POST',
        data: formData,
        url  : baseUrl + '/reconcile/single',
        dataType: 'JSON',
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function() {
            swal.showLoading();
        },
        success: function(data){
            if(data.status === true) {
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
            }else {
                var values = '';
                jQuery.each(data.message, function (key, value) {
                    values += value+"<br>";
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
                }).then(function() { });
            }
        }
    });
});

function checkBank(id, settlementDate, bankType, mid, bankSettlement) {
    var checkbox = document.getElementById(`checkbox_bank_${id}`);
    var tbody = document.querySelector("#bank_selected_items tbody");
    var tfoot = document.querySelector("#bank_selected_items tfoot");
    if (checkbox.checked) {
        // Clear existing rows
        // tbody.innerHTML = "";

        selectedBanks.push(id);

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
        var idx = selectedBanks.indexOf(id);
        if (idx !== -1) {
            selectedBanks.splice(idx, 1);
        }
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

function checkBo(id, settlementDate, bankType, mid, bankPayment) {
    var checkbox = document.getElementById(`checkbox_bo_${id}`);
    var tbody = document.querySelector("#bo_selected_items tbody");
    var tfoot = document.querySelector("#bo_selected_items tfoot");
    if (checkbox.checked) {
        // Clear existing rows
        // tbody.innerHTML = "";

        selectedBo.push(id);
        
        var row = document.createElement("tr");
        row.setAttribute("id", `bo_detail_${id}`);
        row.innerHTML = `
            <td>${to_date(settlementDate)}</td>
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
        var idx = selectedBo.indexOf(id);
        if (idx !== -1) {
            selectedBo.splice(idx, 1);
        }
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
