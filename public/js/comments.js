document.addEventListener('DOMContentLoaded', function() {
    // Handle reply button clicks
    document.querySelectorAll('.reply').forEach(button => {
        button.addEventListener('click', function() {
            const commentItem = this.closest('.comment-item');
            const replyForm = commentItem.querySelector('.reply-form');
            replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
        });
    });

    // Handle edit button clicks
    document.querySelectorAll('.edit-comment, .edit-reply').forEach(button => {
        button.addEventListener('click', function() {
            const contentContainer = this.closest('.comment-item, .reply-item');
            const updateForm = contentContainer.querySelector('.update-comment-form, .update-reply-form');
            updateForm.style.display = updateForm.style.display === 'none' ? 'block' : 'none';
        });
    });

    // Handle cancel buttons
    document.querySelectorAll('.cancel-update-comment, .cancel-update-reply, .cancel-reply').forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('.update-comment-form, .update-reply-form, .reply-form');
            form.style.display = 'none';
        });
    });

    // Handle delete buttons
    document.querySelectorAll('.delete-comment, .delete-reply').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this?')) {
                const item = this.closest('.comment-item, .reply-item');
                const id = item.dataset.commentId || item.dataset.replyId;
                
                fetch(`/comments/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        item.remove();
                    } else {
                        throw new Error(data.message || 'Unknown error occurred');
                    }
                })
                .catch(error => handleError(error));
            }
        });
    });
});

function handleError(error) {
    console.error('Error:', error);
    alert('An error occurred. Please try again.');
} 