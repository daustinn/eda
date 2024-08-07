@extends('modules.users.+layout')

@section('title', 'Correos y accesos')



@section('layout.users')
    <div class="text-black w-full flex-col flex-grow flex overflow-auto">
        <h2 class="py-3 font-semibold tracking-tight text-lg px-1">
            Gestión de correos y accesos
        </h2>
        <div class="p-2 flex o items-center gap-2">
            <div class="flex-grow">
                <input value="{{ request()->query('q') }}" class="dinamic-search" type="text"
                    placeholder="Buscar por correo o usuario">
            </div>
            <div>
                <button id="export-email-access"
                    class="bg-white hover:shadow-md flex items-center rounded-full gap-2 p-2 text-sm font-semibold px-3">
                    <svg width="20" height="20" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000">
                        <g id="SVGRepo_iconCarrier">
                            <defs>
                                <linearGradient id="a" x1="4.494" y1="-2092.086" x2="13.832" y2="-2075.914"
                                    gradientTransform="translate(0 2100)" gradientUnits="userSpaceOnUse">
                                    <stop offset="0" stop-color="#18884f"></stop>
                                    <stop offset="0.5" stop-color="#117e43"></stop>
                                    <stop offset="1" stop-color="#0b6631"></stop>
                                </linearGradient>
                            </defs>
                            <path
                                d="M19.581,15.35,8.512,13.4V27.809A1.192,1.192,0,0,0,9.705,29h19.1A1.192,1.192,0,0,0,30,27.809h0V22.5Z"
                                style="fill:#185c37"></path>
                            <path d="M19.581,3H9.705A1.192,1.192,0,0,0,8.512,4.191h0V9.5L19.581,16l5.861,1.95L30,16V9.5Z"
                                style="fill:#21a366"></path>
                            <path d="M8.512,9.5H19.581V16H8.512Z" style="fill:#107c41"></path>
                            <path
                                d="M16.434,8.2H8.512V24.45h7.922a1.2,1.2,0,0,0,1.194-1.191V9.391A1.2,1.2,0,0,0,16.434,8.2Z"
                                style="opacity:0.10000000149011612;isolation:isolate"></path>
                            <path
                                d="M15.783,8.85H8.512V25.1h7.271a1.2,1.2,0,0,0,1.194-1.191V10.041A1.2,1.2,0,0,0,15.783,8.85Z"
                                style="opacity:0.20000000298023224;isolation:isolate"></path>
                            <path
                                d="M15.783,8.85H8.512V23.8h7.271a1.2,1.2,0,0,0,1.194-1.191V10.041A1.2,1.2,0,0,0,15.783,8.85Z"
                                style="opacity:0.20000000298023224;isolation:isolate"></path>
                            <path
                                d="M15.132,8.85H8.512V23.8h6.62a1.2,1.2,0,0,0,1.194-1.191V10.041A1.2,1.2,0,0,0,15.132,8.85Z"
                                style="opacity:0.20000000298023224;isolation:isolate"></path>
                            <path
                                d="M3.194,8.85H15.132a1.193,1.193,0,0,1,1.194,1.191V21.959a1.193,1.193,0,0,1-1.194,1.191H3.194A1.192,1.192,0,0,1,2,21.959V10.041A1.192,1.192,0,0,1,3.194,8.85Z"
                                style="fill:url(#a)"></path>
                            <path
                                d="M5.7,19.873l2.511-3.884-2.3-3.862H7.758L9.013,14.6c.116.234.2.408.238.524h.017c.082-.188.169-.369.26-.546l1.342-2.447h1.7l-2.359,3.84,2.419,3.905H10.821l-1.45-2.711A2.355,2.355,0,0,1,9.2,16.8H9.176a1.688,1.688,0,0,1-.168.351L7.515,19.873Z"
                                style="fill:#fff"></path>
                            <path d="M28.806,3H19.581V9.5H30V4.191A1.192,1.192,0,0,0,28.806,3Z" style="fill:#33c481">
                            </path>
                            <path d="M19.581,16H30v6.5H19.581Z" style="fill:#107c41"></path>
                        </g>
                    </svg>
                    <span class="max-lg:hidden">Exportar</span>
                </button>
            </div>
        </div>
        <div class=" overflow-auto flex-grow">
            @if ($cuser->hasPrivilege('users:emails-access:show'))
                <table class="w-full text-left" id="table-users">
                    <thead class="border-b">
                        <tr class="[&>th]:font-medium [&>th]:text-nowrap [&>th]:p-1.5 [&>th]:px-2">
                            <th class="w-full">Usuario</th>
                            <th>Nombre de usuario</th>
                            <th class="px-4">Accesos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @if ($users->count() === 0)
                            <tr class="">
                                <td colspan="11" class="text-center py-4">
                                    <div class="p-10">
                                        No hay nada por aquí
                                    </div>
                                </td>
                            </tr>
                        @else
                            @foreach ($users as $user)
                                <tr
                                    class="[&>td]:py-3 hover:border-transparent hover:[&>td]shadow-md relative group first:[&>td]:rounded-l-2xl last:[&>td]:rounded-r-2xl hover:bg-white [&>td]:px-2">
                                    <td>
                                        <div class="flex items-center gap-4">
                                            @if ($cuser->hasPrivilege('users:emails-access:edit'))
                                                <button class="absolute inset-0"
                                                    data-modal-target="dialog-{{ $user->id }}"
                                                    data-modal-toggle="dialog-{{ $user->id }}">
                                                </button>
                                                <div id="dialog-{{ $user->id }}" data-modal-backdrop="static"
                                                    tabindex="-1" aria-hidden="true"
                                                    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                                                    <div
                                                        class="relative w-full flex flex-col overflow-y-auto max-w-xl max-h-full bg-white rounded-2xl shadow">
                                                        <div
                                                            class="flex items-center justify-between p-3 border-b rounded-t">
                                                            <h3 class="text-lg font-semibold text-gray-900">
                                                                Accesos del correos institucionales
                                                            </h3>
                                                            <button type="button"
                                                                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                                                                data-modal-hide="dialog-{{ $user->id }}">
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
                                                        <form action="/api/users/email-access/{{ $user->id }}"
                                                            method="POST" id="dialog-{{ $user->id }}-form"
                                                            class="p-3 dinamic-form grid gap-4 overflow-y-auto">
                                                            @include('modules.users.emails-access.form', [
                                                                'user' => $user,
                                                            ])
                                                        </form>
                                                        <div
                                                            class="flex items-center p-3 border-t border-gray-200 rounded-b">
                                                            <button form="dialog-{{ $user->id }}-form" type="submit"
                                                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl text-sm px-5 py-2.5 text-center">
                                                                Guardar</button>
                                                            <button id="button-close-scheldule-modal"
                                                                data-modal-hide="dialog-{{ $user->id }}" type="button"
                                                                class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-xl border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">Cancelar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @include('commons.avatar', [
                                                'src' => $user->profile,
                                                'className' => 'w-12',
                                                'alt' => $user->first_name . ' ' . $user->last_name,
                                                'altClass' => 'text-lg',
                                            ])
                                            <p class="text-sm font-normal flex-grow text-nowrap">
                                                <span class="text-base block font-semibold">
                                                    {{ $user->last_name }},
                                                    {{ $user->first_name }}
                                                </span>
                                                {{ $user->dni }} - {{ $user->email }}
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        {{ explode('@', $user->email)[0] }}
                                    </td>
                                    <td>
                                        <p class="text-nowrap px-4 text-blue-600 font-medium text-sm">
                                            Cantidad de accesos:
                                            {{ count($user->email_access ?? []) }}
                                        </p>
                                        {{-- <div class="flex items-center gap-2">
                                                @if (!is_null($user->email_access))
                                                    @php
                                                        $array = json_decode($user->email_access, true);
                                                        $finaly = [];
                                                        foreach (json_decode($user->email_access, true) as $access) {
                                                            $code = explode(':', $access)[0];
                                                            if (!isset($finaly[$code])) {
                                                                $finaly[$code] = $access;
                                                            }
                                                        }
                                                    @endphp
                                                    @foreach ($finaly as $access)
                                                        <div
                                                            class="bg-blue-600 text-white uppercase p-1 px-2 shadow-md rounded-full text-sm text-nowrap font-semibold">
                                                            {{ explode(':', $access)[0] }}
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div> --}}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            @else
                @include('+403', [
                    'message' => 'No tienes permisos para visualizar los correos electrónicos.',
                ])
            @endif
        </div>
        <footer class="px-5 pt-4">
            {!! $users->links() !!}
        </footer>
    </div>
@endsection
