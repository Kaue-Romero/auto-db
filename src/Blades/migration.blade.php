use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('{{$tableName}}')) return;
        Schema::create('{{$tableName}}', function (Blueprint $table) {
            @foreach ($properties as $key => $propertie)
            $table->{{$propertie['type']}}('{{$key}}'@if($propertie['lengthOrEnumValues'] != ""),@if(in_array($propertie['type'], ["set", "enum"]))[@endif{!!$propertie['lengthOrEnumValues']!!}@if(in_array($propertie['type'], ["set", "enum"]))]@endif{{""}}@endif)@if($propertie['null'])->nullable()@endif{{""}}@if($propertie['default'])->default({!!$propertie['default']!!})@endif{{""}}@if($propertie['defaultFunction']){!!$propertie['defaultFunction']!!}@endif;
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
        Schema::dropIfExists('{{$tableName}}');
    }
};
