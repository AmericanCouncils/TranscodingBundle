# TODO #

* push new tagged `0.3.0` version of this


# ACTranscodingBundle #

This bundle provides container services for loading the file transcoder in your own code.

> This bundle is developed in sync with the `http://github.com/AmericanCouncils/Transcoding` repository.

## Services ##

* `transcoder` - will return an instance of `AC\Component\Transcoding\Transcoder`, automatically registering any tagged Adapters, Presets, and Listeners

You can use the `transcoder` service to transcode a file:

    $newFile = $container->get('transcoder')->transcodeWithPreset('/path/to/input/file', 'handbrake.classic', '/path/to/output/file');

## Container Tags ##

Various container tags are implemented to allow easy registration of custom Adapters, Presets & event listeners into the transcoding.  See the list below:

* `transcoding.adapter`
* `transcoding.preset`
* `transcoding.listener`
* `transcoding.subscriber`

## Commands ##

The bundle provides a few commands for accessing the transcoder via the command line:

* `transcoder:transcode [infile] [preset] [outfile]` - Transcodes an input file with a preset, creating the output file.  You can use this to test custom presets and adapters if necessary.
* `transcoder:status` - Displays a list of enabled and working adapters based on current configuration, plus a list of usable presets.

## SonataNotificationBundle Integration ##

The bundle also provides a `SonataNotificationBundle` consumer.  Meaning, if you have `SonataNotificationBundle` installed, you can publish transcode messages to be processed asyncronously.  See an example below:

    $container->get('sonata.notification.backend')->create('transcoder', array(
        'infile' => '/absolute/path/to/input/file',             //required
        'preset' => 'preset_key',                               //required
        'outfile' => '/path/to/output'                          //optional
        'conflictMode' => Transcoder::ONCONFLICT_EXCEPTION,     //optional
        'dirMode' => Transcoder::ONDIR_EXCEPTION,               //optional
        'failMode' => Transcoder::ONFAIL_DELETE                 //optional
    ));

## RabbitMQBundle Integration ##

Alternatively, some may want to leverage RabbitMQ directly for more advanced feature support.

To publish a message for RabbitMQ to process asynchronously requires a little extra setup, meaning that you
must configure the RabbitMQBundle with the necessary queues/exchanges that specify the `transcoding.rabbitmq.consumer` 
service as the callback.

Assuming you've set this up correctly, you can publish a message like so:

    $msg = array(
        'infile' => '/absolute/path/to/input/file',             //required
        'preset' => 'preset_key',                               //required
        'outfile' => '/path/to/output'                          //optional
        'conflictMode' => Transcoder::ONCONFLICT_EXCEPTION,     //optional
        'dirMode' => Transcoder::ONDIR_EXCEPTION,               //optional
        'failMode' => Transcoder::ONFAIL_DELETE                 //optional
    );
    
    //the exact name of the publisher service depends on what you've specified in the
    //config for the RabbitMQBundle
    $container->get('YOUR_RABBIT_QUEUE_SERVICE')->publish(serialize($msg));
    
