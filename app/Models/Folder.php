<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'user_id',
    ];

    /**
     * Relationships
     */

    // A folder can have many tasks
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }


    // A folder belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
