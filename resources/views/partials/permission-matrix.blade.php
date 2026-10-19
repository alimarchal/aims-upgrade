{{--
    Permission checkboxes grouped by module, driven by the parent's x-data (see permissionForm()).
    Needs: $catalog. Boxes that only come from a role are shown ticked + locked; direct ones submit as permissions[].
--}}
<div class="ak-card-body">
    <input type="search" x-model="q" class="ak-input" style="width:100%;margin-bottom:14px" placeholder="Filter permissions...">
    @foreach ($catalog as $module)
        @php
            $moduleIds = collect($module['groups'])->flatMap(fn ($g) => collect($g['permissions'])->pluck('id'))->values()->all();
            $search = strtolower($module['label'].' '.collect($module['groups'])->pluck('label')->implode(' '));
        @endphp
        <div class="ak-mod" x-show="q === '' || @js($search).includes(q.toLowerCase())">
            <div class="ak-mod-head">
                <span>{{ $module['label'] }}</span>
                <label class="ak-link" style="font-weight:500;display:inline-flex;gap:6px;align-items:center">
                    <input type="checkbox" :checked="moduleAll(@js($moduleIds))" @change="toggleModule(@js($moduleIds), $event.target.checked)">
                    Select all
                </label>
            </div>
            @foreach ($module['groups'] as $group)
                <div class="ak-perm-row">
                    <span class="ak-perm-label">{{ $group['label'] }}</span>
                    @foreach ($group['permissions'] as $permission)
                        <label class="ak-perm" :class="{ 'is-inherited': isInherited({{ $permission['id'] }}), 'is-locked': isLocked({{ $permission['id'] }}) }" title="{{ $permission['name'] }}">
                            <input type="checkbox" name="permissions[]" value="{{ $permission['id'] }}"
                                :checked="isOn({{ $permission['id'] }})" :disabled="isDisabled({{ $permission['id'] }})"
                                @change="toggle({{ $permission['id'] }}, $event.target.checked)">
                            {{ ucfirst($permission['action']) }}
                            <small x-show="isInherited({{ $permission['id'] }}) && !direct.includes({{ $permission['id'] }})" x-cloak>(role)</small>
                        </label>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endforeach
</div>
@once
<script>
    function permissionForm(c) {
        return {
            q: '',
            direct: (c.direct || []).map(Number),
            roles: (c.roles || []).map(Number),
            rolePermissions: c.rolePermissions || {},
            locked: (c.locked || []).map(Number),
            inheritedSet() {
                const set = new Set();
                this.roles.forEach((r) => (this.rolePermissions[r] || []).forEach((p) => set.add(Number(p))));
                return set;
            },
            isInherited(id) { return this.inheritedSet().has(id); },
            isLocked(id) { return this.locked.includes(id); },
            isOn(id) { return this.direct.includes(id) || this.isInherited(id); },
            isDisabled(id) { return this.isLocked(id) || (this.isInherited(id) && !this.direct.includes(id)); },
            toggle(id, on) {
                if (on) { if (!this.direct.includes(id)) this.direct.push(id); }
                else { this.direct = this.direct.filter((x) => x !== id); }
            },
            editable(ids) { return ids.filter((id) => !this.isDisabled(id)); },
            moduleAll(ids) { const e = this.editable(ids); return e.length > 0 && e.every((id) => this.direct.includes(id)); },
            toggleModule(ids, on) { this.editable(ids).forEach((id) => this.toggle(id, on)); },
        };
    }
</script>
@endonce
