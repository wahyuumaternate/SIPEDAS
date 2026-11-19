<?php

namespace App\Http\Controllers;

use App\Models\DraftFormulir;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Simpan, ambil, dan hapus draft isian formulir pendataan (dipanggil otomatis oleh skrip formulir).
 */
class DraftFormulirController extends Controller
{
    public function show(Request $request, string $kunci): JsonResponse
    {
        $this->pastikanBoleh($request, $kunci);

        $draft = DraftFormulir::query()->milik($request->user(), $kunci)->first();

        return response()->json([
            'draft' => $draft ? [
                'isian' => $draft->isian,
                'disimpan_pada' => $draft->disimpan_pada->toIso8601String(),
            ] : null,
        ]);
    }

    public function update(Request $request, string $kunci): JsonResponse
    {
        $this->pastikanBoleh($request, $kunci);

        $data = $request->validate([
            'isian' => ['present', 'array', 'max:3000'],
            'isian.*' => ['array', 'size:2'],
            'isian.*.0' => ['required', 'string', 'max:200'],
            'isian.*.1' => ['nullable', 'string', 'max:10000'],
            'disimpan_pada' => ['nullable', 'date'],
        ]);

        $disimpanPada = isset($data['disimpan_pada']) ? now()->parse($data['disimpan_pada']) : now();

        DraftFormulir::updateOrCreate(
            ['user_id' => $request->user()->id, 'kunci' => $kunci],
            ['isian' => $data['isian'], 'disimpan_pada' => $disimpanPada->isFuture() ? now() : $disimpanPada],
        );

        return response()->json(['tersimpan' => true]);
    }

    public function destroy(Request $request, string $kunci): JsonResponse
    {
        $this->pastikanBoleh($request, $kunci);

        DraftFormulir::query()->milik($request->user(), $kunci)->delete();

        return response()->json(['dihapus' => true]);
    }

    /**
     * Draft data baru butuh hak tambah modul, draft ubah data butuh hak ubah modul.
     */
    private function pastikanBoleh(Request $request, string $kunci): void
    {
        [$modul, $aksi] = explode('.', $kunci);

        abort_unless($request->user()->hasPermission($aksi === 'create' ? "{$modul}.create" : "{$modul}.edit"), 403);
    }
}
