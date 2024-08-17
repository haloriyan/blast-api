<?php

namespace App\Http\Controllers;

use Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Contact;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserPurchase;
use App\Models\Admin;
use App\Models\Broadcast;
use App\Models\BroadcastLog;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AdminController extends Controller
{
    public function login(Request $request) {
        $adm = Admin::where('username', $request->username);
        $admin = $adm->first();

        if ($admin != null && Hash::check($request->password, $admin->password)) {
            $token = Str::random(32);
            $adm->update([
                'token' => $request->token,
            ]);
            $admin = $adm->first();

            return response()->json([
                'status' => 200,
                'admin' => $admin,
            ]);
        } else {
            return response()->json(['status' => 423]);
        }
    }

    public function broadcast() {
        $broadcasts = Broadcast::orderBy('created_at', 'DESC')
        ->with(['device', 'group', 'logs', 'user'])
        ->paginate(20);

        return response()->json([
            'broadcasts' => $broadcasts,
        ]);
    }
    public function contact(Request $request) {
        $contacts = [];
        $contacts_count = 0;
        $groups = [];
        $groups_count = 0;

        $g = new Group();
        $q = new Contact();

        if ($request->q != "") {
            $q = $q->where('name', 'LIKE', '%'.$request->q.'%');
        }
        
        if ($request->viewing == "group") {
            $groups = $g->with(['user'])->orderBy('created_at', 'DESC')->paginate(25);
            foreach ($groups as $index => $gr) {
                $membersData = GroupMember::where('group_id', $gr->id)->get();
                $membersID = [];
                foreach ($membersData as $mem) {
                    array_push($membersID, $mem->contact_id);
                }
                $groups[$index]['members_id'] = $membersID;
            }
        } else {
            $contacts = $q->with(['groupsJoined'])->with(['user'])->orderBy('created_at', 'DESC')->paginate(25);
        }
        
        $contacts_count = Contact::get(['id'])->count();
        $groups_count = $g->get(['id'])->count();

        return response()->json([
            'contacts' => $contacts,
            'contacts_count' => $contacts_count,
            'groups' => $groups,
            'groups_count' => $groups_count,
        ]);
    }
    public function users(Request $request) {
        $filter = [];
        if ($request->q != "") {
            array_push($filter, ['name', 'LIKE', '%'.$request->q.'%']);
        }

        $users = User::where($filter)->paginate(25);
        foreach ($users as $u => $user) {
            $users[$u]->groups_count = Group::where('user_id', $user->id)->get(['id'])->count();
            $users[$u]->contacts_count = Contact::where('user_id', $user->id)->get(['id'])->count();
        }

        return response()->json([
            'users' => $users,
        ]);
    }
    public function user($id) {
        $user = User::where('id', $id)->first();
        if ($user != null) {
            $user->contacts_count = Contact::where('user_id', $id)->get(['id'])->count();
            $user->groups_count = Group::where('user_id', $id)->get(['id'])->count();
            $user->devices_count = UserDevice::where('user_id', $id)->get(['id'])->count();
        }

        return response()->json([
            'user' => $user,
        ]);
    }
    public function userContact($id, Request $request) {
        $filter = [['user_id', $id]];
        if ($request->q != "") {
            array_push($filter, ['name', 'LIKE', '%'.$request->q.'%']);
        }
        $contacts = Contact::where($filter)->with(['groupsJoined'])->paginate(25);
        
        return response()->json([
            'contacts' => $contacts,
        ]);
    }
    public function userGroup($id, Request $request) {
        $filter = [['user_id', $id]];
        if ($request->q != "") {
            array_push($filter, ['name', 'LIKE', '%'.$request->q.'%']);
        }
        $groups = Group::where($filter)->paginate(25);
        foreach ($groups as $g => $gr) {
            $membersIDRaw = GroupMember::where('group_id', $gr->id)->get(['id']);
            $membersID = [];
            foreach ($membersIDRaw as $id) {
                array_push($membersID, $id->id);
            }
            $groups[$g]->members_id = $membersID;
        }

        return response()->json([
            'groups' => $groups,
        ]);
    }
    public function userDevice($id, Request $request) {
        $devices = UserDevice::where('user_id', $id)->paginate(25);

        return response()->json([
            'devices' => $devices,
        ]);
    }
    public function dashboard() {
        $revenueRaw = UserPurchase::whereBetween('created_at', [
            Carbon::now()->startOfMonth()->format('Y-m-d H:i:s'),
            Carbon::now()->endOfMonth()->format('Y-m-d H:i:s'),
        ])->get(['id', 'total_pay', 'payment_status']);
        $pending = 0;
        $paid = 0;
        $pendingCount = 0;
        $paidCount = 0;
        foreach ($revenueRaw as $rev) {
            if ($rev->payment_status == "settlement") {
                $paid += $rev->total_pay;
                $paidCount += 1;
            } else {
                $pending += $rev->total_pay;
                $pendingCount += 1;
            }
        }

        $usersCount = User::whereBetween('created_at', [
            Carbon::now()->startOfMonth()->format('Y-m-d H:i:s'),
            Carbon::now()->endOfMonth()->format('Y-m-d H:i:s'),
        ])->get(['id'])->count();

        $broadcastsCount = Broadcast::whereBetween('created_at', [
            Carbon::now()->startOfMonth()->format('Y-m-d H:i:s'),
            Carbon::now()->endOfMonth()->format('Y-m-d H:i:s'),
        ])->get(['id'])->count();

        $datesRaw = CarbonPeriod::create(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
        $dates = [];
        $usersChartValue = [];
        $broadcastsChartValue = [];
        $devicesChartValue = [];
        $logsChartValue = [];
        foreach ($datesRaw as $dt) {
            $usersThisDay = User::where('created_at', 'LIKE', '%'.$dt->format('Y-m-d').'%')->get(['id']);
            $bcThisDay = Broadcast::where('created_at', 'LIKE', '%'.$dt->format('Y-m-d').'%')->get(['id']);
            $devicesThisDay = UserDevice::where('created_at', 'LIKE', '%'.$dt->format('Y-m-d').'%')->get(['id']);
            $logsThisDay = BroadcastLog::where('created_at', 'LIKE', '%'.$dt->format('Y-m-d').'%')->get(['id']);
            
            array_push($dates, $dt->isoFormat('DD MMM'));
            array_push($usersChartValue, $usersThisDay->count());
            array_push($devicesChartValue, $devicesThisDay->count());
            array_push($broadcastsChartValue, $bcThisDay->count());
            array_push($logsChartValue, $logsThisDay->count());
        }

        $chartsData = [
            'users' => [
                'labels' => $dates,
                'datasets' => $usersChartValue,
            ],
            'broadcasts' => [
                'labels' => $dates,
                'datasets' => $broadcastsChartValue,
            ],
            'devices' => [
                'labels' => $dates,
                'datasets' => $devicesChartValue,
            ],
            'logs' => [
                'labels' => $dates,
                'datasets' => $logsChartValue,
            ]
        ];
        // Users chart

        return response()->json([
            'users_count' => $usersCount,
            'broadcasts_count' => $broadcastsCount,
            'pending_count' => $pendingCount,
            'paid_count' => $paidCount,
            'paid' => $paid,
            'pending' => $pending,
            'charts_data' => $chartsData,
        ]);
    }
    public function purchase(Request $request) {
        $purc = UserPurchase::orderBy('created_at', 'DESC');
        if ($request->q != "") {
            $purc = $purc->where('order_id', 'LIKE', '%'.$request->q.'%')
            ->orWhereHas('user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%'.$request->q.'%');
            });
        }
        $purchases = $purc->with(['user'])->paginate(25);

        $revenueRaw = UserPurchase::where([
            ['payment_status', 'settlement']
        ])->whereBetween('created_at', [
            Carbon::now()->startOfMonth()->format('Y-m-d H:i:s'),
            Carbon::now()->endOfMonth()->format('Y-m-d H:i:s'),
        ])->get(['total_pay']);
        $revenue = 0;
        foreach ($revenueRaw as $rev) {
            $revenue += $rev->total_pay;
        }
        

        return response()->json([
            'purchases' => $purchases,
            'revenue' => $revenue,
        ]);
    }
    public function makePurchasePaid(Request $request) {
        $purc = UserPurchase::where('id', $request->id);
        $purc->update(['payment_status' => "settlement"]);

        return response()->json(['ok']);
    }

    public function admin(Request $request) {
        $admins = Admin::orderBy('created_at', 'DESC')->get();
        
        return response()->json([
            'admins' => $admins,
        ]);
    }
    public function adminStore(Request $request) {
        $saveData = Admin::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => bcrypt($request->password),
        ]);

        return response()->json(['ok']);
    }
    public function adminDelete(Request $request) {
        $adm = Admin::where('id', $request->id);
        $adm->delete();

        return response()->json(['ok']);
    }
    public function adminChangePass(Request $request) {
        $adm = Admin::where('id', $request->id);
        $adm->update([
            'password' => bcrypt($request->password),
            'token' => null,
        ]);

        return response()->json(['ok']);
    }
}
