<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class insertcsvdata extends Controller
{
     protected $data;
    protected $db;
    protected $other;
    public function store(Request $request){
    	 date_default_timezone_set("Asia/Calcutta");
    	  $date = date('Y-m-d H:i:s');
          $values = array("created_at"=> $date,"updated_at"=>$date);
        $this->data = $request->all();
        $name = "machinetype";
        $val = $this->data["machinetype"];
        $table = $this->data["machinename"];
        $valuesd = array($name => $val);
        $values = array_merge($values,$valuesd);
        $this->other = $request->file('fcsv');
        $filename = $this->other->getClientOriginalName();
        $filepath = $this->other->path();
        $filename = explode(".",$filename);
        if($filename[1] == "csv" || $filename[1] == "CSV"){
            $handle = fopen($filepath, "r");
            $headers = fgetcsv($handle, 1000, ",");
            $colname =  explode(";",$headers[0]);
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
            {
                $colvalue =  explode(";",$data[0]);
                for($j=0;$j<count($colname);++$j){
                    $cval = $colvalue[$j];
                    $colkey = $colname[$j];
                    if($colkey != ""){
                    if($colkey == "id" || $colkey == "Id" || $colkey == "ID"){
                        $colkey = "lb_lb".$colkey;
                    }
                    $valarr = array($colkey => $cval);
                    $values = array_merge($values, $valarr);
                }
                }
                DB::table($table)->insert($values);
            }
            //$this->other = $colname;
            fclose($handle);
       
        }
        try{
            return response(array("status"=>"success","message"=>"Data inserted in database.","data"=>""),200)->header("Content-Type","application/json");
        }
        catch(QueryException $error){
          return response(array("status"=>"error","message"=>"CSV file column name not matched."),404)->header("Content-Type","application/json");
        
            }
    
    }
}
