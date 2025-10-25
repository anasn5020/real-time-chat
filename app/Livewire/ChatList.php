<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ChatList extends Component
{
    public $selectedUser = null;

    public function mount()
    {
        // Optionally select the first user (excluding self) on load
        $this->selectedUser = User::where('id', '!=', auth()->id())->first()?->id;
    }

    public function selectUser($userId)
    {
        Log::info("User selected: " . $userId);
        $this->selectedUser = $userId;
        // Emit an event to the ChatBox component to change the active chat
        $this->dispatch('userSelected', $userId);
    }

    public function render()
    {
        $users = User::where('id', '!=', auth()->id())->get();

        return view('livewire.chat-list', [
            'users' => $users,
        ]);
    }
}
