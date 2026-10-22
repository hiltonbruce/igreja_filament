@if ($suggestions->isNotEmpty())
    <ul class="mt-1 overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
        @foreach ($suggestions as $suggestion)
            <li wire:key="parent-{{ $idField }}-{{ $suggestion->id }}">
                <button
                    type="button"
                    class="w-full px-3 py-2 text-start text-sm text-gray-950 hover:bg-gray-100 dark:text-white dark:hover:bg-white/5"
                    x-on:mousedown.prevent
                    wire:click="linkParentMember('{{ $nameField }}', '{{ $idField }}', {{ $suggestion->id }})"
                >
                    {{ $suggestion->name }}
                </button>
            </li>
        @endforeach
    </ul>
@endif
