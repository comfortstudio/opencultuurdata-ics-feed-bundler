<?php

// Docs: https://github.com/u01jmg3/ics-parser

require_once 'vendor/autoload.php';

use ICal\ICal;

try {

   $ics_file = 'output.ics';
   // $ics_file = 'tetem.ics';
   // $ics_file = 'https://framerframed.nl/?feed=ical';

   $ical = new ICal($ics_file, array(
      'defaultSpan'                 => 2,     // Default value
      'defaultTimeZone'             => 'CET',
      'defaultWeekStart'            => 'MO',  // Default value
      'disableCharacterReplacement' => false, // Default value
      'filterDaysAfter'             => null,  // Default value
      'filterDaysBefore'            => null,  // Default value
      'httpUserAgent'               => null,  // Default value
      'skipRecurrence'              => false, // Default value
   ));
    // $ical->initFile('ICal.ics');
    // $ical->initUrl('https://raw.githubusercontent.com/u01jmg3/ics-parser/master/examples/ICal.ics', $username = null, $password = null, $userAgent = null);
} catch (\Exception $e) {
   die($e);
}

// $events = $ical->eventsFromInterval( '2 weeks' );
$events = $ical->events();

// var_dump( $ical->eventCount );
// print_r( $events );
// die();

foreach ( $events as $event ) {


   echo "-------------------------------------\n";

   // print_r( $event );
   // die();

   $dtstart = $ical->iCalDateToDateTime( $event->dtstart_array[ 3 ] );
   $dtend = $ical->iCalDateToDateTime( $event->dtend_array[ 3 ] );
   echo 'Start:     ' . $dtstart->format('d-m-Y H:i') . "\n";
   echo 'End:       ' . $dtend->format('d-m-Y H:i') . "\n";
   echo 'Location:  ' . $event->location . "\n";
   echo 'Organizer: ' . $event->organizer . "\n";
   echo 'Summary:   ' . $event->summary . "\n";

   // echo ': ' . $event->printData() . "\n";

}
