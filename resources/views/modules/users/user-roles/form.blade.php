@php
    $privileges = isset($role) ? $role->privileges : [];
    $title = isset($role) ? $role->title : null;
@endphp
<label class="block px-1">
    <span>Título:</span>
    <input value="{{ $title }}" required type="text" name="title" placeholder="Ejemplo: Administrador">
</label>
<div class="px-1 overflow-y-auto">
    <p class="tracking-tight text-stone-600">Privilegios:</p>
    <div class="grid items-start gap-2">
        @foreach ($system_privileges as $system_privilege)
            <div class="content">
                <div class="flex items-center gap-2">
                    <button type="button" class="toggle-group">
                        @svg('heroicon-o-chevron-right', 'w-5 h-5')
                    </button>
                    <label class="flex items-center gap-2 ">
                        <input type="checkbox" class="select-all-group rounded-md p-2.5 border-neutral-400">
                        @svg('heroicon-o-folder', 'w-5 h-5')
                        <span>{{ $system_privilege['name'] }}</span>
                    </label>
                </div>
                <div style="display: none" class="group-content gap-2 mt-2 pl-5">
                    @foreach ($system_privilege['items'] as $item)
                        <div class="content">
                            <div class="flex items-center gap-2">
                                <button type="button" class="toggle-subgroup">
                                    @svg('heroicon-o-chevron-right', 'w-5 h-5')
                                </button>
                                <label class="flex items-center gap-2 ">
                                    <input type="checkbox"
                                        class="select-all-subgroup rounded-md p-2.5 border-neutral-400">
                                    @svg('heroicon-o-folder', 'w-5 h-5')
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
                                        @svg('heroicon-o-folder', 'w-5 h-5')
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
