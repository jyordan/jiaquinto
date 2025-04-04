<div class="max-w-2xl mx-auto p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md text-gray-700 dark:text-gray-300">
    <!-- Settings Table -->
    <h2 class="text-lg font-bold dark:text-gray-200 flex justify-between">
        Saved Settings
        <button wire:click="openModal" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
            + Add Setting
        </button>
    </h2>

    <table class="w-full border-collapse border border-gray-300 dark:border-gray-700 mt-2">
        <thead>
            <tr class="bg-gray-200 dark:bg-gray-700">
                <th class="border border-gray-300 dark:border-gray-700 p-2 dark:text-gray-200">Key</th>
                <th class="border border-gray-300 dark:border-gray-700 p-2 dark:text-gray-200">Value</th>
                <th class="border border-gray-300 dark:border-gray-700 p-2 dark:text-gray-200">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($settings as $setting)
                <tr class="border-gray-300 dark:border-gray-700">
                    <td class="border border-gray-300 dark:border-gray-700 p-2 dark:text-gray-200">{{ $setting->key }}
                    </td>
                    <td class="border border-gray-300 dark:border-gray-700 p-2 dark:text-gray-200">{{ $setting->value }}
                    </td>
                    <td class="border border-gray-300 dark:border-gray-700 p-2 flex space-x-2">
                        <button wire:click="editSetting({{ $setting->id }})"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded">
                            Edit
                        </button>
                        <button wire:click="deleteSetting({{ $setting->id }})"
                            class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">
                            Delete
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Modal -->
    @if ($showLogsModal)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
            <div class="bg-white dark:bg-gray-900 p-6 w-2/4 max-w-md rounded-lg shadow-lg relative">
                <h2 class="text-lg font-bold mb-4 dark:text-gray-200">
                    {{ $isEdit ? 'Edit Setting' : 'Add New Setting' }}
                </h2>

                <!-- Form -->
                <form wire:submit.prevent="saveSetting" class="space-y-2">
                    <input type="text" wire:model="key" placeholder="Key"
                        class="w-full border p-2 rounded bg-gray-50 dark:bg-gray-700 dark:text-white">
                    @error('key')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror

                    <input type="text" wire:model="value" placeholder="Value"
                        class="w-full border p-2 rounded bg-gray-50 dark:bg-gray-700 dark:text-white">
                    @error('value')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                            {{ $isEdit ? 'Update' : 'Save' }}
                        </button>

                        <button type="button" wire:click="resetForm"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Loader -->
    <div wire:loading class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-lg w-1/3">
            <span>Updating...</span>
        </div>
    </div>
</div>
