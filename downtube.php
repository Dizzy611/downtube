<?php
/* DownTube YouTube Transcoder
   Transcodes YouTube video from hard-to-decode (for older machines anyway) MPEG4/webm/VP8/whatever to
   easy-to-decode MPEG2. Uses a lot of bandwidth (relatively, anyway, about 15mbit) but, since it uses
   the highest quality video available (youtube-dl -f best), produces a very clear and sharp 480p image.
   May have issues handling 4k videos (Haven't tried any yet) due to this server perhaps not being
   fast enough to scale and transcode them in realtime. Have had no issues yet with 1080p source content.

   Requires youtube-dl and ffmpeg to be installed. May work with avconv's ffmpeg compatibility mode, I have not
   tested it with such. Requires your version of ffmpeg to be compiled to support decoding h.264 and webm video,
   aac and vorbis audio, and encoding mpeg2 video and audio and muxing mpeg2ts streams.

   Copyright (C) 2016 Dylan J. Morrison. Licensed under the GNU GPLv3. */

// Parameters! Modify these to your liking.

// Defaults here were selected due to my intended use case and personal experience streaming over wireless.
// ffmpeg binary, either just the binary name itself (if it's on path) or a full path to the binary. suggested values are "ffmpeg" "avconv" "/usr/bin/ffmpeg", etc.
$ffmpeg_binary = "ffmpeg";
// youtube downloader binary, similar to above. suggested values are "yt-dlp", "youtube-dl", "/usr/bin/yt-dlp", etc.
$ytdl_binary = "yt-dlp";
// Source format identifier (in youtube-dl format, see their documentation. I recommend "best" unless bandwidth prevents.
$ytdl_format = "best";
// Target video bitrate (in ffmpeg format, M for megabit, k for kilobit, no suffix for bits)
$bitrate = "15M";
// Target audio bitrate
$abitrate = "192k";
// Target width (in ffmpeg format, -1 for 'keep aspect ratio')
$width = "-1";
// Target height (in ffmpeg format, -1 for 'keep aspect ratio'). The default here resizes the video to 480 height, keeping the width in proportion.
$height = "480";
// Target video codec (I suggest mpeg2video if you're using this for the same reason I am, playing videos on older machines)
$codec = "mpeg2video";
// Target audio codec (I suggest mp2 if using mpeg2video and mpegts container.)
$acodec = "mp2";
// Target video container (mpegts works well for streaming!).
$container = "mpegts";
// Target audio container for audio only mode (mp3 suggested for compatibility.)
$audio_container = "mp3";
// MIME type for video. Set to "video/mpeg" for MPEGTS containers.
$video_mime = "video/mpeg";
// MIME type for audio. Set to "audio/mpeg" for MP3.
$audio_mime = "audio/mpeg";

// Do "redirect hack" to enable using as a direct substitute for YouTube via
// DNS redirection/URL rewrite. (see accompanying htaccess file for rewrite rule)
$redirecthack = True;
// Whether or not to output yt-dlp and ffmpeg "STDERR" (status messages) to a file in order to facilitate debugging.
$debugmode = False;

// End parameters


// Utility functions

function validId($id) { // Validate ID as conforming to youtube ID format
	return preg_match('/^[a-zA-Z0-9_-]{11}$/', $id) > 0;
} // Taken from stackoverflow question 2742813

function videoExists($id) { // Check if previously validated video exists on youtube servers.
	$headers = get_headers('https://www.youtube.com/oembed?format=json&url=http://www.youtube.com/watch?v=' . $id);
	if(is_array($headers) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/',$headers[0]) : false) {
		return True;
	} else {
		return False;
	}
} // Adapted from stackoverflow question 29166402

function doError($errortext) { // Handle fatal errors cleanly in a consistent format.
	header("HTTP/1.0 404 Not Found");
	// hack: DON'T CACHE OUR FUCKING ERRORS, credit to stackoverflow question 1907653
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	// end hack
	die("<HTML><HEAD><TITLE>YouTube Transcoder Error</TITLE></HEAD><BODY BGCOLOR='#FFFFFF'><H1>An error has occurred loading and transcoding your video.</H1><BR /><P>" . $errortext . "</P></BODY></HTML>");
}

// End utility functions


// Main code follows.

// Little hack here to allow DNS-and-URL-rewrite redirects for transparently redirecting from youtube.com. Allows using ?video= instead of ?id=.
if ($redirecthack == True) {
	if (!isset($_GET['id'])) {
		if (isset($_GET['video'])) {
			$youtube_id = $_GET['video'];
		} else if (isset($_GET['v'])) {
			$youtube_id = $_GET['v'];
		}
	} else {
		$youtube_id = $_GET['id'];
	}
} else {
	$youtube_id = $_GET['id'];
}

// Set a variable to detect if we're in video mode (default) or audio only mode.
if (isset($_GET['aonly'])) {
	if ($_GET['aonly'] == "yes") {
		$audio_mode = True;
	} else {
		$audio_mode = False;
	}
}

$escaped_youtube_id = escapeshellarg($youtube_id);

$valid = False;

$valid = validId($youtube_id);
if (!$valid) {
	doError("Failed pre-validation: ID " . $youtube_id . " does not appear to be a valid youtube video ID.");
}
$valid = videoExists($youtube_id);
if (!$valid) {
	doError("Failed validation: ID " . $youtube_id . " format valid, but does not appear to exist on YouTube.");
}

// Check the mode flag, and output video or audio as requested.
if ($audio_mode == True) {
	header('Content-Type: ' . $audio_mime);
	$ytdlline = $ytdl_binary . " -4 -f " . $ytdl_format . " " . $escaped_youtube_id . " -o -";
	$ffmpegline = $ffmpeg_binary . " -i - -vn -acodec " . $acodec . " -b:a " . $abitrate . " -f " . $audio_container . " - ";
} else {
	header('Content-Type: ' . $video_mime);
	$ytdlline = $ytdl_binary . " -4 -f " . $ytdl_format . " " . $escaped_youtube_id . " -o -";
	$ffmpegline = $ffmpeg_binary . " -i - -vf scale=" . $width . ":" . $height . " -vcodec " . $codec . " -acodec " . $acodec . " -b:v " . $bitrate . " -b:a " . $abitrate . " -muxrate " . $bitrate . " -f " . $container . " - ";
}

if ($debugmode == True) {
	passthru($ytdlline . " 2>>ytdl.log | " . $ffmpegline . " 2>>ffmpeg.log ");
} else {
	passthru($ytdlline . " | " . $ffmpegline);
}

?>
