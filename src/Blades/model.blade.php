namespace {{ $teste }};

use Illuminate\Database\Eloquent\Model;

class {{ $tableName }} extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
    @foreach ($fillables as $fillable)
    '{{ $fillable }}',
    @endforeach
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
    @foreach ($casts as $key => $cast)
    '{{ $key }}' => '{{ $cast }}',
    @endforeach
    ];
}
