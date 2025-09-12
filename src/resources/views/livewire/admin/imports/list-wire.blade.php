<div class="card">
    <div class="card-body">
        <div class="space-y-indent-half">
            <x-tt::notifications.error prefix="import-" />
            <x-tt::notifications.success prefix="import-" />
            @include("gnp::admin.imports.includes.form")
        </div>
    </div>
    @include("gnp::admin.imports.includes.table")
    @include("gnp::admin.imports.includes.table-modals")
</div>
