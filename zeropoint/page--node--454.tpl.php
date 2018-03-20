<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{
direction: rtl;
    text-align: center;
    font-family: Arial, sans-serif;
    font-size: 14px;
color: #000;
    background-color: white !important;
    //padding: 40px;
    line-height: 1.0em;
}
a,p, li, h1, h2, h3{
color: #000;
    line-height: 1.0em;
}
.reg-days{
width: auto;
margin: 20px auto;
padding: 5px;
background: lightgrey;
color: black;
}
.band-gold {
width: auto;
margin: 10px auto;
padding: 2px;
background: goldenrod;
color: white;
}
.band-none{
width: auto;
margin: 10px auto;
padding: 2px;
background: none;
    border-style: dotted;
color: black;
}
.band-red{
width: auto;
margin: 10px auto;
padding: 2px;
background: red;
color: black;
}
.band-blue{
width: auto;
margin: 10px auto;
padding: 2px;
background: blue;
color: white;
}
.band-turq{
width: auto;
margin: 10px auto;
padding: 2px;
background: mediumseagreen ;
color: black;
}
.reg-comments{
width: 60%;
margin: 10px auto;
    padding-top: 10px;
    padding-bottom: 10px;
background: greenyellow ;
color: black;
    border-style: solid;
    border-color: red;
}
.error{
    font-weight: bold;
color: red;
    font-size: 24px;
    line-height: 28px;
}
.success{
    font-weight: bold;
color: green;
    font-size: 24px;
    line-height: 28px;
}
.attention{
    font-weight: bold;
color: orange;
    font-size: 20px;
    line-height: 24px;
}
</style>
</head>
<body>

