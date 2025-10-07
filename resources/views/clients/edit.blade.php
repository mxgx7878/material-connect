@extends('layout.main')

@section('title', 'Edit Client - Material Connect')

@push('styles')
<style>
/* Reuse styles from create page */
.form-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.form-section {
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.form-section:last-child {
    border-bottom: none;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.5rem;
    display: flex;
    align-items-center;
    gap: 0.5rem;
}

.section-title i {
    color: #0d6efd;
}

.section-description {
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.5rem;
}

.required-field::after {
    content: ' *';
    color: #dc3545;
    font-weight: bold;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #6c757d;
    z-index: 10;
}

.password-toggle:hover {
    color: #0d6efd;
}

.info-box {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}

.info-box i {
    color: #ffc107;
    font-size: 1.25rem;
}

.info-box.info-blue {
    background: #e7f1ff;
    border-left-color: #0d6efd;
}

.info-box.info-blue i {
    color: #0d6efd;
}

.sticky-footer {
    position: sticky;
    bottom: 0;
    background: white;
    border-top: 1px solid #e9ecef;
    padding: 1.5rem;
    margin: 0 -1.5rem -1.5rem;
    border-radius: 0 0 12px 12px;
}

.client-status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.delete-zone {
    background: #fff5f5;
    border: 2px dashed #dc3545;
    border-radius: 8px;
    padding: 1.5rem;
}
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/clients') }}">Clients</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Client</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-1">Edit Client</h2>
                <p class="text-muted mb-0">Update client information and settings</p>
            </div>
            <div>
                <span class="client-status-badge bg-success-subtle text-success">
                    <i class="bi bi-check-circle"></i> Active Client
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Form Card -->
<div class="row">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="card form-card">
            {{-- 
                In production, this would be:
                <form action="{{ url('/clients/' . $client->id) }}" method="POST">
                @method('PUT')
            --}}
            <form action="{{ url('/clients/1') }}" method="POST" id="clientEditForm">
                @csrf
                @method('PUT')
                
                <!-- Basic Information Section -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-person-badge"></i>
                        Basic Information
                    </h5>
                    <p class="section-description">
                        Primary client details and account information
                    </p>
                    
                    <div class="row g-3">
                        <!-- Name Field -->
                        <div class="col-12">
                            <label for="name" class="form-label required-field">Full Name / Business Name</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', 'Ahmed Building Co') }}"
                                   placeholder="Enter client or business name"
                                   maxlength="120"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Email Field -->
                        <div class="col-12">
                            <label for="email" class="form-label required-field">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', 'ahmed@buildingco.com') }}"
                                       placeholder="client@example.com"
                                       maxlength="120"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        
                        <!-- Password Confirmation Field -->
                        <div class="col-12 col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <div class="position-relative">
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Re-enter new password"
                                       minlength="8">
                                <span class="password-toggle" onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye" id="password_confirmation-icon"></i>
                                </span>
                            </div>
                            <div class="form-text" id="password-match-text"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Company Information Section -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-building"></i>
                        Company Information
                    </h5>
                    <p class="section-description">
                        Business details for organization
                    </p>
                    
                    <div class="row g-3">
                        <!-- Company Name -->
                        <div class="col-12">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" 
                                   class="form-control @error('company_name') is-invalid @enderror" 
                                   id="company_name" 
                                   name="company_name" 
                                   value="{{ old('company_name', 'ABC Construction Ltd') }}"
                                   placeholder="Enter company or organization name"
                                   maxlength="120">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information Section -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-person-lines-fill"></i>
                        Contact Information
                    </h5>
                    <p class="section-description">
                        Primary contact details for communication
                    </p>
                    
                    <div class="row g-3">
                        <!-- Contact Person Name -->
                        <div class="col-12 col-md-6">
                            <label for="contact_name" class="form-label">Contact Person Name</label>
                            <input type="text" 
                                   class="form-control @error('contact_name') is-invalid @enderror" 
                                   id="contact_name" 
                                   name="contact_name" 
                                   value="{{ old('contact_name', 'Ahmed Khan') }}"
                                   placeholder="Enter contact person name"
                                   maxlength="100">
                            @error('contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Contact Number -->
                        <div class="col-12 col-md-6">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <input type="tel" 
                                       class="form-control @error('contact_number') is-invalid @enderror" 
                                       id="contact_number" 
                                       name="contact_number" 
                                       value="{{ old('contact_number', '+92-321-1234567') }}"
                                       placeholder="+92-300-1234567"
                                       maxlength="20">
                                @error('contact_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Account Status Section -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-toggle-on"></i>
                        Account Status
                    </h5>
                    <p class="section-description">
                        Control client access and account status
                    </p>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       role="switch" 
                                       id="is_active" 
                                       name="is_active"
                                       checked>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active Account</strong>
                                    <div class="text-muted small">
                                        When enabled, client can login and place orders. Disable to temporarily suspend access.
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Danger Zone Section -->
                <div class="form-section">
                    <h5 class="section-title text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Danger Zone
                    </h5>
                    <p class="section-description">
                        Irreversible actions - proceed with caution
                    </p>
                    
                    <div class="delete-zone">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h6 class="mb-1 text-danger">Delete Client Account</h6>
                                <p class="text-muted small mb-0">
                                    This will permanently delete the client account, all associated orders, and data. 
                                    This action cannot be undone.
                                </p>
                            </div>
                            <button type="button" 
                                    class="btn btn-danger" 
                                    onclick="confirmDelete()">
                                <i class="bi bi-trash"></i> Delete Client
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions - Sticky Footer -->
                <div class="sticky-footer">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <a href="{{ url('/clients') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Clients
                        </a>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset Changes
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Update Client
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">
                    <i class="bi bi-exclamation-triangle"></i> Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    <strong>Are you absolutely sure you want to delete this client?</strong>
                </p>
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-circle"></i>
                    <strong>Warning:</strong> This action will permanently delete:
                    <ul class="mb-0 mt-2">
                        <li>Client account and login credentials</li>
                        <li>All associated orders (42 orders)</li>
                        <li>Payment history and invoices</li>
                        <li>Project associations</li>
                    </ul>
                </div>
                <p class="text-danger mb-3">
                    <i class="bi bi-shield-exclamation"></i>
                    <strong>This action cannot be undone!</strong>
                </p>
                <div class="mb-3">
                    <label for="deleteConfirmInput" class="form-label">
                        Type <strong>DELETE</strong> to confirm:
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="deleteConfirmInput" 
                           placeholder="Type DELETE here"
                           autocomplete="off">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" 
                        class="btn btn-danger" 
                        id="confirmDeleteBtn" 
                        disabled
                        onclick="executeDelete()">
                    <i class="bi bi-trash"></i> Yes, Delete Client
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function(){
    console.log('Edit Client Form Loaded');
    
    // Store original form data for reset functionality
    const originalFormData = $('#clientEditForm').serialize();
    
    /**
     * Password Toggle Visibility
     * WHAT: Shows/hides password characters
     * WHY: Allows users to verify their password entry
     * HOW: Toggles input type between 'password' and 'text'
     */
    window.togglePassword = function(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    };
    
    /**
     * Password Match Validator
     * WHAT: Checks if password and confirmation match
     * WHY: Prevents typos in password entry
     * HOW: Compares both field values on input
     * NOTE: Only validates if password field has value
     */
    $('#password, #password_confirmation').on('input', function() {
        const password = $('#password').val();
        const confirmation = $('#password_confirmation').val();
        const matchText = $('#password-match-text');
        
        // Don't validate if password field is empty
        if (password.length === 0) {
            matchText.text('').css('color', '');
            return;
        }
        
        if (confirmation.length === 0) {
            matchText.text('').css('color', '');
            return;
        }
        
        if (password === confirmation) {
            matchText.text('✓ Passwords match').css('color', '#198754');
        } else {
            matchText.text('✗ Passwords do not match').css('color', '#dc3545');
        }
    });
    
    /**
     * Form Validation Before Submit
     * WHAT: Validates all required fields before submission
     * WHY: Provides immediate feedback and prevents invalid submissions
     * HOW: Checks each field and displays appropriate error messages
     * NOTE: Password is optional on edit, only validated if provided
     */
    $('#clientEditForm').on('submit', function(e) {
        let isValid = true;
        const password = $('#password').val();
        const confirmation = $('#password_confirmation').val();
        
        // Clear previous validation states
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').not('.error').remove();
        
        // Validate required fields (not password fields)
        $('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
                $(this).after('<div class="invalid-feedback">This field is required</div>');
            }
        });
        
        // Validate email format
        const email = $('#email').val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            isValid = false;
            $('#email').addClass('is-invalid');
            $('#email').after('<div class="invalid-feedback">Please enter a valid email address</div>');
        }
        
        // Validate password only if it's being changed
        if (password) {
            // Check minimum length
            if (password.length < 8) {
                isValid = false;
                $('#password').addClass('is-invalid');
                $('#password').after('<div class="invalid-feedback">Password must be at least 8 characters</div>');
            }
            
            // Check password match
            if (password !== confirmation) {
                isValid = false;
                $('#password_confirmation').addClass('is-invalid');
                $('#password_confirmation').after('<div class="invalid-feedback">Passwords do not match</div>');
            }
        }
        
        // Prevent submission if validation fails
        if (!isValid) {
            e.preventDefault();
            
            // Scroll to first error
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 300);
            
            // Show error notification
            showNotification('error', 'Please fix the errors in the form');
        } else {
            // Show success notification (in production, this happens after server response)
            // showNotification('success', 'Client updated successfully!');
        }
    });
    
    /**
     * Auto-format Phone Number
     * WHAT: Formats phone number as user types
     * WHY: Ensures consistent format and better UX
     * HOW: Applies formatting pattern on input
     */
    $('#contact_number').on('input', function() {
        let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
        
        // Format as: +92-300-1234567
        if (value.length > 0) {
            if (value.length <= 2) {
                value = '+' + value;
            } else if (value.length <= 5) {
                value = '+' + value.slice(0, 2) + '-' + value.slice(2);
            } else {
                value = '+' + value.slice(0, 2) + '-' + value.slice(2, 5) + '-' + value.slice(5, 12);
            }
        }
        
        $(this).val(value);
    });
    
    /**
     * Delete Confirmation Input Validation
     * WHAT: Enables delete button only when user types "DELETE"
     * WHY: Prevents accidental deletions by requiring explicit confirmation
     * HOW: Listens to input and compares with required text
     */
    $('#deleteConfirmInput').on('input', function() {
        const input = $(this).val();
        const deleteBtn = $('#confirmDeleteBtn');
        
        if (input === 'DELETE') {
            deleteBtn.prop('disabled', false);
        } else {
            deleteBtn.prop('disabled', true);
        }
    });
    
    // Reset delete confirmation when modal closes
    $('#deleteConfirmModal').on('hidden.bs.modal', function() {
        $('#deleteConfirmInput').val('');
        $('#confirmDeleteBtn').prop('disabled', true);
    });
});

