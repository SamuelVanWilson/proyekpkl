<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdfExport extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pdf_exports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'daily_report_id',
        'filename',
        'type',
        'filters',
        'data_snapshot',
        'total_items',
        'total_pages',
        'file_path',
        'exported_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_snapshot' => 'array',
        'filters' => 'array',
        'exported_at' => 'datetime',
    ];
    
    /**
     * Mendefinisikan relasi: Setiap catatan ekspor dimiliki oleh satu User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
