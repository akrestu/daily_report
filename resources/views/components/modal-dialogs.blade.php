<!-- Generic Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmationMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledBy="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertModalLabel">Alert</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle text-warning me-3" style="font-size: 1.5rem;"></i>
                    <p id="alertMessage" class="mb-0"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center">
                    <i id="notificationIcon" class="fas me-3" style="font-size: 1.5rem;"></i>
                    <p id="notificationMessage" class="mb-0"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center">
                    <i class="fas fa-trash-alt text-danger me-3" style="font-size: 1.5rem;"></i>
                    <div>
                        <p id="deleteMessage" class="mb-1">Are you sure you want to delete this item?</p>
                        <p class="text-muted small mb-0">This action cannot be undone.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to ensure modal backdrop is properly removed
        function ensureBackdropRemoval() {
            // Remove any lingering backdrops
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                backdrop.classList.remove('show');
                setTimeout(() => {
                    backdrop.remove();
                }, 150);
            });
            
            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }

        // Override Bootstrap's modal hide method to ensure backdrop removal
        const originalModalHide = bootstrap.Modal.prototype.hide;
        bootstrap.Modal.prototype.hide = function() {
            originalModalHide.call(this);
            setTimeout(ensureBackdropRemoval, 200);
        };
        
        // Show notification modal if session has success or error message
        @if(session('success') || session('error') || session('info') || session('warning'))
            // First ensure any existing backdrops are removed
            ensureBackdropRemoval();
            
            const notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));
            const notificationIcon = document.getElementById('notificationIcon');
            const notificationMessage = document.getElementById('notificationMessage');
            const notificationTitle = document.getElementById('notificationModalLabel');
            
            @if(session('success'))
                notificationTitle.textContent = 'Success';
                notificationIcon.className = 'fas fa-check-circle text-success me-3';
                notificationMessage.textContent = "{{ session('success') }}";
            @elseif(session('error'))
                notificationTitle.textContent = 'Error';
                notificationIcon.className = 'fas fa-times-circle text-danger me-3';
                notificationMessage.textContent = "{{ session('error') }}";
            @elseif(session('warning'))
                notificationTitle.textContent = 'Warning';
                notificationIcon.className = 'fas fa-exclamation-triangle text-warning me-3';
                notificationMessage.textContent = "{{ session('warning') }}";
            @elseif(session('info'))
                notificationTitle.textContent = 'Information';
                notificationIcon.className = 'fas fa-info-circle text-info me-3';
                notificationMessage.textContent = "{{ session('info') }}";
            @endif
            
            notificationModal.show();
        @endif
        
        // Helper function to show alert modal
        window.showAlert = function(message, title = 'Alert') {
            // First ensure any existing backdrops are removed
            ensureBackdropRemoval();
            
            const alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
            document.getElementById('alertModalLabel').textContent = title;
            document.getElementById('alertMessage').textContent = message;
            alertModal.show();
        };
        
        // Helper function to show confirmation modal
        window.showConfirmation = function(message, callback, confirmBtnText = 'Confirm', confirmBtnClass = 'btn-primary') {
            // First ensure any existing backdrops are removed
            ensureBackdropRemoval();
            
            const modal = document.getElementById('confirmationModal');
            const confirmationModal = new bootstrap.Modal(modal);
            const confirmBtn = document.getElementById('confirmActionBtn');
            
            document.getElementById('confirmationMessage').textContent = message;
            confirmBtn.textContent = confirmBtnText;
            confirmBtn.className = 'btn ' + confirmBtnClass;
            
            // Remove previous event listener if exists
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            // Add new event listener
            newConfirmBtn.addEventListener('click', function() {
                callback();
                confirmationModal.hide();
            });
            
            confirmationModal.show();
        };
        
        // Helper function to show delete confirmation modal
        window.showDeleteConfirmation = function(message, callback) {
            // First ensure any existing backdrops are removed
            ensureBackdropRemoval();
            
            const modal = document.getElementById('deleteModal');
            const deleteModal = new bootstrap.Modal(modal);
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            
            document.getElementById('deleteMessage').textContent = message;
            
            // Remove previous event listener if exists
            const newConfirmBtn = confirmDeleteBtn.cloneNode(true);
            confirmDeleteBtn.parentNode.replaceChild(newConfirmBtn, confirmDeleteBtn);
            
            // Add new event listener
            newConfirmBtn.addEventListener('click', function() {
                callback();
                deleteModal.hide();
            });
            
            deleteModal.show();
        };
    });
</script>
@endpush 