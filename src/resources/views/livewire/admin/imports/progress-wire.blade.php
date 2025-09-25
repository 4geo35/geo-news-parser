<div>
    <x-tt::notifications.error :prefix="'progress-' . $import->id . '-'" />
    <x-tt::notifications.success :prefix="'progress-' . $import->id . '-'" />
    <ul class="space-y-indent-half">
        @if ($import->in_progress)
            <li class="flex justify-between space-x-2">
                <span class="font-semibold">Запущен:</span> <span>{{ $import->started_human }}</span>
            </li>
            @if (isset($batchProgress) && is_numeric($batchProgress))
                <li>
                    <div class="bg-secondary/25 w-full h-4 rounded-full overflow-hidden"
                         x-data="{
                            init() {
                                setInterval(() => { this.fireRefreshEvent() }, 5000);
                            },
                            fireRefreshEvent() { $dispatch('refresh-progress', { id: '{{ $import->id }}' }) }
                         }">
                        <div class="flex flex-col justify-center bg-primary h-full" style="width: {{ $batchProgress }}%"></div>
                    </div>
                </li>
            @endif
        @endif
        @if ($import->finished_at)
            <li class="flex justify-between space-x-2">
                <span class="font-semibold">Закончен:</span> <span>{{ $import->finished_human }}</span>
            </li>
        @endif
    </ul>
</div>
