@php
    $class ??= null;
@endphp
<div @class(['form-check form-switch', $class])>
    <!-- Champ caché avec valeur "0" pour assurer l'envoi de la valeur par défaut même si la case à cocher n'est pas cochée. Utile lors de la soumission du formulaire pour garantir la présence d'une valeur définie pour le champ {{ $name }}. -->
    <input type="hidden" value="0" name="{{ $name }}">
    <input @checked(old($name, $value ?? false) ) type="checkbox" value="1" name="{{ $name }}"
        class="form-check-input @error($name) is-invalid @enderror" role="switch" id="{{ $name }}">
    <label class="form-check-label" for="">{{ $label }}</label>
    @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>
