<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ManagementController extends Controller
{
    private function authorizeAccess(Request $request, string $type): void
    {
        abort_unless($request->user()->hasPermission($type.'.manage'), 403);
    }

    private function model(string $type): string
    {
        return match ($type) {
            'users' => User::class, 'roles' => Role::class, 'permissions' => Permission::class, default => abort(404)
        };
    }

    public function index(Request $request, string $type)
    {
        $model = $this->model($type);
        $this->authorizeAccess($request, $type);
        $query = $model::query();
        if ($type === 'users') {
            $query->with('role');
        } if ($type === 'roles') {
            $query->with('permissions')->withCount('users');
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->string('search').'%');
        }

        return Inertia::render('Management', ['type' => $type, 'records' => $query->latest()->paginate(50)->withQueryString(), 'roles' => Role::with('permissions')->get(), 'permissions' => Permission::all(), 'search' => $request->string('search')->toString()]);
    }

    public function save(Request $request, string $type, ?int $id = null)
    {
        $model = $this->model($type);
        $this->authorizeAccess($request, $type);
        $record = $id ? $model::findOrFail($id) : new $model;
        if ($type === 'users') {
            abort_if($record->is_super_admin && ! $request->user()->is_super_admin, 403);
            $data = $request->validate(['name' => 'required|string|max:100', 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($id)], 'password' => [$id ? 'nullable' : 'required', 'confirmed', Password::min(8)->mixedCase()->numbers()], 'is_admin' => 'required|boolean', 'role_id' => 'nullable|exists:roles,id']);
            if ($id === $request->user()->id && (! $data['is_admin'] || ($data['role_id'] ?? null) != $record->role_id)) {
                return back()->withErrors(['role_id' => 'You cannot change your own access.']);
            }
            if ($record->is_super_admin && ! $data['is_admin']) {
                return back()->withErrors(['is_admin' => 'The owner must retain admin access.']);
            }
            if (! $request->user()->is_super_admin && ($data['is_admin'] || $record->is_admin)) {
                abort(403, 'Only the owner can manage administrator accounts.');
            }
            if (empty($data['password'])) {
                unset($data['password']);
            }
            $record->forceFill($data)->save();
        } elseif ($type === 'roles') {
            abort_unless($request->user()->is_super_admin, 403);
            $data = $request->validate(['name' => ['required', 'string', 'max:100', Rule::unique('roles')->ignore($id)], 'permission_ids' => 'array', 'permission_ids.*' => 'integer|exists:permissions,id']);
            $record->fill(['name' => $data['name']])->save();
            $record->permissions()->sync($data['permission_ids'] ?? []);
        } else {
            abort_unless($request->user()->is_super_admin, 403);
            $data = $request->validate(['name' => ['required', 'regex:/^[a-z][a-z0-9._-]+$/', 'max:100', Rule::unique('permissions')->ignore($id)], 'description' => 'nullable|string|max:255']);
            if ($id && in_array($record->name, ['users.manage', 'roles.manage', 'permissions.manage']) && $data['name'] !== $record->name) {
                return back()->withErrors(['name' => 'This core permission cannot be renamed.']);
            }
            $record->fill($data)->save();
        }

        return back()->with('success', ucfirst(rtrim($type, 's')).' saved successfully.');
    }

    public function destroy(Request $request, string $type, int $id)
    {
        $model = $this->model($type);
        $this->authorizeAccess($request, $type);
        $record = $model::findOrFail($id);
        if ($type === 'users' && ($record->id === $request->user()->id || $record->is_super_admin)) {
            return back()->with('error', 'Your account and the store owner cannot be deleted.');
        }
        if ($type === 'users' && $record->is_admin) {
            abort_unless($request->user()->is_super_admin, 403);
        }
        if ($type !== 'users') {
            abort_unless($request->user()->is_super_admin, 403);
        }
        if ($type === 'roles' && $record->users()->exists()) {
            return back()->with('error', 'Reassign users before deleting this role.');
        }
        if ($type === 'permissions' && (in_array($record->name, ['users.manage', 'roles.manage', 'permissions.manage']) || $record->roles()->exists())) {
            return back()->with('error', 'Core or assigned permissions cannot be deleted.');
        }
        $record->delete();

        return back()->with('success','Deleted successfully.');
    }
}
