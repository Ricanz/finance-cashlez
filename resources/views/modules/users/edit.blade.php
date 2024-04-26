<x-app-layout>
    <div class="container">
        <div class="card card-flush px-10 py-6 rounded-sm">
            <div class="card-title">
                <h2 class="fw-bolder">Edit user</h2>
            </div>
            <form class="form" action="#" id="update_user_form">
                @csrf
                <input type="hidden" name="uuid" value="{{ $data->uuid }}">
                <div class="py-10 px-lg-17">
                    <div class="scroll-y me-n7 pe-7">
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fs-6 fw-bold mb-2">Name</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" class="form-control form-control-solid" placeholder="Place your name"
                                name="name" value="{{ $data->name }}" required />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold mb-2">
                                <span class="required">Email</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Email address must be active"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="email" class="form-control form-control-solid" placeholder="Place your email"
                                name="email" value="{{ $data->email }}" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="d-flex flex-column mb-7 fv-row">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold mb-2">
                                <span class="required">Role</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Role of user"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="role_id" aria-label="Select a Role" data-control="select2"
                                data-placeholder="Select a Role..." class="form-select form-select-solid fw-bolder">
                                <option value="{{ $data->role }}">{{ App\Helpers\Utils::getRoleName($data->role) }}</option>
                                @foreach ($role as $item)
                                    <option value="{{ $item->id }}">{{ $item->title }}</option>
                                @endforeach
                            </select>
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold mb-2">
                                <span class="required">Password</span>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="password" class="form-control form-control-solid" placeholder="Place your password" name="password"
                                required />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold mb-2">
                                <span class="required">Password Confirmation</span>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="password" class="form-control form-control-solid" placeholder="Place your password confirmarion" name="password_confirmation"
                                required />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>
                    
                </div>
                <!--end::Modal body-->
                <div class="modal-footer flex-center">
                    <!--begin::Button-->
                    <a href="/users" class="btn btn-light me-3">Back</a>
                    <!--end::Button-->
                    <!--begin::Button-->
                    <button type="submit" name="button" class="btn btn-primary">Save</button>
                    <!--end::Button-->
                </div>
            </form>
        </div>
    </div>

    @section('scripts')
        <script src="{{ asset('cztemp/assets/custom/js/user.js') }}"></script>
    @endsection
</x-app-layout>