<?php
    
    global $user;
    
    if (!in_array('checkin', array_values($user->roles))) {
        print "גישה חסומה\n";
        print '<h2><a href="/">בבקשה הזן שם משתמש וסיסמה</a></h2>';
        return;
    }
    
    $admin_mode = (in_array('checkinadmin', array_values($user->roles)) ? true : false);
   
    // search for SITE_KEY in sites/defaults/civicrm.settings.php.
    define('CIVI_URL', 'https://neworg.kbb1.com');
    define('SITE_KEY', '6d6ccf53722a1cb3a8dcef6780764096');
    define('API_KEY', 'oLEAvAw1QXzglFY64T7j4iZg');
    
    /**
     * Call CiviCRM HTTP API.
     * @param $entity string Name of CiviCRM entity, e.g. Contact, Relationship,...
     * @param $action string 'get' to retrieve, 'create' to create or update
     * @param $params array parameters to be passed to the API
     */
    function _civicrm_http_api($entity, $action, $params) {
        $url = CIVI_URL . '/sites/all/modules/civicrm/extern/rest.php';
        $curl = curl_init();
        $rest_params = array(
                             'key'=> SITE_KEY,
                             'api_key' => API_KEY,
                             'sequential' => 1,
                             'entity' => $entity,
                             'action' => $action,
                             'json' => json_encode($params),
                             );
        $opts = array(
                      CURLOPT_URL => $url,
                      CURLOPT_POST => TRUE,
                      CURLOPT_POSTFIELDS => $rest_params,
                      CURLOPT_RETURNTRANSFER => TRUE,
                      CURLOPT_SSL_VERIFYPEER => FALSE,
                      );
        curl_setopt_array($curl, $opts);
        $result = curl_exec($curl);
        $result = strstr($result, '{');
        curl_close($curl);
        
        $result = json_decode($result, true);
        return $result;
    }
    // Start checkin process ----------------------------
    if (isset($_GET['id'])) {
        
        $participantId = $_GET['id'];
        
        // Get today params --------------------------------------------------------
        $now_m = date("i");
        $now_h = date("H");
        $now_day = date("d");
        /*********************** $now_day = "20";/***********************/
        $now_month = date("m");
        $now_year = date("Y");
        
        // Define current event date ------------------------------------------------
        $event_year = "2018";
        $event_month = "02";
        $event_days = array(
                            '1' => "20",
                            '2' => "21",
                            '3' => "22"
                            );
        // Define sub event ID and start time ------------------------------------------
        $sub_event_ids = array(201, 215, 216, 225);
        $sub_event_start_time = "17:00:00";
        
        // Define <div> colors of band for the current event ------------------------
        // and check if today is date of the current event
        $div_all_days = '<div class="band-turq"><br/><h2>צמיד ירוק</h2><br/>';
        $div_none = '<div class="band-none"><br/><h2 style="color: red">אירוע סגור- לא לתת צמיד</h2><br/>';
        $div_today_only = $div_none;
        $is_valid_day = false;
        $is_valid_time = false;
		$is_valid_event = true;
        foreach($event_days as $num => $day) {
            if ($day == $now_day && $event_month == $now_month && $event_year == $now_year) {
                $is_valid_day = true;
                switch ($num) {
                    case '1':
                        $div_today_only = '<div class="band-red"><br/><h2>צמיד אדום</h2><br/>';
                        break;
                    case '2':
                        $div_today_only = '<div class="band-gold"><br/><h2>צמיד זהב</h2><br/>';
                        break;
                    case '3':
                        $div_today_only = '<div class="band-turq"><br/><h2>צמיד ירוק</h2><br/>';
                        break;
                    default:
						$is_valid_event = false;
                        $div_today_only = $div_none;
                        break;
                }
                break;
            }
        }
        
        // Check if today is day of current event -----------------------------------
        if (!$is_valid_day) {
            print '<h2  class="error">תאריך אירוע שונה מהיום.</h2>';
            print '<br>';
            print "<h2>" . $event_title . "</h2>";
            return;
        }
        
        // Get participant data -----------------------------------------------------
        $params = array(
                        'id' => $participantId,
                        'api.Contact.getsingle' => array(
                                                         'id' => '$value.contact_id',
                                                         'return' => 'display_name'
                                                         ),
                        // Use another chained call to get event title
                        'api.Event.getsingle' => array(
                                                       'id' => '$value.event_id',
                                                       'return' => 'title'
                                                       ),
                        // Use another chained call to get activity data
                        'api.Activity.get' => array(
                                                    //'contact_id' => '$value.contact_id',
                                                    'activity_type_id' => '5',
                                                    'source_record_id' => $participantId,
                                                    'return' => 'subject,activity_date_time,source_contact_id'
                                                    )
                        );
        
        $result = _civicrm_http_api('Participant', 'get', $params);
        if ($result['is_error']) {
            print "שגיאה: <br>" . $result['error_message'];
            return;
        }
        if ($result['count'] != 1){
            print '<h2 class="error">משתתף לא נמצא במערכת<br>נא לגשת לעמדת בירורים.</h2>';
            return;
        }
        
        $participant = $result['values'][0];
        // Init variables ------------------------------------------------------------
        $event_id = $participant['event_id'];
        $is_sub_event = (in_array($event_id, $sub_event_ids) ? true : false);
        $participant_status_id = $participant['participant_status_id'];
        $participant_comments = $participant['custom_108'];
        $participant_fee_level = $participant['participant_fee_level'];
        $participant_status = $participant['participant_status'];
        $contact_name = $participant['api.Contact.getsingle']['display_name'];
        $event_title = $participant['api.Event.getsingle']['title'];
        $act_count = 0;
        $attended_act_list = "";
        $last_act_date = "";
        foreach($participant['api.Activity.get']['values'] as $act ) {
            if (preg_match('/Attended/', $act['subject'])) {
                $act_count = $act_count + 1;
                $attended_act_list = $attended_act_list . $act['activity_date_time'] . " על ידי " . $act['source_contact_id'] . "<br>";
                $last_act_date = $act['activity_date_time'];
            }
        }
        
        // Calculate registration index and event days for display ---------------------
        $event_days_display = "";
        $event_days_index = "";
        $pattern = '';
        $patt_no_zero = '';
        foreach($event_days as $num => $day) {
            foreach($participant_fee_level as $key ) {
                $pattern = '/' . $day . "." . $event_month . "." . $event_year . '/';
                $patt_no_zero = '/' . ltrim($day, "0") . "." . ltrim($event_month, "0") . "." . $event_year . '/';
                if (preg_match($pattern, $key) || preg_match($patt_no_zero, $key)) {
                    $event_days_display = $event_days_display . ltrim($day, "0") . "." . ltrim($event_month, "0") . "." . $event_year . "<br />";
                    $event_days_index = $event_days_index . $num;
                }
            }
        }
        
        // Generate event days display list for any event that does not current event --
        if ($event_days_display == "") {
            foreach ($participant_fee_level as $key ) {
                $event_days_display = $event_days_display . $key . "<br />";
            }
        }
        
        // Print participant name ------------------------------------------------------
        print '<br/>' . '<br/>';
        print "שם משתתף: " . "<h2>" . $contact_name . "</h2>" . "<br /><hr>";
        
        // Check last Attended activity day --------------------------------------------
        if ($last_act_date != "") {
            $activity_date = date_create($last_act_date);
            if (date_format($activity_date, "d") == date("d")) {
				print '<h2 class="error">נא לגשת לעמדת בירורים.</h2>';
                // Display additional information for registration administrator
                if ($admin_mode) {
					print '<h2 class="attention">משתתף כבר עבר תהליך קבלה<br/>היום ב: ';
					print date_format($activity_date, "H:i") . '</h2>';
					print '<h2>' . '<br>' . $event_title . '</h2>';
				}
                return;
            }
        }
        
        // Check registration time for sub event ----------------------------------------
        if ($is_sub_event) {
            if ($now_day == $event_days['1']) {
                print "אירוע: " . "<h2>" . $event_title . "</h2>";
                print '<br>';
                print '<h2 class="error">לא מתקיים היום</h2>';
                return;
            }
            $start_date = date_create($event_year . "-" . $event_month . "-" . $now_day . " " . $sub_event_start_time);
            $today = date_create();
            if ($today < $start_date) {
                print '<h2 class="error">כניסה לאירוע</h2><br/>';
                print "<h2>" . $event_title . "</h2>";
                print '<h2 class="error">מותרת מ: ' . date_format($start_date,"H:i") . '</h2>';
                return;
            }
        }
        
        // If: status of participant is 'Registered' -------------------------------------
        if ($participant_status_id == 1) {
            // Change status to 'Attended'
            $params = array(
                            'id' => $participantId,
                            'status_id' => 2,
                            );
            $result = _civicrm_http_api('Participant', 'create', $params);
            
            if ($result['is_error']) {
                print "שגיאה: <br>" . $result['error_message'];
                return;
            }
            // Success registration
			if($is_valid_event) {
				print '<h2 class="success">משתתף נקלט בהצלחה</h2>';
			}
            //Print band color according to registration dates
            switch($event_days_index) {
                case "123":
                    print $div_all_days;
                    break;
                case "1":
                case "2":
                case "3":
                case "12":
                case "13":
                    print $div_today_only;
                    break;
                case "23":
                    print ($now_day == $event_days['1'] || $is_sub_event ? $div_today_only : $div_all_days);
                    break;
                default:
                    print $div_none;
                    break;
            }
//            print "<br/><h2>" . $event_days_display . "</h2>";    '<br/><h2>צמיד אדום</h2><br/>'
            print '</div>';
        }
        // Elseif: status = 'Attended'  ------------------------------------------------------
        else if ($participant_status_id == 2) {
            if ($act_count == 1 && strlen($event_days_index) == 2) {
                // Change status to 'Registered'
                $params = array(
                                'id' => $participantId,
                                'status_id' => 1,
                                );
                $result = _civicrm_http_api('Participant', 'create', $params);
                
                if ($result['is_error']) {
                    print "שגיאה: <br>" . $result['error_message'];
                    return;
                }
                // Change status to 'Attended'
                $params = array(
                                'id' => $participantId,
                                'status_id' => 2,
                                );
                $result = _civicrm_http_api('Participant', 'create', $params);
                
                if ($result['is_error']) {
                    print "שגיאה: <br>" . $result['error_message'];
                    return;
                }
                // Success registration
                print '<h2 class="success">משתתף נקלט בהצלחה</h2>';
                //Print band color according to registration dates
                print $div_today_only;
//                print "<br/><h2>" . $event_days_display . "</h2>";
                print '</div>';
            }
            else {
                print '<h2 class="error">נא לגשת לעמדת בירורים.</h2>';
                // Display additional information for registration administrator
                if ($admin_mode) {
                    print '<h2 class="attention">משתתף כבר עבר תהליך קבלה<br/>בתאריך:';
                    print '<br/>';
                    print $attended_act_list;
                    print '</h2>';
                    print '<div class="reg-days">';
                    print "<br/><h2>" . $event_days_display . "</h2>";
                    print '</div>';
                }
            }
        }
        // Else: status of participant is not 'Registered' and not 'Attended'----------------
        else {
            print '<h2 class="error">נא לגשת לעמדת בירורים.</h2>' . '<br>';
            print "<h2>סטטוס: " . $participant_status . "</h2>";
        }
        // Print event title -------------------------------------------------------------
        print "<h2>" . $event_title . "</h2>";
        //Print comments if exists -------------------------------------------------------
        if ($participant_comments != "") {
            print '<div class="reg-comments">';
            print '<h2 style="color: red">שימו לב!</h2>';
            print "<h3>" . $participant_comments . "</h3>";
            print '</div>';
        }
        print '<br/>';
    }
    ?>
<div class="result">
</div>
<body>
