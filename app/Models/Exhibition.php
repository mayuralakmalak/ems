<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Exhibition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'venue', 'city', 'state', 'country',
        'start_date', 'end_date', 'start_time', 'end_time',
        'floorplan_image', 'floorplan_images', 'price_per_sqft', 'raw_price_per_sqft', 'orphand_price_per_sqft',
        'side_1_open_percent', 'side_2_open_percent', 'side_3_open_percent', 'side_4_open_percent',
        'premium_price', 'standard_price', 'economy_price',
        'addon_services_cutoff_date', 'document_upload_deadline',
        'initial_payment_percent', 'full_payment_discount_percent', 'member_discount_percent', 'maximum_discount_apply_percent',
        'visitor_fee', 'member_fee', 'delegate_fee', 'vip_registration_fee',
        'visitor_early_bird_end_date', 'visitor_early_bird_fee',
        'visitor_standard_end_date', 'visitor_standard_fee',
        'visitor_last_minute_end_date', 'visitor_last_minute_fee',
        'delegate_free_count', 'delegate_additional_fee',
        'exhibition_manual_pdf', 'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'addon_services_cutoff_date' => 'date',
        'document_upload_deadline' => 'date',
        'visitor_early_bird_end_date' => 'date',
        'visitor_standard_end_date' => 'date',
        'visitor_last_minute_end_date' => 'date',
        'floorplan_images' => 'array',
    ];

    public function booths()
    {
        return $this->hasMany(Booth::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function sponsorships()
    {
        return $this->hasMany(Sponsorship::class);
    }

    public function stallSchemes()
    {
        return $this->hasMany(StallScheme::class);
    }

    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function stallVariations()
    {
        return $this->hasMany(StallVariation::class);
    }

    public function badgeConfigurations()
    {
        return $this->hasMany(BadgeConfiguration::class);
    }

    public function boothSizes()
    {
        return $this->hasMany(ExhibitionBoothSize::class);
    }

    public function addonServices()
    {
        return $this->hasMany(ExhibitionAddonService::class);
    }

    public function requiredDocuments()
    {
        return $this->hasMany(ExhibitionRequiredDocument::class);
    }

    public function floors()
    {
        return $this->hasMany(Floor::class)->orderBy('floor_number', 'asc');
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Get visitor fee and tier applicable for a given date.
     * Returns ['tier' => string, 'fee' => float] or ['tier' => null, 'fee' => visitor_fee] as fallback.
     */
    public function getVisitorFeeForDate($date = null): array
    {
        $date = $date ? \Carbon\Carbon::parse($date) : now();
        if ($this->visitor_early_bird_end_date && $date->lte($this->visitor_early_bird_end_date) && $this->visitor_early_bird_fee !== null) {
            return ['tier' => 'early_bird', 'fee' => (float) $this->visitor_early_bird_fee];
        }
        if ($this->visitor_standard_end_date && $date->lte($this->visitor_standard_end_date) && $this->visitor_standard_fee !== null) {
            return ['tier' => 'standard', 'fee' => (float) $this->visitor_standard_fee];
        }
        if ($this->visitor_last_minute_end_date && $date->lte($this->visitor_last_minute_end_date) && $this->visitor_last_minute_fee !== null) {
            return ['tier' => 'last_minute', 'fee' => (float) $this->visitor_last_minute_fee];
        }
        $fee = $this->visitor_fee !== null ? (float) $this->visitor_fee : 0;
        return ['tier' => null, 'fee' => $fee];
    }

    /**
     * Get registration fee by type. For delegate, optional delegate_count for additional delegates.
     */
    public function getRegistrationFeeByType(string $type, int $delegateCount = 1): float
    {
        switch ($type) {
            case 'visitor':
                return $this->getVisitorFeeForDate()['fee'];
            case 'member':
                return (float) ($this->member_fee ?? 0);
            case 'delegate':
                $free = (int) ($this->delegate_free_count ?? 0);
                $additionalFee = (float) ($this->delegate_additional_fee ?? 0);
                $baseFee = (float) ($this->delegate_fee ?? 0);
                if ($free > 0 && $delegateCount <= $free) {
                    return 0;
                }
                if ($free > 0) {
                    return $baseFee + (($delegateCount - $free) * $additionalFee);
                }
                return $baseFee;
            case 'vip':
                return (float) ($this->vip_registration_fee ?? 0);
            default:
                return 0;
        }
    }

    /**
     * Get the default floor (first floor) for backward compatibility
     */
    public function getDefaultFloor()
    {
        return $this->floors()->first() ?? null;
    }
}
