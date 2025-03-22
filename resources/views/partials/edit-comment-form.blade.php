<div class="update-comment-form" style="display: none;">
    <form action="{{ route('comments.update', $comment->cmt_id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <textarea name="cmt_content" class="update-comment-content">{{ $comment->cmt_content }}</textarea>
        <div class="update-actions">
            <input type="file" name="cmt_attachment" class="update-upload">
            <button type="submit" class="btn update-comment">Update</button>
            <button type="button" class="btn cancel-update-comment">Cancel</button>
        </div>
    </form>
</div> 