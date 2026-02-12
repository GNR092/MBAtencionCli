<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Session;

class AdminChatController extends Controller
{
    public function showUserChatDirectory(Request $request)
    {
        $query = User::where('role', 'usuario');

        
        if ($request->has('search') && $request->input('search') != '') {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }

        
        $sort = $request->input('sort', 'asc');
        if ($sort === 'recent') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort === 'desc') {
            $query->orderBy('name', 'desc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $users = $query->get();

        return view('admin_user_chat_directory', [
            'users' => $users,
            'search' => $request->input('search'),
            'sort' => $sort
        ]);
    }

    public function getMessages($userId)
    {
        $currentAdminId = Session::get('user')->id;
        $allAdminIds = User::where('role', 'administrador')->pluck('id')->toArray();

        $messages = Message::with('sender:id,name,role')
            ->where(function ($query) use ($userId, $allAdminIds) {
                
                
                $query->where('sender_id', $userId)
                    ->whereIn('receiver_id', array_merge([0], $allAdminIds));
            })->orWhere(function ($query) use ($userId, $allAdminIds) {
                
                $query->whereIn('sender_id', $allAdminIds)
                    ->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        
        
        Message::where('sender_id', $userId)
            ->whereIn('receiver_id', [0, $currentAdminId])
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($messages);
    }

    public function sendMessage(Request $request, $userId)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $adminId = Session::get('user')->id;

        $message = Message::create([
            'sender_id' => $adminId,
            'receiver_id' => $userId, 
            'message' => $request->input('message'),
        ]);

        return response()->json($message);
    }
}
