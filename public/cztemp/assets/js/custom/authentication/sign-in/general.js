"use strict";
var KTSigninGeneral = (function () {
    var t, e, i;
    return {
        init: function () {
            (t = document.querySelector("#kt_sign_in_form")),
                (e = document.querySelector("#kt_sign_in_submit")),
                (i = FormValidation.formValidation(t, {
                    fields: {
                        username: {
                            validators: {
                                notEmpty: {
                                    message: "Username is required",
                                },
                            },
                        },
                        password: {
                            validators: {
                                notEmpty: {
                                    message: "The password is required",
                                },
                            },
                        },
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap: new FormValidation.plugins.Bootstrap5({
                            rowSelector: ".fv-row",
                        }),
                    },
                })),
                e.addEventListener("click", function (event) {
                    event.preventDefault();
                    i.validate().then(function (status) {
                        if (status === "Valid") {
                            e.setAttribute("data-kt-indicator", "on");
                            e.disabled = true;
                            
                            // Get form data
                            var formData = $(t).serialize();
                            console.log(formData);
                            // Send AJAX request
                            $.ajax({
                                type: "POST",
                                url: "api/portal/login",
                                data: formData,
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            text: "You have successfully logged in!",
                                            icon: "success",
                                            buttonsStyling: false,
                                            confirmButtonText: "Ok, got it!",
                                            customClass: {
                                                confirmButton: "btn btn-primary",
                                            },
                                        }).then(function () {
                                            t.querySelector('[name="username"]').value = "";
                                            t.querySelector('[name="password"]').value = "";
                                        });
                                    } else {
                                        Swal.fire({
                                            text: response.message || "An error occurred.",
                                            icon: "error",
                                            buttonsStyling: false,
                                            confirmButtonText: "Ok, got it!",
                                            customClass: {
                                                confirmButton: "btn btn-primary",
                                            },
                                        });
                                    }
                                },
                                error: function() {
                                    Swal.fire({
                                        text: "An error occurred. Please try again later.",
                                        icon: "error",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, got it!",
                                        customClass: {
                                            confirmButton: "btn btn-primary",
                                        },
                                    });
                                },
                                complete: function() {
                                    e.removeAttribute("data-kt-indicator");
                                    e.disabled = false;
                                }
                            });
                        } else {
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
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    KTSigninGeneral.init();
});
