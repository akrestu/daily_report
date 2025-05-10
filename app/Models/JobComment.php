<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobComment extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'daily_report_id',
        'user_id',
        'comment',
        'visibility'
    ];
    
    /**
     * Get the daily report that owns the comment.
     */
    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id');
    }
    
    /**
     * Get the user who created the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
