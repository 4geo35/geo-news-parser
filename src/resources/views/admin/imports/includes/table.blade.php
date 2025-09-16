<x-tt::table>
    <x-slot name="head">
        <tr>
            <x-tt::table.heading class="text-left">Ссылки</x-tt::table.heading>
            <x-tt::table.heading class="text-left">Прогресс</x-tt::table.heading>
            <x-tt::table.heading class="text-left">Добавлен</x-tt::table.heading>
            <x-tt::table.heading class="">Действия</x-tt::table.heading>
        </tr>
    </x-slot>
    <x-slot name="body">
        @foreach($imports as $item)
            <tr>
                <td>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ $item->url }}"
                               class="text-primary hover:text-primary-hover" target="_blank">
                                {{ $item->cleared_url }}
                            </a>
                            <div class="text-sm text-secondary">{{ $item->ascii_url }}</div>
                        </li>
                        <li>
                            <a href="{{ $item->first_page_url }}"
                               class="text-primary hover:text-primary-hover" target="_blank">
                                {{ $item->cleared_first_page_url }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ $item->last_page_url }}"
                               class="text-primary hover:text-primary-hover" target="_blank">
                                {{ $item->cleared_last_page_url }}
                            </a>
                        </li>
                    </ul>
                </td>
                <td>
                    <livewire:gnp-import-progress :import="$item" wire:key="$import->id" />
                </td>
                <td>{{ $item->created_human }}</td>
                <td>
                    <div class="flex justify-center space-x-1">
                        @can("update", $item)
                            @if ($item->in_progress)
                                <button type="button" class="btn btn-sm btn-danger px-btn-x-ico"
                                        wire:loading.attr="disabled"
                                        wire:click="stop('{{ $item->id }}')">
                                    <x-gnp::ico.stop-circle />
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-success px-btn-x-ico"
                                        wire:loading.attr="disabled"
                                        wire:click="run('{{ $item->id }}')">
                                    <x-gnp::ico.play-circle />
                                </button>
                            @endcan
                        @endcan
                        @can("delete", $item)
                            <button type="button" class="btn btn-sm btn-danger px-btn-x-ico"
                                    wire:loading.attr="disabled"
                                    wire:click="showDelete('{{ $item->id }}')">
                                <x-tt::ico.trash />
                            </button>
                        @endcan
                        <button type="button" class="btn btn-sm btn-dark px-btn-x-ico"
                                wire:loading.attr="disabled"
                                wire:click="showSettingsList('{{ $item->id }}')">
                            <x-gnp::ico.settings />
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-slot>
    <x-slot name="caption">
        <div class="flex justify-between">
            <div>{{ __("Total") }}: {{ $imports->total() }}</div>
            {{ $imports->links("tt::pagination.live") }}
        </div>
    </x-slot>
</x-tt::table>
