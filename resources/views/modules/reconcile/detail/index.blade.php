@php
    $priv = App\Helpers\Utils::getPrivilege('reconcile');
@endphp

<x-app-layout>
    <div id="kt_content_container" class="container-xxl">
        <div class="card-header border-0 py-5 rounded-sm mb-5">
            <h3 class="card-title fw-bolder">Advance Search</h3>
            <div class="row gy-3 g-xl-8">
                <div class="col-xl-6">
                    <h5 class="fw-bold text-gray-600">BANK Settlement</h5>
                    <div class="d-flex mb-2">
                        <div class="mb-0 w-50 me-1">
                            <input class="form-control form-control-solid" placeholder="Pick date rage"
                                id="kt_daterangepicker_2" />
                        </div>
                        <div class="mb-0 w-50 ms-1">
                            <select name="channel" id="channelSearch" aria-label="Select a Channel"
                                data-placeholder="Select a Channel..." class="form-select form-select-solid fw-bolder">
                                <option value="">Select a Channel...</option>
                                @foreach ($channels as $item)
                                    <option value="{{ $item->description2 }}">{{ $item->description2 }}</option>
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
                        <div class="d-flex align-items-center mb-0 w-250">
                            <button id="clearBoSearch" class="btn btn-sm btn-light-warning w-100">Clear Search</button>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-1">
                        <h5 class="fw-bold text-gray-600 w-50">SUM Amount</h5>
                        @if ($priv->create)
                            <div class="d-flex justify-content-end w-50" data-kt-docs-table-toolbar="base">
                                <!--begin::Filter-->
                                <a href="#" class="btn btn-light-primary me-2 rounded-sm w-100" data-bs-toggle="modal"
                                    data-bs-target="#kt_modal_reconcile">Reconcile</a>
                                <!--end::Filter-->
                            </div>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap">
                        <div class="border border-gray-300 border-dashed rounded  w-50 py-1 px-2 mb-3">
                            <a href={{ url('/reconcile/result?status=match&token=') }}
                                class="card-body p-0 d-flex justify-content-between flex-column overflow-hidden">
                                <!--begin::Hidden-->
                                <div class="d-flex flex-stack flex-wrap flex-grow-1 px-2 pt-2 pb-3">
                                    <div class="me-2">
                                        <span class="fw-bolder text-gray-800 d-block fs-3">Debit</span>
                                        <span class="text-gray-400 fw-bold">{{ $totalDebit }} Trx</span>
                                    </div>
                                    <div class="fw-bolder fs-5 text-primary">IDR Rp. {{ number_format($sumDebit) }}
                                    </div>
                                </div>
                                <!--end::Hidden-->
                            </a>
                        </div>
                        <div class="border border-gray-300 border-dashed rounded  w-50 py-1 px-2 mb-3">
                            <a href={{ url('/reconcile/result?status=dispute&token=') }}
                                class="card-body p-0 d-flex justify-content-between flex-column overflow-hidden">
                                <!--begin::Hidden-->
                                <div class="d-flex flex-stack flex-wrap flex-grow-1 px-2 pt-2 pb-3">
                                    <div class="me-2">
                                        <span class="fw-bolder text-gray-800 d-block fs-3">Credit</span>
                                        <span class="text-gray-400 fw-bold">{{ $totalCredit }} Trx</span>
                                    </div>
                                    <div class="fw-bolder fs-5 text-primary">IDR Rp. {{ number_format($sumCredit) }}
                                    </div>
                                </div>
                                <!--end::Hidden-->
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row gy-3 g-xl-8">
            <!--begin::Col-->
            <div class="col-xl-12">
                <!--begin::Mixed Widget 2-->
                <div class="card card-xl-stretch">
                    <!--begin::Header-->
                    <div class="card-header border-0 bg-danger py-5">
                        <h3 class="card-title fw-bolder text-white">Bank Settlement</h3>
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body p-0">

                        <div class="card card-flush px-10 py-6 rounded-sm">
                            <!--begin::Datatable-->

                            <table id="bank_statement_detail_table"
                                class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th>No</th>
                                        <th>Transfer Date</th>
                                        <th>MID</th>
                                        <th>Channel</th>
                                        <th>Description</th>
                                        <th>Debit Amount</th>
                                        <th>Credit Amount</th>
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
    @include('/modules/reconcile/detail/reconcile-modal')

    @section('scripts')
        <script src="{{ asset('cztemp/assets/custom/js/reconcile_detail.js') }}"></script>
    @endsection
</x-app-layout>
