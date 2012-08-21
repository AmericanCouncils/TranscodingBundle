# ACTranscodingBundle #

This bundle provides container services for loading the file transcoder in your own code.

## Services ##

* `transcoder` - will return an instance of `AC\Component\Transcoding\Transcoder`, automatically registering any tagged Adapters, Presets, Jobs and Listeners

## Container Tags ##

Various container tags are implemented to allow easy registration of custom Adapters, Presets, Jobs into the Transcoder.  See the list below:

* `transcoder.adapter`
* `transcoder.preset`
* `transcoder.job`
* `transcoder.listener`

## Commands ##

The bundle provides one command to transcode a file:

* `transcoder:transcode [infile] [preset] [outfile]` - Transcodes an input file with a preset, creating the output file.  You can use this to test custom presets and adapters if necessary.

## Sonata Notifications ##

The bundle also provides a `SonataNotificationBundle` consumer.  Meaning, if you have `SonataNotificationBundle` installed, you can publish transcode messages to be processed asyncronously.  See an example below:

    $container->get('sonata.notification.backend')->create('transcoder', array(
        'infile' => '/absolute/path/to/input/file',             //required
        'preset' => 'preset_key',                               //required
        'outfile' => '/path/to/output'                          //optional
        'conflictMode' => Transcoder::ONCONFLICT_EXCEPTION,     //optional
        'dirMode' => Transcoder::ONDIR_EXCEPTION,               //optional
        'failMode' => Transcoder::ONFAIL_DELETE                 //optional
    ));

