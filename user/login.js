// $(".header-bottom").hide();
// $(".main-container").css("margin-top", "60px");

function onChangeCallback(ctr){
    var country = $("#countries").val(ctr);
    if (ctr == 'IN') {
        $("#currency option").removeAttr('selected').filter('[value=1]').attr('selected', true);
        $(".styledSelect").text('INR');
    } else {
        $("#currency option").removeAttr('selected').filter('[value=2]').attr('selected', true);
        $(".styledSelect").text('USD');
    }
}

$(document).ready(function () {
    $("#otp_div").hide();
    $("#otp_error").hide();
    $("#registerError").hide();
    $("#confirm_password-error").hide();
    $("#new_password-error").hide();

    const promocode = $("#referral_code").val();
    if (promocode != '' && promocode != undefined) {
        $('#register-modal').modal('show');
        $("#promocode").val(promocode);
        $("#promo_code").val(promocode);
        
    } 
})

$("#login").on('click', function() {
    $("#username").val('');
    $("#password").val('');
    $("#login-error").hide();
    $("#username-error").hide();
    $("#password-error").hide();

})

function login_ajax(logindata, redirect_url) {
    $.ajax({
        url: '/auth/login',
        data: logindata,
        type: "POST",
        dataType: "json",
        success: function(result) {
            // Reset login button state
            $("#loginSubmit").prop('disabled', false).html('LOGIN');
            
            if (result.status === 1) {
                // On successful login, redirect
                window.location.href = redirect_url;
            } else {
                // Show error message
                if (result.message) {
                    $('#login-error').html('<i class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; margin-right: 5px;">error</i> ' + result.message).fadeIn();
                } else {
                    $('#login-error').html('<i class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; margin-right: 5px;">error</i> Invalid email/phone or password').fadeIn();
                }
                
                // Add error class to input fields
                $('#username, #password').addClass('is-invalid');
                
                // Shake animation for error feedback
                $('#loginForm').addClass('shake-animation');
                setTimeout(function() {
                    $('#loginForm').removeClass('shake-animation');
                }, 500);
            }
        },
        error: function(xhr, status, error) {
            // Handle AJAX errors
            $("#loginSubmit").prop('disabled', false).html('LOGIN');
            
            let errorMessage = 'An error occurred. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            $('#login-error').html('<i class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; margin-right: 5px;">error</i> ' + errorMessage).fadeIn();
            
            // Add error class to input fields
            $('#username, #password').addClass('is-invalid');
        }
    });
}

$('#loginForm').validate({
    rules: {
        username: {
            required: true
        },
        password: {
            required: true,
            minlength: 6
        }
    },
    messages: {
        username: {
            required: "Please enter your email or phone number",
        },
        password: {
            required: "Please enter your password",
            minlength: "Password must be at least 6 characters long"
        }
    },
    errorElement: 'div',
    errorPlacement: function(error, element) {
        error.appendTo($('#' + element.attr('id') + '-error'));
        $('#' + element.attr('id') + '-error').show();
    },
    highlight: function(element, errorClass, validClass) {
        $(element).addClass('is-invalid').removeClass('is-valid');
        $(element).closest('.login-controls').addClass('has-error');
    },
    unhighlight: function(element, errorClass, validClass) {
        $(element).removeClass('is-invalid').addClass('is-valid');
        $(element).closest('.login-controls').removeClass('has-error');
        $('#' + $(element).attr('id') + '-error').hide();
    },
    submitHandler: function(form) {
        // Clear previous errors
        $('#login-error').hide().text('');
        $('.is-invalid').removeClass('is-invalid');
        
        $("#loginSubmit").prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...');
        
        // Use login_ajax function to handle the login
        login_ajax($(form).serialize(), "/dashboard");
    }
});

// Add input event listeners to clear error messages when typing
$('#username, #password').on('input', function() {
    const fieldId = $(this).attr('id');
    $('#' + fieldId + '-error').hide();
    $(this).removeClass('is-invalid');
    $('#login-error').hide();
});

