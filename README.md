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
http://www.youtube.com/watch?video=1aVHLL5egRY

http://yourwebserverhere/downtube.php?id=1aVHLL5egRY

*with* the htaccess file, it can also be used as such:

http://yourwebserverhere/yt/1aVHLL5egRY
http://yourwebserverhere/watch?id=1aVHLL5egRY

with the htaccess file *and* the "redirect hack" enabled (see the parameters section of the php file) it can be used as such:

http://yourwebserverhere/watch?video=1aVHll5egRY
http://yourwebserverhere/watch?v=1aVHll5egRY

which, if you have a DNS redirection/hosts file redirect/some other way to turn "youtube.com" into "yourwebserverhere" would allow you to *seamlessly* click youtube links and watch them in MPEG2!



That's really all I have to say. Feel free to report bugs, ask me questions, fork this project, make pull requests, whatever. I don't guarantee a response. :P

What do I need to use DownTube?
-------------------------------

You need a webserver (Apache2 required to use the .htaccess file with the URL rewrites, otherwise any that can interface with PHP), PHP5, youtube-dl, and ffmpeg. That's all.
