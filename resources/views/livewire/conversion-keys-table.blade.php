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
                <th class="border border-gray-300 px-4 py-2">ID</th>
                <th class="border border-gray-300 px-4 py-2">Company Name</th>
                <th class="border border-gray-300 px-4 py-2">Cliniko API Key</th>
                <th class="border border-gray-300 px-4 py-2">GoHighLevel API Key</th>
                <th class="border border-gray-300 px-4 py-2">Cliniko App Type</th>
                <th class="border border-gray-300 px-4 py-2">GoHighLevel Pipeline</th>
                <th class="border border-gray-300 px-4 py-2">GoHighLevel Pipeline Target Stage</th>
                <th class="border border-gray-300 px-4 py-2">Status</th>
                <th class="border border-gray-300 px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($conversionKeys as $key)
                <tr>
                    <td class="border border-gray-300 px-4 py-2 text-wrap">{{ $key->id }}</td>
                    <td class="border border-gray-300 px-4 py-2 text-wrap">{{ $key->company_name }}</td>
                    <td class="border border-gray-300 px-4 py-2 text-wrap">
                        <div class="overflow-hidden whitespace-nowrap text-ellipsis" style="width: 200px">
                            {{ $key->cliniko_api_key }}
                        </div>
                    </td>
                    <td class="border border-gray-300 px-4 py-2 text-wrap">
                        <div class="overflow-hidden whitespace-nowrap text-ellipsis" style="width: 200px">
                            {{ $key->ghl_api_key }}</div>
                    </td>
                    <td class="border border-gray-300 px-4 py-2">{{ $key->cliniko_app_type_name }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $key->ghl_pipeline_name }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $key->ghl_pipeline_stage_name }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $key->active_at ?: 'Disabled' }}</td>
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
            <h2 class="text-lg text-black font-semibold mb-4">{{ $conversionKeyId ? 'Edit' : 'Add' }} Conversion Key
            </h2>

            <!-- Form -->
            <form wire:submit.prevent="save">
                <div class="mb-3">
                    <label class="block text-gray-700">Company Name</label>
                    <input type="text" wire:model.live="form.company_name"
                        class="text-black w-full p-2 border rounded">
                    <div class="mb-4 text-sm text-red-800 dark:text-red-400" role="alert">
                        @error('form.company_name')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-gray-700">Cliniko API Key</label>
                    <input type="text" wire:model.live="form.cliniko_api_key"
                        class="text-black w-full p-2 border rounded">
                    <div class="mb-4 text-sm text-red-800 dark:text-red-400" role="alert">
                        @error('form.cliniko_api_key')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-gray-700">GoHighLevel API Key</label>
                    <input type="text" wire:model.live="form.ghl_api_key"
                        class="text-black w-full p-2 border rounded">
                    <div class="mb-4 text-sm text-red-800 dark:text-red-400" role="alert">
                        @error('form.ghl_api_key')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-gray-700">Cliniko App Type</label>
                    <select wire:model.live="form.cliniko_app_type_id" class="text-black border p-2 rounded w-full"
                        @if (!$form['cliniko_api_key']) disabled @endif>
                        <option value="">Cliniko App Type</option>
                        @foreach ($optionClinikoAppTypes as $k => $appType)
                            <option value="{{ data_get($appType, 'id') }}"
                                wire:key="cliniko_app_type_id-{{ data_get($appType, 'id') }}"
                                {{ $form['cliniko_app_type_id'] == data_get($appType, 'id') ? 'selected' : '' }}>
                                {{ data_get($appType, 'name') }}</option>
                        @endforeach
                    </select>
                    <div class="mb-4 text-sm text-red-800 dark:text-red-400" role="alert">
                        @error('form.cliniko_app_type_id')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-gray-700">GoHighLevel Pipeline</label>
                    <select wire:model.live="form.ghl_pipeline_id" class="text-black border p-2 rounded w-full"
                        @if (!$form['ghl_api_key']) disabled @endif>
                        <option value="">GoHighLevel Pipeline</option>
                        @foreach ($optionGhlPipelines as $k => $pipeline)
                            <option value="{{ data_get($pipeline, 'id') }}"
                                wire:key="ghl_pipeline_id-{{ data_get($pipeline, 'id') }}"
                                {{ $form['ghl_pipeline_id'] == data_get($pipeline, 'id') ? 'selected' : '' }}>
                                {{ data_get($pipeline, 'name') }}</option>
                        @endforeach
                    </select>
                    <div class="mb-4 text-sm text-red-800 dark:text-red-400" role="alert">
                        @error('form.ghl_pipeline_id')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-gray-700">GoHighLevel Pipeline Target Stage</label>
                    <select wire:model="form.ghl_pipeline_stage_id" class="text-black border p-2 rounded w-full"
                        @if (!$form['ghl_pipeline_id']) disabled @endif>
                        <option value="">GoHighLevel Pipeline Target Stage</option>
                        @foreach ($optionGhlPipelineStages as $stage)
                            <option value="{{ data_get($stage, 'id') }}"
                                wire:key="pipeline_stage_{{ data_get($stage, 'id') }}"
                                {{ $form['ghl_pipeline_stage_id'] == data_get($stage, 'id') ? 'selected' : '' }}>
                                {{ data_get($stage, 'name') }}
                            </option>
                        @endforeach
                    </select>
                    <div class="mb-4 text-sm text-red-800 dark:text-red-400" role="alert">
                        @error('form.ghl_pipeline_stage_id')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <input type="checkbox" wire:model="form.active_at"
                        class="w-4 h-4 text-blue-600  border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2  dark:border-gray-600"
                        {{ $form['active_at'] ? 'checked' : '' }}>
                    <label for="default-checkbox" class="ms-2 text-sm font-medium text-black">Active</label>
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
