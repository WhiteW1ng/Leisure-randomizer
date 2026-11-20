@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-line bg-field text-ink focus:border-accent focus:ring-accent rounded-xl shadow-sm']) }}>
