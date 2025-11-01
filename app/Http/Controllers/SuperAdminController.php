<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
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


    public function approveEvent(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($event->user_id == $user->id) {
            return response()->json([
                'error' => 'You cannot approve your own event.'
            ], 403);
        }

        $allowedRoles = [$event->approvalLevel];

        if (!in_array($user->role, $allowedRoles) && !$user->hasRole('superadmin|admin')) {
            return response()->json([
                'error' => 'You are not authorized to approve this event.'
            ], 403);
        }
        $validated = $request->validate([
            'status' => 'required|in:accept,reject'
        ]);

        $event->status = $validated['status'];
        $event->save();

        return response()->json([
            'message' => 'Event has been ' . $validated['status'] . ' successfully.',
            'event' => $event
        ]);
    }


    public function showAllEventaccepted()
    {
        $today = Carbon::now()->startOfDay();

        $events = Event::select('id', 'title', 'description', 'startDate', 'endDate', 'startClock', 'endClock', 'image', 'type', 'address', 'link')
            ->where('status', 'accept')
            ->orderBy('startDate', 'asc')
            ->get()
            ->map(function ($event) use ($today) {
                $startDate = Carbon::parse($event->startDate)->startOfDay();
                $endDate = Carbon::parse($event->endDate)->startOfDay();

                //calculate how many days left
                $diffDays = $today->diffInDays($startDate, false);
                if ($diffDays > 1) {
                    $event->starts_in = $diffDays . " days left";
                } elseif ($diffDays == 1) {
                    $event->starts_in = "tomorrow";
                } elseif ($diffDays == 0) {
                    $event->starts_in = "today";
                } else {
                    $event->starts_in = "already started";
                }

                //calculate duration
                $durationDays = $startDate->diffInDays($endDate) + 1;
                $event->duration = $durationDays . " days";
                $event->current_status = "upcoming";
                if ($today->lt($startDate)) {
                    $event->current_status = "upcoming";
                } elseif ($today->betweenIncluded($startDate, $endDate)) {
                    $event->current_status = "ongoing";
                } else {
                    $event->current_status = "ended";
                }
                return $event;
            });

        return response()->json($events);
    }

    public function showAllEventPending()
    {
        $today = Carbon::now()->startOfDay();

        $events = Event::select('id', 'title', 'description', 'startDate', 'endDate', 'startClock', 'endClock', 'image', 'type', 'address', 'link')
            ->where('status', 'pending')
            ->orderBy('startDate', 'asc')
            ->get()
            ->map(function ($event) use ($today) {
                $startDate = Carbon::parse($event->startDate)->startOfDay();
                $endDate = Carbon::parse($event->endDate)->startOfDay();

                //calculate how many days left
                $diffDays = $today->diffInDays($startDate, false);
                if ($diffDays > 1) {
                    $event->starts_in = $diffDays . " days left";
                } elseif ($diffDays == 1) {
                    $event->starts_in = "tomorrow";
                } elseif ($diffDays == 0) {
                    $event->starts_in = "today";
                } else {
                    $event->starts_in = "already started";
                }

                //calculate duration
                $durationDays = $startDate->diffInDays($endDate) + 1;
                $event->duration = $durationDays . " days";
                $event->current_status = "upcoming";
                if ($today->lt($startDate)) {
                    $event->current_status = "upcoming";
                } elseif ($today->betweenIncluded($startDate, $endDate)) {
                    $event->current_status = "ongoing";
                } else {
                    $event->current_status = "ended";
                }
                return $event;
            });

        return response()->json($events);
    }
    public function showAllEventRejected()
    {
        $today = Carbon::now()->startOfDay();

        $events = Event::select('id', 'title', 'description', 'startDate', 'endDate', 'startClock', 'endClock', 'image', 'type', 'address', 'link')
            ->where('status', 'reject')
            ->orderBy('startDate', 'asc')
            ->get()
            ->map(function ($event) use ($today) {
                $startDate = Carbon::parse($event->startDate)->startOfDay();
                $endDate = Carbon::parse($event->endDate)->startOfDay();

                //calculate how many days left
                $diffDays = $today->diffInDays($startDate, false);
                if ($diffDays > 1) {
                    $event->starts_in = $diffDays . " days left";
                } elseif ($diffDays == 1) {
                    $event->starts_in = "tomorrow";
                } elseif ($diffDays == 0) {
                    $event->starts_in = "today";
                } else {
                    $event->starts_in = "already started";
                }

                //calculate duration
                $durationDays = $startDate->diffInDays($endDate) + 1;
                $event->duration = $durationDays . " days";
                $event->current_status = "upcoming";
                if ($today->lt($startDate)) {
                    $event->current_status = "upcoming";
                } elseif ($today->betweenIncluded($startDate, $endDate)) {
                    $event->current_status = "ongoing";
                } else {
                    $event->current_status = "ended";
                }
                return $event;
            });

        return response()->json($events);
    }
    public function showUsers()
    {
        $users = User::all()->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first() ?? 'no role',
            ];
        });

        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'No users found'
            ], 200);
        }

        return response()->json([
            'users' => $users
        ], 200);
    }
}
