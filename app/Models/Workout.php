<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    // user_id comes from the authenticated user's relationship, never form input.
    protected $fillable = ['name', 'workout_date', 'workout_type_id', 'status', 'notes'];

    protected $casts = ['workout_date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workoutType()
    {
        return $this->belongsTo(WorkoutType::class);
    }

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class)->withTimestamps();
    }

    public function sets()
    {
        return $this->hasMany(WorkoutSet::class)->orderBy('id');
    }
}
