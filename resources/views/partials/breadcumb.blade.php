<div class="toolbar">
    <!--begin::Container-->
    <div class="container-fluid d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
            <!--begin::Title-->
            @if (request()->segment(1) != '')
                <h1 class="d-flex align-items-center text-dark fw-bolder fs-5 my-1">{{ ucwords(request()->segment(1)) }}
                    <!--begin::Separator-->
                    @if (request()->segment(2))
                        <span class="h-20px border-gray-200 border-start ms-3 mx-2"></span>
                        <!--end::Separator-->
                        <!--begin::Description-->
                        <small class="text-muted fs-6 fw-bold my-1 ms-1">{{ ucwords(request()->segment(2)) }}</small>
                        <!--end::Description-->
                    @endif
                </h1>
            @else
                <h1 class="d-flex align-items-center text-dark fw-bolder fs-5 my-1">Dashboard</h1>
            @endif
            <!--end::Title-->
        </div>
        <!--end::Page title-->
    </div>
    <!--end::Container-->
</div>
