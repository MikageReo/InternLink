<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AHPWeight extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ahp_weights';

    protected $fillable = [
        'criteria_comparisons',
        'calculated_weights',
        'consistency_ratio',
        'created_by',
    ];

    protected $casts = [
        'criteria_comparisons' => 'array', // 4x4 pairwise comparison matrix
        'calculated_weights' => 'array', // Calculated weights: ['course_match' => 0.40, ...]
        'consistency_ratio' => 'decimal:4',
    ];

    /**
     * Get the user who created this AHP weight set
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the latest AHP weights (most recent)
     */
    public static function getLatest(): ?self
    {
        return self::latest('created_at')->first();
    }

    /**
     * Check if this weight set is consistent (CR < 0.1)
     */
    public function getIsConsistentAttribute(): bool
    {
        return $this->consistency_ratio < 0.1;
    }

    /**
     * Get weight for a specific criterion
     *
     * @param string $criterion
     * @return float|null
     */
    public function getWeight(string $criterion): ?float
    {
        return $this->calculated_weights[$criterion] ?? null;
    }

    /**
     * Get all weights as an array
     *
     * @return array
     */
    public function getWeights(): array
    {
        return $this->calculated_weights ?? [];
    }
}
