<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\AHPWeightCalculationService;
use App\Models\AHPWeight;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AHPWeightCalculator extends Component
{
    // Matrix properties (4x4 pairwise comparison matrix)
    // Indices: 0=Course Match, 1=Preference Match, 2=Distance Score, 3=Workload Score
    public $matrix = [
        [1.0, 1.0, 1.0, 1.0],
        [1.0, 1.0, 1.0, 1.0],
        [1.0, 1.0, 1.0, 1.0],
        [1.0, 1.0, 1.0, 1.0],
    ];

    // Calculated results
    public $calculatedWeights = [];
    public $consistencyRatio = null;
    public $isConsistent = false;
    public $lambdaMax = null;
    public $errorMessage = null;

    // UI state
    public $showSaveSuccess = false;
    public $latestWeights = null;
    public $mode = 'simple'; // 'simple' or 'advanced'

    // Simple mode: Direct weight sliders (0-100, will be normalized)
    public $directWeights = [
        'course_match' => 40.0,
        'preference_match' => 30.0,
        'distance_score' => 20.0,
        'workload_score' => 10.0,
    ];

    protected $ahpService;

    // Criteria labels for display
    public $criteriaLabels = [
        'Course Match',
        'Preference Match',
        'Distance Score',
        'Workload Score',
    ];

    // AHP scale options for dropdown
    public $scaleOptions = [
        1 => '1 - Equal Importance',
        2 => '2 - Between Equal and Moderate',
        3 => '3 - Moderate Importance',
        4 => '4 - Between Moderate and Strong',
        5 => '5 - Strong Importance',
        6 => '6 - Between Strong and Very Strong',
        7 => '7 - Very Strong Importance',
        8 => '8 - Between Very Strong and Extreme',
        9 => '9 - Extreme Importance',
    ];

    public function boot(AHPWeightCalculationService $ahpService)
    {
        $this->ahpService = $ahpService;
    }

    public function mount()
    {
        // Check if user is admin or coordinator
        $user = Auth::user();
        if (!$user || !$user->lecturer || (!$user->lecturer->isAdmin && !$user->lecturer->isCoordinator)) {
            abort(403, 'Access denied. Only administrators and coordinators can access this page.');
        }

        // Load latest weights if they exist
        $this->loadLatestWeights();

        // Initialize with default matrix if no latest weights
        if (!$this->latestWeights) {
            $this->matrix = $this->ahpService->createDefaultMatrix();
            // Initialize direct weights from default (40%, 30%, 20%, 10%)
            $this->directWeights = [
                'course_match' => 40.0,
                'preference_match' => 30.0,
                'distance_score' => 20.0,
                'workload_score' => 10.0,
            ];
            $this->calculateWeights();
        } else {
            // Load matrix from latest weights
            $this->matrix = $this->latestWeights->criteria_comparisons;
            $this->calculatedWeights = $this->latestWeights->calculated_weights;
            $this->consistencyRatio = $this->latestWeights->consistency_ratio;
            $this->isConsistent = $this->latestWeights->is_consistent;
            // Load direct weights from calculated weights
            foreach ($this->calculatedWeights as $key => $weight) {
                $this->directWeights[$key] = $weight * 100;
            }
        }

        // Ensure weights are calculated in simple mode
        if ($this->mode === 'simple' && empty($this->calculatedWeights)) {
            $this->calculateWeights();
        }
    }

    public function loadLatestWeights()
    {
        $this->latestWeights = AHPWeight::getLatest();
    }

    /**
     * Update matrix value and automatically update reciprocal
     */
    public function updatedMatrix($value, $key)
    {
        // Parse the key (e.g., "0.1" means row 0, col 1)
        $parts = explode('.', $key);
        if (count($parts) !== 2) {
            return;
        }

        $row = (int) $parts[0];
        $col = (int) $parts[1];

        // Skip diagonal (always 1)
        if ($row == $col) {
            return;
        }

        // Validate value
        $value = (float) $value;
        if ($value < 0.111 || $value > 9.0) {
            $this->addError("matrix.{$row}.{$col}", 'Value must be between 1/9 and 9');
            // Reset to previous valid value
            $this->matrix[$row][$col] = 1.0;
            return;
        }

        // Update the value
        $this->matrix[$row][$col] = $value;

        // Update reciprocal (col, row = 1 / value)
        $this->matrix[$col][$row] = 1.0 / $value;

        // Clear error
        $this->resetErrorBag("matrix.{$row}.{$col}");

        // Recalculate weights
        $this->calculateWeights();
    }

    /**
     * Calculate weights using AHP service
     */
    public function calculateWeights()
    {
        $this->errorMessage = null;
        $this->isConsistent = false;

        try {
            if ($this->mode === 'simple') {
                // In simple mode, calculate weights directly from normalized direct weights
                $sum = array_sum($this->directWeights);
                if ($sum == 0) {
                    $sum = 100; // Default to equal weights
                }

                $this->calculatedWeights = [];
                foreach ($this->directWeights as $key => $value) {
                    $this->calculatedWeights[$key] = $value / $sum;
                }

                // Calculate consistency ratio from the generated matrix (without strict validation)
                try {
                    $result = $this->ahpService->calculateWeightsWithoutValidation($this->matrix);
                    $this->consistencyRatio = $result['consistency_ratio'];
                    $this->isConsistent = $result['is_consistent'];
                    $this->lambdaMax = $result['lambda_max'] ?? null;
                } catch (\InvalidArgumentException $e) {
                    // If matrix validation fails, use default consistency
                    $this->consistencyRatio = 0.0;
                    $this->isConsistent = true;
                    $this->lambdaMax = null;
                }
            } else {
                // Advanced mode: use AHP calculation
                $result = $this->ahpService->calculateWeights($this->matrix);

                $this->calculatedWeights = $result['weights'];
                $this->consistencyRatio = $result['consistency_ratio'];
                $this->isConsistent = $result['is_consistent'];
                $this->lambdaMax = $result['lambda_max'] ?? null;
            }
        } catch (\InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
            $this->calculatedWeights = [];
            $this->consistencyRatio = null;
            $this->isConsistent = false;
        }
    }


    /**
     * Reset matrix to default values
     */
    public function resetToDefault()
    {
        $this->matrix = $this->ahpService->createDefaultMatrix();
        $this->calculateWeights();
        Session::flash('info', 'Matrix reset to default values.');
    }

    /**
     * Reset matrix to identity (all equal)
     */
    public function resetToEqual()
    {
        if ($this->mode === 'simple') {
            // Reset direct weights to equal
            foreach ($this->directWeights as $key => $value) {
                $this->directWeights[$key] = 25.0;
            }
            $this->convertDirectWeightsToMatrix();
            $this->calculateWeights();
            Session::flash('info', 'Weights reset to equal (25% each).');
        } else {
            $this->matrix = $this->ahpService->createIdentityMatrix();
            $this->calculateWeights();
            Session::flash('info', 'Matrix reset to equal weights (25% each).');
        }
    }

    /**
     * Get weight percentage for display
     */
    public function getWeightPercentage($criterion): string
    {
        if (isset($this->calculatedWeights[$criterion])) {
            return number_format($this->calculatedWeights[$criterion] * 100, 2) . '%';
        }
        return '0.00%';
    }

    /**
     * Switch between simple and advanced modes
     */
    public function switchMode($newMode)
    {
        if ($newMode === 'simple') {
            // Convert current weights to direct weights
            if (!empty($this->calculatedWeights)) {
                foreach ($this->calculatedWeights as $key => $weight) {
                    $this->directWeights[$key] = $weight * 100;
                }
            }
            // Ensure weights are calculated
            $this->calculateWeights();
        } else {
            // Convert direct weights to approximate matrix
            $this->convertDirectWeightsToMatrix();
            // Recalculate weights from matrix
            $this->calculateWeights();
        }
        $this->mode = $newMode;
    }

    /**
     * Update direct weight and normalize others
     */
    public function updatedDirectWeights($value, $key)
    {
        $value = (float) $value;

        // Clamp value between 0 and 100
        if ($value < 0) $value = 0;
        if ($value > 100) $value = 100;

        $this->directWeights[$key] = $value;

        // Normalize to ensure sum is 100
        $this->normalizeDirectWeights();

        // Convert to matrix and calculate
        $this->convertDirectWeightsToMatrix();
        $this->calculateWeights();
    }

    /**
     * Normalize direct weights to sum to 100
     */
    private function normalizeDirectWeights()
    {
        $sum = array_sum($this->directWeights);

        if ($sum == 0) {
            // If all zero, set to equal weights
            foreach ($this->directWeights as $key => $value) {
                $this->directWeights[$key] = 25.0;
            }
            return;
        }

        // Normalize
        foreach ($this->directWeights as $key => $value) {
            $this->directWeights[$key] = ($value / $sum) * 100;
        }
    }

    /**
     * Convert direct weights to approximate pairwise comparison matrix
     * This creates a consistent matrix that produces the desired weights
     */
    private function convertDirectWeightsToMatrix()
    {
        // Normalize direct weights to 0-1 range
        $normalizedWeights = [];
        $sum = array_sum($this->directWeights);

        if ($sum == 0) {
            $sum = 100; // Default to equal weights
        }

        foreach ($this->directWeights as $key => $value) {
            $normalizedWeights[$key] = $value / $sum;
        }

        // Map to indices
        $criteriaOrder = ['course_match', 'preference_match', 'distance_score', 'workload_score'];
        $weights = [];
        foreach ($criteriaOrder as $criterion) {
            $weights[] = $normalizedWeights[$criterion] ?? 0.25;
        }

        // Create approximate pairwise comparison matrix
        // Using ratio method: a_ij = w_i / w_j
        $matrix = [];
        for ($i = 0; $i < 4; $i++) {
            $row = [];
            for ($j = 0; $j < 4; $j++) {
                if ($i == $j) {
                    $row[] = 1.0;
                } else {
                    // Calculate ratio, but clamp to AHP scale (1/9 to 9)
                    $ratio = $weights[$i] > 0 && $weights[$j] > 0 ? $weights[$i] / $weights[$j] : 1.0;
                    $ratio = max(1 / 9, min(9, $ratio)); // Clamp to valid AHP range
                    $row[] = round($ratio, 3);
                }
            }
            $matrix[] = $row;
        }

        $this->matrix = $matrix;
    }

    /**
     * Save weights (works for both modes)
     */
    public function saveWeights()
    {
        if ($this->mode === 'simple') {
            // Convert direct weights to matrix first
            $this->convertDirectWeightsToMatrix();
        }

        // Recalculate to ensure consistency
        $this->calculateWeights();

        // In simple mode, we allow saving even with higher consistency ratio
        // because weights are directly set and normalized
        if ($this->mode === 'advanced' && !$this->isConsistent) {
            Session::flash('error', 'Cannot save: Consistency ratio exceeds acceptable threshold (0.1). Please adjust your comparisons.');
            return;
        }

        if (empty($this->calculatedWeights)) {
            Session::flash('error', 'Cannot save: Invalid weight calculation.');
            return;
        }

        try {
            AHPWeight::create([
                'criteria_comparisons' => $this->matrix,
                'calculated_weights' => $this->calculatedWeights,
                'consistency_ratio' => $this->consistencyRatio,
                'created_by' => Auth::id(),
            ]);

            Session::flash('success', 'AHP weights saved successfully! They will be used for future supervisor assignments.');
            $this->showSaveSuccess = true;
            $this->loadLatestWeights();

            // Dispatch event to refresh other components if needed
            $this->dispatch('weights-saved');
        } catch (\Exception $e) {
            Session::flash('error', 'Failed to save weights: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.a-h-p-weight-calculator');
    }
}
