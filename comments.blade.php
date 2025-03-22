<!-- Main Comment Section -->
<div class="comment-section">
    <!-- Add New Comment -->
    <div class="add-comment-section">
        <form action="{{ route('comments.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="cmt_fnd_id" value="{{ $funding_id }}">
            <textarea name="cmt_content" class="comment-content" placeholder="Write your comment..."></textarea>
            <div class="comment-actions">
                <input type="file" name="cmt_attachment" class="upload-attachment" accept="image/*,.pdf,.doc,.docx">
                <button type="submit" class="btn reply-comment">Post Comment</button>
            </div>
        </form>
    </div>

    <!-- Comments List -->
    <div class="comments-list">
        @foreach($comments as $comment)
            <!-- Single Comment Template -->
            <div class="comment-item" data-comment-id="{{ $comment->cmt_id }}">
                <div class="comment-content">
                    <div class="comment-header">
                        <span class="user-name">{{ $comment->user->name }}</span>
                        <span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="comment-text">{{ $comment->cmt_content }}</div>
                    @if($comment->cmt_attachment)
                        <div class="comment-attachment">
                            <a href="{{ asset('storage/' . $comment->cmt_attachment) }}" target="_blank">
                                View Attachment
                            </a>
                        </div>
                    @endif
                    <div class="comment-actions">
                        @if(auth()->id() == $comment->cmt_added_by)
                            <button class="edit-comment">Edit</button>
                            <button class="delete-comment">Delete</button>
                        @endif
                        <button class="reply">Reply</button>
                    </div>

                    <!-- Edit Comment Form (Initially Hidden) -->
                    <div class="update-comment-form" style="display: none;">
                        <form action="{{ route('comments.update', $comment->cmt_id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <textarea name="cmt_content" class="update-comment-content">{{ $comment->cmt_content }}</textarea>
                            <div class="update-actions">
                                <input type="file" name="cmt_attachment" class="update-upload">
                                <button type="submit" class="update-comment">Update</button>
                                <button type="button" class="cancel-update-comment">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <!-- Reply Form (Initially Hidden) -->
                    <div class="reply-form" style="display: none;">
                        <form action="{{ route('comments.reply', $comment->cmt_id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <textarea name="cmt_content" class="reply-content"></textarea>
                            <div class="reply-actions">
                                <input type="file" name="cmt_attachment" class="upload-reply">
                                <button type="submit" class="send-reply">Send Reply</button>
                                <button type="button" class="cancel-reply">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <!-- Replies Container -->
                    <div class="replies-container">
                        @foreach($comment->replies as $reply)
                            <div class="reply-item" data-reply-id="{{ $reply->cmt_id }}">
                                <div class="reply-content">
                                    <div class="reply-header">
                                        <span class="user-name">{{ $reply->user->name }}</span>
                                        <span class="reply-date">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="reply-text">{{ $reply->cmt_content }}</div>
                                    @if($reply->cmt_attachment)
                                        <div class="reply-attachment">
                                            <a href="{{ asset('storage/' . $reply->cmt_attachment) }}" target="_blank">
                                                View Attachment
                                            </a>
                                        </div>
                                    @endif
                                    <div class="reply-actions">
                                        @if(auth()->id() == $reply->cmt_added_by)
                                            <button class="edit-reply">Edit</button>
                                            <button class="delete-reply">Delete</button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Edit Reply Form (Initially Hidden) -->
                                <div class="update-reply-form" style="display: none;">
                                    <form action="{{ route('comments.update', $reply->cmt_id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <textarea name="cmt_content" class="update-reply-content">{{ $reply->cmt_content }}</textarea>
                                        <div class="update-reply-actions">
                                            <input type="file" name="cmt_attachment" class="update-upload">
                                            <button type="submit" class="update-reply">Update</button>
                                            <button type="button" class="cancel-update-reply">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div> 