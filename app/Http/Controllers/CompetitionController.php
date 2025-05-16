<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Model\Seller;
use App\Model\SellerWallet;
use App\Models\Competition;
use Illuminate\Http\Request;
use App\Services\UtilityService;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Requests\CompetitionRequest;
use App\Models\CompetitionJoined;

class CompetitionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $competitions = Competition::withCount('competitionJoined')->orderBy('id', 'desc')->get()->map(function ($competition) {
            $start_date = Carbon::createFromFormat('Y-m-d', $competition->start_date);
            $end_date = Carbon::createFromFormat('Y-m-d', $competition->end_date);
            $competition->date_difference = $start_date->diffInDays($end_date);

            $today = Carbon::today();

            // Check if the competition has expired
            if ($end_date->lt($today)) {
                $competition->is_expired = true;
                $competition->expiration_status = 'Expired';
            } else {
                $competition->is_expired = false;
                $competition->expiration_status = 'Active';
            }

            $competition->status_html = (new UtilityService())->competitionStatus($competition->status, true);
            return $competition;
        });

        // return $competitions;

        return view('admin-views.competition.list', compact('competitions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin-views.competition.add');
    }

    public function add_competition(CompetitionRequest $request)
    {
        $data = $request->validated();

        $competition_name = ucfirst($data['competition_name']);
        $minimum_sales_amount = $data['minimum_sales_amount'];
        $competition_start_date = Carbon::createFromFormat('Y-m-d', $data['competition_start_date']);
        $competition_end_date = Carbon::createFromFormat('Y-m-d', $data['competition_end_date']);
        $competition_status = $data['competition_status'];

        $today = Carbon::today();

        // Check if the start date is today or a future date
        if ($competition_start_date->lt($today)) {
            Toastr::error('Competition start date must be today or a future date!');
            return redirect()->back()->withInput();
        }

        // Check if the end date is a future date
        if ($competition_end_date->lte($today)) {
            Toastr::error('Competition end date must be a future date!');
            return redirect()->back()->withInput();
        }

        $isCompetitionExist = Competition::where('competition_name', $competition_name)->first();

        if ($isCompetitionExist) {
            Toastr::error('Competition already exists!');
            return redirect()->back()->withInput();
        }

        $createCompetition = Competition::create([
            'competition_name' => $competition_name,
            'minimum_sales_amount' => $minimum_sales_amount,
            'start_date' => $competition_start_date->format('Y-m-d'),
            'end_date' => $competition_end_date->format('Y-m-d'),
            'status' => (string) $competition_status,
        ]);

        if ($createCompetition) {
            Toastr::success('Competition added successfully!');
            return redirect()->route('admin.competition.index');
        }

        Toastr::error('Unable to add competition!');
        return redirect()->back()->withInput();
    }

    public function delete($id)
    {
        // Use a transaction to ensure data integrity during deletion
        DB::beginTransaction();

        try {
            // Delete the competition
            $delete = Competition::where('id', $id)->delete();

            // Check if the deletion was successful
            if ($delete) {
                // Check if there are any records in CompetitionJoined before deleting
                $competitionJoined = CompetitionJoined::where('competition_id', $id);
                if ($competitionJoined->exists()) {
                    $competitionJoined->delete();
                }

                DB::commit(); // Commit the transaction

                Toastr::success('Competition deleted successfully!');
            } else {
                DB::rollBack(); // Rollback if unable to delete the competition
                Toastr::error('Unable to delete competition!');
            }
        } catch (\Exception $e) {
            // Rollback on error
            DB::rollBack();
            Toastr::error('An error occurred while deleting the competition!');
        }
        return redirect()->back();
    }

    public function editview($id)
    {
        $competition = Competition::where('id', $id)->first();
        return view('admin-views.competition.edit', compact('competition'));
    }

    public function update_competition(CompetitionRequest $request)
    {
        $data = $request->validated();

        $competition_id = ucfirst($data['competition_id']);
        $minimum_sales_amount = $data['minimum_sales_amount'];
        $competition_name = $data['competition_name'];
        $competition_description = $data['competition_description'];
        $competition_start_date = Carbon::createFromFormat('Y-m-d', $data['competition_start_date']);
        $competition_end_date = Carbon::createFromFormat('Y-m-d', $data['competition_end_date']);
        $competition_status = $data['competition_status'];

        $competition = Competition::find($competition_id);

        if (!$competition) {
            Toastr::error('Competition not found!');
            return redirect()->back()->withInput();
        }

        $today = Carbon::today();

        // Check if the start date is today or a future date
        // if ($competition_start_date->lt($today)) {
        //     Toastr::error('Competition start date must be today or a future date!');
        //     return redirect()->back()->withInput();
        // }

        // Check if the end date is a future date
        if ($competition_end_date->lte($today)) {
            Toastr::error('Competition end date must be a future date!');
            return redirect()->back()->withInput();
        }

        $isCompetitionExist = Competition::where('competition_name', $competition_name)->first();

        if ($isCompetitionExist and $competition_id != $isCompetitionExist->id) {
            Toastr::error("Competition ($competition_name) already exists!");
            return redirect()->back()->withInput();
        }

        $competition->competition_name = $competition_name;
        $competition->competition_description = $competition_description;
        $competition->minimum_sales_amount = $minimum_sales_amount;
        $competition->start_date = $competition_start_date->format('Y-m-d');
        $competition->end_date = $competition_end_date->format('Y-m-d');
        $competition->status = (string) $competition_status;

        $updateCompetition = $competition->save();

        if ($updateCompetition) {
            Toastr::success('Competition updated successfully!');
            return redirect()->route('admin.competition.index');
        }

        Toastr::error('Unable to update competition!');
        return redirect()->back()->withInput();
    }

    public function creditLoyaltyView()
    {
        return view('admin-views.competition.credit-loyalty-wallet');
    }

    public function creditLoyaltyWallet(CompetitionRequest $request)
    {
        $data = $request->validated();
        $email = strtolower($data['email_address']);

        // $email = 'valuemaxcommunications@gmail.com';
        $seller = Seller::with('wallet')->where('email', $email)->first();
        if (!$seller) {
            return redirect()->back()->withErrors(['error' => 'Seller not found with the provided email address.'])->withInput();
        }

        // Assuming $data['amount'] contains the amount to credit
        $amountToCredit = $data['amount'];

        // Perform the operation in a transaction
        DB::transaction(function () use ($seller, $amountToCredit) {
            $seller->wallet->loyalty_wallet += $amountToCredit;
            $seller->wallet->save();
        });
        $fullname = $seller->f_name . ' ' . $seller->l_name;

        // Return success message or redirect as necessary
        return redirect()->back()->with("success", "Seller's ($fullname) loyalty wallet credited successfully.");
    }

    public function competitionChart($id)
    {
        $competition = Competition::findOrFail($id);
        if (!$competition) {
            session()->flash('error', 'Competition not found');
            return redirect()->route('admin.competition.index');
        }

        $competition_charts = CompetitionJoined::join('sellers', 'competition_joined.seller_id', '=', 'sellers.id')
            ->join('seller_types', 'sellers.seller_type', '=', 'seller_types.id')
            ->join('competitions', 'competitions.id', '=', 'competition_joined.competition_id')
            ->leftJoin('orders', function ($join) use ($competition) {
                $join->on('sellers.id', '=', 'orders.seller_id')
                    ->where('orders.payment_status', 'paid')
                    ->where('orders.created_at', '>=', $competition->start_date)
                    ->where('orders.created_at', '<=', $competition->end_date);
            })
            ->select(
                'sellers.id',
                'sellers.f_name',
                'sellers.l_name',
                'sellers.phone',
                'sellers.email',
                'sellers.business_shortcode',
                DB::raw("CONCAT(sellers.f_name, ' ', sellers.l_name) as fullname"),
                'seller_types.name as seller_type_name',
                'competitions.*',
                DB::raw('COALESCE(SUM(orders.order_amount), 0) as total_sales')
            )
            ->where('competition_joined.competition_id', $id)
            ->groupBy('sellers.id')
            ->orderBy('total_sales', 'desc') // Descending order based on total sales
            ->get();

        if ($competition_charts->count() > 0) {
            // Calculate date difference and add it to each result
            $competition_charts->each(function ($result) {
                $start_date = Carbon::createFromFormat('Y-m-d', $result->start_date);
                $end_date = Carbon::createFromFormat('Y-m-d', $result->end_date);
                $result->date_difference = $start_date->diffInDays($end_date);
            });
        }

        $competition_name = $competition->competition_name;

        // return $competition_charts;
        return view('admin-views.competition.chart', compact('competition_charts', 'competition_name'));
    }
}
