<?php
// This is the original 3 liner I wrote to do the job the quick and dirty way. The rest of it evolved out of making this safer and more configurable and customizable.
header('Content-Type: video/mpeg');
$youtube_id=escapeshellarg($_GET['id']);
passthru("youtube-dl -4 -f best " . $youtube_id . " -o - | ffmpeg -i - -vf scale=-1:480 -vcodec mpeg2video -acodec mp2 -b:v 15M -b:a 192k -muxrate 15M -f mpegts - ");
?>
