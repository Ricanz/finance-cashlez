@php
    switch (request()->query('status')) {
        case 'match':
            $status = 'MATCH';
            break;
        case 'dispute':
            $status = 'DISPUTE';
            break;
        case 'onHold':
            $status = 'ON HOLD';
            break;
        default:
            $status = 'DISPUTE';
            break;
    }

    $priv = App\Helpers\Utils::getPrivilege('reconcile');
@endphp
<x-app-layout>
    <div id="kt_content_container" class="container-xxl">
        <!--begin::Header-->
        <div class="card-header border-0 py-5 rounded-sm mb-5">
            <h3 class="card-title fw-bolder">Selected items</h3>
            <div class="row gy-3 g-xl-8">

                <div class="col-xl-12 py-2">
                    <table id="bo_selected_items" class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                <th>Settlement Date</th>
                                <th>Channel</th>
                                <th>FTP File</th>
                                <th>Number VA</th>
                                <th>Auth Code</th>
                                <th>SID</th>
                                <th>RRN</th>
                                <th class="text-end">BO Settlement</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-bold">
                        </tbody>
                        <tfoot class="text-black fw-bolder">
                        </tfoot>
                    </table>
                </div>

                <div class="col-xl-12 py-2">
                    <table id="partner_report_items" class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                <th>Statement Date</th>
                                <th>Channel</th>
                                <th>FTP File</th>
                                <th>Number VA</th>
                                <th>Auth Code</th>
                                <th>SID</th>
                                <th>RRN</th>
                                <th class="text-end">Partner Report</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-bold">
                        </tbody>
                        <tfoot class="text-black fw-bolder">
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-end mb-0 w-25 ms-1">
                    <button id="refreshButton" class="btn btn-sm btn-light-warning w-100 me-1">Clear Table</button>
                    @if ($priv->create)
                        <form action="#" id="singleReconcile">
                            @csrf
                            <button type="submit" id="kt_modal_new_target_submit" class="btn btn-sm btn-light-primary w-100 ms-2">Reconcile</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        <!--end::Header-->

        <div class="card-header border-0 py-5 rounded-sm mb-5">
            <h3 class="card-title fw-bolder">Advance Search</h3>
            <div class="row gy-3 g-xl-8">
                <div class="col-xl-6">
                    <h5 class="fw-bold text-gray-600">BO Settlement</h5>
                    <div class="d-flex mb-2">
                        <div class="mb-0 w-50 me-1">
                            <input class="form-control form-control-solid" placeholder="Pick date rage"
                                id="kt_daterangepicker_2" />
                        </div>
                        <div class="mb-0 w-50 ms-1">
                            <select name="bank" id="bankSettlementBoSearch" aria-label="Select a Bank"
                                data-placeholder="Select a Bank..." class="form-select form-select-solid fw-bolder">
                                <option value="">Select a Bank...</option>
                                @foreach ($banks as $item)
                                    <option value="{{ $item->bank_id }}">{{ $item->channel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-flex mb-2 align-items-center">
                        <div class="mb-0 w-75 me-1">
                            <div class="d-flex align-items-center position-relative w-100">
                                <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
                                <span class="svg-icon svg-icon-1 position-absolute ms-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none">
                                        <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2"
                                            rx="1" transform="rotate(45 17.0365 15.1223)" fill="black" />
                                        <path
                                            d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                            fill="black" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                                <input type="text" data-kt-docs-table-filter="searchBo"
                                    class="form-control form-control-solid ps-14" placeholder="Search Table" />
                            </div>
                        </div>
                        <div class="mb-0 w-25 ms-1">
                            <button id="clearBoSearch" class="btn btn-sm btn-light-warning w-100">Clear Search</button>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <h5 class="fw-bold text-gray-600">BANK Statement</h5>
                    <div class="d-flex mb-2">
                        <div class="mb-0 w-50 me-1">
                            <input class="form-control form-control-solid" placeholder="Pick date rage"
                                id="kt_daterangepicker_1" />
                        </div>
                        <div class="mb-0 w-50 ms-1">
                            <select name="bank" id="bankSettlementSearch" aria-label="Select a Bank"
                                data-placeholder="Select a Bank..." class="form-select form-select-solid fw-bolder">
                                <option value="">Select a Bank...</option>
                                @foreach ($banks as $item)
                                    <option value="{{ $item->bank_id }}">{{ $item->channel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-flex mb-2 align-items-center">
                        <div class="mb-0 w-75 me-1">
                            <div class="d-flex align-items-center position-relative w-100">
                                <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
                                <span class="svg-icon svg-icon-1 position-absolute ms-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none">
                                        <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2"
                                            rx="1" transform="rotate(45 17.0365 15.1223)" fill="black" />
                                        <path
                                            d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                            fill="black" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                                <input type="text" data-kt-docs-table-filter="searchBank"
                                    class="form-control form-control-solid ps-14" placeholder="Search Table" />
                            </div>
                        </div>
                        <div class="mb-0 w-25 ms-1">
                            <button id="clearBankSearch" class="btn btn-sm btn-light-warning w-100">Clear Search</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row gy-3 g-xl-8">
            <!--begin::Col-->
            <div class="col-xl-6">
                <!--begin::Mixed Widget 2-->
                <div class="card card-xl-stretch">
                    <!--begin::Header-->
                    <div class="card-header border-0 bg-danger py-5">
                        <h3 class="card-title fw-bolder text-white">BO Settlement</h3>
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body p-0">

                        <div class="card card-flush px-10 py-6 rounded-sm">
                            <!--begin::Datatable-->

                            <table id="bo_settlement_table" class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th>Settlement Date</th>
                                        <th>Channel</th>
                                        <th>FTP File</th>
                                        <th>Number VA</th>
                                        <th>Auth Code</th>
                                        <th>Shopeepay SID</th>
                                        <th>RRN</th>
                                        <th>Bank Payment</th>
                                        <th>Check</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-bold">
                                </tbody>
                            </table>
                            <!--end::Datatable-->
                        </div>
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Mixed Widget 2-->
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-6">
                <!--begin::Mixed Widget 2-->
                <div class="card card-xl-stretch">
                    <!--begin::Header-->
                    <div class="card-header border-0 bg-danger py-5">
                        <h3 class="card-title fw-bolder text-white">Partner Report </h3>
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body p-0">

                        <div class="card card-flush px-10 py-6 rounded-sm">
                            <!--begin::Datatable-->
                            <table id="partner_report_table" class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th>Report Date</th>
                                        <th>Channel</th>
                                        <th>FTP File</th>
                                        <th>Number VA</th>
                                        <th>Auth Code</th>
                                        <th>Shopeepay SID</th>
                                        <th>RRN</th>
                                        <th>Net Amount</th>
                                        <th>Check</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-bold">
                                </tbody>
                            </table>
                            <!--end::Datatable-->
                        </div>
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Mixed Widget 2-->
            </div>
            <!--end::Col-->
        </div>
    </div>
    @include('/modules/reconcile/mrc-modal')

    @section('scripts')
        <script src="{{ asset('cztemp/assets/custom/js/reconcile_partner.js') }}"></script>
    @endsection
</x-app-layout>
