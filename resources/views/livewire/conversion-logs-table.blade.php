<div class="p-6 bg-white dark:bg-gray-800 shadow-md rounded">
    <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">{{ $conversion->company_name }} Conversion Logs
        Table ({{ $logs->count() }})</h2>

    <!-- Search Bar -->
    <input type="text" wire:model.live="search"
        class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 p-2 rounded w-full mb-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        placeholder="Search by patient name, or email">

    <!-- Logs Table -->
    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300 dark:border-gray-600">
            <thead class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                <tr>
                    <th class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">Patient</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">Practitioner</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">Appointment Date</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">Appointment Status</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">Flow Direction</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">Converted At</th>
                    {{-- <th class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">Source</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">Opportunity ID</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">Patient ID</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">Patient Phone</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">Patient Email</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">Contact Name</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">Contact Phone</th>
                    <th class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">Contact Email</th> --}}
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="odd:bg-gray-100 dark:odd:bg-gray-800 even:bg-gray-50 dark:even:bg-gray-700">
                        <td class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">{{ $log->patient_name }}
                        </td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">
                            {{ $log->practitioner_name }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">
                            {{ $log->appointment_date }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">
                            {{ $log->appointment_status }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">
                            {{ $log->flow_direction }}</td>
                        <th class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">
                            {{ $log->converted_stamp }}</td>
                        {{-- <td class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap text-gray-900 dark:text-gray-100">
                        {{ $log->source }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">{{ $log->opportunity_id }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">{{ $log->patient_id }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">{{ $log->patient_phone }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">{{ $log->patient_email }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">{{ $log->contact_name }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">{{ $log->contact_phone }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 p-2 text-nowrap">{{ $log->contact_email }}</td> --}}
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
