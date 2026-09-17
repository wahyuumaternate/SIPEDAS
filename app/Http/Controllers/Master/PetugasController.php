<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ResetPasswordPetugasRequest;
use App\Http\Requests\Master\StorePetugasRequest;
use App\Http\Requests\Master\UpdatePetugasRequest;
use App\Models\Kecamatan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PetugasController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->with(['role', 'kecamatan', 'desaKelurahan']);

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleId = $request->integer('role_id')) {
            $query->where('role_id', $roleId);
        }

        if ($status = $request->string('status')->trim()->value()) {
            $query->where('status', $status);
        }

        $petugas = $query->orderBy('nama')->paginate(15)->withQueryString();

        return view('master.petugas.index', [
            'petugas' => $petugas,
            'roles' => Role::where('is_active', true)->orderBy('nama')->get(),
            'filters' => $request->only(['q', 'role_id', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('master.petugas.create', $this->formReferenceData());
    }

    public function store(StorePetugasRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::create([
            ...$data,
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('master.petugas.index')->with('status', 'Petugas berhasil ditambahkan.');
    }

    public function edit(User $petugas): View
    {
        return view('master.petugas.edit', $this->formReferenceData() + ['petugas' => $petugas]);
    }

    public function update(UpdatePetugasRequest $request, User $petugas): RedirectResponse
    {
        $petugas->update($request->validated());

        return redirect()->route('master.petugas.index')->with('status', 'Data petugas berhasil diperbarui.');
    }

    public function toggleStatus(User $petugas): RedirectResponse
    {
        $petugas->update(['status' => $petugas->status === 'aktif' ? 'nonaktif' : 'aktif']);

        $pesan = $petugas->status === 'aktif' ? 'Petugas berhasil diaktifkan.' : 'Petugas berhasil dinonaktifkan.';

        return back()->with('status', $pesan);
    }

    public function resetPassword(ResetPasswordPetugasRequest $request, User $petugas): RedirectResponse
    {
        $petugas->update(['password' => Hash::make($request->validated('password'))]);

        return back()->with('status', 'Kata sandi petugas berhasil direset.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formReferenceData(): array
    {
        return [
            'roles' => Role::where('is_active', true)->orderBy('nama')->get(),
            'kecamatans' => Kecamatan::where('is_active', true)->with(['desaKelurahans' => fn ($q) => $q->where('is_active', true)->orderBy('nama')])->orderBy('nama')->get(),
        ];
    }
}
