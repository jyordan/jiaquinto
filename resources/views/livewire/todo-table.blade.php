<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">To-Do List (No DB)</h1>

    <!-- Add New Task -->
    <div class="mb-4 flex gap-2">
        <input type="text" wire:model="newTodo.name" placeholder="Name" class="text-black border p-2 rounded w-1/4">
        <input type="text" wire:model="newTodo.task" placeholder="Task" class="text-black border p-2 rounded w-2/4">

        <!-- Country Select -->
        <select wire:model.live="newTodo.country" class="text-black border p-2 rounded w-1/4">
            <option value="">Select Country</option>
            @foreach ($countries as $country => $v)
                <option value="{{ $country }}">{{ $country }}</option>
            @endforeach
        </select>

        <!-- State Select -->
        <select wire:model.live="newTodo.state" class="text-black border p-2 rounded w-1/4"
            @if (!$newTodo['country']) disabled @endif>
            <option value="">Select State</option>
            @if ($newTodo['country'])
                @foreach ($countries[$newTodo['country']] as $state)
                    <option value="{{ $state }}">{{ $state }}</option>
                @endforeach
            @endif
        </select>

        <!-- City Select -->
        <select wire:model="newTodo.city" class="text-black border p-2 rounded w-1/4"
            @if (!$newTodo['state']) disabled @endif>
            <option value="">Select City</option>
            @if ($newTodo['state'])
                @foreach ($states[$newTodo['state']] as $city)
                    <option value="{{ $city }}">{{ $city }}</option>
                @endforeach
            @endif
        </select>

        <button wire:click="add" class="bg-green-500 px-4 py-2 rounded">Add Task</button>
    </div>

    <!-- Table -->
    <table class="table-auto w-full border-collapse border border-gray-300">
        <thead>
            <tr>
                <th class="border px-4 py-2">ID</th>
                <th class="border px-4 py-2">Name</th>
                <th class="border px-4 py-2">Task</th>
                <th class="border px-4 py-2">Country</th>
                <th class="border px-4 py-2">State</th>
                <th class="border px-4 py-2">City</th>
                <th class="border px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($todos as $todo)
                <tr class="border">
                    <td class="border px-4 py-2">{{ $todo['id'] }}</td>

                    @if ($editTodoId === $todo['id'])
                        <!-- Editable Row -->
                        <td class="border px-4 py-2">
                            <input type="text" wire:model="editForm.name" class="text-black w-full border px-2 py-1">
                        </td>
                        <td class="border px-4 py-2">
                            <input type="text" wire:model="editForm.task" class="text-black w-full border px-2 py-1">
                        </td>
                        <td class="border px-4 py-2">
                            <select wire:model.live="editForm.country" class="text-black border p-2 rounded w-full">
                                <option value="">Select Country</option>
                                @foreach ($countries as $country => $v)
                                    <option value="{{ $country }}">{{ $country }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="border px-4 py-2">
                            <select wire:model.live="editForm.state" class="text-black border p-2 rounded w-full"
                                @if (!$editForm['country']) disabled @endif>
                                <option value="">Select State</option>
                                @if ($editForm['country'])
                                    @foreach ($countries[$editForm['country']] as $state)
                                        <option value="{{ $state }}">{{ $state }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td class="border px-4 py-2">
                            <select wire:model="editForm.city" class="text-black border p-2 rounded w-full"
                                @if (!$editForm['state']) disabled @endif>
                                <option value="">Select City</option>
                                @if ($editForm['state'])
                                    @foreach ($states[$editForm['state']] as $city)
                                        <option value="{{ $city }}">{{ $city }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td class="border px-4 py-2">
                            <button wire:click="save" class="bg-green-500 px-2 py-1 rounded">Save</button>
                            <button wire:click="cancel" class="bg-gray-500 px-2 py-1 rounded">Cancel</button>
                        </td>
                    @else
                        <!-- Static Row -->
                        <td class="border px-4 py-2">{{ $todo['name'] }}</td>
                        <td class="border px-4 py-2">{{ $todo['task'] }}</td>
                        <td class="border px-4 py-2">{{ $todo['country'] }}</td>
                        <td class="border px-4 py-2">{{ $todo['state'] }}</td>
                        <td class="border px-4 py-2">{{ $todo['city'] }}</td>
                        <td class="border px-4 py-2">
                            <button wire:click="edit({{ $todo['id'] }})"
                                class="bg-blue-500 px-2 py-1 rounded">Edit</button>
                            <button wire:click="delete({{ $todo['id'] }})"
                                class="bg-red-500 px-2 py-1 rounded">Delete</button>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
