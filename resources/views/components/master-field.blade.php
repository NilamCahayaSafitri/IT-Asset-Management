@props([
    'name',
    'label',
    'type' => 'text',
    'required' => false,
    'placeholder' => '',
    'options' => [],
    'maxlength' => null,
    'rows' => 3,
    'hint' => null,
    'step' => null,
    'min' => null,
    'max' => null,
])

<div class="km-field">
    <label class="km-label" for="f-{{ $name }}">{{ $label }}@if ($required) <span style="color:#dc3545">*</span>@endif</label>

    @if ($type === 'select')
        <select id="f-{{ $name }}" name="{{ $name }}" class="km-input @error($name) invalid @enderror" @required($required)>
            <option value="">Pilih...</option>
            @foreach ($options as $value => $text)
                <option value="{{ $value }}" @selected((string) old($name) === (string) $value)>{{ $text }}</option>
            @endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea id="f-{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
                  @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                  class="km-input @error($name) invalid @enderror" @required($required)>{{ old($name) }}</textarea>
    @else
        <input id="f-{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name) }}" placeholder="{{ $placeholder }}"
               @if ($maxlength) maxlength="{{ $maxlength }}" @endif
               @if ($step) step="{{ $step }}" @endif
               @if ($min !== null) min="{{ $min }}" @endif
               @if ($max !== null) max="{{ $max }}" @endif
               class="km-input @error($name) invalid @enderror" @required($required)>
    @endif

    @if ($hint)<div class="km-muted" style="font-size:11.5px;margin-top:5px">{{ $hint }}</div>@endif
    @error($name)<div class="km-err">{{ $message }}</div>@enderror
</div>
