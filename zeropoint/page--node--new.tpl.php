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
        .band-none{
            width: auto;
            margin: 10px auto;
            padding: 2px;
            background: none;
            border-style: dotted;
            color: black;
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
            border: solid red;
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

// search for SITE_KEY in sites/defaults/civicrm.settings.php.
define('CIVI_URL', 'https://neworg.kbb1.com');
define('SITE_KEY', '6d6ccf53722a1cb3a8dcef6780764096');
define('API_KEY', 'oLEAvAw1QXzglFY64T7j4iZg');

/**
 * Call CiviCRM HTTP API.
 * @param $entity string Name of CiviCRM entity, e.g. Contact, Relationship,...
 * @param $action string 'get' to retrieve, 'create' to create or update
 * @param $params array parameters to be passed to the API
 * @return mixed|string $result array
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

    // Get participant data -----------------------------------------------------
    $params = array(
        'id' => $participantId,
        'api.Contact.getsingle' => array(
            'id' => '$value.contact_id',
            'return' => 'display_name'
        ),
        // Use another chained call to get event title
        'api.Event.get' => array(
            'id' => '$value.event_id',
            'return' => 'title,start_date,end_date'
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

    // Define <div> colors of band for the current event ------------------------
    $div_all_days = '<div class="band-turq"><br/><h2>לתת צמיד</h2><br/>';
    $div_none = '<div class="band-none"><br/><h2 style="color: red">אירוע סגור - לא לתת צמיד</h2><br/>';
    $div_today_only = '<div class="band-red"><br/><h2>צמיד אדום</h2><br/>';

    $participant = $result['values'][0];

    // Init variables ------------------------------------------------------------
    $event_id = $participant['event_id'];
    $participant_status_id = $participant['participant_status_id'];
    $participant_comments = $participant['custom_108'];
    $participant_fee_level = $participant['participant_fee_level'];
    $participant_status = $participant['participant_status'];
    $contact_name = $participant['api.Contact.getsingle']['display_name'];
    $event_title = $participant['api.Event.get']['title'];
    $event_start_date= $participant['api.Event.get']['start_date'];
    $event_end_date = $participant['api.Event.get']['end_date'];
    $today = date_create();
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

    // Print participant name ------------------------------------------------------
    print '<br/>' . '<br/>';
    print "שם משתתף: " . "<h2>" . $contact_name . "</h2>" . "<br /><hr>";

    // Check date and time of event ----------------------------------------
    if ($today < date_create($event_start_date) || (!empty($event_end_date) && $today > date_create($event_end_date))) {
        print '<h2 class="error">נא לגשת לעמדת בירורים.</h2>' . '<br>';
        print $div_none;
        print '</div>';
        return;
    }

    // Check last Attended activity day --------------------------------------------
    if ($last_act_date != "") {
        $activity_date = date_create($last_act_date);
        if (date_format($activity_date, "d") == date("d")) {
            print '<h2 class="error">נא לגשת לעמדת בירורים.</h2>' . '<br>';
            print '<h2 class="error">משתתף כבר נקלת היום.</h2>';
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
        print '<h2 class="success">משתתף נקלט בהצלחה</h2>';
        print $div_all_days;
        print '</div>';
    }
    // Elseif: status = 'Attended'  or other -----------------------------------------
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
