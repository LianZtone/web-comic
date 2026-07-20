@php
    $captchaField ??= 'captcha_answer';
    $captchaLabel ??= 'CAPTCHA';
    $captchaScope ??= 'default';
@endphp

<label class="field-control">
    <div class="field-label flex items-center justify-between gap-3">
        <span class="field-label-text">{{ $captchaLabel }}</span>
        <span class="badge badge-outline" data-captcha-question="{{ $captchaScope }}">{{ $captchaQuestion }}</span>
    </div>
    <input
        type="text"
        name="{{ $captchaField }}"
        value="{{ old($captchaField) }}"
        class="input input-bordered field-shell w-full bg-base-100/80"
        placeholder="Jawab hasil hitungannya"
        inputmode="numeric"
        autocomplete="off"
        data-captcha-answer="{{ $captchaScope }}">
    @error($captchaField)
        <span class="field-error">{{ $message }}</span>
    @enderror
</label>
