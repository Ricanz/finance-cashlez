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

    $token = request()->query('token');
    $status = request()->query('status');

    $downloadUrl = '/reconcile/download';
    if ($token) {
        $downloadUrl = $downloadUrl . '?token=' . $token;
    }
    if ($status) {
        if ($token) {
            $downloadUrl = $downloadUrl . '&status=' . $status;
        } else{
            $downloadUrl = $downloadUrl . '?status=' . $status;
        }
    }
@endphp
<x-app-layout>
    <div class="container">
        <div class="card card-flush px-10 py-6 rounded-sm">

            <div class="d-flex flex-wrap justify-content-between">
                <!--begin::Stats-->
                <div class="d-flex flex-wrap">
                    <div class="border border-gray-300 border-dashed rounded  w-300px py-3 px-4 me-6 mb-3">
                        <a href={{ url('/reconcile/result?status=match&token='.$token) }}
                            class="card-body p-0 d-flex justify-content-between flex-column overflow-hidden">
                            <!--begin::Hidden-->
                            <div class="d-flex flex-stack flex-wrap flex-grow-1 px-2 pt-2 pb-3">
                                <div class="me-2">
                                    <span class="fw-bolder text-gray-800 d-block fs-3">Match</span>
                                    <span class="text-gray-400 fw-bold">{{ $match }} Trx</span>
                                </div>
                                <div class="fw-bolder fs-5 text-primary">IDR Rp. {{ number_format($sumMatch) }}</div>
                            </div>
                            <!--end::Hidden-->
                        </a>
                    </div>
                    <div class="border border-gray-300 border-dashed rounded  w-300px py-3 px-4 me-6 mb-3">
                        <a href={{ url('/reconcile/result?status=dispute&token='.$token) }}
                            class="card-body p-0 d-flex justify-content-between flex-column overflow-hidden">
                            <!--begin::Hidden-->
                            <div class="d-flex flex-stack flex-wrap flex-grow-1 px-2 pt-2 pb-3">
                                <div class="me-2">
                                    <span class="fw-bolder text-gray-800 d-block fs-3">Dispute</span>
                                    <span class="text-gray-400 fw-bold">{{ $dispute }} Trx</span>
                                </div>
                                <div class="fw-bolder fs-5 text-primary">IDR Rp. {{ number_format($sumDispute) }}</div>
                            </div>
                            <!--end::Hidden-->
                        </a>
                    </div>
                    <div class="border border-gray-300 border-dashed rounded  w-300px py-3 px-4 me-6 mb-3">
                        <a href={{ url('/reconcile/result?status=onHold&token='.$token) }}
                            class="card-body p-0 d-flex justify-content-between flex-column overflow-hidden">
                            <!--begin::Hidden-->
                            <div class="d-flex flex-stack flex-wrap flex-grow-1 px-2 pt-2 pb-3">
                                <div class="me-2">
                                    <span class="fw-bolder text-gray-800 d-block fs-3">On Hold</span>
                                    <span class="text-gray-400 fw-bold">{{ $onHold }} Trx</span>
                                </div>
                                <div class="fw-bolder fs-5 text-primary">IDR Rp. {{ number_format($sumHold) }}</div>
                            </div>
                            <!--end::Hidden-->
                        </a>
                    </div>
                </div>
            </div>
            <!--begin::Wrapper-->
            <div class="d-flex flex-stack mb-5">
                <!--begin::Search-->
                <div class="card-title">
                    @if (request()->query('status') !== null)
                        <div class="fw-bolder fs-3 my-4">Result For {{ $status }} Transaction</div>
                    @endif

                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative my-1">
                        <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
                        <span class="svg-icon svg-icon-1 position-absolute ms-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2"
                                    rx="1" transform="rotate(45 17.0365 15.1223)" fill="black" />
                                <path
                                    d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                    fill="black" />
                            </svg>
                        </span>
                        <!--end::Svg Icon-->
                        <input type="text" data-kt-docs-table-filter="search"
                            class="form-control form-control-solid w-250px ps-14 rounded-sm"
                            placeholder="Search Merchant" />
                    </div>
                    <!--end::Search-->
                </div>
                <!--end::Search-->

                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-docs-table-toolbar="base">
                    <!--begin::Filter-->
                    <a href="{{ url($downloadUrl) }} " class="btn btn-light-warning me-3 rounded-sm">Download</a>
                    <!--end::Filter-->
                </div>
                <!--end::Toolbar-->

            </div>
            <!--end::Wrapper-->

            <!--begin::Datatable-->

            <table id="kt_datatable_example_1" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                        <th>No</th>
                        <th>Settlement Date</th>
                        <th>Batch</th>
                        <th>Bank Type</th>
                        <th>MID / MRC</th>
                        <th>Merchant Name</th>
                        <th>Status</th>
                        <th>BO Settlement</th>
                        <th>Bank Settlement</th>
                        <th>Dispute Amount</th>
                        <th>Net Transfer</th>
                        <th>Account Number</th>
                        <th>Bank Code</th>
                        <th>Bank Name</th>
                        <th>Account Holder</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-bold">
                </tbody>
            </table>
            <!--end::Datatable-->
        </div>
    </div>
    @include('/modules/reconcile/mrc-modal')

    @section('scripts')
        <script src="{{ asset('cztemp/assets/custom/js/disbursement.js') }}"></script>
    @endsection
</x-app-layout>
