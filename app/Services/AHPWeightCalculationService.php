<?php

namespace App\Services;

use InvalidArgumentException;

class AHPWeightCalculationService
{
    /**
     * Random Index (RI) values for different matrix sizes
     * These are standard AHP values used for consistency ratio calculation
     */
    private const RANDOM_INDEX = [
        1 => 0.00,
        2 => 0.00,
        3 => 0.58,
        4 => 0.90,
        5 => 1.12,
        6 => 1.24,
        7 => 1.32,
        8 => 1.41,
        9 => 1.45,
        10 => 1.49,
    ];

    /**
     * Criteria names in order (for supervisor assignment)
     */
    private const CRITERIA_NAMES = [
        'course_match',
        'preference_match',
        'distance_score',
        'workload_score',
    ];

    /**
     * Calculate weights from pairwise comparison matrix using eigenvector method
     *
     * @param array $comparisonMatrix 4x4 pairwise comparison matrix
     * @return array ['weights' => [...], 'consistency_ratio' => float, 'is_consistent' => bool]
     * @throws InvalidArgumentException
     */
    public function calculateWeights(array $comparisonMatrix): array
    {
        $this->validateMatrix($comparisonMatrix);

        // Calculate weights using normalized geometric mean method (simpler than power method)
        $weights = $this->calculateEigenvector($comparisonMatrix);

        // Calculate consistency ratio
        $consistencyRatio = $this->calculateConsistencyRatio($comparisonMatrix, $weights);

        // Validate consistency
        $isConsistent = $consistencyRatio < 0.1;

        if (!$isConsistent) {
            throw new InvalidArgumentException(
                "Consistency ratio ({$consistencyRatio}) exceeds acceptable threshold (0.1). " .
                    "Please review your pairwise comparisons for consistency."
            );
        }

        // Normalize weights to ensure they sum to 1.0
        $weights = $this->normalizeWeights($weights);

        // Map weights to criteria names
        $weightMap = [];
        foreach (self::CRITERIA_NAMES as $index => $criterion) {
            $weightMap[$criterion] = $weights[$index];
        }

        return [
            'weights' => $weightMap,
            'consistency_ratio' => round($consistencyRatio, 4),
            'is_consistent' => $isConsistent,
            'lambda_max' => $this->calculateLambdaMax($comparisonMatrix, $weights),
        ];
    }

    /**
     * Calculate eigenvector (weights) using normalized geometric mean method
     *
     * @param array $matrix
     * @return array
     */
    private function calculateEigenvector(array $matrix): array
    {
        $n = count($matrix);
        $geometricMeans = [];

        // Calculate geometric mean for each row
        for ($i = 0; $i < $n; $i++) {
            $product = 1.0;
            for ($j = 0; $j < $n; $j++) {
                $product *= $matrix[$i][$j];
            }
            $geometricMeans[$i] = pow($product, 1.0 / $n);
        }

        // Normalize geometric means to get weights
        $sum = array_sum($geometricMeans);
        $weights = [];
        for ($i = 0; $i < $n; $i++) {
            $weights[$i] = $geometricMeans[$i] / $sum;
        }

        return $weights;
    }

    /**
     * Calculate consistency ratio (CR)
     *
     * CR = CI / RI
     * CI = (λ_max - n) / (n - 1)
     *
     * @param array $matrix
     * @param array $weights
     * @return float
     */
    private function calculateConsistencyRatio(array $matrix, array $weights): float
    {
        $n = count($matrix);
        $lambdaMax = $this->calculateLambdaMax($matrix, $weights);

        // Consistency Index (CI)
        $consistencyIndex = ($lambdaMax - $n) / ($n - 1);

        // Random Index (RI) for matrix size n
        $randomIndex = self::RANDOM_INDEX[$n] ?? 0.90;

        // Consistency Ratio (CR)
        if ($randomIndex == 0) {
            return 0.0; // For n=1 or n=2, CR is always 0
        }

        return $consistencyIndex / $randomIndex;
    }

