<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Conversion Keys</h2>
        <button wire:click="openModal" class="bg-blue-500 text-white px-4 py-2 rounded">Add Conversion Key</button>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-500 text-white px-4 py-2 mb-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- Table -->
    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-200 text-gray-700">
                <th class="border border-gray-300 px-4 py-2">Cliniko API Key</th>
                <th class="border border-gray-300 px-4 py-2">GHL API Key</th>
                <th class="border border-gray-300 px-4 py-2">Cliniko App Type ID</th>
                <th class="border border-gray-300 px-4 py-2">GHL Pipeline ID</th>
                <th class="border border-gray-300 px-4 py-2">GHL Pipeline Stage ID</th>
                <th class="border border-gray-300 px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($conversionKeys as $key)
                <tr>
                    <td class="border border-gray-300 px-4 py-2">{{ $key->cliniko_api_key }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $key->ghl_api_key }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $key->cliniko_app_type_id }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $key->ghl_pipeline_id }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $key->ghl_pipeline_stage_id }}</td>
                    <td class="border border-gray-300 px-4 py-2">
                        <button wire:click="openModal({{ $key->id }})"
                            class="bg-yellow-500 text-white px-3 py-1 rounded">Edit</button>
                        <button wire:click="confirmDelete({{ $key->id }})"
                            class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $conversionKeys->links() }}

    <!-- Modal -->
    <div
        class="{{ $showModal ? 'fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50' : 'hidden' }}">
        <div class="bg-white p-6 rounded-lg w-1/3">
            <h2 class="text-lg font-semibold mb-4">{{ $conversionKeyId ? 'Edit' : 'Add' }} Conversion Key</h2>

            <!-- Form -->
            <form wire:submit.prevent="save">
                <div class="mb-3">
                    <label class="block text-gray-700">Cliniko API Key</label>
                    <input type="text" wire:model="form.cliniko_api_key"
                        class="text-black w-full p-2 border rounded">
                </div>

                <div class="mb-3">
                    <label class="block text-gray-700">GHL API Key</label>
                    <input type="text" wire:model="form.ghl_api_key" class="text-black w-full p-2 border rounded">
                </div>

                <div class="mb-3">
                    <label class="block text-gray-700">Cliniko App Type ID</label>
                    <input type="text" wire:model="form.cliniko_app_type_id"
                        class="text-black w-full p-2 border rounded">
                </div>

                <div class="mb-3">
                    <label class="block text-gray-700">GHL Pipeline ID</label>
                    <input type="text" wire:model="form.ghl_pipeline_id"
                        class="text-black w-full p-2 border rounded">
                </div>

                <div class="mb-3">
                    <label class="block text-gray-700">GHL Pipeline Stage ID</label>
                    <input type="text" wire:model="form.ghl_pipeline_stage_id"
                        class="text-black w-full p-2 border rounded">
                </div>

                <div class="flex justify-end mt-4">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 bg-gray-500 text-white rounded mr-2">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
        class="{{ $showDeleteModal ? 'fixed inset-0 flex items-center justify-center bg-gray-800 text-white bg-opacity-50' : 'hidden' }}">
        <div class="bg-white p-6 rounded-lg w-1/3">
            <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>
            <p class="mb-4">Are you sure you want to delete this record? This action cannot be undone.</p>
            <div class="flex justify-end">
                <button type="button" wire:click="closeModal"
                    class="px-4 py-2 bg-gray-500 text-white rounded mr-2">Cancel</button>
                <button type="button" wire:click="delete"
                    class="px-4 py-2 bg-red-600 text-white rounded">Delete</button>
            </div>
        </div>
    </div>
</div>
