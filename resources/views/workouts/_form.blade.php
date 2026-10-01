<div>
<label for="name">Workout name</label>
<input id="name" name="name" value="{{ old('name', $workout->name) }}" maxlength="100" placeholder="e.g. Monday push" required>
</div>
<div class="grid gap-4 sm:grid-cols-2">
<div>
<label for="workout_date">Date</label>
<input type="date" id="workout_date" name="workout_date" value="{{ old('workout_date', $workout->workout_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" min="2000-01-01" max="2100-12-31" required>
</div>
<div>
<label for="status">Status</label>
<select id="status" name="status">
<option value="planned" @selected(old('status', $workout->status) !== 'completed')>Planned</option>
<option value="completed" @selected(old('status', $workout->status) === 'completed')>Completed</option>
</select>
<p class="hint">Only completed sessions appear in progress history.</p>
</div>
</div>
<div>
<label for="workout_type_id">Workout type</label>
<select id="workout_type_id" name="workout_type_id" required>
<option value="">Choose a type</option>
@foreach($types as $type)<option value="{{ $type->id }}" @selected(old('workout_type_id', $workout->workout_type_id) == $type->id)>{{ $type->name }}</option>
@endforeach
</select>
<p class="hint">Need another type? <a class="link" href="{{ route('workout-types.create') }}">Create one first</a>.</p>
</div>
<div>
<label for="notes">Session notes (optional)</label>
<textarea id="notes" name="notes" rows="3" maxlength="3000">{{ old('notes', $workout->notes) }}</textarea>
</div>
<div>
<label for="attachment">Workout plan attachment (optional)</label>
<input type="file" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png">
<p class="hint">PDF, JPG or PNG, up to 5 MB. A new file replaces the old one.</p>
</div>
@if($workout->attachment_path)<label class="flex items-center gap-2">
<input type="checkbox" name="remove_attachment" value="1" @checked(old('remove_attachment'))> Remove current attachment</label>
@endif

<p class="hint">Save the workout, then add exercises and record individual sets.</p>
