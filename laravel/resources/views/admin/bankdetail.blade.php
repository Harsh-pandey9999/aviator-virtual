@extends('Layout.admindashboard')
@section('css')
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-home"></i>
                </span> Bank Setup
            </h3>
        </div>
        <div class="row">
            <div class="col-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Bank detail</h4>
                        <form class="forms-sample" id="bankdetail">
                            @csrf
                            <input type="hidden" name="id" value="1">
                            <div class="form-group">
                                <label for="bank_name">Bank Name</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name"
                                    placeholder="Bank Name" value="{{ $bank->bank_name }}">
                            </div>
                            <div class="form-group">
                                <label for="account_no">Account No</label>
                                <input type="text" class="form-control" id="account_no" name="account_no"
                                    placeholder="Account No" value="{{ $bank->account_no }}">
                            </div>
                            <div class="form-group">
                                <label for="holdername">Account holder name</label>
                                <input type="text" class="form-control" id="holdername" name="holdername"
                                    placeholder="Account holder name" value="{{ $bank->account_holder_name }}">
                            </div>
                            <div class="form-group">
                                <label for="ifsccode">IFSC Code</label>
                                <input type="text" class="form-control" id="ifsccode" name="ifsccode"
                                    placeholder="IFSC Code" value="{{ $bank->ifsc_code }}">
                            </div>
                            <div class="form-group">
                                <label for="mobile_no">Mobile no</label>
                                <input type="text" class="form-control" id="mobile_no" name="mobile_no"
                                    placeholder="Mobile No." value="{{ $bank->mobile_no }}">
                            </div>
                            <div class="form-group">
                                <label for="upi_id">UPI Id</label>
                                <input type="text" class="form-control" id="upi_id" name="upi_id"
                                    placeholder="UPI Id" value="{{ $bank->upi_id }}">
                            </div>
                            <div class="form-group">
                                <label for="value">Bar code</label>
                                <input type="file" class="form-control" id="barcode" name="barcode">
                            </div>
                            @if (!empty($bank->barcode))
                                <div class="mt-2">
                                    <p>Current QR Code:</p>
                                    <img src="{{ $bank->barcode }}" alt="QR Code" style="max-width: 260px; height: auto;" class="img-thumbnail">
                                </div>
                            @endif
                            <button type="submit" class="btn btn-gradient-primary me-2">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
@endsection

@section('js')
    <script>
        $("#bankdetail").on('submit', function(e) {
            e.preventDefault();
        });
        
        $("#bankdetail").validate({
            submitHandler: function(form) {
                // Show loading state
                const submitBtn = $(form).find('button[type="submit"]');
                const originalBtnText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
                
                // Submit form via AJAX
                $.ajax({
                    url: "{{ url('admin/api/bankdetail') }}",
                    type: 'POST',
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status === 1) {
                            // Show success message
                            iziToast.success({
                                title: response.title,
                                message: response.message,
                                position: 'topRight'
                            });
                            
                            // Update QR code preview if a new one was uploaded
                            if (response.barcode) {
                                // Remove existing QR code preview if it exists
                                $('.qrcode-preview').remove();
                                
                                // Add new QR code preview
                                const qrCodeHtml = `
                                    <div class="mt-2 qrcode-preview">
                                        <p>QR Code:</p>
                                        <img src="${response.barcode}" alt="QR Code" style="max-width: 260px; height: auto;" class="img-thumbnail">
                                    </div>
                                `;
                                
                                // Insert after the file input
                                $('#barcode').after(qrCodeHtml);
                            }
                            
                            // Redirect if needed
                            setTimeout(() => {
                                window.location.href = "/admin/bank-detail";
                            }, 1000);
                        } else {
                            // Show error message
                            iziToast.error({
                                title: response.title || 'Error!',
                                message: response.message || 'Something went wrong!',
                                position: 'topRight'
                            });
                        }
                    },
                    error: function(xhr) {
                        // Show error message
                        const response = xhr.responseJSON || {};
                        iziToast.error({
                            title: 'Error!',
                            message: response.message || 'Failed to update bank details. Please try again.',
                            position: 'topRight'
                        });
                    },
                    complete: function() {
                        // Re-enable submit button
                        submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                });
            }
        });
    </script>
@endsection
