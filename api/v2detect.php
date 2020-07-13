<?php 
ini_set('display_errors', 'On');
error_reporting(E_ALL);
set_time_limit(0);

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD']=='POST')
{
    $content = trim(file_get_contents("php://input"));
    $data_json_input = json_decode($content, true);    
    if( isset($data_json_input) )
    {
        $FLAG_WRITE_ANDROIDBOX = 1;
        $FLAG_BACKEND = 0;

        //// Androidbox ////
        if( isset($data_json_input['androidbox']) && isset($data_json_input['androidbox']['device_id']) )
        { 
            $FLAG_ANDROIDBOX = 1;
            $androidbox_device_id = $data_json_input['androidbox']['device_id'];
        }
        else
        {
            $FLAG_ANDROIDBOX = 0;
        }
        //// END ////

        if( $FLAG_ANDROIDBOX==1 )
        {
            ///// isAndroidbox ////
            $filename = 'json/androidbox.json';
            $content = trim(file_get_contents($filename));
            $data_json = json_decode($content, true);
            $config_androidbox_device_list = $data_json['device'];
            $FLAG_IS_ANDROIDBOX = 0;
            for($num=0;$num<count($config_androidbox_device_list);$num++)
            {
                $config_androidbox_device_list_device_id = $config_androidbox_device_list[$num]['device_id'];
                if($config_androidbox_device_list_device_id==$androidbox_device_id)
                {
                    $FLAG_IS_ANDROIDBOX = 1;
                }
            }
            //// END ////

            //// Check Version iTAG ////
            if($FLAG_IS_ANDROIDBOX==1)
            {
                //// Check iTAG Params ////
                if(isset($data_json_input['itag']))
                {
                    $FLAG_iTAG = 1;
                    $itag_version = $data_json_input['itag']['version'];
                    $itag_list = $data_json_input['itag']['itag_list'];
                }
                else
                {
                    $FLAG_iTAG = 0;
                }
                //// END ////

                //// iTAG////
                if( $FLAG_iTAG==1 )
                {
                    ///// Check iTAG Version ////
                    $filename = 'json/itag.json';
                    $content = trim(file_get_contents($filename));
                    $data_json = json_decode($content, true);
                    $config_itag_list_version = $data_json['version'];

                    //// Check config_itag_list_version Update ////
                    if($config_itag_list_version==$itag_version)
                    {
                        $res_itag_list = ["itag_list"=>null];
                    }
                    else
                    {
                        $config_itag_list_device = $data_json['device'];
                        for($num=0;$num<count($config_itag_list_device);$num++)
                        {
                            $itag_list_data[$num] = array(
                                'mac_address'=> $config_itag_list_device[$num]['mac_address']
                            );
                        }
                        $res_itag_list = ["itag_list"=>$itag_list_data];
                    }
                    //// END ////

                    $code = 200;
                    $message = "Send Success";
                    $version = $config_itag_list_version;
                    $data = $res_itag_list;
                    $FLAG_BACKEND = 1;

                }
                else
                {
                    $code = 500;
                    $message = "No Have iTAG Params => KICK KICK!!!";
                    $version = 'xxxx2020xxxxx';
                    $data = [];
                }
                //// END ////
            }
            else
            {
                $code = 500;
                $message = "Android Box Not Freewill => KICK KICK!!!";
                $version = 'xxxx2020xxxxx';
                $data = []; 
            }
            //// END ////
        }
        else
        {
            $code = 500;
            $message = "No Have Android Box Params => KICK KICK!!!";
            $version = 'xxxx2020xxxxx';
            $data = [];   
        }
        //// END ////
    }
    else
    {
        $code = 500;
        $message = "BODY RAW JSON => KICK KICK!!!";
        $version = 'xxxx2020xxxxx';
        $data = [];
    }
}
else if($_SERVER['REQUEST_METHOD']=='GET')
{
    $code = 400;
    $message = "METHOD WHAT => KICK KICK!!!";
    $version = 'xxxx2020xxxxx';
    $data = [];
}

$data = [
    "head"=>array("code"=>$code,"message"=>$message,"version"=>$version),
    "body"=>$data
];

echo json_encode($data,JSON_PRETTY_PRINT);
 
//// Backend ////
if($FLAG_BACKEND==1)
{
    if($FLAG_WRITE_ANDROIDBOX==1)
    {
        $filename = "json_androidbox/".$androidbox_device_id.".json";
        $file_encode = json_encode($data_json_input,true);
        file_put_contents($filename, $file_encode );
        chmod($filename,0777);    
    }
}
/////////////////////////