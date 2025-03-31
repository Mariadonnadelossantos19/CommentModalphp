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

    // Variables for modals
    const editModal = document.getElementById('editModal');
    const deleteModal = document.getElementById('deleteModal');
    const successToast = document.getElementById('successToast');
    
    let currentCommentId = null;
    let currentCommentElement = null;

    // Simple Clean Edit Modal Functionality
    const editCommentText = document.getElementById('editCommentText');
    const saveEditBtn = document.getElementById('saveEdit');
    const cancelEditBtn = document.getElementById('cancelEdit');

    // ------- EDIT COMMENT FUNCTIONALITY -------
    // Open edit modal when edit button is clicked
    document.querySelectorAll('.edit-comment, .edit-reply').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the comment element
            const commentItem = this.closest('.comment-item, .reply-item, .nested-reply-item');
            const commentText = commentItem.querySelector('.comment-text, .reply-text').innerText;
            
            // Store reference to current comment
            currentCommentId = this.dataset.id || commentItem.dataset.commentId || commentItem.dataset.replyId;
            currentCommentElement = commentItem.querySelector('.comment-text, .reply-text');
            
            // Set text in modal
            editCommentText.value = commentText;
            
            // Show the modal
            editModal.style.display = 'flex';
            editCommentText.focus();
            
            // Place cursor at the end of text
            const textLength = editCommentText.value.length;
            editCommentText.setSelectionRange(textLength, textLength);
        });
    });
    
    // Close modal function
    function closeEditModal() {
        editModal.style.display = 'none';
    }
    
    // Close on Close button click
    cancelEditBtn.addEventListener('click', closeEditModal);
    
    // Close on outside click
    window.addEventListener('click', function(e) {
        if (e.target === editModal) {
            closeEditModal();
        }
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && editModal.style.display === 'flex') {
            closeEditModal();
        }
    });
    
    // Handle save button click
    saveEditBtn.addEventListener('click', function() {
        const newContent = editCommentText.value.trim();
        
        if (!newContent) {
            showToast('Comment cannot be empty');
            return;
        }
        
        // Extract content from HTML tags if present
        let cleanContent = newContent;
        if (newContent.startsWith('<p>') && newContent.endsWith('</p>')) {
            cleanContent = newContent.substring(3, newContent.length - 4);
        }
        
        const formData = new FormData();
        formData.append('comment_id', currentCommentId);
        formData.append('content', cleanContent);
        
        // Show loading state
        saveEditBtn.textContent = 'Saving...';
        saveEditBtn.disabled = true;
        
        fetch('edit_comment.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Reset button
            saveEditBtn.textContent = 'Save';
            saveEditBtn.disabled = false;
            
            if (data.success) {
                // Update comment content
                currentCommentElement.innerText = cleanContent;
                
                // Close modal
                closeEditModal();
                
                // Show success message
                showToast('Comment updated successfully');
            } else {
                showToast(data.message || 'Error updating comment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            saveEditBtn.textContent = 'Save';
            saveEditBtn.disabled = false;
            showToast('An error occurred while updating the comment');
        });
    });

    // ------- DELETE COMMENT FUNCTIONALITY -------
    // Open delete modal when delete button is clicked
    document.querySelectorAll('.delete-comment, .delete-reply').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the comment element and ID
            const commentItem = this.closest('.comment-item, .reply-item, .nested-reply-item');
            currentCommentId = this.dataset.id || commentItem.dataset.commentId || commentItem.dataset.replyId;
            currentCommentElement = commentItem;
            
            // Show the modal
            deleteModal.style.display = 'flex';
        });
    });
    
    // Close modal function
    function closeDeleteModal() {
        deleteModal.style.display = 'none';
    }
    
    // Close on Cancel button click
    const cancelDeleteBtn = document.getElementById('cancelDelete');
    cancelDeleteBtn.addEventListener('click', closeDeleteModal);
    
    // Close on outside click
    window.addEventListener('click', function(e) {
        if (e.target === deleteModal) {
            closeDeleteModal();
        }
    });
    
    // Confirm delete button click
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    confirmDeleteBtn.addEventListener('click', function() {
        // Show loading state
        confirmDeleteBtn.textContent = 'Deleting...';
        confirmDeleteBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('comment_id', currentCommentId);
        
        fetch('delete_comment.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Reset button
            confirmDeleteBtn.textContent = 'Delete';
            confirmDeleteBtn.disabled = false;
            
            if (data.success) {
                // Remove the comment from the DOM
                if (currentCommentElement) {
                    currentCommentElement.remove();
                }
                
                // Close modal
                closeDeleteModal();
                
                // Show success message
                showToast('Comment deleted successfully');
            } else {
                showToast(data.message || 'Error deleting comment');
                closeDeleteModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            confirmDeleteBtn.textContent = 'Delete';
            confirmDeleteBtn.disabled = false;
            showToast('An error occurred while deleting the comment');
            closeDeleteModal();
        });
    });

    // ------- REPLY FUNCTIONALITY -------
    // Toggle reply form visibility
    document.querySelectorAll('.reply').forEach(button => {
        button.addEventListener('click', function() {
            const commentItem = this.closest('.comment-item, .reply-item, .nested-reply-item');
            const replyForm = commentItem.querySelector('.reply-form');
            
            if (replyForm) {
                if (replyForm.style.display === 'none' || replyForm.style.display === '') {
                    replyForm.style.display = 'block';
                    replyForm.querySelector('textarea').focus();
                } else {
                    replyForm.style.display = 'none';
                }
            }
        });
    });
    
    // Cancel reply
    document.querySelectorAll('.cancel-reply').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const replyForm = this.closest('.reply-form');
            if (replyForm) {
                replyForm.style.display = 'none';
            }
        });
    });
    
    // Toast notification function
    function showToast(message) {
        successToast.textContent = message;
        successToast.style.display = 'block';
        
        setTimeout(() => {
            successToast.style.display = 'none';
        }, 3000);
    }

    // Comment and Reply Success Notification
    const successModal = document.getElementById('successModal');
    const successMessage = document.getElementById('successMessage');
    const successOkBtn = document.getElementById('successOk');
    
    // Close success modal function
    function closeSuccessModal() {
        successModal.style.display = 'none';
    }
    
    // Close on OK button click
    successOkBtn.addEventListener('click', closeSuccessModal);
    
    // Close on outside click
    window.addEventListener('click', function(e) {
        if (e.target === successModal) {
            closeSuccessModal();
        }
    });
    
    // Show success modal with custom message
    function showSuccessModal(message) {
        successMessage.textContent = message || 'Your submission was successful.';
        successModal.style.display = 'flex';
    }
    
    // Handle comment form submission
    const commentForm = document.querySelector('.add-comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('add_comment.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reset form
                    this.reset();
                    
                    // Show success message
                    showSuccessModal('Your comment has been submitted successfully.');
                    
                    // Reload comments after a short delay
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    alert(data.message || 'Error submitting comment');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting your comment');
            });
        });
    }
    
    // Handle reply form submissions
    document.querySelectorAll('.reply-form form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const replyForm = this.closest('.reply-form');
            
            fetch('add_reply.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reset form
                    this.reset();
                    
                    // Hide the reply form
                    if (replyForm) {
                        replyForm.style.display = 'none';
                    }
                    
                    // Show success message
                    showSuccessModal('Your reply has been submitted successfully.');
                    
                    // Reload comments after a short delay
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    alert(data.message || 'Error submitting reply');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting your reply');
            });
        });
    });
}); 