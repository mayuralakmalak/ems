<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\Exhibition;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the privacy policy page (from CMS if available).
     */
    public function privacyPolicy(): View
    {
        $page = CmsPage::where('slug', 'privacy-policy')->where('is_active', true)->first();
        if ($page) {
            return view('frontend.cms-page', compact('page'));
        }
        return view('frontend.privacy-policy');
    }

    /**
     * Display the terms and conditions page (from CMS if available).
     */
    public function termsAndConditions(): View
    {
        $page = CmsPage::where('slug', 'terms-and-conditions')->where('is_active', true)->first();
        if ($page) {
            return view('frontend.cms-page', compact('page'));
        }
        return view('frontend.terms-and-conditions');
    }

    /**
     * Display the refund and cancellation policy page (from CMS if available).
     */
    public function refundAndCancellationPolicy(): View
    {
        $page = CmsPage::where('slug', 'refund-and-cancellation-policy')->where('is_active', true)->first();
        if ($page) {
            return view('frontend.cms-page', compact('page'));
        }
        return view('frontend.refund-and-cancellation-policy');
    }

    /**
     * Display the rules for exhibitors page (from CMS if available).
     */
    public function rulesForExhibitors(): View
    {
        $page = CmsPage::where('slug', 'rules-for-exhibitors')->where('is_active', true)->first();
        if ($page) {
            return view('frontend.cms-page', compact('page'));
        }
        return view('frontend.rules-for-exhibitors');
    }

    /**
     * Display a CMS page by slug.
     */
    public function cmsPage(string $slug): View|\Illuminate\Http\RedirectResponse
    {
        $page = CmsPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        return view('frontend.cms-page', compact('page'));
    }

    public function index()
    {
        // Get featured/upcoming exhibition for hero section
        $featuredExhibition = Exhibition::where('status', 'active')
            ->where('start_date', '>', now())
            ->latest()
            ->first();
        
        // If no upcoming, get the most recent active one
        if (!$featuredExhibition) {
            $featuredExhibition = Exhibition::where('status', 'active')
                ->latest()
                ->first();
        }
        
        // Get latest exhibition for the overlap section (only one)
        $latestExhibition = Exhibition::where('status', 'active')
            ->where('id', '!=', $featuredExhibition?->id)
            ->latest()
            ->first();
        
        // Get stats (you can make these dynamic later)
        $stats = [
            'exhibitors' => 500,
            'events' => 400,
            'visitors' => 15000,
        ];
        
        return view('frontend.home', compact('featuredExhibition', 'latestExhibition', 'stats'));
    }
}

