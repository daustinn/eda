@extends('modules.edas.slug.+layout')

@section('title', 'Objetivos: ' . $year->name . ' - ' . $user->first_name . ' ' . $user->last_name)

@section('title_eda', 'Objetivos')


@php
    $userIsDev = $cuser->role === 'dev';
    $hasAddGoals = $cuser->hasPrivilege('edas:goals:sent') && !$eda->approved;
    $hasSentGoals = $cuser->hasPrivilege('edas:goals:sent') && !$eda->approved;
    $hasEditGoals = $cuser->has('edas:goals:edit') && !$eda->approved;
    $hasDeleteGoals = $cuser->hasPrivilege('edas:goals:delete') && !$eda->approved;

    $hasSupervisor = $user->supervisor_id === $cuser->id || $cuser->hasPrivilege('edas:show_all');
    $hasApproveGoals = $cuser->hasPrivilege('edas:goals:approve') && !$eda->approved && $eda->sent && $hasSupervisor;
@endphp

@section('layout.edas.slug')
    <div class="h-full flex overflow-hidden flex-col pt-0 overflow-x-auto">
        @if ($eda->sent)
            <input type="hidden" id="input_id" value="{{ $eda->id }}">
        @endif

        <div {{ $hasEditGoals ? 'data-edit' : '' }} data-state="close" id="goal-sheet"
            data-cusername=" {{ $cuser->last_name }}, {{ $cuser->first_name }}"
            class="data-[state=open]:translate-x-0 flex translate-x-full transition-transform fixed px-5 overflow-y-auto flex-col z-50 bg-white top-0 right-0 w-[600px] shadow-[0_0_20px_rgba(10,10,10,.1)] h-full max-md:w-full">
            <div class="border-b py-4 flex items-center">
                <h2 class="font-semibold flex-grow">Objetivo</h2>
                <button id="goal-sheet-close" class="bg-neutral-200 rounded-full aspect-square p-1">
                    @svg('heroicon-o-x-mark', [
                        'class' => 'w-5 h-5',
                    ])
                </button>
            </div>
            <div class="flex flex-col gap-2 px-1 py-3 w-full overflow-y-auto flex-grow">
                <label>
                    <div class="pb-2 text-sm font-semibold">Título</div>
                    @if ($hasEditGoals)
                        <input id="goal-title" type="text">
                    @else
                        <p id="goal-title" class="opacity-60 whitespace-pre-line"></p>
                    @endif
                </label>
                <label>
                    <div class="pb-2 text-sm font-semibold">Descripción</div>
                    @if ($hasEditGoals)
                        <textarea id="goal-description" rows="4"></textarea>
                    @else
                        <p id="goal-description" class="opacity-60 whitespace-pre-line"></p>
                    @endif
                </label>
                <label>
                    <div class="pb-2 text-sm font-semibold ">Indicadores</div>
                    @if ($hasEditGoals)
                        <textarea id="goal-indicators" rows="6"></textarea>
                    @else
                        <p id="goal-indicators" class="opacity-60 whitespace-pre-line"></p>
                    @endif
                </label>
                <label class="w-[140px] block">
                    <div class="pb-2 text-sm font-semibold">Porcentaje (1 - 100)</div>
                    @if ($hasEditGoals)
                        <input id="goal-percentage" type="number">
                    @else
                        <p id="goal-percentage" class="opacity-60 whitespace-pre-wrap"></p>
                    @endif
                </label>
                <label>
                    <div class="pb-2 text-sm font-semibold">Comentario</div>
                    @if ($hasEditGoals && $hasSupervisor)
                        <textarea id="goal-comments" style="border-color: #c27803; background-color:#fdf6b2; color: #8e4b10 " rows="6"></textarea>
                    @else
                        <p id="goal-comments" data-label class="text-yellow-500 text-sm whitespace-pre-line">
                        </p>
                    @endif
                </label>
                <p id="goal-info" class="text-sm mt-auto text-blue-500">

                </p>
            </div>
            <div class="py-4 border-t flex items-center gap-2">
                <button id="goal-sheet-submit"
                    class="bg-blue-700 shadow-md shadow-blue-500/30 font-semibold hover:bg-blue-600 min-w-max flex items-center rounded-full p-2 gap-1 text-white text-sm px-3">
                    Guardar
                </button>
                <button id="goal-sheet-remove"
                    class="bg-white shadow-md shadow-neutral-500/30 border font-semibold hover:shadow-lg min-w-max flex items-center rounded-full p-2 gap-1 text-black text-sm px-3">
                    Remover
                </button>
            </div>
        </div>

        @if ($hasApproveGoals || $hasSentGoals)
            <div class="flex items-center px-3 gap-2">
                <div class="flex-grow">
                </div>
                <div class="flex gap-2 items-center p-1 tracking-tight">
                    @if ($hasApproveGoals)
                        <button data-atitle="Aprobar objetivos" data-param="/api/goals/approve/{{ $eda->id }}"
                            data-adescription="¿Estás seguro de aprobar los objetivos?. Este paso habilitará el acceso a las evaluaciones del EDA."
                            class="bg-orange-600 shadow-sm shadow-orange-500/10 dinamic-alert hover:bg-orange-700 data-[hidden]:hidden text-white font-semibold justify-center min-w-max flex items-center rounded-full p-1 gap-1 text-sm px-3">
                            @svg('heroicon-s-check', [
                                'class' => 'w-5 h-5',
                            ])
                            Aprobar
                        </button>
                    @endif
                    @if ($hasSentGoals)
                        <button data-id-eda="{{ $eda->id }}" id="sent-goals-button"
                            class="bg-blue-700 shadow-sm shadow-blue-500/10 data-[hidden]:hidden font-semibold justify-center hover:bg-blue-600 min-w-max flex items-center rounded-full p-1 gap-1 text-white text-sm px-3">
                            {{ $eda->sent ? 'Reenviar' : 'Enviar' }} objetivos
                        </button>
                    @endif
                </div>
            </div>
        @endif

        <div id="init-goal-sheet"
            class=" group data-[hidden]:h-auto peer h-full grid text-sm place-content-center data-[hidden]:place-content-stretch text-center">
            <div class="max-w-sm group-data-[hidden]:hidden pb-2">
                <img src="/sheet-pen.png" class="mx-auto" alt="">
                <p class="pt-2 text-xs">
                    Una vez llegue al 100% de total de porcentaje, podrá enviar los objetivos.
                </p>
            </div>
            @if ($hasAddGoals)
                <div class="flex gap-2 justify-center max-md:justify-between max-md:pl-3 items-center w-full">
                    <button
                        class="bg-white open-goal-button max-md:group-data-[hidden]:mx-0 flex items-center gap-2 mx-auto shadow-md px-3 p-2 rounded-full text-black font-semibold text-sm">
                        @svg('heroicon-o-plus-circle', [
                            'class' => 'w-5 h-5 ',
                        ])
                        <span class="max-md:hidden">Agregar objetivo</span>
                    </button>
                </div>
            @endif
        </div>

        <template id="goal-template">
            <tr
                class="[&>td]:py-3 [&>td]:align-top goal group data-[state=open]:bg-blue-600 data-[state=open]:text-white cursor-pointer hover:border-transparent hover:[&>td]shadow-md relative group hover:bg-[#f8fbfd] [&>td]:px-2">
                <td class="text-center goal-index"></td>
                <td>
                    <p class="line-clamp-4 goal-title text-ellipsis overflow-hidden"></p>
                </td>
                <td>
                    <p class="line-clamp-4 goal-description text-ellipsis overflow-hidden"></p>
                </td>
                <td>
                    <p class="line-clamp-4 goal-indicators text-ellipsis overflow-hidden"></p>
                </td>
                <td>
                    <p class="text-center goal-percentage"></p>
                </td>
                <td>
                    <p
                        class="text-left goal-feedback line-clamp-2 text-ellipsis overflow-hidden text-yellow-600 group-data-[state=open]:text-yellow-200">
                    </p>
                </td>
                <td>
                    <p class="goal-time-line line-clamp-3 text-ellipsis overflow-hidden"></p>
                </td>
            </tr>
        </template>

        <div id="table-content" class="h-full data-[open]:block hidden flex-grow overflow-y-auto w-full">
            <table class="pt-3 text-sm w-full max-lg:grid-cols-1 px-1 py-1 gap-5">
                <thead class="border-b border-neutral-300">
                    <tr class="[&>th]:font-medium [&>th]:px-3 [&>th]:py-2">
                        <th>N°</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th class="text-center">Indicadores</th>
                        <th class="text-center">Porcentaje</th>
                        <th class="text-left">Feedback</th>
                        <th class="text-nowrap">Time line</th>
                    </tr>
                </thead>
                <tbody id="goals" class="divide-y divide-neutral-300">
                </tbody>
                <tbody>
                    <tr class="border-t border-neutral-300">
                        <td colspan="4" class="px-4 py-2">Total</td>
                        <td class="text-center">
                            <p id="total-percentage" class="font-semibold text-blue-600"></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="p-2 text-xs">
            @if ($eda->sent)
                <span class="text-neutral-400">Enviados
                    el {{ \Carbon\Carbon::parse($eda->sent)->isoFormat('LL') }} por
                    {{ $eda->createdBy->last_name }},
                    {{ $eda->createdBy->first_name }}
                </span>
            @endif
            @if ($eda->approved)
                <span class="text-neutral-400"> y Aprobado
                    el {{ \Carbon\Carbon::parse($eda->approved)->isoFormat('LL') }} por
                    {{ $eda->approvedBy->last_name }},
                    {{ $eda->approvedBy->first_name }}
                </span>
            @endif
        </p>
    </div>
@endsection
