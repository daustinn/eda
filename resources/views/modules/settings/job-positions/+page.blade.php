@extends('modules.users.+layout')

@section('title', 'Puestos de trabajo')
@php
    $levels = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
@endphp

@section('layout.users')
    <div class="text-black w-full flex-col flex-grow flex overflow-auto">
        @if ($cuser->hasPrivilege('users:job-positions:create'))
            <button type="button" data-modal-target="dialog" data-modal-toggle="dialog"
                class="bg-blue-700 w-fit shadow-md shadow-blue-500/30 font-semibold hover:bg-blue-600 min-w-max flex items-center rounded-full p-2 gap-1 text-white text-sm px-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-plus">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                <span class="max-lg:hidden">Nuevo cargo</span>
            </button>
            <div id="dialog" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
                class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative w-full max-w-md max-h-full bg-white rounded-2xl shadow">
                    <div class="flex items-center justify-between p-3 border-b rounded-t">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Crear cargo
                        </h3>
                        <button type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                            data-modal-hide="dialog">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                        </button>
                    </div>
                    @include('components.users.auditory-card')
                    <form action="/api/job-positions" method="POST" id="dialog-form" class="p-3 dinamic-form grid gap-4">
                        @include('modules.users.job-positions.form', [
                            'job-position' => null,
                        ])
                    </form>
                    <div class="flex items-center p-3 border-t border-gray-200 rounded-b">
                        <button form="dialog-form" type="submit"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl text-sm px-5 py-2.5 text-center">
                            Guardar</button>
                        <button id="button-close-scheldule-modal" data-modal-hide="dialog" type="button"
                            class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-xl border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">Cancelar</button>
                    </div>
                </div>
            </div>
        @endif

        <h2 class="py-3 pb-0 font-semibold tracking-tight text-lg px-1">
            Gestión de puestos de trabajo
        </h2>
        <div class="py-2 px-1 flex o items-center gap-2">
            <div class="w-[200px]">
                <select class="dinamic-select bg-transparent p-1 border-transparent rounded-lg cursor-pointer"
                    name="level">
                    <option value="">Todos los niveles</option>
                    @foreach ($levels as $level)
                        <option value="{{ $level }}">{{ $level }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-grow">
                <input value="{{ request()->query('q') }}" class="dinamic-search" type="text" placeholder="Buscar...">
            </div>
        </div>
        <div class="overflow-auto flex-grow">
            @if ($cuser->hasPrivilege('users:job-positions:show'))
                <table class="w-full text-left" id="table-users">
                    <thead class="">
                        <tr
                            class="[&>th]:font-medium bg-white [&>th]:text-nowrap [&>th]:p-3 first:[&>th]:rounded-l-xl last:[&>th]:rounded-r-xl">
                            <th>Codigo</th>
                            <th>Puesto</th>
                            <th class="text-center">Nivel</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @if ($jobPositions->count() === 0)
                            <tr class="">
                                <td colspan="11" class="text-center py-4">
                                    <div class="p-10">
                                        No hay horarios registrados
                                    </div>
                                </td>
                            </tr>
                        @else
                            @foreach ($jobPositions as $job)
                                <tr
                                    class="[&>td]:py-3 hover:border-transparent hover:[&>td]shadow-md relative group first:[&>td]:rounded-l-2xl last:[&>td]:rounded-r-2xl hover:bg-white [&>td]:px-4">
                                    <td>
                                        <p class="text-nowrap font-semibold">
                                            {{ $job->code }}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-nowrap">
                                            {{ $job->name }}
                                        </p>
                                    </td>
                                    <td class="text-center font-medium text-orange-500">
                                        <p class="text-nowrap">
                                            {{ $job->level }}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="opacity-70 text-nowrap">
                                            {{ \Carbon\Carbon::parse($job->created_at)->isoFormat('LL') }}
                                        </p>
                                        @if ($cuser->hasPrivilege('users:job-positions:edit'))
                                            <button class="absolute inset-0" data-modal-target="dialog-{{ $job->id }}"
                                                data-modal-toggle="dialog-{{ $job->id }}">
                                            </button>
                                            <div id="dialog-{{ $job->id }}" data-modal-backdrop="static" tabindex="-1"
                                                aria-hidden="true"
                                                class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                                                <div
                                                    class="relative w-full max-w-md max-h-full bg-white rounded-2xl shadow">
                                                    <div class="flex items-center justify-between p-3 border-b rounded-t">
                                                        <h3 class="text-lg font-semibold text-gray-900">
                                                            Editar puesto
                                                        </h3>
                                                        <button type="button"
                                                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                                                            data-modal-hide="dialog-{{ $job->id }}">
                                                            <svg class="w-3 h-3" aria-hidden="true"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 14 14">
                                                                <path stroke="currentColor" stroke-linecap="round"
                                                                    stroke-linejoin="round" stroke-width="2"
                                                                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    @include('components.users.auditory-card')
                                                    <form action="/api/job-positions/{{ $job->id }}" method="POST"
                                                        id="dialog-{{ $job->id }}-form"
                                                        class="p-3 dinamic-form grid gap-4">
                                                        @include('modules.users.job-positions.form', [
                                                            'jobPosition' => $job,
                                                        ])
                                                    </form>
                                                    <div class="flex items-center p-3 border-t border-gray-200 rounded-b">
                                                        <button form="dialog-{{ $job->id }}-form" type="submit"
                                                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl text-sm px-5 py-2.5 text-center">
                                                            Actualizar</button>
                                                        <button id="button-close-scheldule-modal"
                                                            data-modal-hide="dialog-{{ $job->id }}" type="button"
                                                            class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-xl border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">Cancelar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            @else
                @include('+403', [
                    'message' => 'No tienes permisos para visualizar los puestos.',
                ])
            @endif
        </div>
        <footer class="px-5 pt-4">
            {!! $jobPositions->links() !!}
        </footer>
    </div>
@endsection
