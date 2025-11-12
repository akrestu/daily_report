import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import { Workbox } from 'workbox-window';

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

// Register Service Worker for PWA offline support
if ('serviceWorker' in navigator) {
    const wb = new Workbox('/sw.js');

    // Show update notification when new service worker is waiting
    wb.addEventListener('waiting', () => {
        console.log('New service worker waiting...');

        // Show update available notification
        const updateNotification = document.createElement('div');
        updateNotification.className = 'position-fixed bottom-0 end-0 p-3';
        updateNotification.style.zIndex = '9999';
        updateNotification.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header bg-primary text-white">
                    <i class="fas fa-sync-alt me-2"></i>
                    <strong class="me-auto">Update Available</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    A new version of SiGAP is available!
                    <div class="mt-2 pt-2 border-top">
                        <button class="btn btn-primary btn-sm w-100" id="reload-button">
                            <i class="fas fa-redo me-1"></i> Update Now
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(updateNotification);

        // Handle update button click
        document.getElementById('reload-button').addEventListener('click', () => {
            wb.messageSkipWaiting();
        });
    });

    // Reload page when new service worker takes control
    wb.addEventListener('controlling', () => {
        console.log('Service worker now controlling, reloading page...');
        window.location.reload();
    });

    // Log when service worker is activated
    wb.addEventListener('activated', (event) => {
        if (!event.isUpdate) {
            console.log('Service worker activated for the first time');
        } else {
            console.log('Service worker updated successfully');
        }
    });

    // Register the service worker
    wb.register()
        .then((registration) => {
            console.log('Service Worker registered successfully:', registration);

            // Check for updates every 60 minutes
            setInterval(() => {
                registration.update();
                console.log('Checking for service worker updates...');
            }, 60 * 60 * 1000);
        })
        .catch((error) => {
            console.error('Service Worker registration failed:', error);
        });
} else {
    console.warn('Service Workers are not supported in this browser');
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