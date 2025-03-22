@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/comments.css') }}">
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
            @forelse($comments as $comment)
                <!-- Single Comment Template -->
                <div class="comment-item" data-comment-id="{{ $comment->cmt_id }}">
                    <div class="comment-content">
                        <div class="comment-header">
                            <span class="user-name">{{ $comment->user->name ?? 'Unknown User' }}</span>
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
                                <button class="btn edit-comment">Edit</button>
                                <button class="btn delete-comment">Delete</button>
                            @endif
                            <button class="btn reply">Reply</button>
                        </div>

                        <!-- Edit Comment Form -->
                        @include('partials.edit-comment-form', ['comment' => $comment])

                        <!-- Reply Form -->
                        @include('partials.reply-form', ['comment' => $comment])

                        <!-- Replies Container -->
                        @include('partials.replies', ['replies' => $comment->replies])
                    </div>
                </div>
            @empty
                <div class="no-comments">
                    No comments yet. Be the first to comment!
                </div>
            @endforelse
        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('js/comments.js') }}"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection 