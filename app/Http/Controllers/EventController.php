<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Schema;


class EventController extends Controller
{

    public function addEvent(Request $request)
    {

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'startDate' => ['required', 'date', 'date_format:d-m-Y'],
            'endDate' => ['required', 'date', 'date_format:d-m-Y'],
            'startClock' => ['required', 'date_format:H:i'],
            'endClock' => ['required', 'date_format:H:i'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'type' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'link' => ['nullable', 'url', 'max:255'],

        ]);
        $imagePath = $request->file('image')->store('eventImage', 'public');

        // $imageFullPath = asset('storage/' . $imagePath);

        $user = Auth::user();
        if ($user->hasRole('superadmin')) {
            $validated['status'] = 'accept';
            $validated['approvalLevel'] = 'superadmin';
        } elseif ($user->hasRole('admin')) {
            $validated['status'] = 'accept';
            $validated['approvalLevel'] = 'admin';
        } else {
            $validated['status'] = 'pending';
            $validated['approvalLevel'] = 'admin' || 'superadmin';
        }
        $validated['eventState'] = 'active';
        $validated['image'] = $imagePath;
        $validated['user_id'] = Auth::id();
        $validated['username'] = Auth::user()->username;
        $event = Event::create($validated);
        return response()->json([
            "message" => "Event created successfully",

            "event" => array_merge(
                $event->toArray(),
                ['username' => Auth::user()->username]
            )

        ]);
    }





    public function showUpcommingEvent()
    {
        $today = Carbon::now()->startOfDay();

        $events = Event::select(
            'id',
            'title',
            'description',
            'startDate',
            'endDate',
            'startClock',
            'endClock',
            'image',
            'type',
            'address',
            'link'
        )
            ->where('status', 'accept')
            ->whereRaw("STR_TO_DATE(startDate, '%d-%m-%Y') >= ?", [$today->format('Y-m-d')])
            ->orderByRaw("STR_TO_DATE(startDate, '%d-%m-%Y') ASC")
            ->get()
            ->map(function ($event) use ($today) {
                $startDate = Carbon::parse($event->startDate)->startOfDay();
                $endDate = Carbon::parse($event->endDate)->startOfDay();

                $diffDays = $today->diffInDays($startDate);
                if ($diffDays > 1) {
                    $event->starts_in = $diffDays . " days left";
                } elseif ($diffDays == 1) {
                    $event->starts_in = "tomorrow";
                } elseif ($diffDays == 0) {
                    $event->starts_in = "today";
                }

                $durationDays = $startDate->diffInDays($endDate) + 1;
                $event->duration = $durationDays;

                $event->current_status = "upcoming";

                return $event;
            });

        return response()->json($events);
    }



    public function cancelEvent(Request $request,$id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $user = Auth::user();

        if (!($user->id === $event->user_id || $user->hasRole('admin') || $user->hasRole('superadmin'))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->eventState = 'cancel';
        $event->save();

        return response()->json([
            'message' => 'Event has been canceled successfully',
            'event' => $event
        ]);
    }

    public function postponeEvent(Request $request,$id)
    {

        $editDate = $request->validate([
            'newStartDate' => ['nullable', 'date', 'date_format:d-m-Y'],
            'newEndDate' => ['nullable', 'date', 'date_format:d-m-Y'],
        ]);

        $event = Event::find($id);
        if (!$event) {
            return response()->json([
                'message' => 'Event not found'
            ], 404);
        }

        $user = Auth::user();

        if (!($user->id == $event->user_id|| $user->hasRole('admin') || $user->hasRole('superadmin'))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!empty($editDate['newStartDate'])) {
        $event->startDate = $editDate['newStartDate'];
    }

    if (!empty($editDate['newEndDate'])) {
        $event->endDate = $editDate['newEndDate'];
    }
        $event->eventState = 'delay';
        $event->save();

        return response()->json([
            'message' => 'Event has been delayed successfully',
            'event' => $event,
        ]);
    }


        public function search($keyword)
    {

        if (empty($keyword)) {
            return response()->json([
                'events' => []
            ], 200);
        }

        $columns = Schema::getColumnListing('events');

        $events = Event::where(function ($query) use ($columns, $keyword) {
            foreach ($columns as $column) {
                if (!in_array($column, ['id', 'user_id', 'created_at', 'updated_at'])) {
                    $query->orWhere($column, 'like', "%{$keyword}%");
                }
            }
        })->get();

        return response()->json([
            'events' => $events
        ], 200);
    }



    public function filterByType($type)
    {
        $events = Event::where('type', 'like', "%$type%")->get();;

        return response()->json([
            'events' => $events
        ]);
        // return view('events.index', compact('events'));
    }

    public function filterByAddress($address)
    {
        // $address = $request->input('address');

        $events = Event::where('address', 'like', "%$address%")->get();
        return response()->json([
            'events' => $events
        ]);
        // return view('events.index', compact('events'));
    }

    public function filterByDate($date)
    {
        $filteredEvents = [];
        $target = Carbon::createFromFormat('d-m-Y', $date);

        $events = Event::all();

        foreach ($events as $event) {
            $start = Carbon::createFromFormat('d-m-Y', $event->startDate);
            $end   = Carbon::createFromFormat('d-m-Y', $event->endDate);

            if ($target->between($start, $end)) {
                $filteredEvents[] = $event;
            }
        }

        return response()->json([
            'events' => $filteredEvents
        ]);
    }


}
