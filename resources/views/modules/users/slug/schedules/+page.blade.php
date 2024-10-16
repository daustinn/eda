@extends('modules.users.slug.+layout')

@section('title', 'Horario: ' . $user->first_name . ', ' . $user->last_name)

@php
    $days = [
        [
            'name' => 'Lu',
            'value' => 'monday',
            'key' => 1,
            'short' => 'L',
        ],
        [
            'name' => 'Ma',
            'value' => 'tuesday',
            'key' => 2,
            'short' => 'M',
        ],
        [
            'name' => 'Mi',
            'value' => 'wednesday',
            'key' => 3,
            'short' => 'M',
        ],
        [
            'name' => 'Ju',
            'value' => 'thursday',
            'key' => 4,
            'short' => 'J',
        ],
        [
            'name' => 'Vi',
            'value' => 'friday',
            'key' => 5,
            'short' => 'V',
        ],
        [
            'name' => 'Sá',
            'value' => 'saturday',
            'key' => 6,
            'short' => 'S',
        ],
        [
            'name' => 'Do',
            'value' => 'sunday',
            'key' => 7,
            'short' => 'D',
        ],
    ];
@endphp

@section('layout.users.slug')
    <div class="flex max-w-2xl w-full mx-auto items-center gap-3 p-2">
        <div class="border-t pt-2 w-full border-neutral-200">
            <p class="pb-3 text-lg">
                Horarios de {{ $user->names() }}
            </p>
            @if (($cuser->has('schedules:create') && $cuser->id !== $user->id && !$user->isDev()) || $cuser->isDev())
                <button type="button" data-modal-target="dialog" data-modal-toggle="dialog" class="primary mb-2">
                    @svg('fluentui-calendar-person-20-o', 'w-5 h-5')
                    <span>Agregar horario único</span>
                </button>
                <div id="dialog" tabindex="-1" aria-hidden="true" class="dialog hidden">
                    <div class="content lg:max-w-lg max-w-full">
                        <header>
                            Agregar horario único
                        </header>
                        <form action="/api/schedules/group/{{ $user->id }}/add" id="form" method="POST"
                            class="p-3 dinamic-form gap-4 overflow-y-auto flex flex-col">
                            @include('modules.schedules.slug.form', [
                                'user' => $user,
                            ])
                        </form>
                        <footer>
                            <button data-modal-hide="dialog" type="button">Cancelar</button>
                            <button form="form" type="submit">
                                Guardar</button>
                        </footer>
                    </div>
                </div>
            @endif
            <div class="grid gap-2 w-full">
                @foreach ($user->summarySchedules() as $schedule)
                    @php
                        $from = date('h:i A', strtotime($schedule->from));
                        $to = date('h:i A', strtotime($schedule->to));
                    @endphp
                    <div class="flex w-full shadow-md bg-white border p-3 rounded-lg text-sm gap-2 items-center">
                        <div class="flex-grow">
                            <div class="border-b w-full flex items-center gap-2 pb-2">
                                @svg($schedule->user_id ? 'fluentui-calendar-person-20-o' : 'fluentui-calendar-info-20-o', 'w-6 h-6 text-blue-700')
                                <p class="font-semibold">
                                    {{ $schedule->title }}
                                    <span class="opacity-50">
                                        {{ $schedule->user_id ? ' (Personal)' : '' }}
                                    </span>
                                </p>
                            </div>
                            <div class="flex pt-2 items-center gap-2">
                                @svg('fluentui-clock-16-o', 'w-6 h-6 opacity-60')
                                <div class="label flex-grow">
                                    <p class="font-semibold">
                                        {{ $from }} - {{ $to }}
                                    </p>
                                    <div class="flex ">
                                        @foreach ($days as $key => $day)
                                            @if (in_array($day['key'], $schedule->days))
                                                <span class="text-xs text-stone-500">
                                                    {{ $day['name'] }}
                                                    {{ $key < count($schedule->days) - 1 ? ',' : '' }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-2">
                            <button class="rounded-full p-2 hover:bg-neutral-200 transition-colors"
                                data-dropdown-toggle="dropdown-{{ $schedule->id }}">
                                @svg('fluentui-more-vertical-16-o', 'w-5 h-5')
                            </button>
                            <div id="dropdown-{{ $schedule->id }}" class="dropdown-content hidden">
                                @if (($cuser->has('schedules:edit') && $schedule->user_id === $cuser->id) || $cuser->isDev())
                                    <button data-modal-target="dialog-{{ $schedule->id }}"
                                        data-modal-toggle="dialog-{{ $schedule->id }}"
                                        class="p-2 hover:bg-neutral-100 text-left w-full block rounded-md hover:bg-gray-10">
                                        Editar
                                    </button>
                                @endif
                                @if (($cuser->has('schedules:delete') && $schedule->user_id === $cuser->id) || $cuser->isDev())
                                    <button data-alertvariant="warning" data-atitle="¿Estás seguro de eliminar el horario?"
                                        data-adescription="Se aliminará completamente el horario y no se podrá recuperar."
                                        data-param="/api/schedules/delete/{{ $schedule->id }}"
                                        class="p-2 dinamic-alert hover:bg-neutral-100 text-left w-full block rounded-md hover:bg-gray-10">
                                        Eliminar
                                    </button>
                                @endif
                                @if (($cuser->has('schedules:archive') && $schedule->user_id === $cuser->id) || $cuser->isDev())
                                    <button data-alertvariant="warning" data-atitle="¿Estás seguro de archivar el horario?"
                                        data-adescription="Este proceso cambiara el rango de fechas del horario y no se podrá visualizar en ningun lado solo en los reportes de horarios."
                                        data-param="/api/schedules/archive/{{ $schedule->id }}"
                                        class="p-2 dinamic-alert hover:bg-neutral-100 text-left w-full block rounded-md hover:bg-gray-10">
                                        Archivar
                                    </button>
                                @endif
                            </div>
                            <div id="dialog-{{ $schedule->id }}" tabindex="-1" aria-hidden="true" class="dialog hidden">
                                <div class="content lg:max-w-lg max-w-full">
                                    <header>
                                        Editar horario: {{ $schedule->title }}
                                    </header>
                                    <form action="/api/schedules/{{ $schedule->id }}" id="dialog-form-{{ $schedule->id }}"
                                        method="POST" class="dinamic-form body grid gap-4">
                                        @include('modules.schedules.slug.form', [
                                            'schedule' => $schedule,
                                        ])
                                    </form>
                                    <footer>
                                        <button data-modal-hide="dialog-{{ $schedule->id }}"
                                            type="button">Cancelar</button>
                                        <button form="dialog-form-{{ $schedule->id }}" type="submit">
                                            Guardar</button>
                                    </footer>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            {{-- @forelse ($schedules as $schedule)
                    <div class="relative flex item-c">
                        @php
                            $from = date('h:i A', strtotime($schedule->from));
                            $to = date('h:i A', strtotime($schedule->to));
                        @endphp
                        <div data-active data-id="{{ $schedule->id }}"
                            class="group cursor-pointer schedule overflow-hidden w-[230px] shadow-md border border-neutral-300 rounded-xl">
                            <div style="background-color: {{ $schedule->background }}"
                                class="block w-1.5 grayscale opacity-30 group-data-[active]:opacity-100 group-data-[active]:grayscale-0 text-transparent">
                            </div>
                            <div class="p-4 space-y-1 h-full flex flex-col flex-grow">
                                <div class="flex items-center">
                                    <p class="font-medium flex-grow overflow-ellipsis text-nowrap tracking-tight">
                                        {{ $schedule->title }}
                                    </p>
                                </div>
                                <p class="text-sm flex items-center gap-1">
                                    svg'bxs-time-five', 'w-5 h-5 opacity-60')
                                    {{ $from }} - {{ $to }}
                                </p>

                                <p class="text-sm flex items-center gap-1">
                                    svg'bxs-calendar', 'w-5 h-5 opacity-60')
                                    {{ date('d/m/Y', strtotime($schedule->start_date)) }} -
                                    {{ date('d/m/Y', strtotime($schedule->end_date)) }}
                                </p>
                                <div>
                                    <span class="text-xs font-semibold text-neutral-600">Se repite cada:</span>
                                    <div class="flex gap-1">
                                        @foreach ($days as $day)
                                            @if (in_array($day['key'], $schedule->days))
                                                <span class="text-xs bg-neutral-700 text-white rounded-full p-1 block px-2">
                                                    {{ $day['name'] }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($cuser->has('users:edit') || $cuser->isDev())
                            <button class="rounded-full p-2 hover:bg-neutral-200 transition-colors absolute top-3 right-3"
                                data-dropdown-toggle="dropdown-{{ $schedule->id }}">
                                svg'bx-dots-vertical-rounded', 'w-5 h-5')
                            </button>

                            <div id="dropdown-{{ $schedule->id }}" class="dropdown-content hidden">
                                @if (($cuser->has('schedules:edit') && $schedule->user_id === $cuser->id) || $cuser->isDev())
                                    <button data-modal-target="dialog-{{ $schedule->id }}"
                                        data-modal-toggle="dialog-{{ $schedule->id }}"
                                        class="p-2 hover:bg-neutral-100 text-left w-full block rounded-md hover:bg-gray-10">
                                        Editar
                                    </button>
                                @endif
                                @if (($cuser->has('schedules:delete') && $schedule->user_id === $cuser->id) || $cuser->isDev())
                                    <button data-alertvariant="warning" data-atitle="¿Estás seguro de eliminar el horario?"
                                        data-adescription="Se aliminará completamente el horario y no se podrá recuperar."
                                        data-param="/api/schedules/delete/{{ $schedule->id }}"
                                        class="p-2 dinamic-alert hover:bg-neutral-100 text-left w-full block rounded-md hover:bg-gray-10">
                                        Eliminar
                                    </button>
                                @endif
                                @if (($cuser->has('schedules:archive') && $schedule->user_id === $cuser->id) || $cuser->isDev())
                                    <button data-alertvariant="warning" data-atitle="¿Estás seguro de archivar el horario?"
                                        data-adescription="Este proceso cambiara el rango de fechas del horario y no se podrá visualizar en ningun lado solo en los reportes de horarios."
                                        data-param="/api/schedules/archive/{{ $schedule->id }}"
                                        class="p-2 dinamic-alert hover:bg-neutral-100 text-left w-full block rounded-md hover:bg-gray-10">
                                        Archivar
                                    </button>
                                @endif
                            </div>

                            <div id="dialog-{{ $schedule->id }}" tabindex="-1" aria-hidden="true" class="dialog hidden">
                                <div class="content lg:max-w-lg max-w-full">
                                    <header>
                                        Editar horario: {{ $schedule->title }}
                                    </header>
                                    <form action="/api/schedules/{{ $schedule->id }}" id="dialog-form-{{ $schedule->id }}"
                                        method="POST" class="dinamic-form body grid gap-4">
                                        @include('modules.users.schedules.slug.form', [
                                            'schedule' => $schedule,
                                        ])
                                    </form>
                                    <footer>
                                        <button data-modal-hide="dialog-{{ $schedule->id }}"
                                            type="button">Cancelar</button>
                                        <button form="dialog-form-{{ $schedule->id }}" type="submit">
                                            Guardar</button>
                                    </footer>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="p-20 text-center w-full text-neutral-600 text-sm">
                        No hay horarios disponibles.
                    </p>
                @endforelse --}}
        </div>
        {{-- <input type="hidden" value="{{ $user->id }}" id="user-id"> --}}
        {{-- <div class="px-2 space-y-1 flex flex-col">
        @if ($cuser->has('schedules:create_unique') || $cuser->isDev())
            <button type="button" data-modal-target="dialog" data-modal-toggle="dialog" class="primary">
                svg'bx-plus', 'w-5 h-5')
                <span>Agregar horario único</span>
            </button>
            <div id="dialog" tabindex="-1" aria-hidden="true" class="dialog hidden">
                <div class="content lg:max-w-lg max-w-full">
                    <header>
                        Agregar horario único
                    </header>
                    <form action="/api/schedules/group/{{ $user->id }}/add" id="schedule-form-add" method="POST"
                        class="p-3 dinamic-form gap-4 overflow-y-auto flex flex-col">
                        @include('modules.users.schedules.slug.form', [
                            'user' => $user,
                        ])
                    </form>
                    <footer>
                        <button data-modal-hide="dialog" type="button">Cancelar</button>
                        <button form="dialog-form" type="submit">
                            Guardar</button>
                    </footer>
                </div>
            </div>
        @endif
        <div id="schedules" class="flex w-full overflow-x-auto pb-5 pt-2 hidden-scroll mt-2 gap-2">
            @forelse ($schedules as $schedule)
                <div class="relative">
                    @php
                        $from = date('h:i A', strtotime($schedule->from));
                        $to = date('h:i A', strtotime($schedule->to));
                    @endphp
                    <div data-active data-id="{{ $schedule->id }}"
                        class="group cursor-pointer schedule overflow-hidden w-[230px] shadow-md border border-neutral-300 rounded-xl">
                        <div style="background-color: {{ $schedule->background }}"
                            class="block w-1.5 grayscale opacity-30 group-data-[active]:opacity-100 group-data-[active]:grayscale-0 text-transparent">
                        </div>
                        <div class="p-4 space-y-1 h-full flex flex-col flex-grow">
                            <div class="flex items-center">
                                <p class="font-medium flex-grow overflow-ellipsis text-nowrap tracking-tight">
                                    {{ $schedule->title }}
                                </p>
                            </div>
                            <p class="text-sm flex items-center gap-1">
                                svg'bxs-time-five', 'w-5 h-5 opacity-60')
                                {{ $from }} - {{ $to }}
                            </p>

                            <p class="text-sm flex items-center gap-1">
                                svg'bxs-calendar', 'w-5 h-5 opacity-60')
                                {{ date('d/m/Y', strtotime($schedule->start_date)) }} -
                                {{ date('d/m/Y', strtotime($schedule->end_date)) }}
                            </p>
                            <div>
                                <span class="text-xs font-semibold text-neutral-600">Se repite cada:</span>
                                <div class="flex gap-1">
                                    @foreach ($days as $day)
                                        @if (in_array($day['key'], $schedule->days))
                                            <span class="text-xs bg-neutral-700 text-white rounded-full p-1 block px-2">
                                                {{ $day['name'] }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($cuser->has('users:edit') || $cuser->isDev())
                        <button class="rounded-full p-2 hover:bg-neutral-200 transition-colors absolute top-3 right-3"
                            data-dropdown-toggle="dropdown-{{ $schedule->id }}">
                            svg'bx-dots-vertical-rounded', 'w-5 h-5')
                        </button>

                        <div id="dropdown-{{ $schedule->id }}" class="dropdown-content hidden">
                            @if (($cuser->has('schedules:edit') && $schedule->user_id === $cuser->id) || $cuser->isDev())
                                <button data-modal-target="dialog-{{ $schedule->id }}"
                                    data-modal-toggle="dialog-{{ $schedule->id }}"
                                    class="p-2 hover:bg-neutral-100 text-left w-full block rounded-md hover:bg-gray-10">
                                    Editar
                                </button>
                            @endif
                            @if (($cuser->has('schedules:delete') && $schedule->user_id === $cuser->id) || $cuser->isDev())
                                <button data-alertvariant="warning" data-atitle="¿Estás seguro de eliminar el horario?"
                                    data-adescription="Se aliminará completamente el horario y no se podrá recuperar."
                                    data-param="/api/schedules/delete/{{ $schedule->id }}"
                                    class="p-2 dinamic-alert hover:bg-neutral-100 text-left w-full block rounded-md hover:bg-gray-10">
                                    Eliminar
                                </button>
                            @endif
                            @if (($cuser->has('schedules:archive') && $schedule->user_id === $cuser->id) || $cuser->isDev())
                                <button data-alertvariant="warning" data-atitle="¿Estás seguro de archivar el horario?"
                                    data-adescription="Este proceso cambiara el rango de fechas del horario y no se podrá visualizar en ningun lado solo en los reportes de horarios."
                                    data-param="/api/schedules/archive/{{ $schedule->id }}"
                                    class="p-2 dinamic-alert hover:bg-neutral-100 text-left w-full block rounded-md hover:bg-gray-10">
                                    Archivar
                                </button>
                            @endif
                        </div>

                        <div id="dialog-{{ $schedule->id }}" tabindex="-1" aria-hidden="true" class="dialog hidden">
                            <div class="content lg:max-w-lg max-w-full">
                                <header>
                                    Editar horario: {{ $schedule->title }}
                                </header>
                                <form action="/api/schedules/{{ $schedule->id }}" id="dialog-form-{{ $schedule->id }}"
                                    method="POST" class="dinamic-form body grid gap-4">
                                    @include('modules.users.schedules.slug.form', [
                                        'schedule' => $schedule,
                                    ])
                                </form>
                                <footer>
                                    <button data-modal-hide="dialog-{{ $schedule->id }}" type="button">Cancelar</button>
                                    <button form="dialog-form-{{ $schedule->id }}" type="submit">
                                        Guardar</button>
                                </footer>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <p class="p-20 text-center w-full text-neutral-600 text-sm">
                    No hay horarios disponibles.
                </p>
            @endforelse
        </div>
    </div>
    <div class="h-full w-full overflow-y-auto">
        <div id="calendar-schedules" class="w-full h-full overflow-y-auto">
        </div>
        <div id="loader" class="absolute grid rounded-xl place-content-center h-full inset-0 bg-white z-10">
            <div class="loader"></div>
        </div>
    </div> --}}
    </div>
@endsection
