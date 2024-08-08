<?php

namespace App\Http\Controllers;

use App\Imports\ContactsImport;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ContactController extends Controller
{
    public function store(Request $request) {
        $user = User::where('token', $request->token)->first();

        $saveData = Contact::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'whatsapp' => $request->whatsapp,
            'country_code' => "62",
            'email' => $request->email,
            'groups' => null,
        ]);

        return response()->json([
            'message' => $request->name . " berhasil ditambahkan sebagai kontak"
        ]);
    }
    public function importForm() {
        return view('importForm');
    }
    public function import(Request $request) {
        $user = User::where('token', $request->token)->first();

        Excel::import(
            new ContactsImport([
                'user' => $user,
            ]),
            $request->file('berkas')
        );
    }
    public function delete(Request $request) {
        $user = User::where('token', $request->token)->first();
        $data = Contact::where([
            ['user_id', $user->id],
            ['id', $request->id]
        ]);
        $contact = $data->first();
        $deleteData = $data->delete();

        return response()->json([
            'message' => "Kontak " . $contact->name . " berhasil dihapus"
        ]);
    }
    public function update(Request $request) {
        $user = User::where('token', $request->token)->first();
        $data = Contact::where([
            ['user_id', $user->id],
            ['id', $request->id]
        ]);
        $contact = $data->first();

        $updateData = $data->update([
            'name' => $request->name,
            'whatsapp' => $request->whatsapp,
            'email' => $request->email,
        ]);

        return response()->json([
            'message' => "Berhasil mengubah data kontak " . $contact->name
        ]);
    }
}
