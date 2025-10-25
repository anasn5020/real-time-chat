<div class="p-4 h-full overflow-y-auto">
    <h3 class="text-lg font-semibold mb-4">Users</h3>
    <ul>
        @foreach ($users as $user)
            <li
                wire:click="selectUser({{ $user->id }})"
                class="p-3 cursor-pointer rounded-lg mb-2 transition-colors
                {{ $selectedUser == $user->id ? 'bg-indigo-600 text-white' : 'hover:bg-gray-100' }}"
            >
                {{ $user->name }}
            </li>
        @endforeach
    </ul>
</div>
