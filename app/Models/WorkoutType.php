<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutType extends Model
{
    protected $fillable = ['name', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workouts()
    {
        return $this->hasMany(Workout::class);
    }
}
