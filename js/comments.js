/*
* js/comments.js
*
* Ito ang JavaScript file para sa comment system
* - Nag-hahandle ng AJAX requests
* - May real-time updates
* - Client-side validations
* - UI interactions (show/hide forms, etc)
*
* Konektado sa:
* - comments.php (main interface)
* - add_comment.php (para sa new comments)
* - add_reply.php (para sa replies)
* - delete_comment.php (para sa deletion)
*/

document.addEventListener('DOMContentLoaded', function() { // Nagse-setup ng event listener pag fully loaded ang page
    // Function to auto-resize textarea
    function autoResize(textarea) { // Function para awtomatikong i-adjust ang height ng textarea
        textarea.style.height = 'auto'; // Nire-reset ang height
        textarea.style.height = textarea.scrollHeight + 'px'; // Nagse-set ng height base sa content
    }

    // Apply auto-resize to all textareas
    function initializeAutoResize() { // Function para i-apply ang auto-resize sa lahat ng textareas
        const textareas = document.querySelectorAll('.comment-content, .reply-content, .edit-content'); // Kumuha ng lahat ng textarea elements
        textareas.forEach(textarea => { // Umiikot sa bawat textarea
            // Initial resize
            autoResize(textarea); // Una munang i-resize

            // Resize on input
            textarea.addEventListener('input', function() { // Nag-a-add ng event listener para sa input
                autoResize(this); // I-resize habang nagta-type
            });

            // Resize on focus
            textarea.addEventListener('focus', function() { // Nag-a-add ng event listener para sa focus
                autoResize(this); // I-resize kapag nag-focus
            });
        });
    }

    // Initialize auto-resize
    initializeAutoResize(); // Tinatawag ang function para mag-initialize

    // Toast notification function
    function showToast(message) {
        const toast = document.getElementById('successToast');
        toast.textContent = message;
        toast.style.display = 'block';
        
        setTimeout(function() {
            toast.style.display = 'none';
        }, 3000);
    }

    // Debug log helper
    function debug(message, data) {
        console.log(`DEBUG: ${message}`, data);
    }

    // =========== REPLY FUNCTIONALITY ===========
    
    // Handle reply button clicks using event delegation
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('reply-button') || 
            (event.target.classList.contains('reply') && !event.target.classList.contains('delete-reply') && !event.target.classList.contains('edit-reply'))) {
            event.preventDefault();
            
            // Get the ID of the item being replied to
            const replyId = event.target.dataset.id || 
                            event.target.closest('.comment-item, .reply-item, .nested-reply-item').dataset.commentId || 
                            event.target.closest('.comment-item, .reply-item, .nested-reply-item').dataset.replyId;
            
            debug('Reply button clicked for ID', replyId);
            
            // Hide all other reply forms first
            document.querySelectorAll('.reply-form').forEach(form => {
                form.style.display = 'none';
            });
            
            // Find and show the appropriate reply form
            const formId = `reply-form-${replyId}`;
            const replyForm = document.getElementById(formId) || 
                             event.target.closest('.comment-item, .reply-item, .nested-reply-item').querySelector('.reply-form');

            if (replyForm) {
                replyForm.style.display = 'block';
                replyForm.querySelector('textarea').focus();
                debug('Reply form displayed', formId);
            } else {
                console.error('Reply form not found for ID:', replyId);
            }
        }
    });
    
    // Handle cancel reply
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('cancel-reply')) {
            event.preventDefault();
            const replyForm = event.target.closest('.reply-form');
            if (replyForm) {
                replyForm.style.display = 'none';
                replyForm.querySelector('textarea').value = '';
                debug('Reply form cancelled');
            }
        }
    });

    // =========== EDIT FUNCTIONALITY ===========
    
    // Handle edit button clicks
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('edit-comment') || event.target.classList.contains('edit-reply')) {
            event.preventDefault();
            
            const container = event.target.closest('.comment-item, .reply-item, .nested-reply-item');
            const contentElement = container.querySelector('.comment-text, .reply-text');
            const originalContent = contentElement.textContent.trim();
            
            // Extract the ID from data attribute or container
            const itemId = event.target.dataset.id || 
                          container.dataset.commentId || 
                          container.dataset.replyId;
            
            debug('Edit button clicked for ID', itemId);
            
            // Check if already in edit mode
            if (container.querySelector('.edit-form')) {
                debug('Already in edit mode, ignoring click');
                return;
            }
            
            // Create edit form
            const editForm = document.createElement('form');
            editForm.className = 'edit-form';
            editForm.innerHTML = `
                <input type="hidden" name="csrf_token" value="${document.querySelector('input[name="csrf_token"]').value}">
                <input type="hidden" name="item_id" value="${itemId}">
                <textarea name="content" class="edit-content">${originalContent}</textarea>
                <div class="edit-actions">
                    <button type="submit" class="btn save-edit">Save</button>
                    <button type="button" class="btn cancel-edit">Cancel</button>
                </div>
            `;
            
            // Replace content with edit form
            contentElement.style.display = 'none';
            contentElement.insertAdjacentElement('afterend', editForm);
            
            // Focus textarea
            editForm.querySelector('textarea').focus();
            debug('Edit form displayed for', itemId);
            
            // Save edit handler
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const newContent = this.querySelector('textarea').value.trim();
                const formItemId = this.querySelector('input[name="item_id"]').value;
                
                debug('Saving edit for ID', formItemId);
                
                if (newContent === '') {
                    alert('Comment cannot be empty.');
                    return;
                }
                
                // Determine if this is a comment or reply
                const isComment = container.classList.contains('comment-item');
                
                debug('Is comment?', isComment);
                debug('Sending AJAX to', isComment ? 'edit_comment.php' : 'edit_reply.php');
                
                // Send AJAX request to update
                const xhr = new XMLHttpRequest();
                xhr.open('POST', isComment ? 'edit_comment.php' : 'edit_reply.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    debug('AJAX response received', xhr.responseText);
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                // Update content
                                contentElement.textContent = newContent;
                                contentElement.style.display = 'block';
                                editForm.remove();
                                
                                // Show success message
                                showToast('Changes saved successfully.');
                                debug('Edit saved successfully');
                            } else {
                                alert(response.message || 'Error saving changes.');
                                debug('Edit failed with message', response.message);
                            }
                        } catch (e) {
                            console.error('Error parsing JSON:', e, 'Response:', xhr.responseText);
                            alert('Invalid response from server.');
                        }
                    } else {
                        debug('HTTP error', xhr.status);
                        alert('Error saving changes. Please try again.');
                    }
                };
                xhr.send(`id=${formItemId}&content=${encodeURIComponent(newContent)}&csrf_token=${encodeURIComponent(document.querySelector('input[name="csrf_token"]').value)}`);
            });
            
            // Cancel edit handler
            editForm.querySelector('.cancel-edit').addEventListener('click', function() {
                contentElement.style.display = 'block';
                editForm.remove();
                debug('Edit cancelled');
            });
        }
    });

    // =========== DELETE FUNCTIONALITY ===========
    
    // Handle delete button clicks
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('delete-comment') || event.target.classList.contains('delete-reply')) {
            event.preventDefault();
            
            const container = event.target.closest('.comment-item, .reply-item, .nested-reply-item');
            const isComment = container.classList.contains('comment-item');
            
            // Extract the ID from data attribute or container
            const itemId = event.target.dataset.id || 
                          container.dataset.commentId || 
                          container.dataset.replyId;
            
            debug('Delete button clicked for ID', itemId);
            debug('Is comment?', isComment);
            
            // Show confirmation modal
            const modal = document.getElementById('deleteModal');
            modal.style.display = 'flex';
            
            // Store reference to the item being deleted
            modal.dataset.deleteItemId = itemId;
            modal.dataset.isComment = isComment ? 'true' : 'false';
            
            // Set up confirm delete button
            document.getElementById('confirmDelete').onclick = function() {
                debug('Confirm delete clicked for', modal.dataset.deleteItemId);
                deleteItem(modal.dataset.deleteItemId, modal.dataset.isComment === 'true', container);
                modal.style.display = 'none';
            };
            
            // Set up cancel delete button
            document.getElementById('cancelDelete').onclick = function() {
                modal.style.display = 'none';
                debug('Delete cancelled');
            };
        }
    });
    
    // Delete item function
    function deleteItem(itemId, isComment, container) {
        debug('Deleting item', { id: itemId, isComment: isComment });
        debug('Sending AJAX to', isComment ? 'delete_comment.php' : 'delete_reply.php');
        
        // Send AJAX request to delete
        const xhr = new XMLHttpRequest();
        xhr.open('POST', isComment ? 'delete_comment.php' : 'delete_reply.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            debug('AJAX response received', xhr.responseText);
            
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Remove the container from DOM
                        container.remove();
                        
                        // Show success message
                        showToast('Item deleted successfully.');
                        debug('Delete successful');
                        
                        // Update reply count if needed
                        if (!isComment) {
                            const parentContainer = container.closest('.comment-item, .reply-item');
                            if (parentContainer) {
                                const replyCountElement = parentContainer.querySelector('.reply-count');
                                if (replyCountElement) {
                                    const currentCount = parseInt(replyCountElement.textContent.split(' ')[0], 10);
                                    if (currentCount > 1) {
                                        replyCountElement.textContent = (currentCount - 1) + ' ' + 
                                           ((currentCount - 1) === 1 ? 'reply' : 'replies');
                                        debug('Updated reply count to', currentCount - 1);
                                    } else {
                                        replyCountElement.remove();
                                        debug('Removed reply count element');
                                    }
                                }
                            }
                        }
                    } else {
                        alert(response.message || 'Error deleting item.');
                        debug('Delete failed with message', response.message);
                    }
                } catch (e) {
                    console.error('Error parsing JSON:', e, 'Response:', xhr.responseText);
                    alert('Invalid response from server.');
                }
            } else {
                debug('HTTP error', xhr.status);
                alert('Error deleting item. Please try again.');
            }
        };
        xhr.send(`id=${itemId}&csrf_token=${encodeURIComponent(document.querySelector('input[name="csrf_token"]').value)}`);
    }
}); 