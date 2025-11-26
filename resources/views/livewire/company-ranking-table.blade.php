<div>
    <!-- Header Section -->
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Partner Companies Ranking
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Companies ranked by number of UMP students accepted
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

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Rank
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer"
                        wire:click="sortBy('companyName')">
                        <div class="flex items-center space-x-1">
                            <span>Company Name</span>
                            @if($sortField === 'companyName')
                                <x-heroicon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="h-4 w-4" />
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer"
                        wire:click="sortBy('student_count')">
                        <div class="flex items-center space-x-1">
                            <span>Students Accepted</span>
                            @if($sortField === 'student_count')
                                <x-heroicon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="h-4 w-4" />
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Location
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer"
                        wire:click="sortBy('last_accepted')">
                        <div class="flex items-center space-x-1">
                            <span>Last Accepted</span>
                            @if($sortField === 'last_accepted')
                                <x-heroicon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="h-4 w-4" />
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer"
                        wire:click="sortBy('avg_allowance')">
                        <div class="flex items-center space-x-1">
                            <span>Avg Allowance</span>
                            @if($sortField === 'avg_allowance')
                                <x-heroicon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="h-4 w-4" />
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($rankings as $company)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    #{{ $company->rank }}
                                </span>
                                @if($badge = $this->getRankingBadge($company->rank))
                                    <span class="px-2 py-1 text-xs rounded-full {{ $badge['class'] }}">
                                        {{ $badge['icon'] }} {{ $badge['text'] }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $company->companyName }}
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button
                                wire:click="viewCompanyDetails('{{ $company->companyName }}')"
                                class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300">
                                View Details
                            </button>
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

    <!-- Company Detail Modal -->
    @if($showDetailModal && $selectedCompany)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
             wire:click="closeDetailModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800"
                 wire:click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $selectedCompany['companyName'] }} - Details
                    </h3>
                    <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon name="x-mark" class="h-6 w-6" />
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Statistics -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                {{ $selectedCompany['totalStudents'] }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Students</div>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {{ $selectedCompany['acceptanceRate'] }}%
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Acceptance Rate</div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ $selectedCompany['firstAccepted'] ? \Carbon\Carbon::parse($selectedCompany['firstAccepted'])->format('Y') : 'N/A' }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">First Partnership</div>
                        </div>
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                @if($selectedCompany['averageAllowance'])
                                    RM {{ number_format($selectedCompany['averageAllowance'], 0) }}
                                @else
                                    N/A
                                @endif
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Avg Allowance</div>
                        </div>
                    </div>

                    <!-- Positions Offered -->
                    @if($selectedCompany['positions']->count() > 0)
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Positions Offered</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($selectedCompany['positions'] as $position)
                                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">
                                        {{ $position->position }} ({{ $position->count }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Work Methods -->
                    @if($selectedCompany['workMethods']->count() > 0)
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Work Methods</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($selectedCompany['workMethods'] as $method)
                                    <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/20 rounded-full text-sm">
                                        {{ $method->methodOfWork }} ({{ $method->count }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Students List -->
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Accepted Students ({{ $selectedCompany['students']->count() }})
                        </h4>
                        <div class="max-h-64 overflow-y-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Student</th>
                                        <th class="px-4 py-2 text-left">Position</th>
                                        <th class="px-4 py-2 text-left">Period</th>
                                        <th class="px-4 py-2 text-left">Allowance</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($selectedCompany['students'] as $student)
                                        <tr>
                                            <td class="px-4 py-2">{{ $student['name'] }}</td>
                                            <td class="px-4 py-2">{{ $student['position'] }}</td>
                                            <td class="px-4 py-2">
                                                {{ $student['startDate'] }} to {{ $student['endDate'] }}
                                            </td>
                                            <td class="px-4 py-2">
                                                @if($student['allowance'])
                                                    RM {{ number_format($student['allowance'], 2) }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
