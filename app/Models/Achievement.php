<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'icon',
        'image',
        'vip_points_reward',
        'requirement_type',
        'requirement_value',
        'status',
        'total_limit',
        'active',
    ];

    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class);
    }
}
