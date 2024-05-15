
<div class="modal fade" id="kt_modal_new_target" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px rounded-sm">
        <!--begin::Modal content-->
        <div class="modal-content rounded-sm">
            <!--begin::Modal header-->
            <div class="modal-header pb-0 border-0 justify-content-end">
                <!--begin::Close-->
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                    <span class="svg-icon svg-icon-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                            <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                        </svg>
                    </span>
                    <!--end::Svg Icon-->
                </div>
                <!--end::Close-->
            </div>
            <!--begin::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <!--begin:Form-->
                <form id="store_bank_form" class="form" action="#">
                    @csrf
                    <!--begin::Heading-->
                    <div class="mb-13 text-center">
                        <!--begin::Title-->
                        <h1 class="mb-3">Add New Bank</h1>
                        <!--end::Title-->
                    </div>
                    <!--end::Heading-->
                    
                    <!--begin::Input group-->
                    <div class="d-flex flex-column mb-8 fv-row">
                        <!--begin::Label-->
                        <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                            <span class="required">Name</span>
                            <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Name of the bank"></i>
                        </label>
                        <!--end::Label-->
                        <select name="bank_id" aria-label="Select a Channel"
                            data-placeholder="Select a Channel..." class="form-select form-select-solid fw-bolder">
                            <option value="">Select a Channel...</option>
                            @foreach ($channel as $item)
                                <option value="{{ $item->bank_id }}">{{ $item->channel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="required fs-6 fw-bold mb-2">Report Partner</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <select name="report_partner" aria-label="Select a Parameter"
                            data-placeholder="Select a Parameter..." class="form-select form-select-solid fw-bolder">
                            <option value="">Select a Parameter...</option>
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
                        <select name="bo_detail_transaction" aria-label="Select a Parameter"
                            data-placeholder="Select a Parameter..." class="form-select form-select-solid fw-bolder">
                            <option value="">Select a Parameter...</option>
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
                        <select name="bo_summary" aria-label="Select a Parameter"
                            data-placeholder="Select a Parameter..." class="form-select form-select-solid fw-bolder">
                            <option value="">Select a Parameter...</option>
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
                        <select name="bank_statement" aria-label="Select a Parameter"
                            data-placeholder="Select a Parameter..." class="form-select form-select-solid fw-bolder">
                            <option value="">Select a Parameter...</option>
                            @foreach ($params as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Actions-->
                    <div class="text-center">
                        <button type="reset" id="kt_modal_new_target_cancel" class="btn btn-light me-3 rounded-sm">Cancel</button>
                        <button type="submit" id="kt_modal_new_target_submit" class="btn btn-primary rounded-sm">Submit</button>
                    </div>
                    <!--end::Actions-->
                </form>
                <!--end:Form-->
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>