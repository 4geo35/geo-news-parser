<x-tt::modal.confirm wire:model="displayDelete">
    <x-slot name="title">Удалить импорт</x-slot>
    <x-slot name="text">Будет невозможно восстановить импорт, а так же он будет остановлен, если работает!</x-slot>
</x-tt::modal.confirm>

<x-tt::modal.dialog wire:model="displaySettingsList" max-width="7xl">
    <x-slot name="title">Настройки поиска данных</x-slot>
    <x-slot name="content">
        <div class="flex flex-col gap-y-indent-half">
            @foreach($settingsList as $key => $value)
                <div class="row">
                    <div class="col w-1/2 font-semibold">
                        @switch($key)
                            @case("titleUrl")
                                Ссылка на страницу
                                @break
                            @case("shortText")
                                Short новости
                                @break
                            @case("imageUrl")
                                Изображение новости
                                @break
                            @case("titleText")
                                Заголовок новости
                                @break
                            @case("fullDescription")
                                Полное описание новости
                                @break
                            @case("createdDate")
                                Дата создания новости
                                @break
                            @case("insideImageUrl")
                                Галерея
                                @break
                            @case("minimalImageWidth")
                                Минимальная ширина изображения в галерее
                                @break
                            @case("minimalImageSize")
                                Минимальный размер изображения в галерее, байт
                                @break
                            @case("metaTitle")
                                Meta title
                                @break
                            @case("metaDescription")
                                Meta description
                                @break
                            @case("metaKeywords")
                                Meta keywords
                                @break
                        @endswitch
                    </div>
                    <div class="col w-1/2">{{ $value }}</div>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-outline-dark mt-indent"
                wire:loading.attr="disabled"
                wire:click="copySettings">
            Копировать настройки
        </button>
    </x-slot>
</x-tt::modal.dialog>

<x-tt::modal.dialog wire:model="displaySettings" max-width="screen">
    <x-slot name="title">Настройки поиска данных</x-slot>
    <x-slot name="content">
        <div class="space-y-indent-half">
            <div>
                <label for="importTitleUrl" class="inline-block mb-2">
                    Ссылка на страницу
                </label>
                <input type="text" id="importTitleUrl"
                       class="form-control {{ $errors->has("titleUrl") ? "border-danger" : "" }}"
                       wire:loading.attr="disabled"
                       wire:model="titleUrl">
                <x-tt::form.error name="titleUrl"/>
            </div>

            <div>
                <label for="importShortText" class="inline-block mb-2">
                    Short новости
                </label>
                <input type="text" id="importShortText"
                       class="form-control {{ $errors->has("shortText") ? "border-danger" : "" }}"
                       wire:loading.attr="disabled"
                       wire:model="shortText">
                <x-tt::form.error name="shortText"/>
            </div>

            <div>
                <label for="importImageUrl" class="inline-block mb-2">
                    Изображение новости
                </label>
                <input type="text" id="importImageUrl"
                       class="form-control {{ $errors->has("imageUrl") ? "border-danger" : "" }}"
                       wire:loading.attr="disabled"
                       wire:model="imageUrl">
                <x-tt::form.error name="imageUrl"/>
            </div>

            <div>
                <label for="importTitleText" class="inline-block mb-2">
                    Заголовок новости
                </label>
                <input type="text" id="importTitleText"
                       class="form-control {{ $errors->has("titleText") ? "border-danger" : "" }}"
                       wire:loading.attr="disabled"
                       wire:model="titleText">
                <x-tt::form.error name="titleText"/>
            </div>

            <div>
                <label for="importFullDescription" class="inline-block mb-2">
                    Полное описание новости
                </label>
                <input type="text" id="importFullDescription"
                       class="form-control {{ $errors->has("fullDescription") ? "border-danger" : "" }}"
                       wire:loading.attr="disabled"
                       wire:model="fullDescription">
                <x-tt::form.error name="fullDescription"/>
            </div>

            <div>
                <label for="importCreatedDate" class="inline-block mb-2">
                    Дата создания новости
                </label>
                <input type="text" id="importCreatedDate"
                       class="form-control {{ $errors->has("createdDate") ? "border-danger" : "" }}"
                       wire:loading.attr="disabled"
                       wire:model="createdDate">
                <x-tt::form.error name="createdDate"/>
            </div>

            <div>
                <label for="importGalleryImageUrls" class="inline-block mb-2">
                    Галерея
                </label>
                <input type="text" id="importGalleryImageUrls"
                       class="form-control {{ $errors->has("galleryImageUrls") ? "border-danger" : "" }}"
                       wire:loading.attr="disabled"
                       wire:model="galleryImageUrls">
                <x-tt::form.error name="galleryImageUrls"/>
            </div>

            <div>
                <label for="importMinimalImageWidth" class="inline-block mb-2">
                    Минимальная ширина изображения в галерее
                </label>
                <input type="text" id="importMinimalImageWidth"
                       class="form-control {{ $errors->has("minimalImageWidth") ? "border-danger" : "" }}"
                       wire:loading.attr="disabled"
                       wire:model="minimalImageWidth">
                <x-tt::form.error name="minimalImageWidth"/>
            </div>

            <div>
                <label for="importMinimalImageSize" class="inline-block mb-2">
                    Минимальный размер изображения в галерее, байт
                </label>
                <input type="text" id="importMinimalImageSize"
                       class="form-control {{ $errors->has("minimalImageSize") ? "border-danger" : "" }}"
                       wire:loading.attr="disabled"
                       wire:model="minimalImageSize">
                <x-tt::form.error name="minimalImageSize"/>
            </div>

            <div>
                <label for="importMetaTitle" class="inline-block mb-2">
                    Meta title
                </label>
                <input type="text" id="importMetaTitle"
                       class="form-control {{ $errors->has("metaTitle") ? "border-danger" : "" }}"
                       wire:loading.attr="disabled"
                       wire:model="metaTitle">
                <x-tt::form.error name="metaTitle"/>
            </div>

            <div>
                <label for="importMetaDescription" class="inline-block mb-2">
                    Meta description
                </label>
                <input type="text" id="importMetaDescription"
                       class="form-control {{ $errors->has("metaDescription") ? "border-danger" : "" }}"
                       wire:loading.attr="disabled"
                       wire:model="metaDescription">
                <x-tt::form.error name="metaDescription"/>
            </div>

            <div>
                <label for="importMetaKeywords" class="inline-block mb-2">
                    Meta keywords
                </label>
                <input type="text" id="importMetaKeywords"
                       class="form-control {{ $errors->has("metaKeywords") ? "border-danger" : "" }}"
                       wire:loading.attr="disabled"
                       wire:model="metaKeywords">
                <x-tt::form.error name="metaKeywords"/>
            </div>

            <div>
                <button type="button" class="btn btn-outline-dark" wire:click="closeSettings">
                    Закрыть
                </button>
            </div>
        </div>
    </x-slot>
</x-tt::modal.dialog>
