<?php

namespace App\Livewire;

use Livewire\Component;

class ChatContainer extends Component
{
    public $selectedUserId = null;

    protected $listeners = ['userSelected' => 'setUser'];

    public function setUser($userId)
    {
        $this->selectedUserId = $userId;
        \Log::info("ChatContainer updated selectedUserId to: " . $userId);
    }


    public function render()
    {
        return view('livewire.chat-container');
    }
}
