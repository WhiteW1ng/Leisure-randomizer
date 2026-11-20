{{-- Галочки фильтров. Нужны: $options, $selected --}}
<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($options as $key => [$label, $items])
        <fieldset>
            <legend class="mb-2 text-sm font-semibold text-ink">{{ $label }}</legend>
            <div class="flex flex-col gap-1">
                @foreach ($items as $name)
                    <label class="flex items-center gap-2 text-sm text-ink">
                        <input type="checkbox" name="{{ $key }}[]" value="{{ $name }}"
                               class="rounded border-line bg-field text-accent shadow-sm"
                               @checked(in_array($name, $selected[$key] ?? [], true))>
                        <span>{{ $name }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endforeach
</div>
