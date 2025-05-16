<?php

namespace App\Http\Controllers\Seller;

use Carbon\Carbon;
use App\Model\Seller;
use App\Models\Competition;
use Illuminate\Http\Request;
use App\Models\CompetitionJoined;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;

class CompetitionController extends Controller
{
    public function competitionLists() {
        $competitions = Competition::withCount('seller_joined')->where('end_date', '>=', now())->orderBy('id', 'desc')->get();
        $competitions = $competitions->map(function($competition) {
            $competition->has_joined = $competition->hasSellerJoined(auth('seller')->id());
            return $competition;
        });
        return view('seller-views.competition.list', compact('competitions'));
    }

    public function joinCompetition(Request $request, $id) {
        $sellerId = auth('seller')->id();
        $sellerData = Seller::with(['sellertype'])->find($sellerId);
        $sellerDataType = $sellerData->sellertype;
        if (str_contains($sellerDataType->name, 'free') OR $sellerDataType->amount <= 0) {
            Toastr::error('You are not a verified seller yet. Please upgrade your seller package to join this competition.');
            return redirect()->route('seller.competition.list-competition');
        }
        $competition = Competition::find($id);

        if (!$competition) {
            Toastr::error('Competition not found');
            return redirect()->route('seller.competition.list-competition');
        }

        if ((int) $competition->status != 1) {
            Toastr::error('Competition is currently not enabled by Admin. Kindly contact Administrator');
            return redirect()->route('seller.competition.list-competition');
        }
        $today = Carbon::today();
        $competition_end_date = Carbon::createFromFormat('Y-m-d', $competition['end_date']);
    
        // Check if the end date is a future date
        if ($competition_end_date->lte($today)) {
            Toastr::error('Competition closed already');
            return redirect()->back()->withInput();
        }
        $sellerId = auth('seller')->id();
        $sellerJoinedAlready = CompetitionJoined::where(['seller_id' => $sellerId])->first();

        if ($sellerJoinedAlready) {
            Toastr::error('You are already a participant of this competition. Boost your sales to get more sales');
            return redirect()->route('seller.competition.list-competition');
        }

        // Let's join the competition...
        $createCompetition = CompetitionJoined::create([
            'seller_id' => $sellerId,
            'competition_id' => $id
        ]);

        if (!$createCompetition) {
            Toastr::error('Unable to create competition. Please try again');
            return redirect()->route('seller.competition.list-competition');
        }   

        Toastr::success('Success! You are now a participant of this competition');
        return redirect()->route('seller.competition.list-competition');
    }

    public function viewCompetition($id) {
        $competition = Competition::find($id);

        if (!$competition) {
            Toastr::error('Unable to find competition. Please try again');
            return redirect()->route('seller.competition.list-competition');
        }   
        $competition->has_joined = $competition->hasSellerJoined(auth('seller')->id());
        $competition_joined =  CompetitionJoined::where(['competition_id' => $id, 'seller_id' => auth('seller')->id()])->first();
        return view('seller-views.competition.view', compact('competition', 'competition_joined'));
    }

    public function myCompetitions() {
        $sellerId = auth('seller')->id();
        $myCompetitions = CompetitionJoined::with('competition')->where('seller_id', $sellerId)->get();
        // return $myCompetitions;
        return view('seller-views.competition.own', compact('myCompetitions'));
    }

    public function competitionChart($id) {
        $competitionjoined = CompetitionJoined::with('competition')->find($id);
        if (!$competitionjoined) {
            Toastr::error('Competition not found');
            return redirect()->route('seller.competition.my-competition');
        }
        $competition = $competitionjoined->competition;
        $competition_name = $competitionjoined->competition->competition_name;

        // $sellers = Seller::select('sellers.*', 'competition_joined.*', 'competitions.*', 'orders.*', DB::raw('COALESCE(SUM(orders.order_amount), 0) as total_sold'))
        $competition_charts = Seller::select('sellers.*', 'sellers.f_name', 'sellers.l_name', 'sellers.business_shortcode', 'seller_types.name as seller_type_name', 'competitions.*', DB::raw('COALESCE(SUM(orders.order_amount), 0) as total_sold'))
            ->join('seller_types', 'sellers.seller_type', '=', 'seller_types.id')
            ->join('competition_joined', 'sellers.id', '=', 'competition_joined.seller_id')
            ->join('competitions', 'competitions.id', '=', 'competition_joined.competition_id')
            ->leftJoin('orders', function ($join) use ($competition) {
                $join->on('sellers.id', '=', 'orders.seller_id')
                    ->where('orders.payment_status', 'paid')
                    ->where('orders.created_at', '>=', $competition->start_date);
            })
            ->where('competition_joined.competition_id', $competition->id)
            ->groupBy('sellers.id')
            ->orderBy('total_sold', 'desc')
            ->get();
        // return $competition_charts;
        return view('seller-views.competition.chart', compact('competition_charts', 'competition_name'));
    }
}
