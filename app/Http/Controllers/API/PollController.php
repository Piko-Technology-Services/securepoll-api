<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\Category;
use App\Models\Nominee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class PollController extends Controller
{
    // 📌 CREATE POLL
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $poll = Poll::create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'description' => $request->description,
                'datetime_start' => $request->datetime_start,
                'datetime_end' => $request->datetime_end,
                'status' => $request->status,
                'visibility' => $request->visibility,
                'voting_method' => $request->voting_method
            ]);

            foreach ($request->categories as $cat) {
                $category = Category::create([
                    'poll_id' => $poll->id,
                    'name' => $cat['name'],
                    'description' => $cat['description'] ?? null
                ]);

                foreach ($cat['nominees'] as $nom) {
                    Nominee::create([
                        'category_id' => $category->id,
                        'name' => $nom['name'],
                        'image' => $nom['image'] ?? null
                    ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Poll created', 'poll' => $poll]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 📌 GET ALL POLLS
    public function index()
    {
        return Poll::with('categories.nominees')->latest()->get();
    }

    // 📌 GET SINGLE POLL
    public function show($id)
    {
        return Poll::with('categories.nominees')->findOrFail($id);
    }

    // 📌 UPDATE
    public function update(Request $request, $id)
    {
        $poll = Poll::findOrFail($id);

        $poll->update($request->all());

        return response()->json(['message' => 'Poll updated']);
    }

    // 📌 DELETE
    public function destroy($id)
    {
        Poll::findOrFail($id)->delete();

        return response()->json(['message' => 'Poll deleted']);
    }

    /**
     * Publish a poll (set status to 'active')
     */
    public function publish(Poll $poll)
    {
        // Ensure only the creator can publish
        if ($poll->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'You are not authorized to publish this poll'
            ], 403);
        }

        // Only draft polls can be published
        if ($poll->status !== 'draft') {
            return response()->json([
                'message' => 'Poll cannot be published. It is already ' . $poll->status
            ], 400);
        }

        $poll->status = 'active';
        $poll->save();

        return response()->json([
            'message' => 'Poll published successfully',
            'poll' => $poll
        ]);
    }

    public function sendOtp(Request $request)
    {
        $user = $request->user();

        // Log OTP send info to storage/logs/laravel.log
        \Log::info('Sending OTP to user:', [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
        ]);

        $otp = rand(1000, 9999);

        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(2);
        $user->save();

        // Simple mail (you can customize later)
        Mail::raw("Your OTP is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your Voting OTP');
        });

        return response()->json([
            'message' => 'OTP sent successfully'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required'
        ]);

        $user = $request->user();

        if (
            $user->otp !== $request->otp ||
            now()->gt($user->otp_expires_at)
        ) {
            return response()->json([
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        // Clear OTP after success
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'message' => 'OTP verified'
        ]);
    }


    public function verifyPassword(Request $request)
{
    $request->validate([
        'password' => 'required|string'
    ]);

    $user = $request->user();

    // 🔐 Check password
    if (!Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Incorrect password'
        ], 401);
    }

    return response()->json([
        'message' => 'Password verified successfully'
    ]);
}

}