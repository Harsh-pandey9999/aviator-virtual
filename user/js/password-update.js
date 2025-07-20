// Add success animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes successTick {
        0% { transform: scale(0.5); opacity: 0; }
        70% { transform: scale(1.1); opacity: 1; }
        100% { transform: scale(1); }
    }
    .success-animation {
        text-align: center;
        padding: 20px;
    }
    .success-animation .checkmark {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: inline-block;
        stroke-width: 3px;
        stroke: #4bb71b;
        stroke-miterlimit: 10;
        margin: 20px auto;
        box-shadow: inset 0px 0px 0px #4bb71b;
        animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
    }
    .success-animation .checkmark__circle {
        stroke-dasharray: 166;
        stroke-dashoffset: 166;
        stroke-width: 3px;
        stroke-miterlimit: 10;
        stroke: #4bb71b;
        fill: none;
        animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }
    .success-animation .checkmark__check {
        transform-origin: 50% 50%;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }
    @keyframes stroke {
        100% { stroke-dashoffset: 0; }
    }
    @keyframes scale {
        0%, 100% { transform: none; }
        50% { transform: scale3d(1.1, 1.1, 1); }
    }
    @keyframes fill {
        100% { box-shadow: inset 0px 0px 0px 40px rgba(75, 183, 27, 0); }
    }
`;
document.head.appendChild(style);

// Toggle password visibility
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility for all password fields
    document.addEventListener('click', function(e) {
        if (e.target.closest('.toggle-password') || e.target.classList.contains('toggle-password')) {
            const toggleBtn = e.target.closest('.toggle-password') || e.target;
            const targetId = toggleBtn.getAttribute('data-target');
            if (!targetId) return;
            
            const input = document.getElementById(targetId);
            if (!input) return;
            
            const icon = toggleBtn.querySelector('.material-symbols-outlined');
            
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) icon.textContent = 'visibility';
            } else {
                input.type = 'password';
                if (icon) icon.textContent = 'visibility_off';
            }
        }
    });
});

// Password update form submission
$(document).on('submit', '#changePasswordForm', function(e) {
    e.preventDefault();
    
    // Reset validation
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    
    // Client-side validation
    let isValid = true;
    const currentPassword = $('#current_password').val();
    const newPassword = $('#new_password').val();
    const confirmPassword = $('#new_password_confirmation').val();
    
    // Validate current password
    if (!currentPassword) {
        $('#current_password').addClass('is-invalid');
        $('#current_password').siblings('.invalid-feedback').text('Current password is required.');
        isValid = false;
    }
    
    // Validate new password
    if (!newPassword) {
        $('#new_password').addClass('is-invalid');
        $('#new_password').siblings('.invalid-feedback').text('New password is required.');
        isValid = false;
    } else if (newPassword.length < 6) {
        $('#new_password').addClass('is-invalid');
        $('#new_password').siblings('.invalid-feedback').text('Password must be at least 6 characters.');
        isValid = false;
    }
    
    // Validate password confirmation
    if (!confirmPassword) {
        $('#new_password_confirmation').addClass('is-invalid');
        $('#new_password_confirmation').siblings('.invalid-feedback').text('Please confirm your new password.');
        isValid = false;
    } else if (newPassword !== confirmPassword) {
        $('#new_password_confirmation').addClass('is-invalid');
        $('#new_password_confirmation').siblings('.invalid-feedback').text('Passwords do not match.');
        isValid = false;
    }
    
    // Prevent form submission if validation fails
    if (!isValid) {
        toastr.error('Please correct the errors in the form.');
        return false;
    }
    
    // Show loading state
    const submitBtn = $(this).find('button[type="submit"]');
    const originalBtnText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
    
    // Get CSRF token from meta tag
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // Prepare form data
    const formData = {
        _token: csrfToken,
        current_password: currentPassword,
        new_password: newPassword,
        new_password_confirmation: confirmPassword
    };
    
    console.log('Sending password update request:', formData);
    
    // Send AJAX request
    $.ajax({
        url: '/update-password',
        method: 'POST',
        data: formData,
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function(response) {
            console.log('Password update response:', response);
            
            if (response.status === 1) {
                // Show success animation and message
                const successHtml = `
                    <div class="success-animation text-center py-4">
                        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                            <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                        <h4 class="text-success mt-3">${response.message || 'Password updated successfully!'}</h4>
                        <p class="text-muted">You will be redirected shortly...</p>
                    </div>
                `;
                
                // Replace form with success message
                $('#changePasswordForm').html(successHtml);
                
                // Close modal and redirect after delay
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
                    if (modal) {
                        modal.hide();
                    } else {
                        $('#changePasswordModal').modal('hide');
                    }
                    
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        window.location.reload();
                    }
                }, 2500);
            } else {
                // Show error message
                if (response.errors) {
                    // Handle validation errors
                    $.each(response.errors, function(field, messages) {
                        const inputField = $('#' + field);
                        inputField.addClass('is-invalid');
                        inputField.siblings('.invalid-feedback').text(Array.isArray(messages) ? messages[0] : messages);
                    });
                    toastr.error('Please correct the errors in the form.');
                } else {
                    toastr.error(response.message || 'Failed to update password. Please try again.');
                }
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        },
        error: function(xhr, status, error) {
            console.error('Password update error:', { status, error, response: xhr.responseText });
            
            let errorMessage = 'An error occurred while updating your password. Please try again.';
            
            try {
                const response = xhr.responseJSON || {};
                
                if (xhr.status === 422 && response.errors) {
                    // Laravel validation errors
                    $.each(response.errors, function(field, messages) {
                        const inputField = $('#' + field);
                        inputField.addClass('is-invalid');
                        inputField.siblings('.invalid-feedback').text(Array.isArray(messages) ? messages[0] : messages);
                    });
                    errorMessage = 'Please correct the errors in the form.';
                } else if (response.message) {
                    errorMessage = response.message;
                }
            } catch (e) {
                console.error('Error parsing error response:', e);
            }
            
            toastr.error(errorMessage);
            submitBtn.prop('disabled', false).html(originalBtnText);
        },
        complete: function() {
            // Reset button state if not already done
            if ($('#changePasswordForm').find('.success-animation').length === 0) {
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        }
    });
});
