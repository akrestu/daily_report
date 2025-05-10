/**
 * Enhanced CRUD Notification System
 * Supports all CRUD operations with beautiful notifications
 */

// Configuration for notifications
const notificationConfig = {
    create: {
        title: 'Data Created Successfully',
        icon: 'success',
        iconClass: 'fas fa-check-circle',
        timer: 3000
    },
    read: {
        title: 'Data Loaded Successfully',
        icon: 'info',
        iconClass: 'fas fa-info-circle',
        timer: 2000
    },
    update: {
        title: 'Data Updated Successfully',
        icon: 'success',
        iconClass: 'fas fa-edit',
        timer: 3000
    },
    delete: {
        title: 'Data Deleted Successfully',
        icon: 'success',
        iconClass: 'fas fa-trash',
        timer: 3000
    },
    error: {
        title: 'Error Occurred',
        icon: 'error',
        iconClass: 'fas fa-exclamation-circle',
        timer: 5000
    },
    warning: {
        title: 'Warning',
        icon: 'warning',
        iconClass: 'fas fa-exclamation-triangle',
        timer: 4000
    }
};

/**
 * Show a CRUD notification
 * @param {string} operation - create, read, update, delete, error, warning
 * @param {string} message - Custom message to display
 * @param {string} customTitle - Optional custom title
 */
function showCrudNotification(operation, message, customTitle = null) {
    const config = notificationConfig[operation] || notificationConfig.info;
    
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: config.icon,
        title: customTitle || config.title,
        text: message,
        showConfirmButton: false,
        timer: config.timer,
        timerProgressBar: true,
        customClass: {
            popup: 'animated fadeInDown colored-toast'
        },
        showClass: {
            popup: 'animated fadeInDown'
        },
        hideClass: {
            popup: 'animated fadeOutUp'
        }
    });
}

/**
 * Show confirmation dialog before delete
 * @param {function} callback - Function to call if confirmed
 * @param {string} itemName - Name of the item being deleted
 * @returns {Promise}
 */
function confirmDelete(callback, itemName = 'item') {
    return Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete this ${itemName}. This action cannot be undone!`,
        icon: 'warning',
        iconHtml: '<i class="fas fa-exclamation-triangle text-warning"></i>',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary',
            popup: 'animated zoomIn'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed && typeof callback === 'function') {
            callback();
            return true;
        }
        return false;
    });
}

/**
 * Handle form submission with AJAX
 * @param {string} formId - ID of the form element
 * @param {string} operation - CRUD operation being performed
 * @param {function} successCallback - Function to call after success
 */
function handleFormSubmit(formId, operation, successCallback = null) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(form);
        
        // Get form action URL and method
        const url = form.getAttribute('action');
        const method = form.getAttribute('method') || 'POST';
        
        // Make AJAX request
        fetch(url, {
            method: method,
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showCrudNotification(operation, data.message || 'Operation completed successfully');
                
                if (typeof successCallback === 'function') {
                    successCallback(data);
                }
                
                // If redirect URL provided, redirect after delay
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                }
            } else {
                showCrudNotification('error', data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showCrudNotification('error', 'An unexpected error occurred');
        });
    });
}

/**
 * Initialize all delete confirmation buttons
 */
function initDeleteConfirmations() {
    document.querySelectorAll('.delete-confirm').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const itemName = this.dataset.name || 'item';
            
            confirmDelete(() => {
                form.submit();
            }, itemName);
        });
    });
}

/**
 * Auto-initialize when document is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize delete confirmations
    initDeleteConfirmations();
    
    // Process flash messages
    processFlashMessages();
});

/**
 * Process flash messages from session
 */
function processFlashMessages() {
    // Success messages
    const successMsg = document.getElementById('success-message');
    if (successMsg && successMsg.dataset.message) {
        showCrudNotification('create', successMsg.dataset.message);
    }
    
    // Error messages
    const errorMsg = document.getElementById('error-message');
    if (errorMsg && errorMsg.dataset.message) {
        showCrudNotification('error', errorMsg.dataset.message);
    }
    
    // Warning messages
    const warningMsg = document.getElementById('warning-flash');
    if (warningMsg && warningMsg.dataset.message) {
        showCrudNotification('warning', warningMsg.dataset.message);
    }
    
    // Info messages
    const infoMsg = document.getElementById('info-flash');
    if (infoMsg && infoMsg.dataset.message) {
        showCrudNotification('read', infoMsg.dataset.message);
    }
    
    // Validation errors
    const validationErrors = document.getElementById('validation-errors');
    if (validationErrors && validationErrors.dataset.errors) {
        const errors = JSON.parse(validationErrors.dataset.errors);
        if (errors.length > 0) {
            Swal.fire({
                title: 'Validation Error',
                html: `<ul class="text-start">${errors.map(error => `<li>${error}</li>`).join('')}</ul>`,
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    popup: 'animated fadeInDown'
                },
                buttonsStyling: false
            });
        }
    }
} 