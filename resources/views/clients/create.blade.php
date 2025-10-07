@extends('layout.main')

@section('title', 'Add New Client - Material Connect')

@push('styles')
<style>
/* Form Specific Styles */
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
    align-items: center;
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

.form-control:focus,
.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}

.invalid-feedback {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
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

.password-strength {
    height: 4px;
    border-radius: 2px;
    background: #e9ecef;
    margin-top: 0.5rem;
    overflow: hidden;
}

.password-strength-bar {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
}

.strength-weak { 
    background: #dc3545; 
    width: 33%;
}

.strength-medium { 
    background: #ffc107; 
    width: 66%;
}

.strength-strong { 
    background: #198754; 
    width: 100%;
}

.info-box {
    background: #e7f1ff;
    border-left: 4px solid #0d6efd;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}

.info-box i {
    color: #0d6efd;
    font-size: 1.25rem;
}

.btn-submit {
    min-width: 140px;
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
                <li class="breadcrumb-item active" aria-current="page">Add New Client</li>
            </ol>
        </nav>
        <h2 class="mb-1">Add New Client</h2>
        <p class="text-muted mb-0">Create a new client account for Material Connect</p>
    </div>
</div>

<!-- Form Card -->
<div class="row">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="card form-card">
            <form action="{{ route('client.add') }}" method="POST" id="clientForm">
                @csrf
                
                <!-- Basic Information Section -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-person-badge"></i>
                        Basic Information
                    </h5>
                    <p class="section-description">
                        Primary client details and login credentials
                    </p>
                    
                    <div class="row g-3">
                        <!-- Name Field -->
                        <div class="col-12">
                            <label for="name" class="form-label required-field">Full Name</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   placeholder="Enter client or business name"
                                   maxlength="120"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">This will be used as the primary identifier</div>
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
                                       value="{{ old('email') }}"
                                       placeholder="client@example.com"
                                       maxlength="120"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Will be used for login and communications</div>
                        </div>
                    </div>
                </div>
                
                <!-- Password Section -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-shield-lock"></i>
                        Security Credentials
                    </h5>
                    <p class="section-description">
                        Set a secure password for client portal access
                    </p>
                    
                    <div class="info-box">
                        <div class="d-flex gap-3">
                            <div><i class="bi bi-info-circle"></i></div>
                            <div>
                                <strong>Password Requirements:</strong>
                                <ul class="mb-0 mt-2 small">
                                    <li>Minimum 8 characters</li>
                                    <li>Mix of uppercase and lowercase letters recommended</li>
                                    <li>Include numbers and special characters for stronger security</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <!-- Password Field -->
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label required-field">Password</label>
                            <div class="position-relative">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Enter password"
                                       minlength="8"
                                       required>
                                <span class="password-toggle" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password-icon"></i>
                                </span>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-bar" id="password-strength-bar"></div>
                            </div>
                            <div class="form-text" id="password-strength-text">Password strength: Not set</div>
                        </div>
                        
                        <!-- Password Confirmation Field -->
                        <div class="col-12 col-md-6">
                            <label for="password_confirmation" class="form-label required-field">Confirm Password</label>
                            <div class="position-relative">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Re-enter password"
                                       minlength="8"
                                       required>
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
                        Optional business details for better organization
                    </p>
                    
                    <div class="row g-3">
                        <!-- Company Name -->
                        <div class="col-12">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" 
                                   class="form-control @error('company_name') is-invalid @enderror" 
                                   id="company_name" 
                                   name="company_name" 
                                   value="{{ old('company_name') }}"
                                   placeholder="Enter company or organization name"
                                   maxlength="120">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Legal business name if different from client name</div>
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
                                   value="{{ old('contact_name') }}"
                                   placeholder="Enter contact person name"
                                   maxlength="100">
                            @error('contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Primary point of contact</div>
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
                                       value="{{ old('contact_number') }}"
                                       placeholder="+92-300-1234567"
                                       maxlength="20">
                                @error('contact_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Phone or mobile number</div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions - Sticky Footer -->
                <div class="sticky-footer">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <a href="{{ url('/clients') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                        <div class="d-flex gap-2">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset Form
                            </button>
                            <button type="submit" class="btn btn-primary btn-submit">
                                <i class="bi bi-check-lg"></i> Create Client
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function(){
    console.log('Add Client Form Loaded');
    
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
     * Password Strength Checker
     * WHAT: Evaluates password strength in real-time
     * WHY: Helps users create secure passwords
     * HOW: Checks length, character variety, and complexity
     */
    $('#password').on('input', function() {
        const password = $(this).val();
        const strengthBar = $('#password-strength-bar');
        const strengthText = $('#password-strength-text');
        
        // Calculate strength score (0-4)
        let strength = 0;
        
        if (password.length >= 8) strength++; // Length check
        if (password.length >= 12) strength++; // Strong length
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++; // Mixed case
        if (/\d/.test(password)) strength++; // Contains number
        if (/[^a-zA-Z0-9]/.test(password)) strength++; // Special char
        
        // Update UI based on strength
        strengthBar.removeClass('strength-weak strength-medium strength-strong');
        
        if (strength <= 2) {
            strengthBar.addClass('strength-weak');
            strengthText.text('Password strength: Weak').css('color', '#dc3545');
        } else if (strength <= 3) {
            strengthBar.addClass('strength-medium');
            strengthText.text('Password strength: Medium').css('color', '#ffc107');
        } else {
            strengthBar.addClass('strength-strong');
            strengthText.text('Password strength: Strong').css('color', '#198754');
        }
    });
    
    /**
     * Password Match Validator
     * WHAT: Checks if password and confirmation match
     * WHY: Prevents typos in password entry
     * HOW: Compares both field values on input
     */
    $('#password, #password_confirmation').on('input', function() {
        const password = $('#password').val();
        const confirmation = $('#password_confirmation').val();
        const matchText = $('#password-match-text');
        
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
     */
    $('#clientForm').on('submit', function(e) {
        let isValid = true;
        const password = $('#password').val();
        const confirmation = $('#password_confirmation').val();
        
        // Clear previous validation states
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validate required fields
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
        
        // Validate password length
        if (password && password.length < 8) {
            isValid = false;
            $('#password').addClass('is-invalid');
            $('#password').after('<div class="invalid-feedback">Password must be at least 8 characters</div>');
        }
        
        // Validate password match
        if (password !== confirmation) {
            isValid = false;
            $('#password_confirmation').addClass('is-invalid');
            $('#password_confirmation').after('<div class="invalid-feedback">Passwords do not match</div>');
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
        }
    });
    
    /**
     * Character Counter for Text Fields
     * WHAT: Shows remaining characters for fields with maxlength
     * WHY: Helps users stay within limits
     * HOW: Updates counter on input event
     */
    $('input[maxlength]').each(function() {
        const maxLength = $(this).attr('maxlength');
        const $counter = $('<div class="form-text text-end small"></div>');
        $(this).after($counter);
        
        const updateCounter = () => {
            const currentLength = $(this).val().length;
            const remaining = maxLength - currentLength;
            $counter.text(`${remaining} characters remaining`);
            
            if (remaining < 20) {
                $counter.css('color', '#ffc107');
            } else {
                $counter.css('color', '#6c757d');
            }
        };
        
        $(this).on('input', updateCounter);
        updateCounter(); // Initial count
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
});

/**
 * Show Notification Helper Function
 * WHAT: Displays toast-style notification messages
 * WHY: Provides user feedback for actions
 * HOW: Creates and animates a notification element
 */
function showNotification(type, message) {
    const iconClass = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
    const bgColor = type === 'success' ? '#198754' : '#dc3545';
    
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