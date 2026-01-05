<div>
    <style>
        /* Slider Styling */
        input[type="range"].slider {
            -webkit-appearance: none;
            appearance: none;
            background: transparent;
            cursor: pointer;
        }

        input[type="range"].slider::-webkit-slider-track {
            background: #e5e7eb;
            height: 8px;
            border-radius: 4px;
        }

        .dark input[type="range"].slider::-webkit-slider-track {
            background: #374151;
        }

        input[type="range"].slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            background: #3b82f6;
            height: 20px;
            width: 20px;
            border-radius: 50%;
            margin-top: -6px;
            transition: background 0.2s;
        }

        input[type="range"].slider::-webkit-slider-thumb:hover {
            background: #2563eb;
        }

        .dark input[type="range"].slider::-webkit-slider-thumb {
            background: #60a5fa;
        }

        .dark input[type="range"].slider::-webkit-slider-thumb:hover {
            background: #3b82f6;
        }

        input[type="range"].slider::-moz-range-track {
            background: #e5e7eb;
            height: 8px;
            border-radius: 4px;
        }

        .dark input[type="range"].slider::-moz-range-track {
            background: #374151;
        }

        input[type="range"].slider::-moz-range-thumb {
            background: #3b82f6;
            height: 20px;
            width: 20px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: background 0.2s;
        }

        input[type="range"].slider::-moz-range-thumb:hover {
            background: #2563eb;
        }

        .dark input[type="range"].slider::-moz-range-thumb {
            background: #60a5fa;
        }

        .dark input[type="range"].slider::-moz-range-thumb:hover {
            background: #3b82f6;
        }
    </style>
    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">

            <!-- Flash Messages -->
            @if (session()->has('success'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
                    <p class="text-green-700 dark:text-green-300">{{ session('success') }}</p>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                    <p class="text-red-700 dark:text-red-300">{{ session('error') }}</p>
                </div>
            @endif

            @if (session()->has('info'))
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                    <p class="text-blue-700 dark:text-blue-300">{{ session('info') }}</p>
                </div>
            @endif

            <!-- Current Weights Info -->
            @if ($latestWeightsData)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Current Active Weights</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Created by: {{ $latestWeightsData['creator_name'] }} on
                                {{ $latestWeightsData['created_at'] }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Consistency Ratio</div>
                            <div
                                class="text-lg font-bold {{ $latestWeightsData['is_consistent'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ number_format($latestWeightsData['consistency_ratio'], 4) }}
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-4 gap-4">
                        @foreach (['course_match', 'preference_match', 'distance_score', 'workload_score'] as $criterion)
                            <div class="text-center">
                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $criterion)) }}
                                </div>
                                <div class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ number_format(($latestWeightsData['weights'][$criterion] ?? 0) * 100, 1) }}%
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Configuration Interface -->
                <div class="lg:col-span-2">
                    <!-- Simple Mode: Slider Interface -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Weight Configuration</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Select the importance level for each factor. Weights will automatically normalize to 100%.
                            </p>
                        </div>

                        <div class="p-6 space-y-6">
                            @foreach (['course_match' => 'Course Match', 'preference_match' => 'Preference Match', 'distance_score' => 'Distance Score', 'workload_score' => 'Workload Score'] as $key => $label)
                                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ $label }}
                                        </label>
                                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                            {{ number_format($directWeights[$key] ?? 0, 1) }}%
                                        </span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <select wire:model.live="importanceLevels.{{ $key }}" 
                                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="very_high">Very High</option>
                                            <option value="high">High</option>
                                            <option value="medium">Medium</option>
                                            <option value="low">Low</option>
                                        </select>
                                    </div>
                                    
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        @if ($key === 'course_match')
                                            How important is matching the student's program with the supervisor's
                                            program?
                                        @elseif($key === 'preference_match')
                                            How important is matching the supervisor's travel preference with the
                                            placement location?
                                        @elseif($key === 'distance_score')
                                            How important is the distance between supervisor and placement location?
                                        @elseif($key === 'workload_score')
                                            How important is the supervisor's current workload (availability)?
                                        @endif
                                    </p>
                                </div>
                            @endforeach

                            <!-- Total Display -->
                            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-blue-900 dark:text-blue-300">Total Weight:</span>
                                    <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                        {{ number_format(array_sum($directWeights), 1) }}%
                                    </span>
                                </div>
                                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                    Weights are automatically normalized to 100% total
                                </p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-6 flex gap-3">
                                <button wire:click="resetToEqual"
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-sm font-medium transition">
                                    Reset to Equal (25% each)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Results -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Calculated Weights</h2>
                        </div>

                        <div class="p-6">
                            @if (!empty($calculatedWeights))

                                <!-- Weights Display -->
                                <div class="space-y-4">
                                    @foreach (['course_match', 'preference_match', 'distance_score', 'workload_score'] as $criterion)
                                        <div>
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ ucfirst(str_replace('_', ' ', $criterion)) }}
                                                </span>
                                                <span class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                                    {{ $this->getWeightPercentage($criterion) }}
                                                </span>
                                            </div>
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                <div class="h-2 rounded-full bg-blue-500 dark:bg-blue-400"
                                                    style="width: {{ ($calculatedWeights[$criterion] ?? 0) * 100 }}%">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Save Button -->
                                <div class="mt-6">
                                    <button wire:click="saveWeights" wire:loading.attr="disabled"
                                        wire:target="saveWeights"
                                        class="w-full px-4 py-2 bg-blue-600 dark:bg-blue-600 text-white rounded-md hover:bg-blue-700 dark:hover:bg-blue-700 text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span wire:loading.remove wire:target="saveWeights">
                                            Save Weights
                                        </span>
                                        <span wire:loading wire:target="saveWeights"
                                            class="flex items-center justify-center">
                                            <x-loading-spinner size="h-4 w-4" color="text-white" class="mr-2" />
                                            Saving...
                                        </span>
                                    </button>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                                        Weights automatically normalize to ensure consistency
                                    </p>
                                </div>
                            @else
                                <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                                    <p class="text-sm">Enter pairwise comparisons to calculate weights</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Instructions Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md mt-6">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Instructions</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <ol class="list-decimal list-inside space-y-1">
                                    <li>Select the importance level for each factor (Very High, High, Medium, Low)</li>
                                    <li>Weights automatically calculate and normalize to 100% total</li>
                                    <li>The system converts your selections to an AHP-compatible format</li>
                                    <li>View the calculated percentages in the right panel</li>
                                    <li>Click "Save Weights" to apply for supervisor assignments</li>
                                </ol>
                                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400 italic">
                                    💡 Tip: Start with "Medium" for all factors and adjust based on your priorities
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
