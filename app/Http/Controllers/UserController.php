<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use App\Models\Contact;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserPurchase;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function form() {
        return view('form');
    }
    public function upload(Request $request) {
        $file = $request->file('file');
        $file->storeAs('public/tes', $file->getClientOriginalName());
        return "ok";
    }
    public function login(Request $request) {
        $email = $request->email;
        $at = $request->at;
        $status = 405;
        $message = "Gagal login.";

        $u = User::where('email', $email);
        $user = $u->first();

        if ($request->password != "") {
            if ($user->password != null) {
                if (Hash::check($request->password, $user->password)) {
                    $status = 200;
                    $message = "Berhasil login";
    
                    $u->update([
                        'token' => Str::random(32),
                    ]);
                    $user = $u->first();
                } else {
                    $user = null;
                    $message = "Kombinasi email dan password tidak tepat";
                }
            } else {
                $user = null;
                $message = "Kamu harus login menggunakan Google";
            }
        } else {
            if ($user == null) {
                $user = User::create([
                    'name' => $request->name,
                    'photo_url' => $request->photo,
                    'email' => $email,
                    'at' => $at,
                    'role' => "user",
                    'requested_to_be_host' => false,
                ]);
            } else {
                $status = 200;
                $message = "Berhasil login";

                $u->update([
                    'token' => Str::random(32),
                ]);
                $user = $u->first();
            }

            $status = 200;
            $message = "Berhasil login";
        }

        return response()->json([
            'message' => $message,
            'user' => $user,
            'status' => $status
        ]);
    }
    public function auth(Request $request) {
        $user = User::where('token', $request->token)->first();
        if ($user != null) {
            $paketName = "BASIC";
            $pakets = config('pakets');
            $paket = UserPurchase::where([
                ['user_id', $user->id],
                ['payment_status', 'settlement']
            ])->orderBy('active_until', 'DESC')->orderBy('created_at', 'DESC')->first();
            if ($paket != null) {
                $paketName = $paket->package_name;
            }
            if ($user != null) {
                $user->package_name = $paketName;
                $user->devices_count = UserDevice::where('user_id', $user->id)->get(['id'])->count();
            }
        }

        return response()->json([
            'user' => $user,
        ]);
    }
    public function hourlyTasks() {
        // SENDING SCHEDULED BROADCAST
        $scheduledBroadcasts = Broadcast::where([
            ['delivery_status', 'SCHEDULED'],
            ['delivery_time', '<', Carbon::now()]
        ])->get();

        if ($scheduledBroadcasts->count() > 0) {
            foreach ($scheduledBroadcasts as $broadcast) {
                BroadcastController::blast($broadcast);
            }
        }

        // REMOVING DEVICES
        $inactiveUsers = User::where('updated_at', '<', Carbon::now()->subDays(3))
        ->get(['id']);

        foreach ($inactiveUsers as $userID) {
            $dev = UserDevice::where('user_id', $userID->id);
            $devices = $dev->get();

            $dev->delete();

            foreach ($devices as $device) {
                Http::post(env('WHATSAPP_URL') . "/disconnect-client", [
                    'client_id' => $device->client_id
                ]);

                UserNotification::create([
                    'user_id' => $this->broadcast->user_id,
                    'body' => "Perangkat " . $device->label . " terputus. Mohon tambahkan ulang untuk menggunakannya kembali",
                    'has_read' => false,
                    'action' => "/devices"
                ]);
            }
        }

        return $inactiveUsers;
    }
    public function onboarding(Request $request) {
        $u = User::where('token', $request->token);

        $u->update([
            'company_name' => $request->company_name,
        ]);
    }
    public static function getUserAbility($user, $ability) {
        if (gettype($user) == "string") {
            $user = User::where('token', $user)->first();
        }
        
        $paketName = "BASIC";
        $pakets = config('pakets');
        $paket = UserPurchase::where([
            ['user_id', $user->id],
            ['payment_status', 'settlement']
        ])->orderBy('active_until', 'DESC')->orderBy('created_at', 'DESC')->first();
        if ($paket != null) {
            $paketName = $paket->package_name;
        }

        $thePaket = $pakets[$paketName];
        
        if ($ability == "max_devices") {
            $devices = UserDevice::where('user_id', $user->id)->get(['id']);
            return $thePaket['max_devices'] > $devices->count();
        }
        if ($ability == "max_contacts") {
            $contacts = Contact::where('user_id', $user->id)->get(['id']);
            return $thePaket['max_contacts'] > $contacts->count();
        }
    }

    public function dashboard(Request $request) {
        $user = User::where('token', $request->token)->first();
        $activeDevicesCount = 0;

        $activeDevicesCount = UserDevice::where('user_id', $user->id)->get(['id'])->count();
        $contacts_count = Contact::where('user_id', $user->id)->get(['id'])->count();
        $broadcasts_count = Broadcast::where('user_id', $user->id)->get(['id'])->count();
        $broadcasts_sent_count = Broadcast::where([
            ['user_id', $user->id],
            ['delivery_status', 'DONE']
        ])->get(['id'])->count();

        if ($user != null) {
            $paketName = "BASIC";
            $pakets = config('pakets');
            $paket = UserPurchase::where([
                ['user_id', $user->id],
                ['payment_status', 'settlement']
            ])->orderBy('active_until', 'DESC')->orderBy('created_at', 'DESC')->first();
            if ($paket != null) {
                $paketName = $paket->package_name;
            }
            if ($user != null) {
                $user->package_name = $paketName;
                $user->devices_count = UserDevice::where('user_id', $user->id)->get(['id'])->count();
            }
        }

        return response()->json([
            'active_devices_count' => $activeDevicesCount,
            'contacts_count' => $contacts_count,
            'broadcasts_count' => $broadcasts_count,
            'broadcasts_sent_count' => $broadcasts_sent_count,
        ]);
    }
    public function contact(Request $request) {
        $user = User::where('token', $request->token)->first();
        $contacts = [];
        $contacts_count = 0;
        $groups = [];
        $groups_count = 0;

        $g = Group::where('user_id', $user->id);
        $q = Contact::where('user_id', $user->id);

        if ($request->q != "") {
            $q = $q->where('name', 'LIKE', '%'.$request->q.'%');
        }
        
        if ($request->viewing == "group") {
            $groups = $g->orderBy('created_at', 'DESC')->paginate(25);
            foreach ($groups as $index => $gr) {
                $membersData = GroupMember::where('group_id', $gr->id)->get();
                $membersID = [];
                foreach ($membersData as $mem) {
                    array_push($membersID, $mem->contact_id);
                }
                $groups[$index]['members_id'] = $membersID;
            }
        } else {
            $contacts = $q->with(['groupsJoined'])->orderBy('created_at', 'DESC')->paginate(25);
        }
        
        $contacts_count = Contact::where('user_id', $user->id)->get(['id'])->count();
        $groups_count = $g->get(['id'])->count();

        return response()->json([
            'contacts' => $contacts,
            'contacts_count' => $contacts_count,
            'groups' => $groups,
            'groups_count' => $groups_count,
        ]);
    }
    public function group(Request $request) {
        $user = User::where('token', $request->token)->first();
        $groups = Group::where('user_id', $user->id)->get();
        foreach ($groups as $g => $gr) {
            $groups[$g]->members_count = GroupMember::where('group_id', $gr->id)->get(['id'])->count();
            $lm = GroupMember::where('group_id', $gr->id)->with(['contact'])->orderBy('created_at', 'DESC')->first();
            $groups[$g]->latest_member = $lm->contact;
        }

        return response()->json([
            'groups' => $groups,
        ]);
    }
    public function update(Request $request) {
        $u = User::where('token', $request->token);
        $u->update([
            'name' => $request->name,
        ]);
        $user = $u->first();

        return response()->json([
            'message' => "Berhasil menyimpan perubahan",
            'user' => $user,
        ]);
    }
    public function broadcast(Request $request) {
        $user = User::where('token', $request->token)->first();
        $broadcasts = Broadcast::where('user_id', $user->id)
        ->with(['device', 'group', 'logs'])
        ->paginate(25);

        return response()->json([
            'broadcasts' => $broadcasts,
        ]);
    }
    public function notification(Request $request) {
        $user = User::where('token', $request->token)->first();
        $notifs = UserNotification::where('user_id', $user->id)->orderBy('created_at', 'DESC')->paginate(10);

        return response()->json([
            'notifications' => $notifs
        ]);
    }

    public function upgrade(Request $request) {
        $user = User::where('token', $request->token)->first();
        $orderID = Str::random(16);
        $startActive = Carbon::now();
        $price = $request->price;
        $period = $request->period;
        $method = $request->payment_method;
        $channel = $request->payment_channel;
        $quantity = $period === "monthly" ? 3 : 12;
        $totalPay = $quantity * $price;

        // GET PAKET
        $paket = UserPurchase::where('user_id', $user->id)->orderBy('active_until', 'DESC')->first();
        if ($paket != null && $request->package_name == $paket->package_name) {
            $masihAdaPaketAktif = Carbon::parse($paket->active_until)->gt($startActive);
            if ($masihAdaPaketAktif) {
                $startActive = Carbon::parse($paket->active_until);
            }
        }

        $midtransMode = env('MIDTRANS_MODE');
        $midtransPayload = [
            'transaction_details' => [
                'order_id' => $orderID,
                'gross_amount' => $totalPay,
            ]
        ];
        if ($method == "bank_transfer") {
            $midtransPayload['payment_type'] = "bank_transfer";
            $midtransPayload['bank_transfer'] = [
                'bank' => strtolower($channel)
            ];
        } else if ($method == "qris") {
            $midtransPayload['payment_type'] = "gopay";
        }

        $midtransResponse = Http::withBasicAuth(MidtransController::getKey('SERVER'), '')
        ->post(MidtransController::getBaseUrl() . "/charge", $midtransPayload)
        ->body();
        $mtRes = json_decode($midtransResponse, false);

        $toSave = [
            'user_id' => $user->id,
            'order_id' => $orderID,
            'transaction_id' => $mtRes->transaction_id,
            'package_name' => $request->package_name,
            'quantity' => $quantity,
            'price' => $price,
            'total_pay' => $totalPay,
            'payment_method' => $method,
            'payment_channel' => $channel,
            'payment_payload' => $midtransResponse,
            'payment_status' => $mtRes->transaction_status,
            'active_until' =>  $startActive->addMonths($period === "monthly" ? 3 : 12)->format('Y-m-d H:i:s'),
        ];

        $saveData = UserPurchase::create($toSave);

        return response()->json([
            'ok'
        ]);
    }
    public function upgradeHistory(Request $request) {
        $user = User::where('token', $request->token)->first();

        $purchases = UserPurchase::where('user_id', $user->id)->orderBy('created_at', 'DESC')->paginate(20);
        
        return response()->json([
            'purchases' => $purchases,
        ]);
    }
}
