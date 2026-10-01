<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    protected $fillable = ['name', 'muscle_group', 'instructions'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workouts()
    {
        return $this->belongsToMany(Workout::class)->withTimestamps();
    }

    public function sets()
    {
        return $this->hasMany(WorkoutSet::class);
    }
}
