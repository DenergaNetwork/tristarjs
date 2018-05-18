						<?php

//please use advanced firewall rules and tunnel through nginx

$tristar_ip="ip:port";

$omega=array(
    "Battery Voltage"  =>array("38","V"),
    "Target Voltage"   =>array("51","V"),
	"Input Power"  =>array("39","A"),
	"Array Voltage"     =>array("27","V"),
	"Array Power"      =>array("29","A"),
	"Output Power"  =>array("58","W"),
	"Sweep Vmp"       =>array("61","V"),
	"Sweep Voc"       =>array("62","V"),
	"Sweep Pmax"      =>array("60","W"),
	"Battery Temp." =>array("37","C"),		
	"Controller Temp." =>array("35","C"),
	"Kilowatt hours"       =>array("56","kWh"),
	"Status"   =>array("50",""),
	"Absorption"   =>array("77","min"),
	"Balance" =>array("78","min"),
	"Float"     =>array("79","min"),
	"Max Power Input(day)" =>array("70","W"),
	"Amper hours(day)"=>array("67","Ah"),
	"Watt hours(day)"=>array("68","Wh"),
	"Max Voltage(day)" =>array("66","V"),	
	"Max Battery Voltage(day)"=>array("65","V"),
	"Min Battery Voltage(day)"=>array("64","V"),
	"Input Power"   =>array("59","W"),
	"LED"   =>array("49","LED"),
	"Battery Poles Voltage"=>array("25","V"),
	"Battery Sensor Voltage"=>array("26","V"),
	);



// functions

function get_data($ip,$alo) {
	if (($handle = fopen("http://".$ip."/MBCSV.cgi?ID=1&F=4&AHI=0&ALO=".$alo."&RHI=0&RLO=1", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$hodnota[1]=$data[3];  
			$hodnota[2]=$data[4];     }
    fclose($handle);
}
Return $hodnota;
}


function get_scale($ip,$alo){
$hi=get_data($ip,$alo);
$lo=get_data($ip,$alo+1);
$hi=$hi[2];
$lo=$lo[2];
$scale_factor=$hi.($lo/65535);
return $scale_factor;
}



function get_scaled_value($raw_data,$jednotka,$vscale,$iscale){

switch ($jednotka) {
	case "V":
	$hodnota=$raw_data[1]*256+$raw_data[2];
	$vysledek=(($hodnota*$vscale)/32768)/10;
	break;
	
	case "A":
	$hodnota=$raw_data[1]*256+$raw_data[2];
	$vysledek=(($hodnota*$iscale)/32768)/10;	
	break;
	
	case "W":	
	$hodnota=$raw_data[1]*256+$raw_data[2];
	$vysledek=(($hodnota*$vscale*$iscale)/131072)/100;
	break;
	
	case "C":	
	$vysledek=$raw_data[2];
	break;
	
	case "kWh":	
	$vysledek=$raw_data[2];
	break;
	
	case "min":	
	$vysledek=($raw_data[1]*256+$raw_data[2])/60;
	break;
	
	case "Ah":	
	$vysledek=($raw_data[1]*256+$raw_data[2])*0.1;
	break;

	case "Wh":	
	$vysledek=($raw_data[1]*256+$raw_data[2]);
	break;
    
	case "LED":
	$vysledek=$raw_data[2];

	$led_state = Array(	"LED_START","LED_START2","LED_BRANCH","Fast blinking green ","Slow blinking Green ","1 blink per second Green ",
	"Lights Green ","Light Green Yellow","Lights Yellow ","UNDEFINED","Blinking Red ","Lights Red","R-Y-G ERROR","R/Y-G ERROR","R/G-Y ERROR",
	"R-Y ERROR (HTD)","R-G ERROR (HVD)","R/Y-G/Y ERROR","G/Y/R ERROR","G/Y/R x 2");
	$vysledek=$led_state[$vysledek];
	break;
	
	default:
	$vysledek=$raw_data[2];
	$charge_state = Array("Start","Night Check","Disconnect","Night","Fault","MPPT","Absorption","Float","Equalize","Slave");
	$vysledek=$charge_state[$vysledek];
	break;
	
}
if(is_numeric($vysledek)) {
return round($vysledek,2);} else {
return $vysledek;
}
}
  


 // calculate 
  
  
$vscale=get_scale($tristar_ip,0);
$iscale=get_scale($tristar_ip,2);


foreach($omega as $polozka=>$hodnota)
  {
  list($alo,$jednotka)=$hodnota;
  $raw_data=get_data($tristar_ip,$alo);
  echo $polozka . ":" . get_scaled_value($raw_data,$jednotka,$vscale,$iscale).$jednotka."<br>";
  }

?>
