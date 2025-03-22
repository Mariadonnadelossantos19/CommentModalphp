<div class="replies-container">
    @foreach($replies as $reply)
        <div class="reply-item" data-reply-id="{{ $reply->cmt_id }}">
            <div class="reply-content">
                <div class="reply-header">
                    <span class="user-name">{{ $reply->user->name ?? 'Unknown User' }}</span>
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
                @if(auth()->id() == $reply->cmt_added_by)
                    <div class="reply-actions">
                        <button class="btn edit-reply">Edit</button>
                        <button class="btn delete-reply">Delete</button>
                    </div>
                @endif
            </div>

            @include('partials.edit-reply-form', ['reply' => $reply])
        </div>
    @endforeach
</div> 