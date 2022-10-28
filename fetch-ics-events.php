<?php

   require_once( __DIR__ . '/class.opencultuurdata-fetch-ics-events.php' );

   $opencultuurdataBundleICSfeeds = new OpencultuurdataFetchICSevents( );
   $opencultuurdataBundleICSfeeds->get_feeds_as_json();
