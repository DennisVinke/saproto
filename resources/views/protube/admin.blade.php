@extends('website.layouts.default')

@section('page-title')
    Protube Admin
@endsection

@section('content')

    <div id="connecting">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-default">
                <div class="panel-body" style="text-align: center;">
                    <br>
                    <h3>Connecting...</h3>
                </div>
            </div>
        </div>
    </div>

    <div id="no_admin">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body" style="text-align: center;">
                    <br>
                    <h3>Could not connect to ProTube admin!</h3>
                    Very probably something went wrong. Please log-out, log-in and try again.
                </div>
            </div>
        </div>
    </div>

    <div id="connected">

        <div class="col-md-4">

            <div class="panel panel-default">
                <div class="panel-heading">ProTube control</div>
                <div class="panel-body">
                    <table class="table">
                        <tr>
                            <td>YouTube</td>
                            <td><input class="slider" id="youtubeV" data-slider-id="youtubeVSlider" type="text"
                                       data-slider-min="0" data-slider-max="100" data-slider-step="1"/></td>
                        </tr>
                        <tr>
                            <td>Radio</td>
                            <td><input class="slider" id="radioV" data-slider-id="radioVSlider" type="text"
                                       data-slider-min="0" data-slider-max="100" data-slider-step="1"/></td>
                        </tr>
                    </table>
                    <div class="btn-group btn-group-justified" role="group">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-default" id="protubeToggle" id="protubeToggle">...
                            </button>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-default" id="shuffleRadio"><i class="fa fa-random"
                                                                                               aria-hidden="true"></i>
                                Radio
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">ProTube<span id="currentPin" style="float: right;">...</span></div>
                <div class="panel-body">
                    <div class="btn-group btn-group-justified" role="group" aria-label="ProTube controls">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-default" id="skip">
                                <i class="fa fa-fast-forward" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-default" id="playpause">
                                ?
                            </button>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-default" id="reload">
                                <i class="fa fa-refresh" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-default" id="togglephotos">
                                <i class="fa fa-youtube-play" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div id="nowPlaying">
                        <!-- Filled by JS -->
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">Soundboard</div>
                <div class="panel-body">


                    <button type="button" class="btn btn-default soundboard" rel="airhorn">Horn</button>
                    <button type="button" class="btn btn-default soundboard" rel="rimshot">Rimshot</button>
                    <button type="button" class="btn btn-default soundboard" rel="baby">Huil Baby</button>
                    <button type="button" class="btn btn-default soundboard" rel="mickey">Mickey Mouse</button>
                    <button type="button" class="btn btn-default soundboard" rel="erg">Echt Erg!</button>
                    <button type="button" class="btn btn-default soundboard" rel="prachtig">Omdat Prachtig!</button>
                    <button type="button" class="btn btn-default soundboard" rel="drama">Drama</button>
                    <button type="button" class="btn btn-default soundboard" rel="sad-trombone">Fail</button>
                    <button type="button" class="btn btn-default soundboard" rel="gay">Hah... Gay!</button>
                    <button type="button" class="btn btn-default soundboard" rel="zoenen">Zoenen!</button>
                    <button type="button" class="btn btn-default soundboard" rel="tongen">Lekker tongen</button>
                    <button type="button" class="btn btn-default soundboard" rel="raar">RAARRR</button>
                    <button type="button" class="btn btn-default soundboard" rel="ovation">Applaus</button>
                    <button type="button" class="btn btn-default soundboard" rel="slowclap">Slow clap</button>
                    <button type="button" class="btn btn-default soundboard" rel="fluitje">NS</button>
                    <button type="button" class="btn btn-default soundboard" rel="porno">Porno</button>
                    <button type="button" class="btn btn-default soundboard" rel="keiharde_porno">Keiharde porno
                    </button>
                    <button type="button" class="btn btn-default soundboard" rel="laura">Laura</button>
                    <button type="button" class="btn btn-default soundboard" rel="jammer_joh">Jammer joh</button>
                    <button type="button" class="btn btn-default soundboard" rel="groen">GROEN!!!</button>
                    <button type="button" class="btn btn-default soundboard" rel="moan">Moan</button>
                    <button type="button" class="btn btn-default soundboard" rel="doodle">Doodle?</button>
                    <button type="button" class="btn btn-default soundboard" rel="wat-ik-voor-je-kan-doen">Zoek het maar
                        uit
                    </button>
                    <button type="button" class="btn btn-default soundboard" rel="tanman">Tan man!</button>
                    <button type="button" class="btn btn-default soundboard" rel="inception">BRAAAAAAM</button>
                    <button type="button" class="btn btn-default soundboard" rel="nooo">NOOOOO</button>
                    <button type="button" class="btn btn-default soundboard" rel="evil">MUHAHAHA</button>
                    <button type="button" class="btn btn-default soundboard" rel="boo">BOOOOOO</button>
                    <button type="button" class="btn btn-default soundboard" rel="chewbacca">Chewie</button>
                    <button type="button" class="btn btn-default soundboard" rel="batman">Batman</button>
                    <button type="button" class="btn btn-default soundboard" rel="IK_BEN_REINIER">IK BEN REINIER!!!
                    </button>
                    <button type="button" class="btn btn-default soundboard" rel="Liefje_aandacht">Aandacht!</button>
                    <button type="button" class="btn btn-default soundboard" rel="bestuuuuuuuuur">Bestuuuuur!</button>
                </div>
            </div>

        </div>

        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">Search</div>
                <div class="panel-body">
                    <form id="searchForm">
                        <div class="form-group" width="100%">
                            <div class="input-group">
                                <div class="input-group-addon"><label for="showVideo"><i class="fa fa-eye"
                                                                                         aria-hidden="true"></i></label>
                                    <input type="checkbox" id="showVideo" checked="checked"></div>
                                <input type="text" class="form-control" id="searchBox" placeholder="Search YouTube...">
                                <div class="input-group-addon" id="clearSearch">x</div>
                            </div>
                        </div>
                    </form>
                    <div id="searchResults"></div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">Queue</div>
                <div class="panel-body">
                    <div id="queue"></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">Light control</div>
                <div class="panel-body">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Waving light</h3>
                        </div>
                        <div class="panel-body">
                            <div class="btn-group btn-group-justified" role="group">
                                <a class="btn btn-default lampOn" href="#" rel="18">On</a>
                                <a class="btn btn-default lampOff" href="#" rel="18">Off</a>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Disco light</h3>
                        </div>
                        <div class="panel-body">
                            <div class="btn-group btn-group-justified" role="group">
                                <a class="btn btn-default lampOn" href="#" rel="2">On</a>
                                <a class="btn btn-default lampOff" href="#" rel="2">Off</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Screens</div>
                <div class="panel-body" id="protubeScreens">
                    Loading client list...
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Admins</div>
                <div class="panel-body" id="protubeAdmins">
                    Loading client list...
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Users</div>
                <div class="panel-body" id="protubeUsers">
                    Loading client list...
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')

    @parent
    <script type="text/javascript"
            src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/9.1.1/bootstrap-slider.min.js"></script>

    <script>
        var server = "{!! env('HERBERT_SERVER') !!}";
        var token = "{!! Session::get('token') !!}";

        $(document).ready(function () {
            var errorElement = $("body");

            var admin = io(server + '/protube-admin');

            admin.on("connect", function () {
                admin.emit("authenticate", token);
            });

            // On disconnect, hide admin and show connecting screen
            admin.on("disconnect", function () {
                $("#connected").hide(0);
                $("#connecting").show(0);
            });

            // On connect, hide connecting screen and show admin
            admin.on("authenticated", function (data) {
                $("#connecting").hide(0);
                $("#connected").show(0);
            });

            // On connect, hide connecting screen and show admin
            admin.on("no_admin", function (data) {
                $("#connecting").hide(0);
                $("#no_admin").show(0);
            });

            // Initialize volume sliders.
            $("#youtubeV").slider().on("slideStop", function (event) {
                admin.emit("setYoutubeVolume", event.value);
            });
            $("#radioV").slider().on("slideStop", function (event) {
                admin.emit("setRadioVolume", event.value);
            });

            admin.on("queue", function (data) {
                var queue = $("#queue");
                queue.html("");

                for (var i in data) {
                    var controls = "";
                    if (i > 0) controls += '<span class="up" data-index="' + i + '"><i class="fa fa-arrow-circle-up" aria-hidden="true"></i></span>';
                    if (i < data.length - 1) controls += '<span class="down" data-index="' + i + '"><i class="fa fa-arrow-circle-down" aria-hidden="true"></i></span>';
                    controls += '<span class="veto" data-index="' + i + '"><i class="fa fa-minus-circle" aria-hidden="true"></i></span>';

                    queue.append('<div class="item" data-ytId="' + data[i].id + '">' +
                        '<img src="//img.youtube.com/vi/' + data[i].id + '/0.jpg" />' +
                        '<div>' +
                        '<h1>' + data[i].title + '</h1>' +
                        '<h2>' + prettifyDuration(data[i].duration) + '</h2>' +
                        '<h3>' + controls + '</h3>' +
                        '</div>' +
                        '<div style="clear: both;"></div>' +
                        '</div>');
                }

                $(".up").click(function (e) {
                    e.preventDefault();
                    moveQueueItem($(this).attr("data-index"), 'up');
                });

                $(".down").click(function (e) {
                    e.preventDefault();
                    moveQueueItem($(this).attr("data-index"), 'down');
                });

                $(".veto").click(function (e) {
                    e.preventDefault();
                    admin.emit("veto", $(this).attr("data-index"));
                });
            });

            function moveQueueItem(index, direction) {

                var data = {
                    'index': index,
                    'direction': direction
                };

                admin.emit("move", data);
            }

            admin.on("ytInfo", function (data) {
                if (!$.isEmptyObject(data)) {
                    $("#nowPlaying").html('<img src="//img.youtube.com/vi/' + data.id + '/0.jpg" width="100px" class="pull-left img-thumbnail" />' +
                        '<h1>' + data.title + '</h1>' +
                        '<strong id="current_time">0:00</strong> <input class="slider" id="progress" data-slider-id="progressSlider" type="text" data-slider-min="0" data-slider-max="' + data.duration +
                        '" data-slider-step="1" data-slider-value="' + data.progress + '"/> <strong>' + prettifyDuration(data.duration) + '</strong>');
                    $("#progress").slider({
                        formatter: function (value) {
                            return prettifyDuration(value);
                        }
                    }).on("slideStop", function (event) {
                        admin.emit("setTime", event.value);
                    });
                } else {
                    $("#nowPlaying").html("");
                }
            });

            admin.on("progress", function (data) {
                $("#progress").slider('setValue', data);
                $("#current_time").html(prettifyDuration(data));
            });

            admin.on("pin", function (data) {
                $("#currentPin").html("PIN: " + data);
            });

            admin.on("playerState", function (data) {
                if (data.slideshow) {
                    $("#togglephotos").html('<i class="fa fa-youtube-play" aria-hidden="true"></i>');
                } else {
                    $("#togglephotos").html('<i class="fa fa-picture-o" aria-hidden="true"></i>');
                }
                if (data.playing) {
                    if (data.paused) {
                        $("#playpause").html('<i class="fa fa-play" aria-hidden="true"></i>');
                    } else {
                        $("#playpause").html('<i class="fa fa-pause" aria-hidden="true"></i>');
                    }
                    $("#skip").html('<i class="fa fa-fast-forward" aria-hidden="true"></i>');
                } else {
                    $("#playpause").html('<i class="fa fa-ellipsis-h" aria-hidden="true"></i>');
                    $("#togglephotos").html('<i class="fa fa-ellipsis-h" aria-hidden="true"></i>');
                    $("#skip").html('<i class="fa fa-ellipsis-h" aria-hidden="true"></i>');
                }
                if (data.protubeOn) {
                    $("#protubeToggle").html('<i class="fa fa-toggle-on" aria-hidden="true"></i> ProTube');
                } else {
                    $("#protubeToggle").html('<i class="fa fa-toggle-off" aria-hidden="true"></i> ProTube');
                }
            });


            $('#searchForm').bind('submit', function (e) {
                e.preventDefault();
                admin.emit("search", $("#searchBox").val());
                $("#results").html("Loading...");
            });

            admin.on("searchResults", function (data) {
                var results = $("#searchResults");

                results.html("");

                for (var i in data) {
                    results.append(generateResult(data[i]));
                }

                $(".result").each(function (i) {
                    var current = $(this);
                    current.click(function (e) {
                        e.preventDefault();
                        admin.emit("add", {
                            id: current.attr("ytId"),
                            showVideo: ($("#showVideo").prop("checked") ? true : false)
                        });
                    });
                });

                results.show(100);
            });

            admin.on("clients", function (data) {
                $("#protubeScreens").html("");
                $("#protubeAdmins").html("");
                $("#protubeUsers").html("");
                for (var i in data) {
                    var client = data[i];
                    switch (client.type) {
                        case'screen':
                            $("#protubeScreens").append("<p>Connection from <strong>" + client.network + "</strong></p>")
                            break;
                        case'admin':
                            $("#protubeAdmins").append("<p><strong>" + client.name + "</strong><br><sup>Connection from " + client.network + "</sup></p>")
                            break;
                        case 'remote':
                            $("#protubeUsers").append("<p><strong>" + client.name + "</strong><br><sup>Connection from " + client.network + "</sup></p>")
                            break;
                    }

                }
            });

            $("#clearSearch").click(function (e) {
                e.preventDefault();
                $("#searchResults").hide(0);
                $("#searchBox").val("");
            });

            $("#playpause").click(function (e) {
                e.preventDefault();
                admin.emit("pause");
            });

            $("#skip").click(function (e) {
                e.preventDefault();
                admin.emit("skip");
            });

            $("#reload").click(function (e) {
                e.preventDefault();
                admin.emit("fullReload");
            });

            $("#togglephotos").click(function (e) {
                e.preventDefault();
                admin.emit("togglePhotos");
            });

            $("#protubeToggle").click(function (e) {
                e.preventDefault();
                admin.emit("protubeToggle");
            });

            $("#shuffleRadio").click(function (e) {
                e.preventDefault();
                admin.emit("shuffleRadio");
            });

            $(".soundboard").click(function (e) {
                e.preventDefault();
                admin.emit("soundboard", $(this).attr("rel"));
            });

            $(".lampOn").click(function (e) {
                e.preventDefault();
                admin.emit("lampOn", $(this).attr("rel"));
            });

            $(".lampOff").click(function (e) {
                e.preventDefault();
                admin.emit("lampOff", $(this).attr("rel"));
            });

            admin.on("volume", function (data) {
                $("#youtubeV").slider('setValue', data.youtube);
                $("#radioV").slider('setValue', data.radio);
            });
        });

        function generateResult(item) {
            var result = '<div class="result" ytId="' + item.id + '">' +
                '<img src="//img.youtube.com/vi/' + item.id + '/0.jpg" />' +
                '<div>' +
                '<h1>' + item.title + '</h1>' +
                '<h2>' + item.channelTitle + '</h2>' +
                '<h3>' + item.duration + '</h3>' +
                '</div>' +
                '<div style="clear: both;"></div>' +
                '</div>';

            return result;
        }

        // Based on //stackoverflow.com/questions/3733227/javascript-seconds-to-minutes-and-seconds
        function prettifyDuration(time) {
            var minutes = Math.floor(time / 60);
            var seconds = time - minutes * 60;

            function str_pad_left(string, pad, length) {
                return (new Array(length + 1).join(pad) + string).slice(-length);
            }

            var finalTime = str_pad_left(minutes, '0', 2) + ':' + str_pad_left(seconds, '0', 2);

            return finalTime;
        }
    </script>
