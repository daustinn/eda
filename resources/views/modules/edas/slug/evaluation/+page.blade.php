@extends('modules.edas.slug.+layout')

@section('title', 'Eda: ' . $current_year->name . ' - ' . $user->first_name . ' ' . $user->last_name)

@section('title_eda', 'Evaluación N°' . $evaluation->number)

@section('layout.edas.slug')

    @php

        // if current user is supervisor
        $isSupervisor = $current_user->id === $user->supervisor_id;

        // if has self qualification
        $hasSelfQualify =
            $current_user->hasPrivilege('edas:evaluations:self-qualify') &&
            !$evaluation->closed &&
            !$evaluation->self_qualification;

        // if has average evaluation
        $hasQualify =
            $current_user->hasPrivilege('edas:evaluations:qualify') &&
            !$evaluation->closed &&
            $isSupervisor &&
            $evaluation->self_qualification &&
            !$evaluation->qualification;

        // if has close evaluation
        $hasCloseEvaluation =
            $current_user->hasPrivilege('edas:evaluations:close') &&
            !$evaluation->closed &&
            $evaluation->qualification &&
            $evaluation->self_qualification &&
            $isSupervisor;
    @endphp
    <div class="h-full flex overflow-hidden flex-col pt-0 overflow-x-auto">

        @if ($hasSelfQualify || $hasQualify || $hasCloseEvaluation)
            <div class="flex items-center px-3 gap-2">
                <div class="flex-grow">
                </div>
                <div class="flex gap-2 items-center p-1 tracking-tight">
                    @if ($hasSelfQualify)
                        <button id="evaluation-self-qualify-button" data-id="{{ $evaluation->id }}"
                            class="bg-sky-600 shadow-sm shadow-sky-500/10 hover:bg-sky-700 data-[hidden]:hidden text-white font-semibold justify-center min-w-max flex items-center rounded-full p-1 gap-1 text-sm px-3">
                            @svg('heroicon-s-check', [
                                'class' => 'w-5 h-5',
                            ])
                            Autocalificar
                        </button>
                    @endif
                    @if ($hasQualify)
                        <button data-id="{{ $evaluation->id }}" id="evaluation-qualify-button"
                            class="bg-violet-700 shadow-sm shadow-violet-500/10 data-[hidden]:hidden font-semibold justify-center hover:bg-violet-600 min-w-max flex items-center rounded-full p-1 gap-1 text-white text-sm px-3">
                            @svg('heroicon-o-clipboard-document-check', [
                                'class' => 'w-5 h-5',
                            ])
                            Calificiar
                        </button>
                    @endif

                    @if ($hasCloseEvaluation)
                        <button data-id="{{ $evaluation->id }}" id="evaluation-close-button"
                            class="bg-red-600 shadow-sm shadow-red-500/10 data-[hidden]:hidden font-semibold justify-center hover:bg-red-700 min-w-max flex items-center rounded-full p-1 gap-1 text-white text-sm px-3">
                            @svg('heroicon-o-x-mark', [
                                'class' => 'w-5 h-5',
                            ])
                            Cerrar
                        </button>
                    @endif
                </div>
            </div>
        @endif

        @if ($evaluation->closed)
            <span class="text-neutral-400 block text-center w-full p-3">Esta evaluación ha sido cerrada
                el {{ \Carbon\Carbon::parse($evaluation->closed_at)->isoFormat('LL') }} por
                {{ $evaluation->closedBy->first_name }}
                {{ $evaluation->closedBy->last_name }}.
            </span>
        @endif

        <div class="h-full flex-grow overflow-y-auto w-full">
            <table class="pt-3 text-sm w-full max-lg:grid-cols-1 px-1 py-1 gap-5">
                <thead class="border-b border-neutral-300">
                    <tr class="[&>th]:font-medium [&>th]:px-3 [&>th]:py-2">
                        <th>N°</th>
                        <th>Título</th>
                        <th class="min-w-[300px]">Descripción</th>
                        <th class="min-w-[300px]">Indicadores</th>
                        <th class="text-center">Porcentaje</th>
                        <th>Autocalificación</th>
                        <th>Calificación</th>
                    </tr>
                </thead>
                <tbody id="evaluations" class="divide-y divide-neutral-300">
                    @foreach ($goalevaluations as $index => $eva)
                        <tr data-id="{{ $eva->id }}"
                            class="[&>td]:py-3 [&>td]:align-top hover:border-transparent hover:[&>td]shadow-md relative group [&>td]:px-2">
                            <td class="text-center goal-index whitespace-pre-line">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </td>
                            <td>
                                <p class="goal-title whitespace-pre-line">
                                    {{ $eva->goal->title }}
                                </p>
                            </td>
                            <td>
                                <p class="goal-description whitespace-pre-line">
                                    {{ $eva->goal->description }}
                                </p>
                            </td>
                            <td>
                                <p class="goal-indicators whitespace-pre-line">
                                    {{ $eva->goal->indicators }}
                                </p>
                            </td>
                            <td>
                                <p data-percentage="{{ $eva->goal->percentage }}"
                                    class="text-center goal-percentage percentage whitespace-pre-line">
                                    {{ $eva->goal->percentage }}%
                                </p>
                            </td>
                            <td>
                                <div class="flex justify-center whitespace-pre-line">
                                    @if ($hasSelfQualify)
                                        <select style="width: 60px" class="self-qualification">
                                            <option value="" selected> - </option>
                                            @foreach ([1, 2, 3, 4, 5] as $i)
                                                <option value="{{ $i }}"
                                                    {{ $i == $eva->self_qualification ? 'selected' : '' }}>
                                                    {{ $i }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{ $eva->self_qualification ?? '-' }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="flex justify-center whitespace-pre-line">
                                    @if ($hasQualify)
                                        <select style="width: 60px" class="qualification">
                                            <option value="" selected>-</option>
                                            @foreach ([1, 2, 3, 4, 5] as $i)
                                                <option value="{{ $i }}"
                                                    {{ $i == $eva->qualification ? 'selected' : '' }}>
                                                    {{ $i }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{ $eva->qualification ?? '-' }}
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tbody>
                    <tr class="border-t border-neutral-300">
                        <td colspan="5" class="px-4 py-2">Totales</td>
                        <td class="text-center">
                            <p id="total-self-qualification" class="font-semibold text-blue-600">
                                {{ $evaluation->self_qualification ?? '-' }}
                            </p>
                        </td>
                        <td class="text-center">
                            <p id="total-qualification" class="font-semibold text-blue-600">
                                {{ $evaluation->qualification ?? '-' }}
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="p-2 text-xs">
            @if ($evaluation->selfRatedBy)
                <span class="text-neutral-400">Autocaficado el
                    {{ \Carbon\Carbon::parse($evaluation->self_rated_at)->isoFormat('LL') }} por
                    {{ $evaluation->selfRatedBy->last_name }}, {{ $evaluation->selfRatedBy->first_name }}
                </span>
            @endif
            @if ($evaluation->qualifiedBy)
                <span class="text-neutral-400">y Aprobado
                    el {{ \Carbon\Carbon::parse($evaluation->qualified_at)->isoFormat('LL') }} por
                    {{ $evaluation->qualifiedBy->first_name }}
                    {{ $evaluation->qualifiedBy->last_name }}.
                </span>
            @endif
        </p>
    </div>
@endsection
