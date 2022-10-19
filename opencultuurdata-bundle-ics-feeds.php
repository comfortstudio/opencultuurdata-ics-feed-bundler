<?php

   class OpencultuurdataBundleICSfeeds {

      private $options = array();

      public function __construct( $options = array() ) {

         // error_reporting( 0 );

         $default_options = array(
            'feeds_file' => __DIR__ . '/feeds.txt'
         );

         $this->options = array_merge( $default_options, $options );

      }

      public function get_feeds_as_one_big_ics() {

         $urls = $this->get_urls_from_feeds_file();
         $ics_content = $this->merge_ics_feeds( $urls );

         echo $ics_content;

      }

      public function get_urls_from_feeds_file() {

         $urls = array();

         if ( ! file_exists( $this->options['feeds_file'] ) ) {
            die( 'Feeds file does not exist: ' . $this->options['feeds_file'] );
         }

         $lines = explode( "\n", file_get_contents( $this->options['feeds_file'] ) );

         foreach ( $lines as $line ) {
            $line = trim( $line );

            if ( ! empty( $line ) && strpos( $line, '#' ) !== 0 ) {
               $urls[] = $line;
            }

         }

         return $urls;

      }

      public function merge_ics_feeds( $urls ) {

         $output_ics = array();
         $adding = true; // Start with true to add the ICS heading, gets ended by first event

         foreach ( $urls as $i => $url ) {

            $ics_content = $this->fetch_url( $url );

            if ( ! empty( $ics_content ) ) {
               $ics_lines = explode( "\n", $ics_content );

               foreach ( $ics_lines as $line ) {

                  $line = trim( $line );

                  if ( strpos( $line, 'BEGIN:VEVENT' ) === 0 ) {
                     $adding = true;
                  }

                  if ( strpos( $line, 'END:VEVENT' ) === 0 ) {
                     $output_ics[] = $line;
                     $adding = false;
                  }

                  if ( strpos( $line, 'END:VCALENDAR' ) === 0 ) {
                     $adding = false;
                  }

                  if ( $adding ) {
                     $output_ics[] = $line;
                  }
               }

            }

         }

         $output_ics[] = "END:VCALENDAR";
         
         return implode( "\n", $output_ics );

      }

      public function fetch_url( $url ) {

         $curl = curl_init();
         curl_setopt( $curl, CURLOPT_HEADER, false );
         curl_setopt( $curl, CURLOPT_URL, $url );
         curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
         $result = curl_exec( $curl );
         curl_close( $curl );

         return $result;

      }

   }