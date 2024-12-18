@php
    $privileges = isset($role) ? $role->privileges : [];
    $title = isset($role) ? $role->title : null;
    $level = isset($role) ? $role->level : null;
@endphp
<label class="label">
    <span>Título:</span>
    <input value="{{ $title }}" required type="" name="title" placeholder="Ejemplo: Administrador">
</label>
<label class="label">
    <span>Nivel:</span>
    <input value="{{ $level }}" required type="" name="level" placeholder="Ejemplo: 10">
</label>
<div class="px-1">
    <p class="tracking-tight py-1 text-stone-600">Privilegios:</p>
    <div class="grid items-start gap-2">
        @foreach ($system_privileges as $index => $system_privilege)
            @if ($index === 0 && !$cuser->isDev())
                @continue
            @endif
            <div class="content">
                <div class="flex items-center gap-2">
                    <button type="button" class="toggle-group">
                        @svg('fluentui-chevron-right-24', 'w-5 h-5')
                    </button>
                    <label class="flex items-center gap-2 ">
                        <input type="checkbox" class="select-all-group rounded-md p-2.5 border-neutral-400">
                        @svg('fluentui-folder-16-o', 'w-5 h-5')
                        <span>{{ $system_privilege['name'] }}</span>
                    </label>
                </div>
                <div style="display: none" class="group-content gap-2 mt-2 pl-5">
                    @foreach ($system_privilege['items'] as $item)
                        <div class="content">
                            <div class="flex items-center gap-2">
                                <button type="button" class="toggle-subgroup">
                                    @svg('fluentui-chevron-right-24', 'w-5 h-5')
                                </button>
                                <label class="flex items-center gap-2 ">
                                    <input type="checkbox"
                                        class="select-all-subgroup rounded-md p-2.5 border-neutral-400">
                                    @svg('fluentui-folder-16-o', 'w-5 h-5')
                                    <span>{{ $item['name'] }}</span>
                                </label>
                                <span class="privilege-count text-sm text-gray-500">(0/0)</span>
                            </div>
                            <div class="subgroup-content mt-2 grid pl-10 gap-3" style="display: none;">
                                @foreach ($item['privileges'] as $privilege => $privilege_name)
                                    <label class="flex items-center gap-2">
                                        <input {{ in_array($privilege, $privileges) ? 'checked' : '' }} type="checkbox"
                                            name="privileges[]" value="{{ $privilege }}"
                                            class="privilege-checkbox rounded-md p-2.5 border-neutral-400">
                                        @svg('fluentui-folder-16-o', 'w-5 h-5')
                                        <span>{{ $privilege_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
