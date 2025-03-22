use App\Http\Controllers\CommentsController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\FundingController;

// Comments routes
Route::middleware(['auth'])->group(function () {
    Route::post('/comments', [CommentsController::class, 'store'])->name('comments.store');
    Route::post('/comments/{comment}/reply', [CommentsController::class, 'reply'])->name('comments.reply');
    Route::put('/comments/{comment}', [CommentsController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentsController::class, 'destroy'])->name('comments.destroy');
});

Route::get('/test-db', function() {
    try {
        DB::connection()->getPdo();
        $comments = \App\Models\Comment::all();
        return "Database connected successfully. Found " . $comments->count() . " comments.";
    } catch (\Exception $e) {
        return "Could not connect to the database. Error: " . $e->getMessage();
    }
});

Route::get('/funding/{funding_id}/comments', [FundingController::class, 'show'])->name('funding.comments');

// Add this test route
Route::get('/test-comments', function() {
    $funding_id = 1; // Test funding ID
    $comments = \App\Models\Comment::whereNull('cmt_isReply_to')
        ->orderBy('created_at', 'desc')
        ->get();
    return view('comments', compact('comments', 'funding_id'));
})->name('test.comments'); 