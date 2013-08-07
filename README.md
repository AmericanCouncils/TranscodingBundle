# ACTranscodingBundle #

This bundle provides container services for loading the file transcoder in your own code.

> This bundle is developed in sync with the `http://github.com/AmericanCouncils/Transcoding` repository.

## Configuration ##

You can copy/paste the config block below into your `app/config.yml` and modify as needed:

```yml
ac_transcoding:
    ffmpeg: 
        enabled: true           #if false, other keys need not be specified
        path: /usr/bin/ffmpeg
        timeout: 0
    handbrake:
        enabled: true           #if false, other keys need not be specified
        path: /usr/local/bin/HandBrakeCLI
        timeout: 0
```

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

