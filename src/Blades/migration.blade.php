use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Create{{$migrationName}}Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('{{$tableName}}', function (Blueprint $table) {
            @foreach ($properties as $key => $propertie)
            $table->{{$propertie['type']}}('{{$key}}'@if($propertie['lengthOrEnumValues'] != ""),@if(in_array($propertie['type'], ["set", "enum"]))[@endif{!!$propertie['lengthOrEnumValues']!!}@if(in_array($propertie['type'], ["set", "enum"]))]@endif @endif);
            @endforeach
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
