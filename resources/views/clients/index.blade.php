@extends('layout.main')

@section('title', 'Clients - Material Connect')

@push('styles')
<style>
/* Clients Listing Specific Styles */
.search-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
}

.stats-card {
    border: none;
    border-radius: 10px;
    transition: all 0.3s ease;
    background: #ffffff;
    border-left: 4px solid #0d6efd;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.client-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
    color: #ffffff;
}

.client-row {
    transition: all 0.2s ease;
    cursor: pointer;
}

.client-row:hover {
    background-color: #f8f9ff;
    transform: translateX(4px);
}

.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.action-btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    border: none;
    transition: all 0.2s;
}

.action-btn i {
    font-size: 1rem;
    line-height: 1;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    background: #e7f1ff;
    color: #0d6efd;
    border-radius: 20px;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.filter-chip:hover {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.filter-chip.active {
    background: #0d6efd;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-state i {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

.pagination-info {
    color: #6c757d;
    font-size: 0.875rem;
}
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-1">Clients Management</h2>
                <p class="text-muted mb-0">Manage your construction clients and contacts</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" onclick="exportClients()">
                    <i class="bi bi-download"></i> Export
                </button>
                <a href="{{ url('/clients/create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add New Client
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stats-card" style="border-left-color: #0d6efd;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Total Clients</div>
                        <h3 class="mb-0" id="totalClients">{{$totalClients}}</h3>
                    </div>
                    <div class="text-primary" style="font-size: 2rem; opacity: 0.2;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stats-card" style="border-left-color: #198754;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Active Clients</div>
                        <h3 class="mb-0 text-success" id="activeClients">{{$activeClients}}</h3>
                    </div>
                    <div class="text-success" style="font-size: 2rem; opacity: 0.2;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stats-card" style="border-left-color: #ffc107;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">New This Month</div>
                        <h3 class="mb-0 text-warning" id="newClients">{{$joinedThisMonth}}</h3>
                    </div>
                    <div class="text-warning" style="font-size: 2rem; opacity: 0.2;">
                        <i class="bi bi-star-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stats-card" style="border-left-color: #dc3545;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Inactive</div>
                        <h3 class="mb-0 text-danger" id="inactiveClients">{{$inactiveClients}}</h3>
                    </div>
                    <div class="text-danger" style="font-size: 2rem; opacity: 0.2;">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="search-section">
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md-5">
            <label for="searchInput" class="form-label fw-semibold small">Search Clients</label>
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" 
                       class="form-control" 
                       id="searchInput" 
                       placeholder="Search by name, email, company..."
                       value="">
            </div>
        </div>
        
        <div class="col-12 col-md-3">
            <label for="statusFilter" class="form-label fw-semibold small">Status</label>
            <select class="form-select" id="statusFilter">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="In-Active">Inactive</option>
            </select>
        </div>
        
        <div class="col-12 col-md-2">
            <label for="sortBy" class="form-label fw-semibold small">Sort By</label>
            <select class="form-select" id="sortBy">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="name_asc">Name (A-Z)</option>
                <option value="name_desc">Name (Z-A)</option>
            </select>
        </div>
        
        <div class="col-12 col-md-2">
            <button class="btn btn-secondary w-100" onclick="resetFilters()">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>
        </div>
    </div>
</div>

<!-- Clients Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="clientsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th>Client</th>
                        <th>Company</th>
                        <!-- <th>Contact</th> -->
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="clientsTableBody">
                    @foreach($clients as $client)
                    <!-- Sample Data Row - Will be dynamically generated -->
                    <tr class="client-row">
                        <td>
                            <input type="checkbox" class="form-check-input client-checkbox" value="1">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="client-avatar bg-primary">
                                    AB
                                </div>
                                <div>
                                    <div class="fw-semibold">{{$client->name}}</div>
                                    <div class="text-muted small">{{$client->email}}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="fw-medium">{{$client->company_name}}</span>
                        </td>
            
                        <td>
                            <span class="badge {{($client->status=='Active')? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'}} status-badge">
                                <i class="bi bi-check-circle"></i> {{$client->status}}
                            </span>
                        </td>
                        <td>
                            <span class="text-muted small">{{$client->created_at}}</span>
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-end">
                                <button class="btn btn-sm btn-primary action-btn d-flex align-items-center justify-content-center" 
                                        onclick="viewClient({{$client->id}})"
                                        title="View Details"
                                        style="width: 32px; height: 32px; padding: 0;">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="{{ url('/clients/1/edit') }}" 
                                   class="btn btn-sm btn-secondary action-btn d-flex align-items-center justify-content-center"
                                   title="Edit Client"
                                   style="width: 32px; height: 32px; padding: 0;">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-danger action-btn d-flex align-items-center justify-content-center" 
                                        onclick="deleteClient({{$client->id}})"
                                        title="Delete Client"
                                        style="width: 32px; height: 32px; padding: 0;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Empty State (Hidden when there's data) -->
        <div class="empty-state d-none" id="emptyState">
            <i class="bi bi-people"></i>
            <h5 class="mt-3">No Clients Found</h5>
            <p class="text-muted">Try adjusting your search or filter to find what you're looking for.</p>
            <a href="{{ url('/clients/create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add Your First Client
            </a>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
            <div class="pagination-info">
                Showing <strong>1</strong> to <strong>3</strong> of <strong>84</strong> clients
            </div>
            
            <nav aria-label="Clients pagination">
                <ul class="pagination mb-0">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- View Client Modal -->
<div class="modal fade" id="viewClientModal" tabindex="-1" aria-labelledby="viewClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewClientModalLabel">
                    <i class="bi bi-person-circle text-primary"></i> Client Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="clientDetailsContent">
                <!-- Client details will be loaded here -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary" id="editClientBtn">
                    <i class="bi bi-pencil"></i> Edit Client
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function(){
    console.log('Clients Listing Page Loaded');
    
    // Select All Checkbox Logic
    $('#selectAll').on('change', function() {
        $('.client-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    // Individual Checkbox Logic
    $('.client-checkbox').on('change', function() {
        const allChecked = $('.client-checkbox:checked').length === $('.client-checkbox').length;
        $('#selectAll').prop('checked', allChecked);
    });
    
    // Search Functionality
    /**
     * WHAT: Real-time search across client name, email, and company
     * WHY: Provides instant feedback to users as they type
     * HOW: Uses jQuery to filter table rows based on input value
     */
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val().toLowerCase();
        
        searchTimeout = setTimeout(function() {
            filterClients();
        }, 300); // Debounce: Wait 300ms after user stops typing
    });
    
    // Status Filter
    $('#statusFilter').on('change', function() {
        filterClients();
    });
    
    // Sort Functionality
    $('#sortBy').on('change', function() {
        sortClients($(this).val());
    });
    
    /**
     * Filter Clients Function
     * WHAT: Filters table rows based on search and status filters
     * WHY: Allows users to narrow down the client list
     * HOW: Iterates through each row and shows/hides based on criteria
     */
    function filterClients() {
        const searchTerm = $('#searchInput').val().toLowerCase();
        const statusFilter = $('#statusFilter').val().toLowerCase();
        let visibleCount = 0;
        
        $('#clientsTableBody tr').each(function() {
            const $row = $(this);
            const clientName = $row.find('td:eq(1)').text().toLowerCase();
            const company = $row.find('td:eq(2)').text().toLowerCase();
            const statusBadge = $row.find('.status-badge').text().toLowerCase();
            
            // Check if row matches search term
            const matchesSearch = searchTerm === '' || 
                                clientName.includes(searchTerm) || 
                                company.includes(searchTerm);
            
            // Check if row matches status filter
            const matchesStatus = statusFilter === '' || 
                                statusBadge.includes(statusFilter);
            
            // Show or hide row based on filters
            if (matchesSearch && matchesStatus) {
                $row.show();
                visibleCount++;
            } else {
                $row.hide();
            }
        });
        
        // Show empty state if no results
        if (visibleCount === 0) {
            $('#emptyState').removeClass('d-none');
            $('.pagination').hide();
        } else {
            $('#emptyState').addClass('d-none');
            $('.pagination').show();
        }
    }
    
    /**
     * Sort Clients Function
     * WHAT: Sorts table rows based on selected criteria
     * WHY: Helps users organize data in their preferred order
     * HOW: Detaches rows, sorts them, and re-appends to tbody
     */
    function sortClients(sortBy) {
        const $tbody = $('#clientsTableBody');
        const $rows = $tbody.find('tr').detach();
        
        $rows.sort(function(a, b) {
            let aVal, bVal;
            
            switch(sortBy) {
                case 'name_asc':
                    aVal = $(a).find('td:eq(1) .fw-semibold').text();
                    bVal = $(b).find('td:eq(1) .fw-semibold').text();
                    return aVal.localeCompare(bVal);
                
                case 'name_desc':
                    aVal = $(a).find('td:eq(1) .fw-semibold').text();
                    bVal = $(b).find('td:eq(1) .fw-semibold').text();
                    return bVal.localeCompare(aVal);
                
                case 'newest':
                    // In real app, sort by actual date value
                    return -1;
                
                case 'oldest':
                    return 1;
                
                default:
                    return 0;
            }
        });
        
        $tbody.append($rows);
    }
});

/**
 * Reset Filters Function
 * WHAT: Clears all search and filter inputs
 * WHY: Provides quick way to view all clients again
 * HOW: Resets input values and triggers filter function
 */
function resetFilters() {
    $('#searchInput').val('');
    $('#statusFilter').val('');
    $('#sortBy').val('newest');
    $('#clientsTableBody tr').show();
    $('#emptyState').addClass('d-none');
    $('.pagination').show();
}

/**
 * View Client Function
 * WHAT: Opens modal with detailed client information
 * WHY: Provides quick overview without navigating away
 * HOW: Fetches client data via AJAX and populates modal
 */
function viewClient(clientId) {
    const modal = new bootstrap.Modal(document.getElementById('viewClientModal'));
    modal.show();
    
    // In production, fetch from API: GET /api/clients/{id}
    setTimeout(function() {
        $('#clientDetailsContent').html(`
            <div class="row g-3">
                <div class="col-12 text-center mb-3">
                    <div class="client-avatar bg-primary d-inline-flex" style="width: 80px; height: 80px; font-size: 2rem;">
                        AB
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Full Name</label>
                    <p class="fw-semibold">Ahmed Building Co</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Email</label>
                    <p class="fw-semibold">ahmed@buildingco.com</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Company Name</label>
                    <p class="fw-semibold">ABC Construction Ltd</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Status</label>
                    <p><span class="badge bg-success-subtle text-success">Active</span></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Contact Person</label>
                    <p class="fw-semibold">Ahmed Khan</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Contact Number</label>
                    <p class="fw-semibold">+92-321-1234567</p>
                </div>
                <div class="col-12">
                    <label class="text-muted small">Member Since</label>
                    <p class="fw-semibold">October 1, 2025</p>
                </div>
            </div>
        `);
        $('#editClientBtn').attr('href', '/clients/' + clientId + '/edit');
    }, 500);
}

/**
 * Delete Client Function
 * WHAT: Prompts user for confirmation and deletes client
 * WHY: Prevents accidental deletions with confirmation dialog
 * HOW: Shows confirmation, makes DELETE request to API
 */
function deleteClient(clientId) {
    if (confirm('Are you sure you want to delete this client? This action cannot be undone.')) {
        // In production: Send DELETE request to /api/clients/{id}
        console.log('Deleting client ID:', clientId);
        
        // Show success message
        alert('Client deleted successfully!');
        
        // Remove row from table with animation
        $('input[value="' + clientId + '"]').closest('tr').fadeOut(300, function() {
            $(this).remove();
            
            // Update stats (in real app, fetch from API)
            const currentTotal = parseInt($('#totalClients').text());
            $('#totalClients').text(currentTotal - 1);
        });
    }
}

/**
 * Export Clients Function
 * WHAT: Exports client list to CSV/Excel
 * WHY: Allows users to work with data offline
 * HOW: Generates file and triggers download
 */
function exportClients() {
    // In production: Call API endpoint GET /api/clients/export
    console.log('Exporting clients...');
    alert('Export feature will generate a CSV file with all client data.');
}
</script>
@endpush