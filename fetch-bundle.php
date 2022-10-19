<?php

   require_once( __DIR__ . '/opencultuurdata-bundle-ics-feeds.php' );

   $opencultuurdataBundleICSfeeds = new OpencultuurdataBundleICSfeeds( );
   $opencultuurdataBundleICSfeeds->get_feeds_as_one_big_ics();
