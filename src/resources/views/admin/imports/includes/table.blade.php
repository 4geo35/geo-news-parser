<x-tt::table>
    <x-slot name="head">
        <tr>
            <x-tt::table.heading class="text-left">ID</x-tt::table.heading>
            <x-tt::table.heading class="text-left"></x-tt::table.heading>
            <x-tt::table.heading class="text-left">Добавлен</x-tt::table.heading>
        </tr>
    </x-slot>
    <x-slot name="body">
        @foreach($imports as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td></td>
                <td>{{ $item->created_human }}</td>
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
