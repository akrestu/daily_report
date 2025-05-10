import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

// Make Chart.js available globally
window.Chart = Chart;

// Prevent multiple Alpine.js initializations
if (!window.__alpine_was_already_initialized) {
    window.Alpine = Alpine;
    window.__alpine_was_already_initialized = true;
    console.log('Alpine.js first initialization marked');
    Alpine.start();
} else {
    console.log('Alpine.js already initialized, skipping...');
}

// Common UI functions
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all Bootstrap dropdowns
    try {
        const dropdownTriggerList = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        const dropdownList = [...dropdownTriggerList].map(dropdownTriggerEl => {
            return new bootstrap.Dropdown(dropdownTriggerEl);
        });
        console.log('Initialized dropdowns:', dropdownList.length);
    } catch (error) {
        console.error('Error initializing dropdowns:', error);
    }
    
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    // Initialize Bootstrap popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    
    // Handle bulk selection in tables
    const mainCheckbox = document.querySelector('.select-all-checkbox');
    if (mainCheckbox) {
        mainCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.select-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = mainCheckbox.checked;
            });
            
            const bulkActionButtons = document.querySelectorAll('.bulk-action-button');
            if (bulkActionButtons.length > 0) {
                const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
                bulkActionButtons.forEach(button => {
                    button.disabled = !anyChecked;
                });
            }
        });
        
        const checkboxes = document.querySelectorAll('.select-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
                const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
                
                mainCheckbox.checked = allChecked;
                mainCheckbox.indeterminate = anyChecked && !allChecked;
                
                const bulkActionButtons = document.querySelectorAll('.bulk-action-button');
                if (bulkActionButtons.length > 0) {
                    bulkActionButtons.forEach(button => {
                        button.disabled = !anyChecked;
                    });
                }
            });
        });
    }
    
    // Handle auto-dismissed alerts
    const alerts = document.querySelectorAll('.alert-auto-dismiss');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000); // Auto-dismiss after 5 seconds
    });
    
    // Handle confirmation dialogs
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Are you sure you want to proceed?')) {
                e.preventDefault();
                return false;
            }
        });
    });
}); 