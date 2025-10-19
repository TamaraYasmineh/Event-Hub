<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SuperAdminController extends Controller
{
    public function upgradeUser($id)
    {
        $user = User::findOrFail($id);

        $user->syncRoles(['admin']);

        return response()->json([
            'message' => 'user upgraded successfully',
            'user' => $user
        ]);
    }

    public function downgradeUser($id)
    {
        $authUser = Auth::user();

        $user = User::findOrFail($id);

        $user->syncRoles(['user']);

        return response()->json([
            'message' => 'User downgraded successfully to regular user',
            'user' => $user->only(['id', 'username', 'email']),
            'new_role' => $user->getRoleNames()->first(),
        ], 200);
    }
}
