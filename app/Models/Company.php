<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $primaryKey = 'companyID';

    protected $fillable = [
        'companyName',
        'companyEmail',
        'companyNumber',
        'companyAddressLine',
        'companyCity',
        'companyPostcode',
        'companyState',
        'companyCountry',
        'companyLatitude',
        'companyLongitude',
        'industrySupervisorName',
        'industrySupervisorContact',
        'industrySupervisorEmail',
        'status',
    ];

    protected $casts = [
        'companyLatitude' => 'decimal:8',
        'companyLongitude' => 'decimal:8',
    ];

    /**
     * Get all placement applications for this company.
     */
    public function placementApplications(): HasMany
    {
        return $this->hasMany(PlacementApplication::class, 'companyName', 'companyName');
    }

    /**
     * Get the full company address as a formatted string
     */
    public function getCompanyFullAddressAttribute(): string
    {
        $addressParts = array_filter([
            $this->companyAddressLine,
            $this->companyCity,
            $this->companyPostcode,
            $this->companyState,
            $this->companyCountry
        ]);

        return implode(', ', $addressParts);
    }

    /**
     * Check if the company has geocoding coordinates
     */
    public function getHasGeocodingAttribute(): bool
    {
        return !is_null($this->companyLatitude) && !is_null($this->companyLongitude);
    }
}
