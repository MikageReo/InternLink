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

        input[type="range"].slider::-moz-range-track {
            background: #e5e7eb;
            height: 8px;
            border-radius: 4px;
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
    </style>
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
            @if ($latestWeightsData)
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Current Active Weights</h3>
                            <p class="text-sm text-gray-600">
                                Created by: {{ $latestWeightsData['creator_name'] }} on
                                {{ $latestWeightsData['created_at'] }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-600 mb-1">Consistency Ratio</div>
                            <div
                                class="text-lg font-bold {{ $latestWeightsData['is_consistent'] ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($latestWeightsData['consistency_ratio'], 4) }}
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-4 gap-4">
                        @foreach (['course_match', 'preference_match', 'distance_score', 'workload_score'] as $criterion)
                            <div class="text-center">
                                <div class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $criterion)) }}
                                </div>
                                <div class="text-xl font-bold text-gray-900">
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
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Weight Configuration</h2>
                            <p class="text-sm text-gray-600 mt-1">
                                Adjust the sliders to set the importance of each factor. Weights will automatically
                                normalize to 100%.
                            </p>
                        </div>

                        <div class="p-6 space-y-6">
                                @foreach (['course_match' => 'Course Match', 'preference_match' => 'Preference Match', 'distance_score' => 'Distance Score', 'workload_score' => 'Workload Score'] as $key => $label)
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="text-sm font-medium text-gray-700">
                                                {{ $label }}
                                            </label>
                                            <div class="flex items-center gap-3">
                                                <input type="number"
                                                    wire:model.live.debounce.300ms="directWeights.{{ $key }}"
                                                    min="0" max="100" step="0.1"
                                                    class="w-20 text-center border border-gray-300 rounded-md px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <span class="text-sm font-bold text-gray-900 w-12 text-right">
                                                    {{ number_format($directWeights[$key] ?? 0, 1) }}%
                                                </span>
                                            </div>
                                        </div>
                                        <input type="range"
                                            wire:model.live.debounce.300ms="directWeights.{{ $key }}"
                                            min="0" max="100" step="0.1"
                                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider">
                                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                                            <span>0%</span>
                                            <span>50%</span>
                                            <span>100%</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">
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
                                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-blue-900">Total Weight:</span>
                                        <span class="text-lg font-bold text-green-600">
                                            {{ number_format(array_sum($directWeights), 1) }}%
                                        </span>
                                    </div>
                                    <p class="text-xs text-blue-600 mt-1">
                                        Weights are automatically normalized to 100% total
                                    </p>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-6 flex gap-3">
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
                                    <button wire:click="saveWeights"
                                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium transition">
                                        Save Weights
                                    </button>
                                    <p class="text-xs text-gray-500 mt-2 text-center">
                                        Weights automatically normalize to ensure consistency
                                    </p>
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
                            <div class="space-y-2 text-sm text-gray-600">
                                <ol class="list-decimal list-inside space-y-1">
                                    <li>Use the sliders to adjust the importance of each factor</li>
                                    <li>You can also type values directly in the number inputs</li>
                                    <li>Keep the total weight at 100% or less for best results</li>
                                    <li>Weights automatically normalize to 100% total if needed</li>
                                    <li>The system converts your weights to an AHP-compatible format</li>
                                    <li>Click "Save Weights" to apply for supervisor assignments</li>
                                </ol>
                                <p class="mt-3 text-xs text-gray-500 italic">
                                    💡 Tip: Start with equal weights (25% each) and adjust based on your priorities
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
