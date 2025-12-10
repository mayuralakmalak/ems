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
        'floorplan_image', 'price_per_sqft', 'raw_price_per_sqft', 'orphand_price_per_sqft',
        'side_1_open_percent', 'side_2_open_percent', 'side_3_open_percent', 'side_4_open_percent',
        'premium_price', 'standard_price', 'economy_price',
        'addon_services_cutoff_date', 'document_upload_deadline',
        'initial_payment_percent', 'exhibition_manual_pdf', 'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'addon_services_cutoff_date' => 'date',
        'document_upload_deadline' => 'date',
    ];

    public function booths()
    {
        return $this->hasMany(Booth::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
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
}
