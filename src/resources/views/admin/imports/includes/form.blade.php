<form wire:submit.prevent="store" id="importDataForm" class="flex flex-col gap-y-indent-half">
    <div>
        <label for="importFullPage" class="inline-block mb-2">
            Ссылка на первую страницу новостей
        </label>
        <input type="text" id="importFullPage"
               class="form-control"
               wire:loading.attr="disabled"
               wire:model.live.debounce.300ms="fullPage">
        <div class="text-info">Вставь ссылку на страницу с новостями и форма заполнится сама (:</div>
    </div>

    <div class="row">
        <div class="col w-1/3">
            <label for="importUrl" class="inline-block mb-2">
                Ссылка на сайт<span class="text-danger">*</span>
            </label>
            <input type="url" id="importUrl"
                   class="form-control {{ $errors->has("url") ? "border-danger" : "" }}"
                   required
                   wire:loading.attr="disabled"
                   wire:model="url">
            <x-tt::form.error name="url"/>
        </div>
        <div class="col w-1/3">
            <label for="importPage" class="inline-block mb-2">
                Страница новостей
            </label>
            <input type="text" id="importPage"
                   class="form-control {{ $errors->has("page") ? "border-danger" : "" }}"
                   wire:loading.attr="disabled"
                   wire:model="page">
            <x-tt::form.error name="page"/>
        </div>
        <div>
            <label for="importPaginator" class="inline-block mb-2">
                Адрес пагинатора
            </label>
            <input type="text" id="importPaginator"
                   class="form-control {{ $errors->has("paginator") ? "border-danger" : "" }}"
                   wire:loading.attr="disabled"
                   wire:model="paginator">
            <x-tt::form.error name="paginator"/>
        </div>
    </div>
    <div class="row">
        <div class="col w-1/2">
            <label for="importFirstPage" class="inline-block mb-2">
                Первая страница<span class="text-danger">*</span>
            </label>
            <input type="number" id="importFirstPage" min="1"
                   class="form-control {{ $errors->has("firstPage") ? "border-danger" : "" }}"
                   required
                   wire:loading.attr="disabled"
                   wire:model="firstPage">
            <x-tt::form.error name="firstPage"/>
        </div>
        <div class="col w-1/2">
            <label for="importLastPage" class="inline-block mb-2">
                Последняя страница<span class="text-danger">*</span>
            </label>
            <input type="number" id="importLastPage"
                   class="form-control {{ $errors->has("lastPage") ? "border-danger" : "" }}"
                   required
                   wire:loading.attr="disabled"
                   wire:model="lastPage">
            <x-tt::form.error name="lastPage"/>
        </div>
    </div>
    <div class="form-check">
        <input type="checkbox" wire:model="clearAll" id="importClearAll"
               class="form-check-input {{ $errors->has('clearAll') ? 'border-danger' : '' }}"/>
        <label for="importClearAll" class="form-check-label">
            Очистить все статьи
        </label>
    </div>
    <div>
        <button type="submit" form="importDataForm" class="btn btn-primary">
            Запустить
        </button>
    </div>
</form>
