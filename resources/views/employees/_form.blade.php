@csrf

<div class="row g-4">
    <div class="col-md-6">
        <label class="form-label" for="field-employees-1">Nom</label>
        <input id="field-employees-1" class="form-control control-lg" name="name"
               value="{{ old('name', $employee->name ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="field-employees-2">Code PIN</label>
        <input id="field-employees-2" class="form-control control-lg" name="pin_code"
               value="{{ old('pin_code', $employee->user->pin_code ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="field-employees-3">Email</label>
        <input id="field-employees-3" class="form-control control-lg" type="email" name="email"
               value="{{ old('email', $employee->email ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label" for="field-employees-4">Téléphone</label>
        <input id="field-employees-4" class="form-control control-lg" name="phone"
               value="{{ old('phone', $employee->phone ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label" for="field-employees-5">Poste</label>
        <input id="field-employees-5" class="form-control control-lg" name="position"
               value="{{ old('position', $employee->position ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label" for="field-employees-6">Département</label>
        <input id="field-employees-6" class="form-control control-lg" name="department"
               value="{{ old('department', $employee->department ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label" for="field-employees-7">Statut</label>
        <select id="field-employees-7" class="form-select control-lg" name="status" required>
            @foreach (['active' => 'Actif', 'inactive' => 'Inactif'] as $value => $label)
                <option value="{{ $value }}"
                    @selected(old('status', $employee->status ?? 'active') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="field-employees-8">Date d’embauche</label>
        <input id="field-employees-8" class="form-control control-lg" type="date" name="hire_date"
               value="{{ old('hire_date', isset($employee) && $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '') }}">
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-4">
    <button class="btn btn-primary px-4">{{ $button }}</button>
    <a class="btn btn-outline-secondary px-4" href="{{ route('employees.index') }}">Annuler</a>
</div>
