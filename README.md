# downtube
DownTube YouTube Transcoder - Transcodes YouTube videos on the fly for older devices!


What is DownTube?
-----------------

DownTube is a project I honestly just created for myself, but hey, some people might find a use for it? It originally started because my fiancee has an old laptop I sometimes use for chatting and such, and my friends (one in particular, you know who you are) like to link YouTube videos, and its processor just wasn't good enough to handle modern HTML5 or Flash video. So I figured "Well, with the magic of youtube-dl and ffmpeg, I could probably turn it into MPEG2 which it should handle just fine... What if I could do that automatically, relatively transparently?"... And thus DownTube was born.

DownTube is an automatic transcoder for YouTube. It streams content off of youtube and serves it to your browser directly in any format you want (or at least, any format that your browser/video player of choice/etc will accept as a 'video/mpeg' stream). I use this along with the VLC mozilla plugin to play youtube videos pretty much seamlessly in pretty decent quality. I use this on my local network, so bandwidth isn't too much of a concern, though I had to keep it to under 15mbit for reliable smooth playback over wifi. On a wired connection you could probably safely serve 50mbit, 80mbit, even 100mbit plus on gigabit. This basically overcomes the inherent limitation of MPEG2 which is how big it is for the same quality. But you need a machine that's fast enough to do the transcoding for you, of course.

To use DownTube, edit the PHP file and change things in the parameter section (which should be fairly well commented and documented, look in the documentation of ffmpeg and youtube-dl if you're confused), and then simply put it on your webserver, optionally with the included .htaccess file for url rewriting.

Without the htaccess file, it can be used like such:
(I'm using a hootie and the blowfish video here for demonstration because I really like this song...)

given the youtube url:
http://www.youtube.com/watch?v=1aVHLL5egRY

http://yourwebserverhere/downtube.php?id=1aVHLL5egRY

*with* the htaccess file, it can also be used as such:

http://yourwebserverhere/yt/1aVHLL5egRY
http://yourwebserverhere/watch?id=1aVHLL5egRY

with the htaccess file *and* the "redirect hack" enabled (see the parameters section of the php file) it can be used as such:

http://yourwebserverhere/watch?video=1aVHll5egRY
http://yourwebserverhere/watch?v=1aVHll5egRY

which, if you have a DNS redirection/hosts file redirect/some other way to turn "youtube.com" into "yourwebserverhere" would allow you to *seamlessly* click youtube links and watch them in MPEG2!


**NEW:** There is now an audio only mode, intended for use with audio players like Winamp. MP3 (codec and container) is suggested for Pentium or better machines, MP2 for a 486. Add &aonly=yes to the URL to enable this, check the script for how to configure it.

**NEW:** There is now a debug mode. Enable writing by the webserver to the directory DownTube is running in, and set $debugmode to True in the PHP file. This will create a pair of log files that will allow you to debug yt-dlp/ffmpeg issues.

That's really all I have to say. Feel free to report bugs, ask me questions, fork this project, make pull requests, whatever. I don't guarantee a response. :P

What do I need to use DownTube?
-------------------------------

You need a webserver (Apache2 required to use the .htaccess file with the URL rewrites, otherwise any that can interface with PHP), PHP5 or later, yt-dlp, and ffmpeg. That's all.

FAQ
---

Q. DownTube produces 0 byte videos or videos that are invalid in my media player. What's going wrong and how do I fix it?

A. This is generally the result of using a copy of yt-dlp or youtube-dl that is too old. youtube-dl is no longer maintained, and yt-dlp needs to be updated regularly due to it being in a bit of an arms race with YouTube, the owners of which really don't want you downloading its videos without paying them. Download yt-dlp using pip or directly from their GitHub, do not rely on linux distribution packages.


Q. How do I use DownTube on my retro machine?

A. This is a multi-step process, so this answer is going to be in a couple parts.

  1. First you need to set up a webserver on a modern machine that's capable of both decoding VP9/H.264 video and encoding MPEG2/MPEG1 video at the same time. Most machines made in the last 10 years or so should be able to do this. This webserver needs to have PHP enabled, and the PHP needs to be configured to allow executing programs on the server. This is a very insecure configuration, so it's recommended to run it in a VM and to never allow inbound access from the internet. The server needs to have an up-to-date copy of yt-dlp and a properly configured copy of ffmpeg installed. Most binaries of ffmpeg distributed in binary form by the ffmpeg team or distributed in package managers on both Windows and Linux platforms will be good enough, as basically all that's needed is H.264/VP9 decoding and encoding to your target codec, usually MPEG1 or MPEG2. Put downtube.php in a directory on the webserver, and edit it to configure it how you'd like. The comments should serve as a guide for how to set it up, refer to the ffmpeg and yt-dlp documentation for any parameters you don't understand. Notably, whether you configure for MPEG2 or MPEG1 video should depend on the retro machine or machines you intend to stream to. See the next step for details.

  2. Second, you need either a browser plugin or media player capable of handling MPEG1 or MPEG2 video on your retro machine. For MPEG2, this retro machine should probably be a fast Pentium II or a Pentium III at least. Reports are MPEG1, at low enough resolutions, can work on things as slow as a 486. Get the video ID of the YouTube video you intend to watch (for example, dQw4w9WgXcQ is the video id for https://www.youtube.com/watch?v=dQw4w9WgXcQ), and put it into your media player or plugin equipped browser as http://your.server.here/downtube.php?id=videoidhere, for example for a server with the IP 192.168.1.15 and the aforementioned ID, `http://192.168.1.15/downtube.php?id=dQw4w9WgXcQ`. If DownTube is properly configured, the video should play. Subtitles and transport controls are generally not supported. See the next answer for how to use DownTube to handle youtube URLs directly/automatically.


Q. How do I use the "redirect hack" mentioned in the comments? How do I have DownTube automatically handle YouTube URLs for me so I don't need to fix the URL?

A. The redirect hack requires that your webserver support URL rewriting, .htaccess files, and VHosts, for a VHost of "youtube.com" or a vhost of "*" to be configured on the server pointing to the directory with downtube.php in it as the root, and for the retro machine to be configured with either a hosts file entry or a local DNS server that makes it so that DNS queries for "youtube.com" point to the server running downtube. With these things in place, the .htaccess file provided with DownTube in place, and the "redirect hack" enabled, when your browser accesses `http://youtube.com/?video=video_id_here` or `http://youtube.com/?v=video_id_here` for any valid video id (even playlist URLs, though it won't actually play the whole playlist), it should effectively treat that as though you accessed `http://192.168.1.15/downtube.php?id=dQw4w9WgXcQ` and allow you to play it in your plugin-equipped browser or media player of choice.


Q. How do I scale the video so it properly fits on my target display?/The video is too big/too wide, how do I fix it?

A. See the $width and $height parameters in the script. To preserve the aspect ratio of the video, set one of these to the larger of the two dimensions for your target display/window size. For example, if you're watching videos on a computer with a 640x480 max resolution in fullscreen, the larger dimension is "640", which is the width, so set $width to "640". Then set whichever dimension you did not set to "-1", so in this case set "height" to "-1". ffmpeg's vf=scale filter will handle the rest. For the example given of a 640x480 display, with a 16:9 (widescreen) video, this would effectively give you an output resolution of 640x360.
