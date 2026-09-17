<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::query()->with('user');

        if ($userId = $request->integer('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($modul = $request->string('modul')->trim()->value()) {
            $query->where('modul', $modul);
        }

        if ($aksi = $request->string('aksi')->trim()->value()) {
            $query->where('aksi', $aksi);
        }

        if ($dari = $request->date('dari')) {
            $query->whereDate('created_at', '>=', $dari);
        }

        if ($sampai = $request->date('sampai')) {
            $query->whereDate('created_at', '<=', $sampai);
        }

        $logs = $query->latest('created_at')->paginate(20)->withQueryString();

        return view('audit-log.index', [
            'logs' => $logs,
            'users' => User::orderBy('nama')->get(['id', 'nama']),
            'modulOptions' => AuditLog::query()->distinct()->orderBy('modul')->pluck('modul'),
            'aksiOptions' => AuditLog::query()->distinct()->orderBy('aksi')->pluck('aksi'),
            'filters' => $request->only(['user_id', 'modul', 'aksi', 'dari', 'sampai']),
        ]);
    }

    public function show(AuditLog $auditLog): View
    {
        $auditLog->load('user');

        return view('audit-log.show', [
            'log' => $auditLog,
        ]);
    }
}