$( "#forgotPassword" ).on('click', function(){
    $(".email_text").text("To recover your password, enter your email or phone number used during registration");
    $(".email_text").css("color","#094b95");
    $("#processSubmit").text('PROCEED');
    $("#user_name_div").show();
    $("#otp_div").hide();
    $("#user_name").val('');
    $("#processSubmit").prop('disabled', false);
    $("#otp_error").hide();
    $("#otp").val('');
    $("#otp").prop('disabled', false);
});

$("#forgotPasswordForm").on('submit', function(e) {
    e.preventDefault(); 
    $("#processSubmit").prop('disabled', true);
    $.ajax({
        url: '/forgot_password_post',
        data: $(this).serialize(),
        type: "POST",
        dataType: "json",
        success: function(result) {
            $("#processSubmit").prop('disabled', false);
            if (result.isSuccess) {
                $(".email_text").text(result.message);
                $(".email_text").css("color","#88c20a");
                $("#user_name").val(result.data.username);
                $("#user_name_div").hide();
                $("#otp_div").show();
                $("#processSubmit").text('SEND CODE AGAIN');
                $("#processSubmit").prop('disabled', true);
                $("#otp_id").val(result.data.id);
                setTimeout(() => {
                    $("#processSubmit").prop('disabled', false);
                }, 10000);
            } else {
            }
        }
    });
})

$("#otp").on('input', function() {
    var otp = $(this).val();
    var otp_id = $("#otp_id").val();
    var username = $("#user_name").val();
    if(otp.length == 4) {
        $(this).prop('disabled', true);
        $.ajax({
            url  : '/verify_otp',
            type : 'post',
            data :  {
                'otp' : otp,
                'otp_id' : otp_id,
                'username' : username,
            },
            success : function(result) {
                $("#new_password").val('');
                $("#confirm_password").val('');
                $("#confirm_password-error").hide();
                $("#new_password-error").hide();
                if(result.isSuccess) {
                    $('#forgot-modal').modal('hide');
                    $('#reset-password-modal').modal('show');
                    $("#reset_username").val(result.data.username);
                    $("#otp_error").hide();
                } else {
                    $("#otp").prop('disabled', false);
                    $("#otp_error").text(result.message);
                    $("#otp_error").show();
                }
            }
        })
    }
})

$('#resetPasswordForm').validate({ 
    rules : {
        password : {
            minlength : 6,
        },
        confirm_password : {
            equalTo: "#reset_password"
        }
    },
    messages : {
        password : {
            minlength : "Minimum password length is 6 characters",
        },
        confirm_password : {
            equalTo: "Passwords don't match"
        }
    },
    submitHandler: function(form) {
        $("#saveSubmit").prop('disabled', true);
        $.ajax({
            url: '/reset_password',
            data : $(form).serialize(),
            type: "POST",
            success: function(result) {
                if (result.isSuccess) {
                    $("#saveSubmit").prop('disabled', false);
                    $('#reset-password-modal').modal('hide');
                    let data = {
                        username : result.data.username,
                        password : result.data.password,
                    }
                    // login_ajax(data, '/aviator')
                    login_ajax(data, '/dashboard')
                } else {
    
                }
            }
        });
    }
});

