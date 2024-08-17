<?php

namespace App\Http\Controllers;

use App\Jobs\KirimWa;
use App\Models\Broadcast;
use App\Models\BroadcastLog;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BroadcastController extends Controller
{
    public function tes() {
        $response = Http::post('http://127.0.0.1:2024/send-message', [
            'clientId' => 1148,
            'number' => "6282142038064",
            'message' => "Halo semua",
        ]);

        return $response->body();
    }
    public static function getVars($string) {
        $pattern = '/%([^%]*)%/';
        preg_match_all($pattern, $string, $matches);
        return $matches[1];
    }
    public static function blast($broadcast, $group = null, $device = null) {
        Log::info("Broadcasting : " . $broadcast->title);
        if ($group == null) {
            $group = $broadcast->group;
        }
        if ($device == null) {
            $device = $broadcast->device;
        }
        foreach ($group->members as $member) {
            $theContent = $broadcast->content;
            $vars = self::getVars($theContent);
            $templates = [
                'contact' => $member,
                'group' => $group
            ];

            foreach ($vars as $var) {
                $v = explode(".", $var);
                $theContent = str_replace("%".$var."%", $templates[$v[0]]->{$v[1]}, $theContent);
            }

            KirimWa::dispatch([
                'broadcast' => $broadcast,
                'destination' => $member,
                'device' => $device,
                'message' => $theContent,
            ]);
        }
    }
    public function send(Request $request) {
        $user = User::where('token', $request->token)->first();
        $group = Group::where('id', $request->group_id)->with(['members'])->first();
        $device = UserDevice::where('id', $request->device_id)->first();

        $toSave = [
            'user_id' => $user->id,
            'device_id' => $request->device_id,
            'group_id' => $group->id,
            'title' => $request->title,
            'content' => $request->content, 
            'delay_time' => $request->delay_time,
            'group_member' => $group->members->count(),
        ];
        if ($request->delivery_time != "PROCESSING") {
            $toSave['delivery_time'] = $request->delivery_time;
            $toSave['delivery_status'] = "SCHEDULED";
        } else {
            $toSave['delivery_status'] = $request->delivery_time;
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageFileName = $image->getClientOriginalName();
            $image->storeAs('public/bc_images', $imageFileName);
            $toSave['image'] = $imageFileName;
        }

        $saveData = Broadcast::create($toSave);

        if ($request->delivery_time == "PROCESSING") {
            self::blast($saveData, $group, $device);
        }

        return response()->json([
            'ok'
        ]);
    }
    public function detail($id, Request $request) {
        $broadcast = Broadcast::where('id', $id)->with(['device', 'group'])->first();

        return response()->json([
            'broadcast' => $broadcast,
        ]);
    }
    public function log($id) {
        $logs = BroadcastLog::where('broadcast_id', $id)->orderBy('created_at', 'DESC')->with(['contact'])->paginate(50);

        return response()->json(['logs' => $logs]);
    }
    public function sendNow($id, Request $request) {
        $bc = Broadcast::where('id', $id);
        $broadcast = $bc->with(['device', 'group'])->first();
        $group = $broadcast->group;
        $device = $broadcast->device;

        $bc->update(['delivery_status' => "PROCESSING"]);
        
        self::blast($broadcast, $group, $device);

        return response()->json(['ok']);
    }
}
