<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Flash Messages -->
            @if (session()->has('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <p class="text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            @if (session()->has('info'))
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-blue-700">{{ session('info') }}</p>
                </div>
            @endif

            <!-- Current Weights Info -->
            @if ($latestWeights)
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Current Active Weights</h3>
                            <p class="text-sm text-gray-600">
                                Created by: {{ $latestWeights->creator->name ?? 'Unknown' }} on
                                {{ $latestWeights->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-600 mb-1">Consistency Ratio</div>
                            <div
                                class="text-lg font-bold {{ $latestWeights->is_consistent ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($latestWeights->consistency_ratio, 4) }}
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-4 gap-4">
                        @foreach (['course_match', 'preference_match', 'distance_score', 'workload_score'] as $criterion)
                            <div class="text-center">
                                <div class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $criterion)) }}
                                </div>
                                <div class="text-xl font-bold text-gray-900">
                                    {{ number_format($latestWeights->getWeight($criterion) * 100, 1) }}%
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Pairwise Comparison Matrix -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Pairwise Comparison Matrix</h2>
                            <p class="text-sm text-gray-600 mt-1">
                                Compare each criterion with others. Values are automatically reciprocated.
                            </p>
                        </div>

                        <div class="p-6">
                            @if ($errorMessage)
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                                    <p class="text-red-700 text-sm">{{ $errorMessage }}</p>
                                </div>
                            @endif

                            <!-- AHP Scale Reference -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                                <h4 class="text-sm font-semibold text-blue-900 mb-2">AHP Scale Reference:</h4>
                                <div class="grid grid-cols-2 gap-2 text-xs text-blue-800">
                                    <div>1 = Equal Importance</div>
                                    <div>3 = Moderate Importance</div>
                                    <div>5 = Strong Importance</div>
                                    <div>7 = Very Strong Importance</div>
                                    <div>9 = Extreme Importance</div>
                                    <div>1/3, 1/5, 1/7, 1/9 = Inverse values</div>
                                </div>
                            </div>

                            <!-- Matrix Table -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">
                                            </th>
                                            @foreach ($criteriaLabels as $label)
                                                <th
                                                    class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">
                                                    {{ $label }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($matrix as $rowIndex => $row)
                                            <tr>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900 bg-gray-50">
                                                    {{ $criteriaLabels[$rowIndex] }}
                                                </td>
                                                @foreach ($row as $colIndex => $value)
                                                    <td class="px-4 py-3 text-center">
                                                        @if ($rowIndex == $colIndex)
                                                            <!-- Diagonal: Always 1 -->
                                                            <input type="text" value="1" disabled
                                                                class="w-20 text-center border border-gray-300 rounded-md px-2 py-1 text-sm bg-gray-100">
                                                        @elseif($rowIndex < $colIndex)
                                                            <!-- Upper triangle: Editable -->
                                                            <input type="number" step="0.01" min="0.11"
                                                                max="9"
                                                                wire:model.live.debounce.500ms="matrix.{{ $rowIndex }}.{{ $colIndex }}"
                                                                class="w-20 text-center border border-gray-300 rounded-md px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                placeholder="1.0">
                                                        @else
                                                            <!-- Lower triangle: Auto-calculated reciprocal (read-only) -->
                                                            <input type="text"
                                                                value="{{ number_format($value, 3) }}" disabled
                                                                class="w-20 text-center border border-gray-300 rounded-md px-2 py-1 text-sm bg-gray-100 text-gray-600">
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-6 flex gap-3">
                                <button wire:click="resetToDefault"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium transition">
                                    Reset to Default
                                </button>
                                <button wire:click="resetToEqual"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium transition">
                                    Reset to Equal (25% each)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Results -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Calculated Weights</h2>
                        </div>

                        <div class="p-6">
                            @if (!empty($calculatedWeights))
                                <!-- Consistency Ratio -->
                                <div class="mb-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Consistency Ratio (CR)</span>
                                        <span
                                            class="text-sm font-bold {{ $isConsistent ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $consistencyRatio ? number_format($consistencyRatio, 4) : 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $isConsistent ? 'bg-green-500' : 'bg-red-500' }}"
                                            style="width: {{ min(($consistencyRatio ?? 0) * 1000, 100) }}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $isConsistent ? '✓ Acceptable (CR < 0.1)' : '✗ Inconsistent (CR ≥ 0.1)' }}
                                    </p>
                                    @if ($lambdaMax)
                                        <p class="text-xs text-gray-400 mt-1">λ_max: {{ number_format($lambdaMax, 4) }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Weights Display -->
                                <div class="space-y-4">
                                    @foreach (['course_match', 'preference_match', 'distance_score', 'workload_score'] as $criterion)
                                        <div>
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ ucfirst(str_replace('_', ' ', $criterion)) }}
                                                </span>
                                                <span class="text-lg font-bold text-gray-900">
                                                    {{ $this->getWeightPercentage($criterion) }}
                                                </span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="h-2 rounded-full bg-blue-500"
                                                    style="width: {{ ($calculatedWeights[$criterion] ?? 0) * 100 }}%">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Save Button -->
                                <div class="mt-6">
                                    <button wire:click="saveWeights" @if (!$isConsistent) disabled @endif
                                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-sm font-medium transition">
                                        Save Weights
                                    </button>
                                    @if (!$isConsistent)
                                        <p class="text-xs text-red-600 mt-2 text-center">
                                            Fix consistency issues before saving
                                        </p>
                                    @endif
                                </div>
                            @else
                                <div class="text-center text-gray-500 py-8">
                                    <p class="text-sm">Enter pairwise comparisons to calculate weights</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Instructions Card -->
                    <div class="bg-white rounded-lg shadow mt-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Instructions</h3>
                        </div>
                        <div class="p-6">
                            <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600">
                                <li>Compare each criterion in the upper triangle of the matrix</li>
                                <li>Use values from 1 (equal) to 9 (extreme importance)</li>
                                <li>Lower triangle values are automatically calculated</li>
                                <li>Ensure Consistency Ratio (CR) is below 0.1</li>
                                <li>Click "Save Weights" to apply for supervisor assignments</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
