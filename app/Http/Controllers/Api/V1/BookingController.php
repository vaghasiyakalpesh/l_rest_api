<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Gate::allows('viewAny', Booking::class)) {
            throw new AuthorizationException('You do not have permission to view bookings');
        }
        $bookings = Booking::all();

        return response()->json([
            'success' => true,
            'message' => 'Bookings retrieved successfully',
            'data' => BookingResource::collection($bookings),
            'status_code' => 200
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('create', Booking::class)) {
            throw new AuthorizationException('You do not have permission to create bookings');
        }

        $request->validate([
            'title' => ['required'],
            'date' => ['required', 'date'],
            'status' => ['required']
        ]);

        $booking = Booking::create([
            'title' => $request->title,
            'date' => $request->date,
            'status' => $request->status,
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => new BookingResource($booking),
            'status_code' => 201
        ], 201);
    }

    /**
     * Display the specified resource.
     * Using Route Model Binding - Laravel will automatically inject the model
     */
    public function show(Booking $booking)
    {
        if (!Gate::allows('view', $booking)) {
            throw new AuthorizationException('You do not have permission to view this booking');
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking retrieved successfully',
            'data' => new BookingResource($booking),
            'status_code' => 200
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        $request->validate([
            'title' => ['required'],
            'date' => ['required', 'date'],
            'status' => ['required']
        ]);

        if (!Gate::allows('update', $booking)) {
            throw new AuthorizationException('You do not have permission to update this booking');
        }

        $booking->update([
            'title' => $request->title,
            'date' => $request->date,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'data' => new BookingResource($booking),
            'status_code' => 200
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        if (!Gate::allows('delete', $booking)) {
            throw new AuthorizationException('You do not have permission to delete this booking.');
        }

        $booking->delete();

        return response()->json([
            'success' => true,
            'message' => 'Booking deleted successfully',
            'status_code' => 200
        ], 200);
    }
}