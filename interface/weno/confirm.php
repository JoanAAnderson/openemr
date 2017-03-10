

<?php
/** Copyright (C) 2016 Sherwin Gaddis <sherwingaddis@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 */
 
$fake_register_globals=false;
$sanitize_all_escapes=true;	
include_once('../globals.php');
include_once('transmitDataClass.php');
include_once('$srcdir/patient.inc');

$date = date("Y-m-d");
$pid = $GLOBALS['pid'];
$uid = $_SESSION['authUserID'];          //username of the person for this session

$tData = new transmitData();

$send = $tData->getDrugList($pid, $date);
$provider = $tData->getProviderFacility($uid);
$patientPharmacy = $tData->patientPharmacyInfo($pid);
$mailOrder = $tData->mailOrderPharmacy();


?>

<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="../../library/js/jquery-ui.min.css" type="text/css" />

<script type="text/javascript" charset="utf-8">
function validate(){
	var pharmacy = document.getElementById('pharm');
	if(text.value.length == 0){
		alert("Must select a pharmacy first");
		return false;
	}
}


</script>
<style>
footer {
    padding: 0.5em;
    font-size: 0.5em;

    clear: left;
    text-align: center;
    top: 200px;
}
</style>
</head>

<body class="body_top">
<h1>Prescription Transmit Review</h1>
<table>
<th width="200px">Drug</th>
<th width="100px">Quantity</th>

<?php
//List drugs to be sent 

  $drug = array(); //list of records that need to updated with pharmacy information
while($list = sqlFetchArray($send)){
    //print "<tr align='center'><td>".$list['drug'] . " </td><td> " . $list['dosage'] . " </td><td> " . $list['quantity'] . "</td></tr>";
	print "<tr align='center'><td>".$list['drug'] . " </td><td> " . $list['quantity'] . "</td></tr>";
	$drug[] = $list['id'];
}


?>
</table>
<?php if(empty($drug)){
	echo "<br> <strong> <font color='red'>No prescriptions selected. </strong></font>";
	exit;
}
?>
<div id="fields">
<h3>Select Pharmacy</h3>
	    Patient Default <br>
	    <input type = 'radio' name = "pharmacy" id = 'patientPharmacy' value="<?php print $patientPharmacy['pharmacy_id'] ?>" checked="checked">
	    <?php if(!$patientPharmacy['name']){
                   print "<b>Please set pharmacy in patient's chart!</b><br> <br>";
               }else{
	    	      print $patientPharmacy['name']; 
               } 
	    	      ?><br> <br>
	    	      
        Mail Order <br>
        <input type = 'radio' name = 'pharmacy' id = 'mailOrder' value = "<?php print $mailOrder['id'] ?>">CCS Medical 	14255 49th Street, North, Clearwater, FL 33762<br> 
	    <!-- removed from site but has future plans. 
		<input type='text' size='10' name='city' id="city" value='' placeholder='Enter City First' title="type all or three letters of a city name">
		<input type='text' size='30' name='address' id='address' value='' placeholder='Enter 3 #s or Letters of the Address' title="when searching by street name only put in the first three letters of the name"><br>
		<input type='text' size='70' name='pharmacy_id' id="pharm" value='' class='pharmacy' placeholder='Enter City First Then Type Pharmacy' >
		-->

  <div id="confirm">
  <br><br>
      <input type='submit' id='confirm_btn' value='Aprove Order' >
  </div>
 
  <div id="transmit">
      <input type='submit' id='order' value='Transmit Order' >
  </div> 
  <div id="success"></div>  
</div>
<script type="text/javascript" src="../../library/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="../../library/js/jquery-1.6.4.min.js"></script>
<script type="text/javascript" src="../../library/js/jquery-ui.min.js"></script>
<script type="text/javascript" charset="utf-8" src="../../library/js/prism.js" ></script>
<script type="text/javascript">

