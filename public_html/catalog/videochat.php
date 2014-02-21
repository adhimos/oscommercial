<?php

require('includes/application_top.php');

if (!tep_session_is_registered('customer_id'))
{
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
}

$check_customer_query = tep_db_query("select customers_firstname, customers_lastname, customers_id from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
$check_customer = tep_db_fetch_array($check_customer_query);
$name=$check_customer['customers_firstname']."_".$check_customer['customers_lastname']."$".$check_customer['customers_id'];
$name=mysql_real_escape_string($name);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Video Chat</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <link rel="stylesheet" href="https://www.webrtc-experiment.com/style.css">        
        <style>
            audio, video {
                -moz-transition: all 1s ease;
                -ms-transition: all 1s ease;
                
                -o-transition: all 1s ease;
                -webkit-transition: all 1s ease;
                transition: all 1s ease;
                vertical-align: top;
            }

            input {
                border: 1px solid #d9d9d9;
                border-radius: 1px;
                font-size: 2em;
                margin: .2em;
                width: 30%;
            }

            .setup {
                border-bottom-left-radius: 0;
                border-top-left-radius: 0;
                font-size: 102%;
                height: 47px;
                margin-left: -9px;
                margin-top: 8px;
                position: absolute;
            }

            p { padding: 1em; }

            li {
                border-bottom: 1px solid rgb(189, 189, 189);
                border-left: 1px solid rgb(189, 189, 189);
                padding: .5em;
            }

            .highlight { color: rgb(0, 8, 189); }
        </style>

        <script>
            document.createElement('article');
            document.createElement('footer');
        </script>
        <script type="text/javascript" src="ext/jquery/jquery-1.8.0.min.js"></script>

        <!--scripts used for peers connection -->
        <script src="ext/webrtc/socket.io.js"> </script>
        <script src="ext/webrtc/PeerConnection.js"> </script>
    </head>

    <body>
        <article>
            <header style="text-align: center;">
                <h1>Video Conference</h1>

   		    </header>

            <div class="github-stargazers"></div>

            
            <!-- just copy this <section> and next script -->
            <section class="experiment">                
                <section>
					<!--<span>
                        Private ?? <a href="/socket.io/" target="_blank" title="Setup Private Room!"><code><strong id="unique-token">#123456789</strong></code></a>
                    </span>-->
                    <?php
                       echo "<input type='hidden' id='your-name' value='$name'>";

                    ?>

                    <button id="start-broadcasting" class="setup">Start Video</button>
                </section>
                
                <!-- list of all available conferencing rooms -->
                <table id="rooms-list" style="width: 100%;"></table>
                
                <!-- local/remote videos container -->
                <div id="videos-container"></div>
            </section>
        
            <script>
               var channel = location.href.replace(/\/|:|#|%|\.|\[|\]/g, '');
				var sender =  document.getElementById('your-name').value; //Math.round(Math.random() * 999999999) + 999999999;
				var SIGNALING_SERVER = 'http://group15cs5331.info:8888/';
				io.connect(SIGNALING_SERVER).emit('new-channel', {
					channel: channel,
					sender: sender
				});

				var socket = io.connect(SIGNALING_SERVER + channel);
				socket.on('connect', function () {
					// setup peer connection & pass socket object over the constructor!
				});

				socket.send = function (message) {
					socket.emit('message', {
						sender: sender,
						data: message
					});
				};

				// var peer = new PeerConnection('http://socketio-signaling.jit.su:80');
                var peer = new PeerConnection(socket);
               peer.userid = sender;
                peer.onUserFound = function(userid) {
                    if (document.getElementById(userid)) return;
                    var tr = document.createElement('tr');

                    var td1 = document.createElement('td');
                    var td2 = document.createElement('td');

                    td1.innerHTML = userid.replace(/\#\d*$/g, "") + ' is waiting for video chat. Click start to initiate video chat?';

                    var button = document.createElement('button');
                    button.innerHTML = 'Join';
                    button.id = userid;
                    button.style.float = 'right';
                    button.onclick = function() {
                    button = this;
                        getUserMedia(function(stream) {
                                peer.addStream(stream);
                                peer.sendParticipationRequest(button.id);
                        });
					button.disabled = true;
                    };
                    td2.appendChild(button);

                    tr.appendChild(td1);
                    tr.appendChild(td2);
                    roomsList.appendChild(tr);
                    jQuery('#start-broadcasting').hide();
                };

                peer.onStreamAdded = function(e) {
                    var video = e.mediaElement;
                    video.setAttribute('width', 600);
                    videosContainer.insertBefore(video, videosContainer.firstChild);

                    video.play();
                    rotateVideo(video);
                    scaleVideos();
                };

                peer.onStreamEnded = function(e) {
                    var video = e.mediaElement;
                    if (video) {
                        video.style.opacity = 0;
                        rotateVideo(video);
                        setTimeout(function() {
                            video.parentNode.removeChild(video);
                            scaleVideos();
                        }, 1000);
                    }
                };

                jQuery('#start-broadcasting').click(function() {

                    this.disabled = true;
					getUserMedia(function(stream) {
						peer.addStream(stream);
						peer.startBroadcasting();
					});
                });

                document.querySelector('#your-name').onchange = function() {
                    //alert(this.value);
                    peer.userid = this.value;
                };

                var videosContainer = document.getElementById('videos-container') || document.body;
                var btnSetupNewRoom = document.getElementById('setup-new-room');
                var roomsList = document.getElementById('rooms-list');

                if (btnSetupNewRoom) btnSetupNewRoom.onclick = setupNewRoomButtonClickHandler;

                function rotateVideo(video) {
                    video.style[navigator.mozGetUserMedia ? 'transform' : '-webkit-transform'] = 'rotate(0deg)';
                    setTimeout(function() {
                        video.style[navigator.mozGetUserMedia ? 'transform' : '-webkit-transform'] = 'rotate(360deg)';
                    }, 1000);
                }

                function scaleVideos() {
                    var videos = document.querySelectorAll('video'),
                        length = videos.length, video;

                    var minus = 130;
                    var windowHeight = 700;
                    var windowWidth = 600;
                    var windowAspectRatio = windowWidth / windowHeight;
                    var videoAspectRatio = 4 / 3;
                    var blockAspectRatio;
                    var tempVideoWidth = 0;
                    var maxVideoWidth = 0;

                    for (var i = length; i > 0; i--) {
                        blockAspectRatio = i * videoAspectRatio / Math.ceil(length / i);
                        if (blockAspectRatio <= windowAspectRatio) {
                            tempVideoWidth = videoAspectRatio * windowHeight / Math.ceil(length / i);
                        } else {
                            tempVideoWidth = windowWidth / i;
                        }
                        if (tempVideoWidth > maxVideoWidth)
                            maxVideoWidth = tempVideoWidth;
                    }
                    for (var i = 0; i < length; i++) {
                        video = videos[i];
                        if (video)
                            video.width = maxVideoWidth - minus;
                    }
                }

                window.onresize = scaleVideos;
				
				// you need to capture getUserMedia yourself!
				function getUserMedia(callback) {
					var hints = {audio:true,video:{
						optional: [],
						mandatory: {
							minWidth: 1280,
							minHeight: 720,
							maxWidth: 1920,
							maxHeight: 1080,
							minAspectRatio: 1.77
						}
					}};
					navigator.getUserMedia(hints,function(stream) {
						var video = document.createElement('video');
						video.src = URL.createObjectURL(stream);
						video.controls = true;
						video.muted = true;
						
						peer.onStreamAdded({
							mediaElement: video,
							userid: 'self',
							stream: stream
						});
						
						callback(stream);
					});
				}
				
			    (function() {
                    var uniqueToken = document.getElementById('unique-token');
                    //if (uniqueToken)
                        //if (location.hash.length > 2)
                        //    uniqueToken.parentNode.parentNode.parentNode.innerHTML = '<h2 style="text-align:center;"><a href="' + location.href + '" target="_blank">Share this link</a></h2>';
                        //else
                        //    uniqueToken.innerHTML = uniqueToken.parentNode.parentNode.href = '#' + (Math.random() * new Date().getTime()).toString(36).toUpperCase().replace( /\./g , '-');
                })();

            </script>
            
             </body>
</html>
<?php
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>