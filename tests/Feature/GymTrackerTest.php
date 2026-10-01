<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GymTrackerTest extends TestCase
{
    use RefreshDatabase;

    private function workout(User $user, array $extra = [])
    {
        $type = $user->workoutTypes()->firstOrCreate(['name' => 'Push']);

        return $user->workouts()->create(array_merge(['name' => 'Push day', 'workout_date' => '2026-09-01', 'workout_type_id' => $type->id, 'status' => 'planned'], $extra));
    }

    private function exercise(User $user)
    {
        return $user->exercises()->create(['name' => 'Bench press', 'muscle_group' => 'Chest']);
    }

    private function payload($workout, array $extra = [])
    {
        return array_merge(['name' => 'Updated workout', 'workout_date' => '2026-09-02', 'workout_type_id' => $workout->workout_type_id, 'status' => 'completed'], $extra);
    }

    public function test_guests_cannot_access_training_pages_or_create_workouts(): void
    {
        foreach (['/workouts', '/workouts/create', '/exercises', '/workout-types', '/progress'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->post('/workouts', [])->assertRedirect('/login');
        $this->assertDatabaseCount('workouts', 0);
    }

    public function test_register_login_and_logout(): void
    {
        $this->post('/register', ['name' => 'Alex', 'email' => 'alex@test.com', 'password' => 'Password123!', 'password_confirmation' => 'Password123!'])->assertRedirect('/workouts');
        $user = User::first();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123!'])->assertRedirect('/workouts');
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_registration_is_rejected(): void
    {
        $this->post('/register', ['name' => '', 'email' => 'bad', 'password' => 'short', 'password_confirmation' => 'different'])->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_workout_crud_ignores_forged_ownership(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $type = $user->workoutTypes()->create(['name' => 'Legs']);
        $this->actingAs($user)->post('/workouts', ['name' => 'Leg day', 'workout_date' => '2026-09-01', 'workout_type_id' => $type->id, 'status' => 'planned', 'user_id' => $other->id])->assertSessionHasNoErrors();
        $w = Workout::first();
        $this->assertEquals($user->id, $w->user_id);
        $this->get('/workouts/'.$w->id)->assertOk()->assertSee('Leg day');
        $this->put('/workouts/'.$w->id, $this->payload($w))->assertRedirect('/workouts/'.$w->id);
        $this->assertDatabaseHas('workouts', ['id' => $w->id, 'name' => 'Updated workout', 'status' => 'completed']);
        $this->delete('/workouts/'.$w->id)->assertRedirect('/workouts');
        $this->assertDatabaseMissing('workouts', ['id' => $w->id]);
    }

    public function test_workout_validation_checks_fields_and_owned_foreign_keys(): void
    {
        $user = User::factory()->create();
        $foreign = $this->workout(User::factory()->create());
        $this->actingAs($user)->post('/workouts', ['name' => '', 'workout_date' => 'bad', 'workout_type_id' => $foreign->workout_type_id, 'status' => 'bad', 'notes' => str_repeat('x', 3001)])->assertSessionHasErrors(['name', 'workout_date', 'workout_type_id', 'status', 'notes']);
        $this->post('/workouts', ['name' => 'X', 'workout_date' => '2026-09-01', 'workout_type_id' => 999999, 'status' => 'planned'])->assertSessionHasErrors('workout_type_id');
        $w = $this->workout($user);
        $this->put('/workouts/'.$w->id, $this->payload($w, ['workout_date' => now()->addDay()->toDateString()]))->assertSessionHasErrors('workout_date');
    }

    public function test_other_accounts_cannot_read_change_delete_or_download_workouts(): void
    {
        $w = $this->workout(User::factory()->create(), ['name' => 'Secret session']);
        $this->actingAs(User::factory()->create());
        $this->get('/workouts')->assertOk()->assertDontSee('Secret session');
        foreach (['/workouts/'.$w->id, '/workouts/'.$w->id.'/edit', '/workouts/'.$w->id.'/attachment'] as $url) {
            $this->get($url)->assertNotFound();
        }
        $this->put('/workouts/'.$w->id, $this->payload($w))->assertNotFound();
        $this->delete('/workouts/'.$w->id)->assertNotFound();
        $this->assertDatabaseHas('workouts', ['id' => $w->id, 'name' => 'Secret session']);
    }

    public function test_types_and_exercises_have_crud_and_validation(): void
    {
        $this->actingAs($user = User::factory()->create());
        $this->post('/workout-types', ['name' => 'Push'])->assertRedirect('/workout-types');
        $type = $user->workoutTypes()->first();
        $this->post('/workout-types', ['name' => 'Push'])->assertSessionHasErrors('name');
        $this->put('/workout-types/'.$type->id, ['name' => 'Upper', 'description' => 'Changed'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('workout_types', ['id' => $type->id, 'name' => 'Upper']);
        $this->post('/exercises', ['name' => 'Row', 'muscle_group' => 'Back'])->assertSessionHasNoErrors();
        $e = $user->exercises()->first();
        $this->post('/exercises', ['name' => 'Row', 'muscle_group' => 'Fake'])->assertSessionHasErrors(['name', 'muscle_group']);
        $this->put('/exercises/'.$e->id, ['name' => 'Seated row', 'muscle_group' => 'Back'])->assertSessionHasNoErrors();
        $this->get('/exercises/'.$e->id)->assertOk()->assertSee('Seated row');
        $this->delete('/exercises/'.$e->id)->assertRedirect('/exercises');
        $this->delete('/workout-types/'.$type->id)->assertRedirect('/workout-types');
        $this->assertDatabaseCount('exercises', 0);
        $this->assertDatabaseCount('workout_types', 0);
    }

    public function test_lookup_ownership_cannot_be_bypassed(): void
    {
        $owner = User::factory()->create();
        $w = $this->workout($owner);
        $e = $this->exercise($owner);
        $this->actingAs(User::factory()->create());
        foreach (['/exercises/'.$e->id, '/exercises/'.$e->id.'/edit', '/workout-types/'.$w->workout_type_id.'/edit'] as $url) {
            $this->get($url)->assertNotFound();
        }
        $this->put('/exercises/'.$e->id, ['name' => 'Stolen', 'muscle_group' => 'Chest'])->assertNotFound();
        $this->delete('/exercises/'.$e->id)->assertNotFound();
        $this->put('/workout-types/'.$w->workout_type_id, ['name' => 'Stolen'])->assertNotFound();
        $this->delete('/workout-types/'.$w->workout_type_id)->assertNotFound();
    }

    public function test_exercise_links_are_visible_unique_and_owner_checked(): void
    {
        $user = User::factory()->create();
        $w = $this->workout($user);
        $e = $this->exercise($user);
        $this->actingAs($user);
        for ($i = 0; $i < 2; $i++) {
            $this->post('/workouts/'.$w->id.'/exercises', ['exercise_id' => $e->id])->assertSessionHasNoErrors();
        }
        $this->assertDatabaseCount('exercise_workout', 1);
        $this->get('/workouts/'.$w->id)->assertOk()->assertSee('Bench press');
        $foreign = $this->exercise(User::factory()->create());
        $this->post('/workouts/'.$w->id.'/exercises', ['exercise_id' => $foreign->id])->assertSessionHasErrors('exercise_id');
        $this->post('/workouts/'.$w->id.'/exercises', ['exercise_id' => 999999])->assertSessionHasErrors('exercise_id');
    }

    public function test_sets_can_be_created_edited_and_deleted(): void
    {
        $user = User::factory()->create();
        $w = $this->workout($user);
        $e = $this->exercise($user);
        $w->exercises()->attach($e);
        $this->actingAs($user)->post('/workouts/'.$w->id.'/exercises/'.$e->id.'/sets', ['weight' => 40.5, 'reps' => 10])->assertSessionHasNoErrors();
        $set = $w->sets()->first();
        $this->assertDatabaseHas('workout_sets', ['id' => $set->id, 'weight' => 40.5, 'reps' => 10]);
        $this->get('/workouts/'.$w->id.'/sets/'.$set->id.'/edit')->assertOk();
        $this->put('/workouts/'.$w->id.'/sets/'.$set->id, ['weight' => 42.5, 'reps' => 8])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('workout_sets', ['id' => $set->id, 'weight' => 42.5, 'reps' => 8]);
        $this->delete('/workouts/'.$w->id.'/sets/'.$set->id)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('workout_sets', 0);
    }

    public function test_set_validation_rejects_bad_ranges_precision_and_unlinked_exercises(): void
    {
        $user = User::factory()->create();
        $w = $this->workout($user);
        $e = $this->exercise($user);
        $url = '/workouts/'.$w->id.'/exercises/'.$e->id.'/sets';
        $this->actingAs($user)->post($url, ['weight' => 40, 'reps' => 10])->assertNotFound();
        $w->exercises()->attach($e);
        foreach ([['weight' => -1, 'reps' => 0], ['weight' => 'bad', 'reps' => 1.5], ['weight' => 1001, 'reps' => 1001]] as $data) {
            $this->post($url, $data)->assertSessionHasErrors(['weight', 'reps']);
        }
        $this->post($url, ['weight' => 10.123, 'reps' => 10])->assertSessionHasErrors('weight');
        $this->post($url, ['weight' => 0, 'reps' => 10, 'exercise_id' => 999999])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('workout_sets', ['exercise_id' => $e->id, 'weight' => 0]);
        $this->assertDatabaseCount('workout_sets', 1);
    }

    public function test_nested_urls_cannot_target_another_workout_or_owner(): void
    {
        $owner = User::factory()->create();
        $w = $this->workout($owner);
        $e = $this->exercise($owner);
        $w->exercises()->attach($e);
        $set = $w->sets()->create(['exercise_id' => $e->id, 'weight' => 50, 'reps' => 10]);
        $second = $this->workout($owner);
        $this->actingAs($owner)->put('/workouts/'.$second->id.'/sets/'.$set->id, ['weight' => 1, 'reps' => 1])->assertNotFound();
        $this->actingAs(User::factory()->create());
        $this->post('/workouts/'.$w->id.'/exercises', ['exercise_id' => $e->id])->assertNotFound();
        $this->post('/workouts/'.$w->id.'/exercises/'.$e->id.'/sets', ['weight' => 1, 'reps' => 1])->assertNotFound();
        $this->get('/workouts/'.$w->id.'/sets/'.$set->id.'/edit')->assertNotFound();
        $this->put('/workouts/'.$w->id.'/sets/'.$set->id, ['weight' => 1, 'reps' => 1])->assertNotFound();
        $this->delete('/workouts/'.$w->id.'/sets/'.$set->id)->assertNotFound();
        $this->delete('/workouts/'.$w->id.'/exercises/'.$e->id)->assertNotFound();
        $this->assertDatabaseHas('workout_sets', ['id' => $set->id, 'weight' => 50]);
    }

    public function test_detaching_exercise_cleans_only_its_current_workout_sets(): void
    {
        $user = User::factory()->create();
        $one = $this->workout($user);
        $two = $this->workout($user);
        $e = $this->exercise($user);
        foreach ([$one, $two] as $w) {
            $w->exercises()->attach($e);
            $w->sets()->create(['exercise_id' => $e->id, 'weight' => 40, 'reps' => 10]);
        }
        $this->actingAs($user)->delete('/workouts/'.$one->id.'/exercises/'.$e->id)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('exercise_workout', ['workout_id' => $one->id, 'exercise_id' => $e->id]);
        $this->assertDatabaseMissing('workout_sets', ['workout_id' => $one->id]);
        $this->assertDatabaseHas('workout_sets', ['workout_id' => $two->id]);
        $this->assertDatabaseHas('exercises', ['id' => $e->id]);
    }

    public function test_deleting_workout_cascades_links_and_sets_but_preserves_library(): void
    {
        $user = User::factory()->create();
        $w = $this->workout($user);
        $e = $this->exercise($user);
        $w->exercises()->attach($e);
        $w->sets()->create(['exercise_id' => $e->id, 'weight' => 40, 'reps' => 10]);
        $this->actingAs($user)->delete('/workout-types/'.$w->workout_type_id)->assertSessionHasErrors('type');
        $this->delete('/exercises/'.$e->id)->assertSessionHasErrors('exercise');
        $this->delete('/workouts/'.$w->id)->assertRedirect('/workouts');
        $this->assertDatabaseCount('exercise_workout', 0);
        $this->assertDatabaseCount('workout_sets', 0);
        $this->assertDatabaseCount('exercises', 1);
    }

    public function test_search_filters_and_pagination_preserve_query_and_privacy(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 10; $i++) {
            $this->workout($user, ['name' => 'Target '.$i, 'status' => 'completed']);
        }
        $this->workout($user, ['name' => 'Unrelated', 'status' => 'completed']);
        $this->workout($user, ['name' => 'Target planned']);
        $this->workout(User::factory()->create(), ['name' => 'Target secret', 'status' => 'completed']);
        $url = '/workouts?search=Target&status=completed&from=2026-09-01&to=2026-09-30';
        $response = $this->actingAs($user)->get($url)->assertOk()->assertDontSee('Target secret')->assertDontSee('Target planned')->assertDontSee('Unrelated');
        $response->assertViewHas('workouts', fn ($rows) => $rows->total() === 10 && $rows->count() === 8);
        $response->assertSee('search=Target', false)->assertSee('status=completed', false)->assertSee('page=2', false);
        $this->get($url.'&page=2')->assertOk()->assertViewHas('workouts', fn ($rows) => $rows->count() === 2);
        $this->get('/workouts?to=2026-09-30')->assertOk();
        $this->get('/workouts?from=2026-09-30&to=2026-09-01')->assertSessionHasErrors('to');
    }

    public function test_progress_uses_only_completed_sessions_and_owned_exercises(): void
    {
        $user = User::factory()->create();
        $e = $this->exercise($user);
        foreach ([['completed', 50], ['planned', 100]] as [$status, $weight]) {
            $w = $this->workout($user, ['name' => $status.' example', 'status' => $status]);
            $w->exercises()->attach($e);
            $w->sets()->create(['exercise_id' => $e->id, 'weight' => $weight, 'reps' => 10]);
        }
        $this->actingAs($user)->get('/progress?exercise_id='.$e->id)->assertOk()->assertSee('50.00 kg')->assertSee('completed example')->assertDontSee('planned example')->assertViewHas('bestWeight', fn ($value) => (float) $value === 50.0);
        $this->actingAs(User::factory()->create())->get('/progress?exercise_id='.$e->id)->assertSessionHasErrors('exercise_id');
    }

    public function test_upload_download_replacement_removal_and_cleanup(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $w = $this->workout($user);
        $pdf = fn () => UploadedFile::fake()->createWithContent('plan.pdf', "%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\n%%EOF");
        $this->actingAs($user)->put('/workouts/'.$w->id, $this->payload($w, ['attachment' => $pdf()]))->assertSessionHasNoErrors();
        $old = $w->fresh()->attachment_path;
        Storage::disk('local')->assertExists($old);
        $this->get('/workouts/'.$w->id)->assertSee('Download workout plan');
        $this->get('/workouts/'.$w->id.'/attachment')->assertOk()->assertDownload('workout-plan.pdf');
        $this->put('/workouts/'.$w->id, $this->payload($w, ['attachment' => $pdf()]))->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($old);
        $new = $w->fresh()->attachment_path;
        $this->put('/workouts/'.$w->id, $this->payload($w, ['remove_attachment' => 1]))->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($new);
        $this->assertNull($w->fresh()->attachment_path);
        $this->get('/workouts/'.$w->id.'/attachment')->assertNotFound();
        $this->put('/workouts/'.$w->id, $this->payload($w, ['attachment' => $pdf()]))->assertSessionHasNoErrors();
        $last = $w->fresh()->attachment_path;
        $this->delete('/workouts/'.$w->id);
        Storage::disk('local')->assertMissing($last);
    }

    public function test_invalid_and_oversized_uploads_are_rejected(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $w = $this->workout($user);
        $this->actingAs($user)->put('/workouts/'.$w->id, $this->payload($w, ['attachment' => UploadedFile::fake()->createWithContent('bad.php', '<?php echo 1;')]))->assertSessionHasErrors('attachment');
        $this->put('/workouts/'.$w->id, $this->payload($w, ['attachment' => UploadedFile::fake()->create('large.pdf', 5121, 'application/pdf')]))->assertSessionHasErrors('attachment');
        $this->assertNull($w->fresh()->attachment_path);
    }

    public function test_navigation_search_filters_owned_types_and_progress_history(): void
    {
        $user = User::factory()->create();
        $user->workoutTypes()->create(['name' => 'Upper body']);
        $user->workoutTypes()->create(['name' => 'Lower body']);
        User::factory()->create()->workoutTypes()->create(['name' => 'Upper private']);
        $this->actingAs($user)->get('/workout-types?search=Upper')
            ->assertOk()->assertSee('Upper body')->assertDontSee('Lower body')->assertDontSee('Upper private');

        $exercise = $this->exercise($user);
        foreach (['Morning session', 'Evening session'] as $name) {
            $workout = $this->workout($user, ['name' => $name, 'status' => 'completed']);
            $workout->exercises()->attach($exercise);
            $workout->sets()->create(['exercise_id' => $exercise->id, 'reps' => 8, 'weight' => 40]);
        }
        $this->get('/progress?exercise_id='.$exercise->id.'&search=Morning')
            ->assertOk()->assertSee('Morning session')->assertDontSee('Evening session');
        $this->get('/progress?search='.str_repeat('x', 101))->assertSessionHasErrors('search');
        $this->get('/workout-types?search='.str_repeat('x', 101))->assertSessionHasErrors('search');
    }

    public function test_pages_render_username_and_escape_user_content(): void
    {
        $user = User::factory()->create();
        $w = $this->workout($user, ['name' => '<script>alert(1)</script>']);
        $e = $this->exercise($user);
        $this->actingAs($user);
        foreach (['/workouts', '/workouts/create', '/workouts/'.$w->id, '/workouts/'.$w->id.'/edit', '/exercises', '/exercises/create', '/exercises/'.$e->id, '/exercises/'.$e->id.'/edit', '/workout-types', '/workout-types/create', '/workout-types/'.$w->workout_type_id.'/edit', '/progress'] as $url) {
            $this->get($url)->assertOk()->assertSee($user->name);
        }
        $this->get('/workouts/'.$w->id)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->get('/workouts/99999')->assertNotFound()->assertSee($user->name);
    }
}