/**
 * Reset Form Function
 * WHAT: Resets form to original values
 * WHY: Allows users to undo changes before saving
 * HOW: Clears form and reloads with stored original data
 */
function resetForm() {
    if (confirm('Are you sure you want to reset all changes? Unsaved changes will be lost.')) {
        // Reset the form
        document.getElementById('clientEditForm').reset();
        
        // Clear password fields (they should stay empty on reset)
        $('#password').val('');
        $('#password_confirmation').val('');
        
        // Clear validation states
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').not('.error').remove();
        $('#password-match-text').text('').css('color', '');
        
        showNotification('success', 'Form reset to original values');
    }
}

/**
 * Confirm Delete Function
 * WHAT: Opens modal for delete confirmation
 * WHY: Provides additional safety layer before deletion
 * HOW: Shows Bootstrap modal with warning message
 */
function confirmDelete() {
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

/**
 * Execute Delete Function
 * WHAT: Sends DELETE request to server
 * WHY: Permanently removes client from system
 * HOW: Makes AJAX call to API endpoint
 */
function executeDelete() {
    // In production: Send DELETE request to /api/clients/1
    console.log('Deleting client...');
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
    modal.hide();
    
    // Show loading state
    showNotification('info', 'Deleting client...');
    
    // Simulate API call
    setTimeout(function() {
        showNotification('success', 'Client deleted successfully!');
        
        // Redirect to clients list
        setTimeout(function() {
            window.location.href = '/clients';
        }, 1500);
    }, 1000);
}

/**
 * Show Notification Helper Function
 * WHAT: Displays toast-style notification messages
 * WHY: Provides user feedback for actions
 * HOW: Creates and animates a notification element
 */
function showNotification(type, message) {
    const icons = {
        success: 'bi-check-circle-fill',
        error: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill'
    };
    
    const colors = {
        success: '#198754',
        error: '#dc3545',
        info: '#0d6efd'
    };
    
    const iconClass = icons[type] || icons.info;
    const bgColor = colors[type] || colors.info;
    
    const notification = $(`
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            <div class="toast show" role="alert" style="background: ${bgColor}; color: white;">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi ${iconClass}"></i>
                    <span>${message}</span>
                </div>
            </div>
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(() => {
        notification.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}
</script>
@endpush
                            <div class="form-text">Used for login and communications</div>
                        </div>
                        
                        <!-- Account Created Info -->
                        <div class="col-12">
                            <div class="info-box info-blue">
                                <div class="d-flex gap-3">
                                    <div><i class="bi bi-info-circle"></i></div>
                                    <div>
                                        <strong>Account Information:</strong>
                                        <div class="small mt-1">
                                            <div>Created: <strong>October 1, 2025</strong></div>
                                            <div>Last Login: <strong>October 5, 2025 at 10:30 AM</strong></div>
                                            <div>Total Orders: <strong>42</strong> | Total Spent: <strong>$128,450</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Password Update Section -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-shield-lock"></i>
                        Update Password
                    </h5>
                    <p class="section-description">
                        Leave blank to keep current password unchanged
                    </p>
                    
                    <div class="info-box">
                        <div class="d-flex gap-3">
                            <div><i class="bi bi-exclamation-triangle"></i></div>
                            <div>
                                <strong>Password Change Notice:</strong>
                                <p class="mb-0 mt-1 small">
                                    Only fill these fields if you want to change the client's password. 
                                    The client will need to use the new password for their next login.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <!-- New Password Field -->
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label">New Password</label>
                            <div class="position-relative">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Enter new password"
                                       minlength="8">
                                <span class="password-toggle" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password-icon"></i>
                                </span>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>