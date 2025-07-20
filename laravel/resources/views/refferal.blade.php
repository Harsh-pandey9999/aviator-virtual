@extends('Layout.usergame2')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="text-center">Referral Program</h2>
                    
                    <div class="mb-5">
                        <h4>Your Referral Code</h4>
                        <div class="input-group">
                            <input type="text" class="form-control" id="referralCode" value="{{ user('id') }}" readonly>
                            <button class="btn btn-primary" type="button" id="copyCodeBtn">
                                <i class="fas fa-copy"></i> Copy Code
                            </button>
                        </div>
                    </div>

                    <div>
                        <h4>Your Referral Link</h4>
                        <div class="input-group">
                            <input type="text" class="form-control" id="referralLink" 
                                   value="{{ url('register?refer=' . user('id')) }}" readonly>
                            <button class="btn btn-primary" type="button" id="copyLinkBtn">
                                <i class="fas fa-copy"></i> Copy Link
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block">Share this link with friends to earn rewards!</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="toast"></div>

<style>
    #toast {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #28a745;
        color: white;
        padding: 12px 30px;
        border-radius: 6px;
        z-index: 9999;
        transition: transform 0.3s ease;
        font-weight: 500;
        font-size: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    #toast.show {
        transform: translateX(-50%) translateY(0);
    }
    .copied { 
        background-color: #28a745 !important; 
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set the full referral link
        const referralLink = window.location.origin + '/register?refer=' + '{{ user('id') }}';
        document.getElementById('referralLink').value = referralLink;

        // Simple copy function
        function copyText(text) {
            const input = document.createElement('input');
            input.value = text;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            return true;
        }

        // Handle copy button clicks
        document.getElementById('copyCodeBtn').addEventListener('click', function() {
            const code = document.getElementById('referralCode').value;
            if (copyText(code)) {
                showFeedback(this);
            }
        });
        
        document.getElementById('copyLinkBtn').addEventListener('click', function() {
            const link = document.getElementById('referralLink').value;
            if (copyText(link)) {
                showFeedback(this);
            }
        });

        // Show feedback
        function showFeedback(button) {
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Copied!';
            button.classList.add('copied');
            
            // Show toast
            const toast = document.getElementById('toast');
            toast.textContent = 'Copied to clipboard!';
            toast.classList.add('show');
            
            // Reset after delay
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.classList.remove('copied');
                toast.classList.remove('show');
            }, 2000);
        }
    });
</script>
@endsection
    </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set the correct referral link
        const referralLink = window.location.origin + '/register?refer=' + '{{ user('id') }}';
        document.getElementById('referralLink').value = referralLink;
        
        // Initialize copy buttons
        initCopyButtons();
    });

    function initCopyButtons() {
        // Handle copy code button
        document.getElementById('copyCodeBtn').addEventListener('click', function() {
            const code = document.getElementById('referralCode');
            copyToClipboard(code, this);
        });

        // Handle copy link button
        document.getElementById('copyLinkBtn').addEventListener('click', function() {
            const link = document.getElementById('referralLink');
            copyToClipboard(link, this);
        });
    }

    function copyToClipboard(element, button) {
        // Select the text
        element.select();
        element.setSelectionRange(0, 99999); // For mobile devices

        try {
            // Copy the text
            document.execCommand('copy');
            
            // Change button style and text
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Copied!';
            button.classList.add('copied');
            
            // Show toast
            showToast('Copied to clipboard!');
            
            // Reset button after 2 seconds
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('copied');
            }, 2000);
            
        } catch (err) {
            console.error('Failed to copy text: ', err);
            showToast('Failed to copy. Please try again.');
        }
    }

    function showToast(message) {
        // Create or get toast element
        let toast = document.getElementById('toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'toast';
            document.body.appendChild(toast);
        }
        
        // Set message and show
        toast.textContent = message;
        toast.classList.add('show');
        
        // Hide after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }
</script>
@endpush

    <style>
        .card {
            border: 1px solid #444;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-body {
            padding: 1.5rem;
        }
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            z-index: 1;
        }
        
        /* Copy Success State */
        .btn.copy-success {
            background-color: #28a745;
            border-color: #28a745;
            transform: scale(0.95);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn.copy-success::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%, -50%);
            transform-origin: 50% 50%;
        }
        
        .btn.copy-success:focus:not(:active)::after {
            animation: ripple 0.6s ease-out;
        }
        
        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            20% {
                transform: scale(20, 20);
                opacity: 0.3;
            }
            100% {
                opacity: 0;
                transform: scale(40, 40);
            }
        }
        .input-group-text {
            background-color: #343a40;
            color: #fff;
            border: 1px solid #495057;
        }
        .form-control:focus {
            background-color: #1e1e1e;
            color: #fff;
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        /* Toast notification styles */
        .toast {
            visibility: hidden;
            min-width: 250px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 4px;
            padding: 12px 24px;
            position: fixed;
            z-index: 1000;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s, visibility 0.3s;
        }
        
        .toast.show {
            visibility: visible;
            opacity: 1;
        }
    </style>