    /**
     * Calculate maximum eigenvalue (λ_max)
     *
     * @param array $matrix
     * @param array $weights
     * @return float
     */
    private function calculateLambdaMax(array $matrix, array $weights): float
    {
        $n = count($matrix);
        $weightedSum = [];

        // Calculate A * w
        for ($i = 0; $i < $n; $i++) {
            $sum = 0.0;
            for ($j = 0; $j < $n; $j++) {
                $sum += $matrix[$i][$j] * $weights[$j];
            }
            $weightedSum[$i] = $sum;
        }

        // Calculate λ_max = average of (weighted_sum[i] / weights[i])
        $lambdaSum = 0.0;
        for ($i = 0; $i < $n; $i++) {
            if ($weights[$i] > 0) {
                $lambdaSum += $weightedSum[$i] / $weights[$i];
            }
        }

        return $lambdaSum / $n;
    }

    /**
     * Validate pairwise comparison matrix
     *
     * @param array $matrix
     * @throws InvalidArgumentException
     */
    private function validateMatrix(array $matrix): void
    {
        $n = count($matrix);

        // Check if matrix is 4x4
        if ($n !== 4) {
            throw new InvalidArgumentException("Matrix must be 4x4 for 4 criteria. Got {$n}x{$n}.");
        }

        // Check each row has 4 elements
        foreach ($matrix as $i => $row) {
            if (count($row) !== 4) {
                throw new InvalidArgumentException("Row {$i} must have 4 elements. Got " . count($row) . ".");
            }
        }

        // Check diagonal elements are 1
        for ($i = 0; $i < $n; $i++) {
            if (abs($matrix[$i][$i] - 1.0) > 0.0001) {
                throw new InvalidArgumentException("Diagonal elements must be 1.0. Found {$matrix[$i][$i]} at position [{$i}][{$i}].");
            }
        }

        // Check reciprocity: a_ij = 1 / a_ji
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $expected = 1.0 / $matrix[$j][$i];
                $actual = $matrix[$i][$j];
                $tolerance = 0.01; // Allow small floating point differences

                if (abs($actual - $expected) > $tolerance) {
                    throw new InvalidArgumentException(
                        "Matrix must be reciprocal. " .
                            "Expected a[{$i}][{$j}] = " . round($expected, 4) . " (1 / a[{$j}][{$i}]), " .
                            "but got " . round($actual, 4) . "."
                    );
                }
            }
        }

        // Check values are within valid AHP scale (1-9 or reciprocals)
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $value = $matrix[$i][$j];
                if ($value < 0.111 || $value > 9.0) {
                    throw new InvalidArgumentException(
                        "Matrix values must be between 1/9 and 9 (AHP scale). " .
                            "Found {$value} at position [{$i}][{$j}]."
                    );
                }
            }
        }
    }

    /**
     * Normalize weights to ensure they sum to exactly 1.0
     *
     * @param array $weights
     * @return array
     */
    private function normalizeWeights(array $weights): array
    {
        $sum = array_sum($weights);
        if ($sum == 0) {
            throw new InvalidArgumentException("Weights sum to zero. Invalid calculation.");
        }

        $normalized = [];
        foreach ($weights as $weight) {
            $normalized[] = $weight / $sum;
        }

        return $normalized;
    }

    /**
     * Get criteria names
     *
     * @return array
     */
    public function getCriteriaNames(): array
    {
        return self::CRITERIA_NAMES;
    }

    /**
     * Create an identity matrix (all comparisons equal = 1)
     * Useful for testing or as a default
     *
     * @return array
     */
    public function createIdentityMatrix(): array
    {
        return [
            [1.0, 1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0, 1.0],
        ];
    }

    /**
     * Create a matrix from current default weights
     * This can be used as a starting point or reference
     *
     * Note: This is approximate - actual AHP requires pairwise comparisons
     *
     * @return array
     */
    public function createDefaultMatrix(): array
    {
        // Based on current weights: Course(40%), Preference(30%), Distance(20%), Workload(10%)
        // Approximate pairwise comparisons:
        // Course vs Preference: 40/30 ≈ 1.33
        // Course vs Distance: 40/20 = 2.0
        // Course vs Workload: 40/10 = 4.0
        // Preference vs Distance: 30/20 = 1.5
        // Preference vs Workload: 30/10 = 3.0
        // Distance vs Workload: 20/10 = 2.0

        return [
            [1.0, 1.33, 2.0, 4.0],      // Course Match row
            [0.75, 1.0, 1.5, 3.0],      // Preference Match row (1/1.33 ≈ 0.75)
            [0.5, 0.67, 1.0, 2.0],      // Distance Score row
            [0.25, 0.33, 0.5, 1.0],     // Workload Score row
        ];
    }
}
