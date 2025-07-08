<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Pesan
 *
 */
class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');

        $this->middleware('is_admin')->only(['index']);
    }

    /**

     *
     * @authenticated
     *
     * @response 201 {
     * "message": "Pesan berhasil dikirim!",
     * "data": {
     * "id": 1,
     * "sender_id": 2,
     * "receiver_id": 1,
     * "pesan": "Halo admin, saya ada pertanyaan tentang pesanan saya.",
     * "created_at": "2025-06-21T16:00:00.000000Z",
     * "updated_at": "2025-06-21T16:00:00.000000Z"
     * }
     * }
     *
     * @response 400 {
     * "message": "Anda tidak bisa mengirim pesan ke diri sendiri."
     * }
     * @response 403 {
     * "message": "Customer hanya bisa mengirim pesan ke admin."
     * }
     * @response 404 {
     * "message": "Penerima pesan tidak ditemukan."
     * }
     * @response 422 {
     * "message": "The given data was invalid.",
     * "errors": {
     * "pesan": [
     * "The pesan field is required."
     * ]
     * }
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'pesan' => 'required|string',
        ]);

        $sender = Auth::user(); 
        $receiver = User::find($request->receiver_id);

        if (!$receiver) {
            return response()->json(['message' => 'Penerima pesan tidak ditemukan.'], 404);
        }

        if ($sender->id === $receiver->id) {
            return response()->json(['message' => 'Anda tidak bisa mengirim pesan ke diri sendiri.'], 400);
        }

        if ($sender->role->nama === 'customer' && $receiver->role->nama !== 'admin') {
            return response()->json(['message' => 'Customer hanya bisa mengirim pesan ke admin.'], 403);
        }

        $message = Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $request->receiver_id,
            'pesan' => $request->pesan,
        ]);

        return response()->json([
            'message' => 'Pesan berhasil dikirim!',
            'data' => $message,
        ], 201);
    }

    /**
     *
     * @authenticated
     *
     * @response {
     * "data": [
     * {
     * "id": 1,
     * "sender_id": 2,
     * "receiver_id": 1,
     * "pesan": "Halo admin, bagaimana status pesanan saya?",
     * "created_at": "2025-06-21T15:00:00.000000Z",
     * "updated_at": "2025-06-21T15:00:00.000000Z",
     * "sender": {
     * "id": 2,
     * "nama": "Customer A",
     * "email": "customerA@example.com",
     * "role": { "nama": "customer" }
     * },
     * "receiver": {
     * "id": 1,
     * "nama": "Administrator",
     * "email": "admin@example.com",
     * "role": { "nama": "admin" }
     * }
     * }
     * ]
     * }
     *
     * @response 403 {
     * "message": "Anda tidak memiliki izin untuk melakukan tindakan ini."
     * }
     */
    public function index()
    {
        $messages = Message::with('sender.role', 'receiver.role')->get();
        return response()->json(['data' => $messages]);
    }

    /**
     *
     * @authenticated
     *
     * @response {
     * "data": [
     * {
     * "id": 1,
     * "sender_id": 2,
     * "receiver_id": 1,
     * "pesan": "Halo admin, bagaimana status pesanan saya?",
     * "created_at": "2025-06-21T15:00:00.000000Z",
     * "updated_at": "2025-06-21T15:00:00.000000Z",
     * "sender": { "id": 2, "nama": "Customer A" },
     * "receiver": { "id": 1, "nama": "Administrator" }
     * },
     * {
     * "id": 2,
     * "sender_id": 1,
     * "receiver_id": 2,
     * "pesan": "Pesanan Anda sedang dalam proses, mohon tunggu sebentar.",
     * "created_at": "2025-06-21T15:30:00.000000Z",
     * "updated_at": "2025-06-21T15:30:00.000000Z",
     * "sender": { "id": 1, "nama": "Administrator" },
     * "receiver": { "id": 2, "nama": "Customer A" }
     * }
     * ]
     * }
     *
     * @response 403 {
     * "message": "Hanya customer yang dapat melihat riwayat pesan mereka."
     * }
     */
    public function myMessages()
    {
        $user = Auth::user();
        if ($user->role->nama !== 'customer') {
            return response()->json(['message' => 'Hanya customer yang dapat melihat riwayat pesan mereka.'], 403);
        }

        $messages = Message::where('sender_id', $user->id)
                            ->orWhere('receiver_id', $user->id)
                            ->with('sender', 'receiver') 
                            ->orderBy('created_at', 'asc')
                            ->get();

        return response()->json(['data' => $messages]);
    }
}