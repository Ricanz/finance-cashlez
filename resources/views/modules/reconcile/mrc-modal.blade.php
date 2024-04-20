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
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none">
                            <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                transform="rotate(-45 6 17.3137)" fill="black" />
                            <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                transform="rotate(45 7.41422 6)" fill="black" />
                        </svg>
                    </span>
                    <!--end::Svg Icon-->
                </div>
                <!--end::Close-->
            </div>
            <!--begin::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <!--begin::Heading-->
                <div class="mb-4 text-start">
                    <!--begin::Title-->
                    <h1 class="mb-1">Detail Settlement</h1>
                    <!--end::Title-->
                </div>
                <!--end::Heading-->
                <div class="separator my-2"></div>

                <!--begin::Input group-->
                <div class="d-flex flex-column mb-2 fv-row">
                    <!--begin::Label-->
                    <label class="d-flex align-items-center mb-2 fs-4 ">
                        <span class="w-150px text-start mx-4 fw-bold">Settlement Date</span>
                        <span class="w-10px text-start mx-4">:</span>
                        <span class="text-start" id="settlementDate"></span>
                    </label>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="d-flex flex-column mb-2 fv-row">
                    <!--begin::Label-->
                    <label class="d-flex align-items-center mb-2 fs-4 ">
                        <span class="w-150px text-start mx-4 fw-bold">Batch</span>
                        <span class="w-10px text-start mx-4">:</span>
                        <span class="text-start" id="batch"></span>
                    </label>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="d-flex flex-column mb-2 fv-row">
                    <!--begin::Label-->
                    <label class="d-flex align-items-center mb-2 fs-4 ">
                        <span class="w-150px text-start mx-4 fw-bold">Bank Type</span>
                        <span class="w-10px text-start mx-4">:</span>
                        <span class="text-start" id="bankType"></span>
                    </label>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="d-flex flex-column mb-2 fv-row">
                    <!--begin::Label-->
                    <label class="d-flex align-items-center mb-2 fs-4 ">
                        <span class="w-150px text-start mx-4 fw-bold">MRC</span>
                        <span class="w-10px text-start mx-4">:</span>
                        <span class="text-start" id="mrc"></span>
                    </label>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="d-flex flex-column mb-2 fv-row">
                    <!--begin::Label-->
                    <label class="d-flex align-items-center mb-2 fs-4 ">
                        <span class="w-150px text-start mx-4 fw-bold">Merchant Name</span>
                        <span class="w-10px text-start mx-4">:</span>
                        <span class="text-start" id="merchantName"></span>
                    </label>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="d-flex flex-column mb-2 fv-row">
                    <!--begin::Label-->
                    <label class="d-flex align-items-center mb-2 fs-4 ">
                        <span class="w-150px text-start mx-4 fw-bold">Gross Trf Amount</span>
                        <span class="w-10px text-start mx-4">:</span>
                        <span class="text-start" id="grossTrf"></span>
                    </label>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="d-flex flex-column mb-2 fv-row">
                    <!--begin::Label-->
                    <label class="d-flex align-items-center mb-2 fs-4 ">
                        <span class="w-150px text-start mx-4 fw-bold">Bank Admin</span>
                        <span class="w-10px text-start mx-4">:</span>
                        <span class="text-start" id="bankAdmin"></span>
                    </label>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="d-flex flex-column mb-2 fv-row">
                    <!--begin::Label-->
                    <label class="d-flex align-items-center mb-2 fs-4 ">
                        <span class="w-150px text-start mx-4 fw-bold">Net Trf</span>
                        <span class="w-10px text-start mx-4">:</span>
                        <span class="text-start" id="netTransfer"></span>
                    </label>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="d-flex flex-column mb-2 fv-row">
                    <!--begin::Label-->
                    <label class="d-flex align-items-center mb-2 fs-4 ">
                        <span class="w-150px text-start mx-4 fw-bold">Account Number</span>
                        <span class="w-10px text-start mx-4">:</span>
                        <span class="text-start" id="accountNumber"></span>
                    </label>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="d-flex flex-column mb-2 fv-row">
                    <!--begin::Label-->
                    <label class="d-flex align-items-center mb-2 fs-4 ">
                        <span class="w-150px text-start mx-4 fw-bold">Bank Code</span>
                        <span class="w-10px text-start mx-4">:</span>
                        <span class="text-start" id="bankCode"></span>
                    </label>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="d-flex flex-column mb-2 fv-row">
                    <!--begin::Label-->
                    <label class="d-flex align-items-center mb-2 fs-4 ">
                        <span class="w-150px text-start mx-4 fw-bold">Bank Name</span>
                        <span class="w-10px text-start mx-4">:</span>
                        <span class="text-start" id="bankName"></span>
                    </label>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="d-flex flex-column mb-2 fv-row">
                    <!--begin::Label-->
                    <label class="d-flex align-items-center mb-2 fs-4 ">
                        <span class="w-150px text-start mx-4 fw-bold">Account Holder</span>
                        <span class="w-10px text-start mx-4">:</span>
                        <span class="text-start" id="accounttHolder"></span>
                    </label>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="d-flex flex-column mb-2 fv-row">
                    <!--begin::Label-->
                    <label class="d-flex align-items-center mb-2 fs-4 ">
                        <span class="w-150px text-start mx-4 fw-bold">Account Email</span>
                        <span class="w-10px text-start mx-4">:</span>
                        <span class="text-start" id="accountEmail"></span>
                    </label>
                </div>
                <!--end::Input group-->
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
