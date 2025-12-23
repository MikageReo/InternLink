<div>
    <!-- Header Section -->
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Partner Companies
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    List of companies with UMP students accepted
                </p>
            </div>

            <!-- Search -->
            <div class="w-full sm:w-auto">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search company name..."
                    class="w-full sm:w-64 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-200"
                />
            </div>
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
            <div class="flex items-center space-x-2">
                <span class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                    {{ $totalCompanies }}
                </span>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Total Partner Compan{{ $totalCompanies !== 1 ? 'ies' : 'y' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Company Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Company Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Students Accepted
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Location
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Last Accepted
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Avg Allowance
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Position Offered
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($rankings as $company)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $company->companyName }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $company->companyEmail ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="text-sm font-semibold text-purple-600 dark:text-purple-400">
                                    {{ $company->student_count }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">
                                    student{{ $company->student_count !== 1 ? 's' : '' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                @if($company->states)
                                    {{ explode(',', $company->states)[0] }}
                                @else
                                    N/A
                                @endif
                            </div>
                            @if($company->state_count > 1)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    +{{ $company->state_count - 1 }} more location{{ $company->state_count - 1 !== 1 ? 's' : '' }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $company->last_accepted ? \Carbon\Carbon::parse($company->last_accepted)->format('M Y') : 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                @if($company->avg_allowance)
                                    RM {{ number_format($company->avg_allowance, 2) }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                @if($company->positions)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(explode(',', $company->positions) as $position)
                                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">
                                                {{ trim($position) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">N/A</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No companies found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="p-6 border-t border-gray-200 dark:border-gray-700">
        {{ $rankings->links() }}
    </div>
</div>
