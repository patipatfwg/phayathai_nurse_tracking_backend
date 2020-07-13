<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
set_time_limit(0);

header('Content-Type: application/json');

$headers_Authorization = $_SERVER['HTTP_AUTHORIZATION'];

if($_SERVER['REQUEST_METHOD']=='POST' || $_SERVER['REQUEST_METHOD']=='GET')
{
    if($headers_Authorization=='phayathai@freewill')
    {
        $FLAG_GET_ROOM=1;
        if($FLAG_GET_ROOM==1)
        {
            $androidbox_device_json = trim(file_get_contents("json/androidbox.json"));
            $androidbox_device_json = json_decode($androidbox_device_json, true);
            $config_androidbox_device_list = $androidbox_device_json['device'];
            for($getRoom=0;$getRoom<count($config_androidbox_device_list);$getRoom++)
            {
                $get_nurse_list = [];
                $device_device_id = $config_androidbox_device_list[$getRoom]['device_id'];
                $device_title = $config_androidbox_device_list[$getRoom]['title'];
                $device_ordinal = $config_androidbox_device_list[$getRoom]['ordinal'];
                $device_device_id_URL = $device_device_id.".json";
                $device_device_id_URL_path = "json_androidbox/".$device_device_id_URL;

                //// Get Nurse ////
                if( file_exists($device_device_id_URL_path) )
                {

                    $androidbox_json = trim(file_get_contents($device_device_id_URL_path));
                    $androidbox_json = json_decode($androidbox_json, true);
                    $iTAG_list = $androidbox_json['itag']['itag_list'];
                    for($getNurse=0;$getNurse<count($iTAG_list);$getNurse++)
                    {
                        $mac_address = $iTAG_list[$getNurse]['mac_address'];
                        $distance = $iTAG_list[$getNurse]['distance'];
                        $title = "Unknown";

                        //// Get Title ////
                        if($mac_address!='')
                        {
                            $filename = 'json/itag.json';
                            $itag_device_json = trim(file_get_contents($filename));
                            $itag_device_json = json_decode($itag_device_json, true);
                            $iTAG_device = $itag_device_json['device'];
                    
                            for($Anum=0;$Anum<count($iTAG_device);$Anum++)
                            {
                                if($mac_address==$iTAG_device[$Anum]['mac_address'])
                                {
                                    $title = $iTAG_device[$Anum]['title'];
                                }
                            }
                        }
                        //// END ////
                        $get_nurse_list[$getNurse] = array(
                            "title"=>$title,
                            "mac_address"=>$mac_address,
                            "distance"=>$distance
                        );
                    }

                    //Delete
                    unlink($device_device_id_URL_path);

                }
                //// END ////
                $DataRoom[$getRoom] = array(
                    "ordinal"=>$device_ordinal,
                    "device_id"=>$device_device_id,
                    "room_title"=>$device_title,
                    "nurse_list"=>$get_nurse_list
                );
                if( file_exists($device_device_id_URL) )
                {
                    unlink($device_device_id_URL);  
                }
            }
    
            //Sort
            sort($DataRoom);
            foreach ($DataRoom as $key => $val) {
                $DataRoom[$key] = array(
                    "ordinal"=>$val['ordinal'],
                    "device_id"=>$val['device_id'],
                    "room_title"=>$val['room_title'],
                    "nurse_list"=>$val['nurse_list']
                );
            }
            //
    
            if(count($DataRoom)>1)
            {
                $DataRoom = $DataRoom;
            }
            else
            {
                $DataRoom = [$DataRoom];
            }
            $data = [
                "head"=>array("code"=>200,"message"=>"OK"),
                "body"=>array("room"=>$DataRoom)
            ]; 
        }
    }
}
else
{
    $code = 400;
    $message = "METHOD WHAT => KICK KICK!!!";
    $version = 'xxxx2020xxxxx';
    $data = [
        "head"=>array("code"=>$code,"message"=>$message,"version"=>$version),
        "body"=>[]
    ];
}

echo json_encode($data,JSON_PRETTY_PRINT);