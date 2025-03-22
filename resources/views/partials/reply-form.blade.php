<div class="reply-form" style="display: none;">
    <form action="{{ route('comments.reply', $comment->cmt_id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <textarea name="cmt_content" class="reply-content"></textarea>
        <div class="reply-actions">
            <input type="file" name="cmt_attachment" class="upload-reply">
            <button type="submit" class="btn send-reply">Send Reply</button>
            <button type="button" class="btn cancel-reply">Cancel</button>
        </div>
    </form>
</div> 