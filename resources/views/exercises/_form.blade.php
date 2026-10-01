<div>
<label for="name">Exercise name</label>
<input id="name" name="name" maxlength="100" value="{{ old('name', $exercise->name) }}" placeholder="e.g. Bench press" required>
</div>
<div>
<label for="muscle_group">Muscle group</label>
<select id="muscle_group" name="muscle_group" required>
@foreach($groups as $group)<option value="{{ $group }}" @selected(old('muscle_group', $exercise->muscle_group) === $group)>{{ $group }}</option>
@endforeach
</select>
</div>
<div>
<label for="instructions">Personal notes (optional)</label>
<textarea id="instructions" name="instructions" rows="4" maxlength="2000">{{ old('instructions', $exercise->instructions) }}</textarea>
<p class="hint">Record your equipment setup or a reminder for next time.</p>
</div>
