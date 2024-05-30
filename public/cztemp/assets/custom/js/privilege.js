"use strict";
var KTUsersUpdatePermissions = (function () {
    const t = document.getElementById("kt_modal_update_role"),
        e = t.querySelector("#kt_modal_update_role_form"),
        n = new bootstrap.Modal(t);
    return {
        init: function () {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        role_name: {
                            validators: {
                                notEmpty: { message: "Role name is required" },
                            },
                        },
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap: new FormValidation.plugins.Bootstrap5({
                            rowSelector: ".fv-row",
                            eleInvalidClass: "",
                            eleValidClass: "",
                        }),
                    },
                });
                t
                    .querySelector('[data-kt-roles-modal-action="close"]')
                    .addEventListener("click", (t) => {
                        t.preventDefault(),
                            Swal.fire({
                                text: "Are you sure you would like to close?",
                                icon: "warning",
                                showCancelButton: !0,
                                buttonsStyling: !1,
                                confirmButtonText: "Yes, close it!",
                                cancelButtonText: "No, return",
                                customClass: {
                                    confirmButton: "btn btn-primary",
                                    cancelButton: "btn btn-active-light",
                                },
                            }).then(function (t) {
                                t.value && n.hide();
                            });
                    }),
                    t
                        .querySelector('[data-kt-roles-modal-action="cancel"]')
                        .addEventListener("click", (t) => {
                            t.preventDefault(),
                                Swal.fire({
                                    text: "Are you sure you would like to cancel?",
                                    icon: "warning",
                                    showCancelButton: !0,
                                    buttonsStyling: !1,
                                    confirmButtonText: "Yes, cancel it!",
                                    cancelButtonText: "No, return",
                                    customClass: {
                                        confirmButton: "btn btn-primary",
                                        cancelButton: "btn btn-active-light",
                                    },
                                }).then(function (t) {
                                    t.value
                                        ? (e.reset(), n.hide())
                                        : "cancel" === t.dismiss &&
                                          Swal.fire({
                                              text: "Your form has not been cancelled!.",
                                              icon: "error",
                                              buttonsStyling: !1,
                                              confirmButtonText: "Ok, got it!",
                                              customClass: {
                                                  confirmButton:
                                                      "btn btn-primary",
                                              },
                                          });
                                });
                        });
                const i = t.querySelector(
                    '[data-kt-roles-modal-action="submit"]'
                );
                i.addEventListener("click", function (t) {
                    t.preventDefault();
                    o &&
                        o.validate().then(function (t) {
                            console.log("validated!");
                            if ("Valid" == t) {
                                i.setAttribute("data-kt-indicator", "on");
                                i.disabled = true;

                                // Collect form data
                                var formData = new FormData(
                                    document.getElementById("kt_modal_update_role_form")
                                );

                                // Make AJAX request
                                $.ajax({
                                    url: baseUrl + "/privilege/update",
                                    type: "POST",
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    success: function (data) {
                                        console.log(data); // Handle response data as needed

                                        // Reset form state
                                        i.removeAttribute("data-kt-indicator");
                                        i.disabled = false;

                                        // Show success message
                                        Swal.fire({
                                            text: "Form has been successfully submitted!",
                                            icon: "success",
                                            buttonsStyling: false,
                                            confirmButtonText: "Ok, got it!",
                                            customClass: {
                                                confirmButton:
                                                    "btn btn-primary",
                                            },
                                        }).then(function (t) {
                                            if (t.isConfirmed) {
                                                // n.hide();
                                                window.location.href = baseUrl + '/roles'
                                            }
                                        });
                                    },
                                    error: function (
                                        jqXHR,
                                        textStatus,
                                        errorThrown
                                    ) {
                                        console.error("Error:", errorThrown);

                                        // Reset form state
                                        i.removeAttribute("data-kt-indicator");
                                        i.disabled = false;

                                        // Show error message
                                        Swal.fire({
                                            text: "An error occurred. Please try again later.",
                                            icon: "error",
                                            buttonsStyling: false,
                                            confirmButtonText: "Ok, got it!",
                                            customClass: {
                                                confirmButton:
                                                    "btn btn-primary",
                                            },
                                        });
                                    },
                                });
                            } else {
                                // If validation fails, show error message
                                Swal.fire({
                                    text: "Sorry, looks like there are some errors detected, please try again.",
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn btn-primary",
                                    },
                                });
                            }
                        });
                });
            })();
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    KTUsersUpdatePermissions.init();
});

