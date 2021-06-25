<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RfCategory;
use App\Models\MediaName;

use DB;
use Illuminate\Support\Str;

class LeadController extends Controller
{
   public $media;
    // ------------- [ Import Leads ] ----------------
    public function importLeads(Request $request)
    {
        $data           =       array();
        $request->validate([
            "csv_file" => "required",
        ]);
        $this->media = MediaName::select("id","name", "cat_name")->get();
        $file = $request->file("csv_file");
        $csvData = file_get_contents($file);

        $rows = array_map("str_getcsv", explode("\n", $csvData));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            if (isset($row[0])) {
                if ($row[0] != "") {
                    $row = array_combine($header, $row);
                    $uuid = Str::uuid()->toString();
                    // master lead data
                    $leadData = array(
                        "rfnumber" => $row['rfnumber'],
                        "first_name" => $row['first_name'],
                        "last_name" => $row['last_name'],
                        "email" => $row["email"],
                        // "phone" => str_replace("'", "", $row["phone_no"]),
                        "password" => bcrypt($row["password"]),
                        "active" => $row["active"],
                        "confirmed" => $row["confirmed"],
                        "status" => $row["status"],
                        "user_type" => $row["user_type"],
                        "bio" => $row["bio"],
                        "uuid" => $uuid,
                        "directshare" => strtolower($row["directshare"]),
                    );

                    // ----------- check if lead already exists ----------------
                    $checkLead  =   User::where("email", "=", $row["email"])->first();
                    
                     if (!is_null($checkLead)) {

                        $data["status"]     =       "failed";
                        $data["message"]    =       "These users already exist!";
                    } else {
                        $lead = User::create($leadData);
                        DB::table('role_user')->insert(array(['user_id' => $lead->id, 'role_id' => '3']));
                        foreach ($this->media as $madata) {
                               
                            isset($row[$madata->name]) == true ? $this->socialmedia(array("url" => $row[$madata->name], 'user_id' =>$lead->id, "cat_name"=> $madata->cat_name,"media_id"=>$madata->id)) : "";
                        }
                        
                        if(!is_null($lead)) {
                            $data["status"]     =       "success";
                            $data["message"]    =       "Users imported successfully";
                        }
                    }
                }
            }
        }

        return back()->with($data["status"], $data["message"]);
    }
    public function importrftags(Request $request)
    {
        $data           =       array();
        $request->validate([
            "csv_file" => "required",
        ]);

        $file = $request->file("csv_file");
        $csvData = file_get_contents($file);

        $rows = array_map("str_getcsv", explode("\n", $csvData));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            if (isset($row[0])) {
                if ($row[0] != "") {
                    $row = array_combine($header, $row);
                    if (empty($row["assignto"])) {
                        $assignto =   null;
                    } else {
                        $assignto =   $row["assignto"];
                    }
                    $leadData = array(
                        "name" => $row['name'],
                        "status" => '1',
                        "created_by" => '1',
                        "assignto" => $assignto,

                    );

                    // ----------- check if lead already exists ----------------
                    $checkLead        =       RfCategory::where("name", "=", $row["name"])->first();

                    if (!is_null($checkLead)) {

                        $data["status"]     =       "failed";
                        $data["message"]    =       "These RF Tags already exist!";
                    } else {
                        $lead = RfCategory::create($leadData);
                        if (!is_null($lead)) {
                            $data["status"]     =       "success";
                            $data["message"]    =       "RF Tags imported successfully";
                        }
                    }
                }
            }
        }

        return back()->with($data["status"], $data["message"]);
    } 
    // start social media save 
    private function socialmedia($sdata){
        $sdata["url"] = $sdata["url"] == "" ? "NULL" : $sdata["url"];
            DB::table('media_data')->insert(array(['user_id' => $sdata["user_id"], 'category_id' => $sdata['cat_name'],"media_id"=> $sdata['media_id'], "value"=> $sdata["url"],"sort_orders"=> $sdata['media_id']]));
    }
    // end social media save 
}
