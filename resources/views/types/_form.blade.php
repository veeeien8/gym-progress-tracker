<div>
<label for="name">Type name</label>
<input id="name" name="name" maxlength="80" value="{{ old('name', $type->name) }}" placeholder="e.g. Push, Pull, Legs" required>
</div>
<div>
<label for="description">Description (optional)</label>
<textarea id="description" name="description" rows="3" maxlength="500">{{ old('description', $type->description) }}</textarea>
</div>