function privilegeDetail(slug) {
    var tbody = document.querySelector("#table_privilege_list tbody");
    tbody.innerHTML = "";
    var row = document.createElement("tr");
    row.innerHTML = `
        <tr>
            <td class="text-gray-800">Administrator Access
            </td>
            <td>
                <label
                    class="form-check form-check-sm form-check-custom form-check-solid me-9">
                    <input class="form-check-input" type="checkbox"
                        value="0" id="selectAll" onchange="selectAllCheckbox()" name="select_all"/>
                    <span class="form-check-label"
                        for="selectAll">Select all</span>
                </label>
            </td>
        </tr>
        `;
    tbody.appendChild(row);

    $.ajax({
        url: baseUrl + "/role/detail/" + slug,
        type: "GET",
        success: function (response) {
            var data = response.data;
            var header = response.header;
            document.getElementById("roleName").value = header.title;
            document.getElementById("idRole").value = header.id;
            data.forEach((item) => {
                var content = document.createElement("tr");
                if (item.title == "Disbursement") {
                    content.innerHTML = `
                        <tr>
                            <td class="text-gray-800">${item.title}</td>
                            <td>
                                <div class="d-flex">
                                    <label
                                        class="form-check form-check-sm form-check-custom form-check-solid me-3 me-lg-10">
                                        <input class="form-check-input" type="checkbox"
                                            value="${!item.read ? "0" : "1"}" name="read_${item.id}" ${!item.read ? "" : "checked"}/>
                                        <span class="form-check-label">View</span>
                                    </label>
                                    <label
                                        class="form-check form-check-custom form-check-solid me-3 me-lg-10">
                                        <input class="form-check-input" type="checkbox"
                                            value="${!item.create ? "0" : "1"}" name="create_${item.id}" ${!item.create ? "" : "checked"}/>
                                        <span class="form-check-label">Create</span>
                                    </label>
                                    <label
                                        class="form-check form-check-custom form-check-solid me-3 me-lg-10">
                                        <input class="form-check-input" type="checkbox"
                                            value="${!item.update ? "0" : "1"}" name="update_${item.id}" ${!item.update ? "" : "checked"}/>
                                        <span class="form-check-label">Approve</span>
                                    </label>
                                    <label
                                        class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox"
                                            value="${!item.delete ? "0" : "1"}" name="delete_${item.id}" ${!item.delete ? "" : "checked"}/>
                                        <span class="form-check-label">Reject</span>
                                    </label>
                                </div>
                            </td>
                        </tr>`;
                    tbody.appendChild(content);
                } else if(item.title == "Reconcile Result"){
                    content.innerHTML = `
                        <tr>
                            <td class="text-gray-800">${item.title}</td>
                            <td>
                                <div class="d-flex">
                                    <label
                                        class="form-check form-check-sm form-check-custom form-check-solid me-3 me-lg-10">
                                        <input class="form-check-input" type="checkbox"
                                            value="${!item.read ? "0" : "1"}" name="read_${item.id}" ${!item.read ? "" : "checked"}/>
                                        <span class="form-check-label">View</span>
                                    </label>
                                </div>
                            </td>
                        </tr>`;
                    tbody.appendChild(content);
                }
                else {
                    content.innerHTML = `
                        <tr>
                            <td class="text-gray-800">${item.title}</td>
                            <td>
                                <div class="d-flex">
                                    <label
                                        class="form-check form-check-sm form-check-custom form-check-solid me-3 me-lg-10">
                                        <input class="form-check-input" type="checkbox"
                                            value="${!item.read ? "0" : "1"}" name="read_${item.id}" ${!item.read ? "" : "checked"}/>
                                        <span class="form-check-label">View</span>
                                    </label>
                                    <label
                                        class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox"
                                            value="${!item.read ? "0" : "1"}" name="create_${item.id}" ${!item.create ? "" : "checked"}/>
                                        <span class="form-check-label">Create</span>
                                    </label>
                                </div>
                            </td>
                        </tr>`;
                    tbody.appendChild(content);
                }
            });
        },
        error: function (xhr, status, error) {
            Swal.fire({
                text: "Failed to get data.",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn fw-bold btn-primary",
                },
            });
        },
    });
}

function selectAllCheckbox() {
    var val = $("#selectAll").prop("checked");
    if (!val) {
        $('input[type="checkbox"]').prop("checked", false);
    } else {
        $('input[type="checkbox"]').prop("checked", true);
    }
}

$("#kt_roles_select_all").on("click", function (event) {
    console.log(event);
});

$(document).ready(function () {});
