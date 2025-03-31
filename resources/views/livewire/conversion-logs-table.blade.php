<div class="p-6 bg-white dark:bg-gray-800 shadow-md rounded">
    <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">Conversion Logs Table</h2>

    <!-- Search Bar -->
    <input type="text" wire:model="search"
        class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 p-2 rounded w-full mb-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        placeholder="Search by patient name, email, or contact email...">

    <!-- Logs Table -->
    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300 dark:border-gray-600">
            <thead class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                <tr>
                    <th class="border border-gray-300 dark:border-gray-600 p-2">Source</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2">Opportunity ID</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2">Patient ID</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2">Patient Name</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2">Patient Phone</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2">Patient Email</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2">Contact Name</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2">Contact Phone</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2">Contact Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="odd:bg-gray-100 dark:odd:bg-gray-800 even:bg-gray-50 dark:even:bg-gray-700">
                        <td class="border border-gray-300 dark:border-gray-600 p-2 text-gray-900 dark:text-gray-100">
                            {{ $log->source }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2">{{ $log->opportunity_id }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2">{{ $log->patient_id }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2">{{ $log->patient_name }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2">{{ $log->patient_phone }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2">{{ $log->patient_email }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2">{{ $log->contact_name }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2">{{ $log->contact_phone }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2">{{ $log->contact_email }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9"
                            class="border border-gray-300 dark:border-gray-600 p-2 text-center text-gray-900 dark:text-gray-100">
                            No conversion logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 text-gray-900 dark:text-gray-100">
        {{ $logs->links() }}
    </div>
</div>
