<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date') && $request->date !== 'all') {
            $date = $request->date;
            if ($date === 'today') {
                $query->whereDate('created_at', today());
            } elseif ($date === 'last_7_days') {
                $query->where('created_at', '>=', now()->subDays(7));
            } elseif ($date === 'last_30_days') {
                $query->where('created_at', '>=', now()->subDays(30));
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('entity_type', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = min(max((int) $request->input('per_page', 25), 1), 100);
        $logs = $query->paginate($perPage);

        $summary = [
            'total' => AuditLog::count(),
            'today' => AuditLog::whereDate('created_at', today())->count(),
            'successful_logins' => AuditLog::where('action', 'login')->count(),
            'failed_logins' => AuditLog::where('action', 'login_failed')->count(),
        ];

        $users = User::select('id', 'name', 'username')->orderBy('name')->get();

        return AuditLogResource::collection($logs)->additional([
            'meta' => [
                'summary' => $summary,
                'users' => $users,
            ],
        ]);
    }
}

