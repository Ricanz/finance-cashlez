<x-app-layout>
    <div class="container">
        <div class="card card-flush px-10 py-6 rounded-sm">
            <div class="card-title">
                <h2 class="fw-bolder">Edit role</h2>
            </div>
            <form class="form" action="#" id="update_role_form">
                @csrf
                <input type="hidden" name="id" value="{{ $data->id }}">
                <div class="py-10 px-lg-17">
                    <div class="scroll-y me-n7 pe-7">
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fs-6 fw-bold mb-2">Title</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" class="form-control form-control-solid" placeholder="Place role's title"
                                name="title" value="{{ $data->title }}" required />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>
                    
                </div>
                <!--end::Modal body-->
                <div class="modal-footer flex-center">
                    <!--begin::Button-->
                    <a href="/roles" class="btn btn-light me-3 rounded-sm">Back</a>
                    <!--end::Button-->
                    <!--begin::Button-->
                    <button type="submit" name="button" class="btn btn-primary rounded-sm">Submit</button>
                    <!--end::Button-->
                </div>
            </form>
        </div>
    </div>

    @section('scripts')
        <script src="{{ asset('cztemp/assets/custom/js/role.js') }}"></script>
    @endsection
</x-app-layout>
