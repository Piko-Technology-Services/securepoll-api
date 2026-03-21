<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\Category;
use App\Models\Nominee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
}