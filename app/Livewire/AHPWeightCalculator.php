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
            $this->calculateWeights();
        } else {
            // Load matrix from latest weights
            $this->matrix = $this->latestWeights->criteria_comparisons;
            $this->calculatedWeights = $this->latestWeights->calculated_weights;
            $this->consistencyRatio = $this->latestWeights->consistency_ratio;
            $this->isConsistent = $this->latestWeights->is_consistent;
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
            $result = $this->ahpService->calculateWeights($this->matrix);

            $this->calculatedWeights = $result['weights'];
            $this->consistencyRatio = $result['consistency_ratio'];
            $this->isConsistent = $result['is_consistent'];
            $this->lambdaMax = $result['lambda_max'] ?? null;
        } catch (\InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
            $this->calculatedWeights = [];
            $this->consistencyRatio = null;
            $this->isConsistent = false;
        }
    }

    /**
     * Save weights to database
     */
    public function saveWeights()
    {
        // Recalculate to ensure consistency
        $this->calculateWeights();

        if (!$this->isConsistent) {
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
        $this->matrix = $this->ahpService->createIdentityMatrix();
        $this->calculateWeights();
        Session::flash('info', 'Matrix reset to equal weights (25% each).');
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

    public function render()
    {
        return view('livewire.a-h-p-weight-calculator');
    }
}
