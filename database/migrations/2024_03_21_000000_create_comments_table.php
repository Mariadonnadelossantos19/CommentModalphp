use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    public function up()
    {
        Schema::create('tblcomments', function (Blueprint $table) {
            $table->increments('cmt_id');
            $table->unsignedInteger('cmt_fnd_id')->nullable();
            $table->text('cmt_content')->nullable();
            $table->text('cmt_attachment')->nullable();
            $table->integer('cmt_added_by')->nullable();
            $table->integer('cmt_isReply_to')->nullable();
            $table->integer('cmt_isOpened')->default(0);
            $table->integer('cmt_isArchived')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tblcomments');
    }
} 