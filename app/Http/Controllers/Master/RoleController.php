<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreRoleRequest;
use App\Http\Requests\Master\UpdateRoleRequest;
use App\Models\AuditLog;
use App\Models\Role;
use App\Permission;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount('users')->orderBy('nama')->get();

        return view('master.role.index', [
            'roles' => $roles,
            'permissionGroups' => Permission::groups(),
        ]);
    }

    public function create(): View
    {
        return view('master.role.create', [
            'permissionGroups' => Permission::groups(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create($request->validated());

        AuditLog::catat('role', 'create', $role, null, $role->only(['nama', 'slug', 'hak_akses', 'is_active']), "Membuat role \"{$role->nama}\".");

        return redirect()->route('master.role.index')->with('status', 'Role berhasil ditambahkan.');
    }

    public function edit(Role $role): View
    {
        return view('master.role.edit', [
            'role' => $role,
            'permissionGroups' => Permission::groups(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $dataLama = $role->only(['nama', 'slug', 'hak_akses', 'is_active']);

        $role->update($request->validated());

        AuditLog::catat('role', 'update', $role, $dataLama, $role->only(['nama', 'slug', 'hak_akses', 'is_active']), "Mengubah role \"{$role->nama}\".");

        return redirect()->route('master.role.index')->with('status', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->slug === 'super-admin') {
            return back()->with('error', 'Role Super Admin tidak dapat dihapus.');
        }

        try {
            $role->delete();
        } catch (QueryException) {
            return back()->with('error', 'Role tidak dapat dihapus karena masih dipakai oleh pengguna.');
        }

        AuditLog::catat('role', 'delete', null, $role->only(['nama', 'slug', 'hak_akses', 'is_active']), null, "Menghapus role \"{$role->nama}\".");

        return back()->with('status', 'Role berhasil dihapus.');
    }
}
