<?php

namespace App\Http\Controllers\Api;

use App\Models\Message;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\AuthenticationException;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api'); // Enforce JWT authentication on all methods
    }

    /**
     * @OA\Schema(
     *     schema="Message",
     *     type="object",
     *     required={"title", "body"},
     *     @OA\Property(property="id", type="integer", description="ID of the message"),
     *     @OA\Property(property="title", type="string", description="Title of the message"),
     *     @OA\Property(property="body", type="string", description="Body of the message"),
     *     @OA\Property(property="user_id", type="integer", description="ID of the user who created the message"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
     * )
    
     * @OA\Get(
     *     path="/api/messages",
     *     summary="Fetch messages based on user role",
     *     tags={"Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of messages",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Message"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index()
    {
        // Check if the user is an admin
        if (auth()->user()->hasRole('admin')) {
            // If admin, return all messages with the associated username
            $messages = Message::with('user:id,name')->get(); // Assuming 'name' is the column in 'users' table
        } else {
            // If not admin, return only the messages of the logged-in user
            $messages = Message::where('user_id', auth()->id())->get();
        }

        return response()->json($messages);
    }

    /**
     * @OA\Post(
     *     path="/api/messages",
     *     summary="Create a new message",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "body"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="body", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Message")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $message = Message::create([
            'title' => $request->title,
            'body' => $request->body,
            'user_id' => auth()->id(),
        ]);

        return response()->json($message, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/messages/{id}",
     *     summary="Get a specific message",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved the message",
     *         @OA\JsonContent(ref="#/components/schemas/Message")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show($id)
    {
        $message = Message::where('user_id', auth()->id())->find($id);

        if (!$message) {
            return response()->json(['error' => 'Message not found.'], 404);
        }

        return response()->json($message);
    }

    /**
     * @OA\Put(
     *     path="/api/messages/{id}",
     *     summary="Update a message",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "body"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="body", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated the message",
     *         @OA\JsonContent(ref="#/components/schemas/Message")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message not found"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $message = Message::where('user_id', auth()->id())->find($id);

        if (!$message) {
            return response()->json(['error' => 'Message not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $message->update([
            'title' => $request->title,
            'body' => $request->body,
        ]);

        return response()->json($message);
    }

    /**
     * @OA\Delete(
     *     path="/api/messages/{id}",
     *     summary="Delete a message",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted the message"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $message = Message::where('user_id', auth()->id())->find($id);

        if (!$message) {
            return response()->json(['error' => 'Message not found.'], 404);
        }

        $message->delete();
        return response()->json(['message' => 'Message deleted successfully.']);
    }
}
