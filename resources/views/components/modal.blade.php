@props([
    'id' => 'modalGeral',
    'labelId' => null,
    'bodyId' => null,
    'closeLabel' => 'Fechar',
])

@php
    $labelId = $labelId ?? ($id . 'Label');
    $bodyId = $bodyId ?? ($id . 'Body');
    $hasTitleSlot = isset($title) && ! $title->isEmpty();
    $hasBodySlot = ! $slot->isEmpty();
    $hasActionsSlot = isset($actions) && ! $actions->isEmpty();
@endphp

<div {{ $attributes->merge([
        'id' => $id,
        'class' => 'modal fade',
        'tabindex' => '-1',
        'aria-labelledby' => $labelId,
        'aria-hidden' => 'true',
    ]) }}>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $labelId }}">
                    {{ $hasTitleSlot ? $title : 'Título do Modal' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ $closeLabel }}"></button>
            </div>

            <div class="modal-body" id="{{ $bodyId }}">
                {{ $hasBodySlot ? $slot : 'Corpo do modal...' }}
            </div>

            <div class="modal-footer">
                @if ($hasActionsSlot)
                    {{ $actions }}
                @else
                    <button type="button" class="btn btn-primary" id="modalGeralConfirmar">Confirmar</button>
                    <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                @endif
            </div>
        </div>
    </div>
</div>
