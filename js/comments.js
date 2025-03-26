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

    // Handle reply button clicks
    document.querySelectorAll('.reply').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const replyItem = this.closest('.reply-item, .comment-item');
            const replyForm = replyItem.querySelector('.reply-form');
            
            // Hide all other reply forms first
            document.querySelectorAll('.reply-form').forEach(form => {
                if (form !== replyForm) {
                    form.style.display = 'none';
                }
            });

            // Toggle this reply form
            replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';

            // Initialize auto-resize for the textarea
            if (replyForm.style.display === 'block') {
                const textarea = replyForm.querySelector('textarea');
                if (textarea) {
                    autoResize(textarea);
                }
            }
        });
    });

    // Handle cancel reply buttons
    document.querySelectorAll('.cancel-reply').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const replyForm = this.closest('.reply-form');
            replyForm.style.display = 'none';
        });
    });

    // Handle edit button clicks
    document.querySelectorAll('.edit-comment, .edit-reply').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const commentItem = this.closest('.comment-item, .reply-item');
            
            // Find the text content based on whether it's a comment or reply
            let textElement;
            if (commentItem.classList.contains('comment-item')) {
                textElement = commentItem.querySelector('.comment-text');
            } else {
                textElement = commentItem.querySelector('.reply-text');
            }

            if (!textElement) {
                console.error('Could not find text element');
                return;
            }

            const commentText = textElement.textContent.trim();
            
            // Create edit form
            const editForm = document.createElement('div');
            editForm.className = 'edit-form';
            editForm.innerHTML = `
                <form class="edit-comment-form">
                    <textarea class="edit-content" rows="1">${commentText}</textarea>
                    <div class="edit-actions">
                        <input type="file" name="cmt_attachment" class="edit-attachment" accept="image/*,.pdf,.doc,.docx">
                        <button type="submit" class="btn save-edit">Save</button>
                        <button type="button" class="btn cancel-edit">Cancel</button>
                    </div>
                </form>
            `;

            // Hide the original content
            textElement.style.display = 'none';
            const actionsElement = commentItem.querySelector('.comment-actions, .reply-actions');
            if (actionsElement) {
                actionsElement.style.display = 'none';
            }

            // Insert the edit form
            if (commentItem.classList.contains('comment-item')) {
                commentItem.querySelector('.comment-content').appendChild(editForm);
            } else {
                commentItem.querySelector('.reply-content').appendChild(editForm);
            }

            // Handle save edit
            editForm.querySelector('form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData();
                formData.append('comment_id', commentItem.dataset.commentId || commentItem.dataset.replyId);
                formData.append('cmt_content', editForm.querySelector('.edit-content').value);
                
                const attachment = editForm.querySelector('.edit-attachment').files[0];
                if (attachment) {
                    formData.append('cmt_attachment', attachment);
                }

                fetch('update_comment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update the text content
                        textElement.textContent = editForm.querySelector('.edit-content').value;
                        textElement.style.display = 'block';
                        if (actionsElement) {
                            actionsElement.style.display = 'block';
                        }
                        editForm.remove();
                        
                        // Only reload if there was an attachment
                        if (attachment) {
                            location.reload();
                        }
                    } else {
                        throw new Error(data.message || 'Failed to update comment');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update comment: ' + error.message);
                });
            });

            // Handle cancel edit
            editForm.querySelector('.cancel-edit').addEventListener('click', function() {
                textElement.style.display = 'block';
                if (actionsElement) {
                    actionsElement.style.display = 'block';
                }
                editForm.remove();
            });

            // After creating the edit form, initialize auto-resize for its textarea
            setTimeout(() => {
                initializeAutoResize();
            }, 0);
        });
    });

    // Add these functions at the top
    function showModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
    }

    function hideModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('successToast');
        toast.textContent = message;
        toast.className = 'toast ' + type;
        toast.style.display = 'block';
        
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }

    // Update the delete button handler
    document.querySelectorAll('.delete-comment, .delete-reply').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const commentItem = this.closest('.comment-item, .reply-item');
            const deleteModal = document.getElementById('deleteModal');
            const confirmDelete = document.getElementById('confirmDelete');
            const cancelDelete = document.getElementById('cancelDelete');

            // Show the modal
            deleteModal.classList.add('show');

            // Close modal when clicking outside
            window.onclick = function(e) {
                if (e.target === deleteModal) {
                    deleteModal.classList.remove('show');
                }
            };

            // Handle cancel
            cancelDelete.onclick = function() {
                deleteModal.classList.remove('show');
            };

            // Handle confirm
            confirmDelete.onclick = function() {
                const formData = new FormData();
                formData.append('comment_id', commentItem.dataset.commentId || commentItem.dataset.replyId);

                fetch('delete_comment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        commentItem.remove();
                        deleteModal.classList.remove('show');
                        showToast('Comment deleted successfully');
                    } else {
                        throw new Error(data.message || 'Failed to delete comment');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete comment');
                    deleteModal.classList.remove('show');
                });
            };
        });
    });

    // Update the reply form submission
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (this.action.includes('add_reply.php') || this.action.includes('add_comment.php')) {
                e.preventDefault();
                
                fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Comment submitted successfully');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        throw new Error(data.message || 'Failed to submit comment');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to submit comment');
                });
            }
        });
    });

    // When a form is submitted for edit
    document.body.addEventListener('submit', function(e) {
        if (e.target.classList.contains('edit-form')) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const contentDiv = form.closest('.comment-text, .reply-text');
            const item = form.closest('.comment-item, .reply-item');
            
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Replace the form with the updated content
                    contentDiv.innerHTML = data.content || formData.get('content');
                    
                    // Update attachment if needed
                    if (data.attachment) {
                        let attachmentDiv = item.querySelector('.comment-attachment, .reply-attachment');
                        if (!attachmentDiv) {
                            attachmentDiv = document.createElement('div');
                            attachmentDiv.className = item.classList.contains('comment-item') ? 
                                'comment-attachment' : 'reply-attachment';
                            contentDiv.after(attachmentDiv);
                        }
                        attachmentDiv.innerHTML = `<a href="${data.attachment}" target="_blank">View Attachment</a>`;
                    } else {
                        // Remove attachment div if attachment was removed
                        const attachmentDiv = item.querySelector('.comment-attachment, .reply-attachment');
                        if (attachmentDiv) {
                            attachmentDiv.remove();
                        }
                    }
                    
                    // Show success message
                    showToast('Comment updated successfully', 'success');
                } else {
                    showToast(data.message || 'Error updating comment', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while updating', 'error');
            });
        }
    });
}); 