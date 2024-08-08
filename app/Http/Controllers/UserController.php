<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use App\Models\Contact;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserController extends Controller
{
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

    public function dashboard(Request $request) {
        $user = User::where('token', $request->token)->first();
        $activeDevicesCount = UserDevice::where('user_id', $user->id)->get(['id'])->count();

        return response()->json([
            'active_devices_count' => $activeDevicesCount,
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
}
