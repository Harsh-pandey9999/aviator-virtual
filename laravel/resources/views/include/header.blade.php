<!--====== Header Start ======-->
<header>
    <div class="header-top">
        <div class="header-left" onclick="window.location.href='/dashboard'">
            <img src="images/logo.png" class="logo1" />
        </div>
        @if (session()->has('userlogin'))
            <div class="header-right d-flex align-items-center">
                <a href="/deposit">
                    <button class="deposite-btn rounded-pill d-flex align-items-center me-2">
                        <span class="material-symbols-outlined me-2"> payments </span>
                        <!-- <span>$</span> -->
                        <span class="me-2" id="header_wallet_balance">₹{{ wallet(user('id')) }}</span>
                        DEPOSIT
                    </button>
                </a>
                <div class="btn-group">
                    <button type="button"
                        class="btn btn-transparent dropdown-toggle p-0 d-flex align-items-center justify-content-center caret-none"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="material-symbols-outlined f-24 menu-icon text-white">
                            menu
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark profile-dropdown p-0">
                        <li class="profile-head d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <img src="images/avtar/av-1.png" class="avtar-ico" id="avatar_img">
                                <div>
                                    <div class="profile-name mb-1">{{ user('email') }} </div>
                                    <div class="profile-name" id="username">{{ user('id') }}</div>
                                </div>

                            </div>
                        </li>


                        <li>
                            <a href="/crash" class="f-12 justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined ico f-20">
                                        flight_takeoff
                                    </span>
                                    <img src="../../images/logo.svg" class="side_logo">
                                </div>
                            </a>
                        </li>


                        <li>
                            <a href="/deposit" class="f-12 justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined ico f-20">
                                        payments
                                    </span>DEPOSIT FUNDS
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="/withdraw" class="f-12 justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined ico f-20">
                                        payments
                                    </span>WITHDRAW FUNDS FROM THE ACCOUNT
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="/amount-transfer" class="f-12 justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined ico f-20">
                                        payments
                                    </span>AMOUNT TRANSFER
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="/profile" class="f-12 justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined ico f-20">
                                        account_circle
                                    </span>PERSONAL DETAILS
                                </div>
                            </a>
                        </li>
                        {{-- <li>
                            <a href="#" class="f-12 justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined ico f-20">
                                        payments
                                    </span>TRANSFER FUNDS
                                </div>
                            </a>
                        </li> --}}
                        <li>
                            <a href="/deposit_withdrawals" class="f-12 justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined ico f-20">
                                        payments
                                    </span>TRANSACTION HISTORY
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="/level-management" class="f-12 justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined ico f-20">
                                        payments
                                    </span>LEVEL MANAGEMENT
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="/referal" class="f-12 justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined ico f-20">
                                        payments
                                    </span>YOUR REFERRALS
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="f-12 justify-content-between" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined ico f-20">
                                        lock
                                    </span>CHANGE PASSWORD
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="/logout" class="f-12 justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined ico f-20">
                                        payments
                                    </span>SIGN OUT
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        @else
            <div class="header-right d-flex align-items-center">
                <button class="register-btn rounded-pill d-flex align-items-center me-2 reg_btn" data-bs-toggle="modal"
                    data-bs-target="#register-modal">
                    Register
                </button>
                <button class="login-btn rounded-pill d-flex align-items-center me-2" data-bs-toggle="modal"
                    data-bs-target="#login-modal" id="login">
                    Login
                </button>
            </div>
        @endif
    </div>
</header>
<!--====== Header End ======-->

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    @csrf
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                <i class="material-symbols-outlined">visibility_off</i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Please enter your current password.</div>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                <i class="material-symbols-outlined">visibility_off</i>
                            </button>
                        </div>
                        <div class="form-text">Password must be at least 6 characters long.</div>
                        <div class="invalid-feedback">Please enter a valid password.</div>
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password_confirmation">
                                <i class="material-symbols-outlined">visibility_off</i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Passwords do not match.</div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<style>
/* Style for password toggle buttons */
.toggle-password {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    padding: 0.375rem 0.75rem;
}

.input-group-text {
    background-color: transparent;
}

/* Ensure the password input and button are properly aligned */
.input-group > .form-control:not(:last-child) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

/* Style for the eye icon */
.material-symbols-outlined {
    font-size: 1.25rem;
    vertical-align: middle;
}
</style>
