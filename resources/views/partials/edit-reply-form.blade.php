<div class="update-reply-form" style="display: none;">
    <form action="{{ route('comments.update', $reply->cmt_id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <textarea name="cmt_content" class="update-reply-content">{{ $reply->cmt_content }}</textarea>
        <div class="update-reply-actions">
            <input type="file" name="cmt_attachment" class="update-upload">
            <button type="submit" class="btn update-reply">Update</button>
            <button type="button" class="btn cancel-update-reply">Cancel</button>
        </div>
    </form>
</div> 