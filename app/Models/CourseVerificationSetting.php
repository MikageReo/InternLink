<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseVerificationSetting extends Model
{
    protected $fillable = [
        'minimum_credit_hour',
        'maximum_credit_hour',
    ];

    /**
     * Get the current settings (singleton pattern)
     */
    public static function getSettings()
    {
        $settings = self::first();

        if (!$settings) {
            // Create default settings if none exist
            $settings = self::create([
                'minimum_credit_hour' => 118,
                'maximum_credit_hour' => 130,
            ]);
        }

        return $settings;
    }

    /**
     * Update settings
     */
    public static function updateSettings($minimumCreditHour, $maximumCreditHour)
    {
        $settings = self::getSettings();
        $settings->update([
            'minimum_credit_hour' => $minimumCreditHour,
            'maximum_credit_hour' => $maximumCreditHour,
        ]);
        return $settings;
    }
}