$('#registerViaEmailForm').validate({
    rules: {
        email: {
            required: true
        },
        regpassword: {
            required: function (element) {
                return $('#email').val() != '' && $('#regpassword').val() == '';
            }
        }
    },
    messages: {
        email: {
            required: "Field must not be empty!",
        },
        regpassword: {
            required: "Field must not be empty!",
        }
    },
    submitHandler: function(form) {
        $(".registerSubmit").prop('disabled', true);
        $.ajax({
            url: $(form).attr('action'),
            data: $(form).serialize(),
            type: "POST",
            dataType: "json",
            success: function(result) {
                console.log(result);
                $("#email").val('');
                $("#regpassword").val('');
                $(".registerSubmit").prop('disabled', false);
                if(result.isSuccess) {
                    $('#register-modal').modal('hide');
                    const data = {
                        _token: result.data.token,
                        username : result.data.username,
                        password : result.data.password,
                    }
                    login_ajax(data,'/dashboard')
                } else if (result.data.is_email_exist == 1) {
                    $('#forgot-modal').modal('show');
                    $("#user_name").val(result.data.email);
                } else {
                    $("#promo_code_error").show();
                    $("#promo_code_error").text(result.message);
                }
            }
        });
    }
});
$('#amounttransfer').validate({
    rules: {
        userid: {
            required: true
        },
        amount: {
            required: true
        }
    },
    messages: {
        userid: {
            required: "Enter user id!",
        },
        amount: {
            required: "Enter Amount!",
        }
    },
    submitHandler: function(form) {
        $(".registerSubmit").prop('disabled', true);
        $.ajax({
            url: $(form).attr('action'),
            data: $(form).serialize(),
            type: "POST",
            dataType: "json",
            success: function(result) {
                $("#userid").val('');
                $("#amount").val('');
                $(".registerSubmit").prop('disabled', false);
                if(result.isSuccess) {
                    window.location.href='/';
                } else {
                    $("#promo_code_error").show();
                    $("#promo_code_error").html(result.message);
                }
            }
        });
    }
});

$('#registerOneClickForm').validate({
    submitHandler: function(form) {
        $("#registerSubmit").prop('disabled', true);
        $.ajax({
            url: $(form).attr('action'),
            data: $(form).serialize(),
            type: "POST",
            dataType: "json",
            success: function(result) {
                console.log(result);
                if(result.isSuccess) {
                    const data = {
                        username : result.data.user_name,
                        password : result.data.password,
                    }
                    login_ajax(data,`/deposit?username=${result.data.user_name}&password=${result.data.password}`)
                } else {    
                    $("#promocode_error").text(result.message);
                    $("#promocode_error").show();

                }
            }
        });
    }
});

$(".reg_btn").on('click', function() {
    $("#promocode").val('');
    $("#reg_email").val('');
    $("#regpassword").val('');
    $("#promo_code").val('');
    $("#promo_code_error").hide();
    $("#promocode_error").hide();
})

$("#one_click_check").click(function() {
    if(!$(this).is(":checked")) {
        $("#one_click_register").prop('disabled', true)
        $("#one_click_register").css({
            'background-image' : 'linear-gradient(0deg,#9fa8b3,#becad7)',
            'box-shadow'       : 'none',
            'color'            : '#d4d9df',
        })
    } else {
        $("#one_click_register").prop('disabled', false)
        $("#one_click_register").css({
            'background-image' : 'linear-gradient(0deg,#fa5e00 0,#fa7c00)',
            'box-shadow'       : '0 20px 30px rgb(250 65 0 / 40%)',
            'color'            : '#fff',
        })
    }  
}); 

$("#email_policy").click(function() {
    if(!$(this).is(":checked")) {
        $("#register_via_email").prop('disabled', true)
        $("#register_via_email").css({
            'background-image' : 'linear-gradient(0deg,#9fa8b3,#becad7)',
            'box-shadow'       : 'none',
            'color'            : '#d4d9df',
        })
    } else {
        $("#register_via_email").prop('disabled', false)
        $("#register_via_email").css({
            'background-image' : 'linear-gradient(0deg,#fa5e00 0,#fa7c00)',
            'box-shadow'       : '0 20px 30px rgb(250 65 0 / 40%)',
            'color'            : '#fff',
        })
    }  
}); 

/*-------HINAL (START)-------*/

$("#view_password").on('click', function() {
    let type = $("#password").prop('type')
    if (type == 'password') {
        $(this).text('visibility');
        $("#password").prop('type', 'text');
    } else {
        $(this).text('visibility_off');
        $("#password").prop('type', 'password');
    }
})

$("#view_password_register").on('click', function() {
    let type = $("#regpassword").prop('type')
    if (type == 'password') {
        $(this).text('visibility');
        $("#regpassword").prop('type', 'text');
    } else {
        $(this).text('visibility_off');
        $("#regpassword").prop('type', 'password');
    }
})

/*-------HINAL (END)-------*/