<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function detail(Request $request) {
        $user = User::where('token', $request->token)->first();
        $group = Group::where([
            ['id', $request->id],
            ['user_id', $user->id]
        ])->first();
        $members = [];
        $members_count = 0;

        if ($group != null) {
            $membersData = GroupMember::where('group_id', $group->id)->get();
            $membersID = [];
            foreach ($membersData as $mem) {
                array_push($membersID, $mem->contact_id);
            }
            $group->members_id = $membersID;
            $members = GroupMember::where('group_id', $group->id)->with(['contact'])->paginate(25);
            $members_count = GroupMember::where('group_id', $group->id)->get(['id'])->count();
        }

        return response()->json([
            'group' => $group,
            'members' => $members,
            'members_count' => $members_count,
        ]);
    }
    public function store(Request $request) {
        $user = User::where('token', $request->token)->first();
        
        $saveData = Group::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'color' => $request->color,
            'members_count' => 0,
        ]);

        return response()->json([
            'message' => "Grup " . $request->name . " berhasil dibuat"
        ]);
    }
    public function delete(Request $request) {
        $user = User::where('token', $request->token)->first();
        $data = Group::where('id', $request->id);
        $group = $data->first();

        if ($group->user_id == $user->id) {
            $deleteData = $data->delete();
        }

        return response()->json([
            'message' => "Grup " . $group->name . " berhasil dihapus"
        ]);
    }
    public function changeName($id, Request $request) {
        $data = Group::where('id', $id);
        $updateName = $data->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => "Nama grup berhasil diubah menjadi " . $request->name,
        ]);
    }

    // MEMBER AREA
    public function addMember(Request $request) {
        $saveData = GroupMember::create([
            'contact_id' => $request->contact_id,
            'group_id' => $request->group_id,
        ]);

        Group::where('id', $request->group_id)->increment('members_count');

        return response()->json(['ok']);
    }
    public function removeMember(Request $request) {
        $data = GroupMember::where('id', $request->id);
        $member = $data->with(['contact', 'group'])->first();
        $del = $data->delete();

        Group::where('id', $request->id)->decrement('members_count');

        return response()->json([
            'message' => "Berhasil menghapus " . $member->contact->name . " dari " . $member->group->name,
        ]);
    }
}
