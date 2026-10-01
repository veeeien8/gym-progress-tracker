<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('muscle_group', 40);
            $table->text('instructions')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });
        Schema::create('workouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workout_type_id')->constrained()->restrictOnDelete();
            $table->string('name', 100);
            $table->date('workout_date');
            $table->string('status', 20)->default('planned');
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'workout_date']);
        });
        // Each pair exists at most once; sets are separate because reps/weight vary.
        Schema::create('exercise_workout', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained()->restrictOnDelete();
            $table->foreignId('workout_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['exercise_id', 'workout_id']);
        });
        Schema::create('workout_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('reps');
            $table->decimal('weight', 7, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_sets');
        Schema::dropIfExists('exercise_workout');
        Schema::dropIfExists('workouts');
        Schema::dropIfExists('exercises');
        Schema::dropIfExists('workout_types');
    }
};