<!-- This is not used right now but dont want to delete yet-->	
$(function() {
	//Pharmacy autocomplete
	$("#pharm").click(function() {
	var city = $("#city").val();
	var address = $("#address").val();
 
	var str = "../../library/ajax/pharmacy_autocomplete/search.php?city="+city+"&address="+address;
		//autocomplete
		$(".pharmacy").autocomplete({
			source: str,
			minLength: 1
		});	
 
    });
});

$(document).ready(function(){


	var toTran = <?php echo json_encode($drug); ?>; //pass php array to jquery script
	var jsonArray = [];

    //Hides the transmit button until 
    $("#transmit").hide();
	
	//Updates the order with the pharmacy information
    $("#confirm_btn").click(function(){
     
     /*
	    var pharmSelect = $("#pharm").val();
		if(pharmSelect.length == 0){
			alert("Must select a pharmacy first");
			return;
		}
		*/
		var pharm_Id = $("input[name='pharmacy']:checked").val();
		//var pharm_Id = 3;//pharmId.filter(':checked').val();
		if($('#patientPharmacy').is(':checked')) { 
               
			pharm_Id; 

		}
		if($('#mailOrder').is(':checked')) { 
               
			pharm_Id; 

		}		
        //alert(pharm_Id);
		$("#transmit").show();
        
		//this is to set the pharmacy for the presciption(s)
		$.ajax({ url: 'markTx.php?arr='+pharm_Id+','+toTran });
		
		//Makes the returned ajax call a global variable
        function getReturnJson(x){
        	//to process multiple prescriptions. 
        	jsonArray.push(returnedJson = x);

        }

          //loop through the prescription to create json code on fly 
        $.each(toTran, function( index, value ) {
		//this is to create the json script to be transmitted
        $.ajax({
                   //feeds the json generator
			url: 'jsonScript.php?getJson='+pharm_Id+','+value, 
            
            success: function(response){
            	console.log(response);
               getReturnJson(response); 
            },
            error: function(xhr, status, error){
				 console.log(xhr);
				 console.log(status);
				 console.log(error);
				 console.warn(xhr.responseText);							 
		  }
		});
      }); //end of   
    }); //end of confirm button
	
	//Transmit order(s)
  $('#order').click(function(){

  	$('<div id="overlay"/>').css({
        position: 'fixed',
        top: 0,
        left: 0,
        width: '100&#37;',
        height: $(window).height() + 'px',
        background: 'white'
    }).hide().appendTo('body');

       $('#overlay').show();   
         $.each(jsonArray, function(index, value){
		 var send = value;
		 $.ajax({
			type: 'POST',
		dataType: 'JSON',
			 url: 'https://apa.openmedpractice.com/apa/interface/weno/receivingrx.php?',
			data: {"scripts": send},

		  success: function(response){
			  console.log(response);
			  
			  $('#success').append('<p>'+response+'</p>');
			  $('#overlay').hide();
		  },
			error: function(xhr, status, error){
				 console.log(xhr);
				 console.log(status);
				 console.log(error);
				 console.warn(xhr.responseText);							 
		  }			
		 }); //end of ajax call
      }); // end of each loop

         $("#transmit").hide();
	});
	
	
}); //end of doc ready


</script>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<footer>
<p>Open Med Practice and its suppliers use their commercially reasonable efforts to provide the most current and complete data available to them concerning prescription histories, drug interactions and formularies, patient allergies and other factors, but by your use of this service you acknowledge that (1) the completeness and accuracy of such data depends upon the completeness and accuracy with which it is entered into connected electronic databases by physicians, physician’s offices, pharmaceutical benefits managers, electronic medical records firms, and other network participants, (2) such data is subject to error or omission in input, storage or retrieval, transmission and display, technical disruption, power or service outages, or other interruptions in electronic communication, any or all of which may be beyond the control of Open Med Practice and its suppliers, and (3) some information may be unavailable due to regulatory, contractual, privacy or other legal restrictions. You are responsible to use your clinical judgment at all times in rendering medical service and advice.</p>
</footer>		
</body>
</html>