<?php

   require_once 'vendor/autoload.php';

   use ICal\ICal;

   class OpencultuurdataFetchICSevents {

      private $options = array();
      private $climate;

      public function __construct( ) {

         // Init commandline arguments

            // Docs: https://climate.thephpleague.com/

            $this->climate = new \League\CLImate\CLImate;
            $this->climate->arguments->add([
               'output' => [
                  'prefix'      => 'o',
                  'longPrefix'  => 'output',
                  'description' => 'Output file (JSON)',
                  'required'    => true,
               ],            
               'feeds' => [
                  'prefix'      => 'f',
                  'longPrefix'  => 'feeds',
                  'description' => 'Feeds file (TXT)',
                  'defaultValue' => __DIR__ . '/feeds.txt'
               ],
               'help' => [
                  'longPrefix'  => 'help',
                  'description' => 'Prints a usage statement',
                  'noValue'     => true,
               ],            
            ]);

            try {
               $this->climate->arguments->parse();

            } catch (Exception $e ) {
               $this->climate->usage();
               die();
            }

      }

      public function get_feeds_as_json() {

         $urls = $this->get_urls_from_feeds_file();

         $events = array();

         foreach ( $urls as $url ) {
            $url_events = $this->fetch_ics_as_objects( $url );
            if ( is_array( $url_events ) ) {
               $events = array_merge( $events, $url_events );
            }
         }

         file_put_contents( $this->climate->arguments->get('output'), json_encode( $events ) );

      }

      public function get_urls_from_feeds_file() {

         $urls = array();

         $feeds_file = $this->climate->arguments->get('feeds');

         if ( ! file_exists( $feeds_file ) ) {
            die( 'Feeds file does not exist: ' . $feeds_file );
         }

         $lines = explode( "\n", file_get_contents( $feeds_file ) );

         foreach ( $lines as $line ) {
            $line = trim( $line );

            if ( ! empty( $line ) && strpos( $line, '#' ) !== 0 ) {
               $urls[] = $line;
            }

         }

         return $urls;

      }

      function fetch_ics_as_objects( $url ) {

         $this->climate->out('Processing ' . $url );

         // Fetch URL as tmp file (avoid SSL issues)

            $ics_data = $this->fetch_url( $url );

         // Store as as a tmp file

            $tmpfile = sys_get_temp_dir() . '/ics_tmp_'.sha1( time() . rand(0,9999) ) . '.ics';
            file_put_contents( $tmpfile, $ics_data );

         // Load ICS data from file

            // Docs: https://github.com/u01jmg3/ics-parser

            try {

               $ical = new ICal($tmpfile, array(
                  'defaultSpan'                 => 3,     // Default value
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
               return false;
            }

         // Build events object

            $events = array();

            $ical_events = $ical->events();
            
            $this->climate->out('- Found ' . sizeof( $ical_events ) . ' events' );

            foreach ( $ical_events as $event ) {
               $dtstart = $ical->iCalDateToDateTime( $event->dtstart_array[ 3 ] );
               $dtend = $ical->iCalDateToDateTime( $event->dtend_array[ 3 ] );

               $events[] = array(
                  'uid'          => $event->uid,
                  'start'        => $dtstart->format('d-m-Y H:i' ),
                  'end'          => $dtend->format('d-m-Y H:i' ),
                  'location'     => $event->location,
                  'organizer'    => $event->organizer,
                  'title'        => $event->summary,
                  'description'  => $event->description,
                  'link'        => $this->get_link_from_description( $event->description ),
                  'source'       => $url,
               );

            }

         // Cleanup and deliver

            unlink( $tmpfile );
            return $events;

      }


      public function get_link_from_description( $description ) {

         if ( preg_match( '/(https:\/\/.*)\s?/' , $description , $matches ) ) {
            return $matches[ 1 ];
         }

         return '';

      }

      public function fetch_url( $url ) {

         $curl = curl_init();
         curl_setopt( $curl, CURLOPT_HEADER, false );
         curl_setopt( $curl, CURLOPT_URL, $url );
         curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
         curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
         curl_setopt( $curl, CURLOPT_SSL_VERIFYSTATUS, false );
         $result = curl_exec( $curl );
         curl_close( $curl );

         return $result;

      }

   }