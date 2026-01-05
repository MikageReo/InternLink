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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
    </div>

    <!-- Chart Section -->
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="mb-4">
            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">
                Top Companies Ranking
            </h4>
            <div class="flex flex-wrap gap-2 mb-4 items-center">
                <button
                    wire:click="sort('student_count')"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ $sortBy === 'student_count' ? 'bg-purple-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}"
                >
                    Sort by Students
                </button>
                <button
                    wire:click="sort('avg_allowance')"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ $sortBy === 'avg_allowance' ? 'bg-purple-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}"
                >
                    Sort by Allowance
                </button>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Filter by Status:</label>
                    <select
                        wire:model.live="statusFilter"
                        class="px-3 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    >
                        <option value="">All Statuses</option>
                        <option value="Active">Active</option>
                        <option value="Downsize">Downsize</option>
                        <option value="Blacklisted">Blacklisted</option>
                        <option value="Closed Down">Closed Down</option>
                        <option value="Illegal">Illegal</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4" wire:key="chart-container-{{ $sortBy }}-{{ $sortDirection }}-{{ $statusFilter }}">
            @if($topCompanies->isNotEmpty())
                <div class="relative" style="height: 400px;">
                    <canvas id="companyChart"></canvas>
                </div>
            @else
                <div class="text-center text-gray-500 dark:text-gray-400 py-12">
                    No companies found to display in chart.
                </div>
            @endif
        </div>
    </div>

    @once
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endonce

    @if($topCompanies->isNotEmpty())
    <script>
        function renderCompanyChart() {
            const ctx = document.getElementById('companyChart');
            if (!ctx) {
                // Retry if canvas not ready
                setTimeout(renderCompanyChart, 50);
                return;
            }

            // Destroy existing chart if it exists
            if (window.currentCompanyChart) {
                window.currentCompanyChart.destroy();
                window.currentCompanyChart = null;
            }

            const companies = @json($chartData);
            const sortBy = @json($sortBy);
            const sortDirection = @json($sortDirection);

            // Prepare data based on sort type
            let labels = companies.map(c => {
                // Truncate long names
                return c.name.length > 20 ? c.name.substring(0, 20) + '...' : c.name;
            });

            let data = [];
            let label = '';
            let backgroundColor = [];

            if (sortBy === 'student_count') {
                data = companies.map(c => c.students);
                label = 'Students Accepted';
            } else if (sortBy === 'avg_allowance') {
                data = companies.map(c => c.allowance);
                label = 'Average Allowance (RM)';
            } else if (sortBy === 'status') {
                data = companies.map(c => c.students);
                label = 'Students by Status';
            } else {
                data = companies.map(c => c.students);
                label = 'Students Accepted';
            }

            // Color code bars by company status
            backgroundColor = companies.map(c => {
                if (c.status === 'Active') return 'rgba(34, 197, 94, 0.8)'; // green
                if (c.status === 'Downsize') return 'rgba(234, 179, 8, 0.8)'; // yellow
                if (c.status === 'Blacklisted') return 'rgba(239, 68, 68, 0.8)'; // red
                if (c.status === 'Closed Down') return 'rgba(107, 114, 128, 0.8)'; // gray
                return 'rgba(249, 115, 22, 0.8)'; // orange for Illegal
            });

                // Check if dark mode is active
                const isDark = document.documentElement.classList.contains('dark');
                const textColor = isDark ? 'rgba(229, 231, 235, 1)' : 'rgba(17, 24, 39, 1)';
                const gridColor = isDark ? 'rgba(75, 85, 99, 0.3)' : 'rgba(229, 231, 235, 0.5)';

                // Create and store chart instance
                window.currentCompanyChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: backgroundColor,
                        borderColor: companies.map(c => {
                            if (c.status === 'Active') return 'rgba(34, 197, 94, 1)';
                            if (c.status === 'Downsize') return 'rgba(234, 179, 8, 1)';
                            if (c.status === 'Blacklisted') return 'rgba(239, 68, 68, 1)';
                            if (c.status === 'Closed Down') return 'rgba(107, 114, 128, 1)';
                            return 'rgba(249, 115, 22, 1)';
                        }),
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                color: textColor,
                                font: {
                                    size: 12,
                                    weight: '500'
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: isDark ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                            titleColor: textColor,
                            bodyColor: textColor,
                            borderColor: isDark ? 'rgba(75, 85, 99, 1)' : 'rgba(229, 231, 235, 1)',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                title: function(context) {
                                    const index = context[0].dataIndex;
                                    return companies[index].name;
                                },
                                label: function(context) {
                                    if (sortBy === 'avg_allowance') {
                                        return 'Average Allowance: RM ' + context.parsed.y.toFixed(2);
                                    } else {
                                        return 'Students: ' + context.parsed.y;
                                    }
                                },
                                afterLabel: function(context) {
                                    const index = context.dataIndex;
                                    return 'Status: ' + companies[index].status;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: textColor,
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    if (sortBy === 'avg_allowance') {
                                        return 'RM ' + value.toFixed(0);
                                    }
                                    return value;
                                }
                            },
                            grid: {
                                color: gridColor,
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                color: textColor,
                                font: {
                                    size: 11
                                },
                                maxRotation: 45,
                                minRotation: 45
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    animation: {
                        duration: 750,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }

        // Function to initialize chart rendering
        function initChartRendering() {
            // Initial render
            setTimeout(renderCompanyChart, 100);

            // Watch for chart container changes using MutationObserver
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1 && node.querySelector && node.querySelector('#companyChart')) {
                                setTimeout(renderCompanyChart, 100);
                            }
                        });
                    }
                });
            });

            // Observe the parent container for changes
            const chartSection = document.querySelector('.p-6.border-b');
            if (chartSection) {
                observer.observe(chartSection, {
                    childList: true,
                    subtree: true
                });
            }

            // Also use Livewire hooks as backup
            if (typeof Livewire !== 'undefined') {
                document.addEventListener('livewire:init', () => {
                    Livewire.hook('morph.updated', () => {
                        if (document.getElementById('companyChart')) {
                            setTimeout(renderCompanyChart, 150);
                        }
                    });
                });
            }

            // Listen for any Livewire updates
            document.addEventListener('livewire:update', () => {
                if (document.getElementById('companyChart')) {
                    setTimeout(renderCompanyChart, 150);
                }
            });
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initChartRendering);
        } else {
            initChartRendering();
        }
    </script>
    @endif

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                        wire:click="sort('companyName')">
                        <div class="flex items-center space-x-1">
                            <span>Company Name</span>
                            @if($sortBy === 'companyName')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Company Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                        wire:click="sort('student_count')">
                        <div class="flex items-center space-x-1">
                            <span>Students Accepted</span>
                            @if($sortBy === 'student_count')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                        wire:click="sort('status')">
                        <div class="flex items-center space-x-1">
                            <span>Status</span>
                            @if($sortBy === 'status')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Location
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                        wire:click="sort('avg_allowance')">
                        <div class="flex items-center space-x-1">
                            <span>Avg Allowance</span>
                            @if($sortBy === 'avg_allowance')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Position Offered
                    </th>
                    @if($this->isCoordinator())
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    @endif
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($company->status === 'Active') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                @elseif($company->status === 'Downsize') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                @elseif($company->status === 'Blacklisted') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                @elseif($company->status === 'Closed Down') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400
                                @else bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                                @endif">
                                {{ $company->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                @if($company->states)
                                    {{ explode(',', $company->states)[0] }}
                                @else
                                    {{ $company->companyState ?? 'N/A' }}
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
                        @if($this->isCoordinator())
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button
                                    wire:click="editCompany({{ $company->companyID }})"
                                    class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 text-sm font-medium"
                                >
                                    Edit
                                </button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $this->isCoordinator() ? '8' : '7' }}" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
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

    <!-- Edit Company Modal -->
    @if($editingCompanyId)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="cancelEdit">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Edit Company</h3>
                        <button wire:click="cancelEdit" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    @if(session()->has('message'))
                        <div class="mb-4 p-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 rounded">
                            {{ session('message') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="updateCompany">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company Name *</label>
                                <input type="text" wire:model="editCompanyName"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200">
                                @error('editCompanyName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company Email</label>
                                <input type="email" wire:model="editCompanyEmail"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200">
                                @error('editCompanyEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company Number</label>
                                <input type="text" wire:model="editCompanyNumber"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200">
                                @error('editCompanyNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address Line</label>
                                <input type="text" wire:model="editCompanyAddressLine"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">City</label>
                                    <input type="text" wire:model="editCompanyCity"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Postcode</label>
                                    <input type="text" wire:model="editCompanyPostcode"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">State</label>
                                    <input type="text" wire:model="editCompanyState"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                                    <input type="text" wire:model="editCompanyCountry"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status *</label>
                                <select wire:model="editStatus"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200">
                                    <option value="Active">Active</option>
                                    <option value="Downsize">Downsize</option>
                                    <option value="Blacklisted">Blacklisted</option>
                                    <option value="Closed Down">Closed Down</option>
                                    <option value="Illegal">Illegal</option>
                                </select>
                                @error('editStatus') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" wire:click="cancelEdit"
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                                Update Company
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
