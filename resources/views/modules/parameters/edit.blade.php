<x-app-layout>
    <div class="container">
        <div class="card card-flush px-10 py-6 rounded-sm">
            <div class="card-title">
                <h2 class="fw-bolder">Edit Channel Parameter</h2>
            </div>
            <form class="form" action="#" id="update_role_form">
                @csrf
                <input type="hidden" name="id" value="{{ $data->id }}">
                <div class="py-10 px-lg-17">
                    <div class="scroll-y me-n7 pe-7">
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fs-6 fw-bold mb-2">Channel Name</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" class="form-control form-control-solid" placeholder="Place channels's name"
                                name="name" value="{{ $data->channel }}" required />
                            <!--end::Input-->
                        </div>
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fs-6 fw-bold mb-2">Report Partner</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="report_partner" aria-label="Select a Parameter" data-control="select2"
                                data-placeholder="Select a Parameter..." class="form-select form-select-solid fw-bolder">
                                <option value="{{ $data->parameter->report_partner }}">{{ $data->parameter->report_partner }}</option>
                                @foreach ($params as $item)
                                    <option value="{{ $item }}">{{ $item }}</option>
                                @endforeach
                            </select>
                            <!--end::Input-->
                        </div>
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fs-6 fw-bold mb-2">BO Detail Transaction</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="bo_detail_transaction" aria-label="Select a Parameter" data-control="select2"
                                data-placeholder="Select a Parameter..." class="form-select form-select-solid fw-bolder">
                                <option value="{{ $data->parameter->bo_detail_transaction }}">{{ $data->parameter->bo_detail_transaction }}</option>
                                @foreach ($params as $item)
                                    <option value="{{ $item }}">{{ $item }}</option>
                                @endforeach
                            </select>
                            <!--end::Input-->
                        </div>
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fs-6 fw-bold mb-2">BO Summary</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="bo_summary" aria-label="Select a Parameter" data-control="select2"
                                data-placeholder="Select a Parameter..." class="form-select form-select-solid fw-bolder">
                                <option value="{{ $data->parameter->bo_summary }}">{{ $data->parameter->bo_summary }}</option>
                                @foreach ($params as $item)
                                    <option value="{{ $item }}">{{ $item }}</option>
                                @endforeach
                            </select>
                            <!--end::Input-->
                        </div>
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fs-6 fw-bold mb-2">Bank Statement</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="bank_statement" aria-label="Select a Parameter" data-control="select2"
                                data-placeholder="Select a Parameter..." class="form-select form-select-solid fw-bolder">
                                <option value="{{ $data->parameter->bank_statement }}">{{ $data->parameter->bank_statement }}</option>
                                @foreach ($params as $item)
                                    <option value="{{ $item }}">{{ $item }}</option>
                                @endforeach
                            </select>
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>
                
                </div>
                <!--end::Modal body-->
                <div class="modal-footer flex-center">
                    <!--begin::Button-->
                    <a href="{{ url('parameters') }}" class="btn btn-light me-3 rounded-sm">Back</a>
                    <!--end::Button-->
                    <!--begin::Button-->
                    <button type="submit" name="button" class="btn btn-primary rounded-sm">Submit</button>
                    <!--end::Button-->
                </div>
            </form>
        </div>
    </div>

    @section('scripts')
        <script src="{{ asset('cztemp/assets/custom/js/parameter.js') }}"></script>
    @endsection
</x-app-layout>
