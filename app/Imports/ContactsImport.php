<?php

namespace App\Imports;

use App\Models\Contact;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;

class ContactsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public $user;

    public function __construct($props)
    {
        $this->user = $props['user'];
    }

    public function model(array $row)
    {
        if (
            $row[0] != "" &&
            substr($row[0], 0, 3) != "Nam" &&
            substr($row[1], 0, 3) != "Wha"
        ) {
            return new Contact([
                'user_id' => $this->user->id,
                'name' => $row[0],
                'whatsapp' => $row[1],
                'email' => $row[2],
                'groups' => NULL
            ]);
        }
    }
}
