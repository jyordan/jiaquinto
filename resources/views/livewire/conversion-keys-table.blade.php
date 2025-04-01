<div class="w-full mx-auto p-6 text-gray-700 dark:text-gray-300 overflow-auto">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Conversion Keys</h2>
        <button wire:click="openModal" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            Add Conversion Key
        </button>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-500 text-white px-4 py-2 mb-4 dark:bg-green-700">
            {{ session('message') }}
        </div>
    @endif

    @if (app()->environment('local'))
        <div class="flex justify-center mt-4">
            <span wire:loading>Loading...</span>
        </div>
    @endif

    <!-- Table -->
    <table class="w-full border-collapse border border-gray-300 dark:border-gray-700">
        <thead>
            <tr class="bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">ID</th>
                <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">Company Name</th>
                <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">Cliniko API Key</th>
                <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">GoHighLevel API Key</th>
                <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">Cliniko App Type</th>
                <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">GoHighLevel Pipeline</th>
                <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">GoHighLevel Pipeline Stage Source</th>
                <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">GoHighLevel Pipeline Stage Target</th>
                <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">Lead
                    Count</th>
                <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">Customer
                    Count</th>
                <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">Status</th>
                <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($conversionKeys as $key)
                <tr>
                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-wrap">{{ $key->id }}</td>
                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-wrap">{{ $key->company_name }}
                    </td>
                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-wrap">
                        <div class="overflow-hidden whitespace-nowrap text-ellipsis" style="width: 100px">
                            {{ $key->cliniko_api_key }}
                        </div>
                    </td>
                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-wrap">
                        <div class="overflow-hidden whitespace-nowrap text-ellipsis" style="width: 100px">
                            {{ $key->ghl_api_key }}</div>
                    </td>
                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">{{ $key->cliniko_app_type_name }}
                    </td>
                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">{{ $key->ghl_pipeline_name }}</td>
                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                        {{ $key->ghl_pipeline_stage_source_name }}</td>
                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                        {{ $key->ghl_pipeline_stage_target_name }}</td>
                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                        {{ $key->ghl_pipeline_stage_source_count }}</td>
                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                        {{ $key->ghl_pipeline_stage_target_count }}</td>
                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                        {{ $key->active_at ? 'Enable' : 'Disable' }}</td>
                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                        <button wire:click="openLogsModal({{ $key->id }})"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">Logs</button>
                        <button wire:click="openModal({{ $key->id }})"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">Edit</button>
                        <button wire:click="confirmDelete({{ $key->id }})"
                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $conversionKeys->links() }}

    <!-- Modal -->
    <div
        class="{{ $showModal ? 'fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50' : 'hidden' }}">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-lg w-1/3">
            <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                {{ $conversionKeyId ? 'Edit' : 'Add' }} Conversion Key
            </h2>

            <!-- Form -->
            <form wire:submit.prevent="save">
                <div class="mb-3">
                    <label class="block" for="form-company_name">Company Name</label>
                    <input type="text" wire:model.live="form.company_name" id="form-company_name"
                        class="w-full p-2 border rounded text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                    <div class="mb-4 text-sm text-red-800 dark:text-red-400" role="alert">
                        @error('form.company_name')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block" for="form-cliniko_api_key">Cliniko API Key</label>
                    <input type="text" wire:model.live="form.cliniko_api_key" id="form-cliniko_api_key"
                        class="w-full p-2 border rounded text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                    <div class="mb-4 text-sm text-red-800 dark:text-red-400" role="alert">
                        @error('form.cliniko_api_key')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block" for="form-ghl_api_key">GoHighLevel API Key</label>
                    <input type="text" wire:model.live="form.ghl_api_key" id="form-ghl_api_key"
                        class="w-full p-2 border rounded text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                    <div class="mb-4 text-sm text-red-800 dark:text-red-400" role="alert">
                        @error('form.ghl_api_key')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block" for="form-cliniko_app_type_id">Cliniko App Type</label>
                    <select wire:model="form.cliniko_app_type_id" id="form-cliniko_app_type_id"
                        class="w-full p-2 border rounded text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600"
                        @if (!$form['cliniko_api_key']) disabled @endif>
                        <option value="" disabled>Choose Cliniko App Type</option>
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
                    <label class="block" for="form-ghl_pipeline_id">GoHighLevel Pipeline</label>
                    <select wire:model.live="form.ghl_pipeline_id" id="form-ghl_pipeline_id"
                        class="w-full p-2 border rounded text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600"
                        @if (!$form['ghl_api_key']) disabled @endif>
                        <option value="" disabled>Choose GoHighLevel Pipeline</option>
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
                    <label class="block" for="form-ghl_pipeline_stage_source_id">GoHighLevel Pipeline Stage Source
                        (Lead)</label>
                    <select wire:model="form.ghl_pipeline_stage_source_id" id="form-ghl_pipeline_stage_source_id"
                        class="w-full p-2 border rounded text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600"
                        @if (!$form['ghl_pipeline_id']) disabled @endif>
                        <option value="" disabled>Choose GoHighLevel Pipeline Target Stage</option>
                        @foreach ($optionGhlPipelineStages as $stage)
                            <option value="{{ data_get($stage, 'id') }}"
                                wire:key="pipeline_stage_{{ data_get($stage, 'id') }}"
                                {{ $form['ghl_pipeline_stage_source_id'] == data_get($stage, 'id') ? 'selected' : '' }}>
                                {{ data_get($stage, 'name') }}
                            </option>
                        @endforeach
                    </select>
                    <div class="mb-4 text-sm text-red-800 dark:text-red-400" role="alert">
                        @error('form.ghl_pipeline_stage_source_id')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block" for="form-ghl_pipeline_stage_target_id">GoHighLevel Pipeline Stage Target
                        (Customer)</label>
                    <select wire:model="form.ghl_pipeline_stage_target_id" id="form-ghl_pipeline_stage_target_id"
                        class="w-full p-2 border rounded text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600"
                        @if (!$form['ghl_pipeline_id']) disabled @endif>
                        <option value="" disabled>Choose GoHighLevel Pipeline Target Stage</option>
                        @foreach ($optionGhlPipelineStages as $stage)
                            <option value="{{ data_get($stage, 'id') }}"
                                wire:key="pipeline_stage_{{ data_get($stage, 'id') }}"
                                {{ $form['ghl_pipeline_stage_target_id'] == data_get($stage, 'id') ? 'selected' : '' }}>
                                {{ data_get($stage, 'name') }}
                            </option>
                        @endforeach
                    </select>
                    <div class="mb-4 text-sm text-red-800 dark:text-red-400" role="alert">
                        @error('form.ghl_pipeline_stage_target_id')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <input type="checkbox" id="form-active_at" wire:model="form.active_at"
                        class="w-4 h-4 text-blue-600  border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2  dark:border-gray-600"
                        {{ !!$form['active_at'] ? 'checked' : '' }}>
                    <label for="form-active_at" class="ms-2 text-sm font-medium">Active</label>
                    <div class="mb-4 text-sm text-red-800 dark:text-red-400" role="alert">
                        @error('form.active_at')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="flex justify-center mt-4">
                    <span wire:loading>Updating...</span>
                </div>

                <div class="flex justify-end mt-4">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 bg-gray-500 rounded mr-2">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
        class="{{ $showDeleteModal ? 'fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50' : 'hidden' }}">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-lg w-1/3">
            <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Confirm Delete</h2>
            <p class="mb-4 text-gray-800 dark:text-gray-300">Are you sure you want to delete this record? This action
                cannot be undone.</p>
            <div class="flex justify-end">
                <button type="button" wire:click="closeModal"
                    class="px-4 py-2 bg-gray-500 text-white rounded mr-2">Cancel</button>
                <button type="button" wire:click="delete"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded">Delete</button>
            </div>
        </div>
    </div>

    @if ($showLogsModal)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
            <div class="bg-white dark:bg-gray-900 p-6 w-full h-full max-w-full max-h-full overflow-auto flex flex-col">
                <!-- Close button -->
                <div class="flex justify-end w-full mb-4">
                    <button wire:click="closeLogsModal" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal content -->
                @livewire('conversion-logs-table', ['conversionId' => $selectedConversionId])
            </div>
        </div>
    @endif

</div>
