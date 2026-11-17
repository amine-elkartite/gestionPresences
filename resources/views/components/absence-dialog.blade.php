@props(['employees', 'date'])
<dialog id="absence-dialog" class="app-dialog" aria-labelledby="absence-dialog-title" @if($errors->has('reason') || old('_form') === 'absence') data-reopen @endif>
    <form action="{{ route('absences.store') }}" method="POST" data-submit-state>
        @csrf
        <input type="hidden" name="_form" value="absence">
        <div class="dialog-head">
            <div><span class="dialog-icon"><x-icon name="calendar-plus" /></span><h2 id="absence-dialog-title">Ajouter une absence</h2><p>Enregistrer une absence pour un collaborateur.</p></div>
            <button type="button" class="icon-button" data-close-dialog aria-label="Fermer" title="Fermer"><x-icon name="x" /></button>
        </div>
        <div class="dialog-body">
            @if($errors->any() && old('_form') === 'absence')<div class="alert alert-danger" role="alert">Vérifiez les champs du formulaire.</div>@endif
            <label class="form-label" for="absence-employee">Employé <span class="required-mark">*</span></label>
            <select id="absence-employee" name="user_id" class="form-select" required autofocus>
                <option value="">Sélectionner un employé</option>
                @foreach($employees->where('status', 'active') as $employee)
                    <option value="{{ $employee->user_id }}" @selected((string) old('user_id') === (string) $employee->user_id)>{{ $employee->name }}</option>
                @endforeach
            </select>
            @error('user_id')<div class="field-error">{{ $message }}</div>@enderror
            <label class="form-label mt-4" for="absence-date">Date <span class="required-mark">*</span></label>
            <input id="absence-date" class="form-control" type="date" name="date" value="{{ old('date', $date) }}" required>
            @error('date')<div class="field-error">{{ $message }}</div>@enderror
            <label class="form-label mt-4" for="absence-reason">Motif <span class="required-mark">*</span></label>
            <input id="absence-reason" class="form-control" name="reason" list="absence-reasons" maxlength="255" value="{{ old('reason') }}" placeholder="Sélectionner ou saisir un motif" required>
            <datalist id="absence-reasons"><option value="Maladie"><option value="Personnel"><option value="Congé"><option value="Autre"></datalist>
            @error('reason')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="dialog-footer"><button class="btn btn-outline-secondary" type="button" data-close-dialog>Annuler</button><button class="btn btn-primary" type="submit"><x-icon name="check" /><span>Enregistrer l’absence</span></button></div>
    </form>
</dialog>