@endsection

@section('stylesheet')

    @parent
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/9.1.1/css/bootstrap-slider.min.css">

    <style>
        #connected {
            display: none;
            margin-top: 20px;
        }

        #no_admin {
            display: none;
        }

        #progressSlider .slider-selection {
            background: #BABABA;
        }

        #nowPlaying {
            margin-top: 10px;
        }

        #nowPlaying h1 {
            padding: 0;
            margin: 10px 0;
            font-size: 18px;
        }

        #nowPlaying img {
            margin-right: 10px;
        }

        #clearSearch:hover {
            background-color: #ddd;
            cursor: pointer;
        }

        #searchResults {
            display: none;
            height: 200px;
            overflow-y: scroll;
        }

        #searchResults .result:hover {
            cursor: pointer;
            background-color: #eee;
        }

        #searchResults .result img {
            width: 100px;
            float: left;
            margin-right: 10px;
        }

        #searchResults .result h1 {
            font-size: 16px;
            margin: 5px 0;
        }

        #searchResults .result h2 {
            font-size: 12px;
            margin: 2px 0;
        }

        #searchResults .result h3 {
            font-size: 10px;
            margin: 0;
        }

        #queue {
        }

        #queue .item:hover {
            cursor: pointer;
            background-color: #eee;
        }

        #queue .item img {
            width: 100px;
            float: left;
            margin-right: 10px;
        }

        #queue .item h1 {
            font-size: 16px;
            margin: 5px 0;
        }

        #queue .item h2 {
            font-size: 12px;
            margin: 2px 0;
        }

        .soundboard {
            width: 48%;
        }
    </style>
@endsection
