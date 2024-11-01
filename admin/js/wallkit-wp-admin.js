(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

    $( window ).load(function() {

        // Confirmation for restore Appearance data to default.
        $('.wk-restore-button').on("click", function (e) {
            if(!confirm('Are you sure you want restore to defaults?\nAll current data will be cleaned.')) {
                e.preventDefault();
            }
        });

        if($("div#wk-tools-data").length > 0)
        {
            start_watch_task()
        }

        $("#stop-wk_sync_all_posts").on("click", function() {

            var data = {
                action: 'wk_stop_sync_task'
            };

            $.post( ajaxurl, data, function(response) {
                //console.log(response);
                //jQuery("#wk-tools-data").html(response);
            });

        });

        $("#continue-wk_sync_all_posts").on("click", function() {

            var data = {
                action: 'wk_continue_sync_task'
            };

            $.post( ajaxurl, data, function(response) {
                //console.log(response);
                //jQuery("#wk-tools-data").html(response);
            });

        });

        $("#pause-wk_sync_all_posts").on("click", function() {

            var data = {
                action: 'wk_pause_sync_task'
            };

            $.post( ajaxurl, data, function(response) {
                //console.log(response);
                //jQuery("#wk-tools-data").html(response);
            });

        });

        $("button#run-wk_sync_all_posts").on("click", function() {

            //console.log("Run all posts sync task");

            if(confirm("Notice: This process might consume significant server resources on large content database. Okay to proceed?")) {
                var data = {
                  action: 'wk_run_sync_task'
                };

                $.post( ajaxurl, data, function(response) {
                  //console.log(response);
                });
            }

        });


      $("#tabs").tabs();

    });



    var start_watch_task = function ()
    {
        setInterval(function() {
            $.post( ajaxurl, { action: 'wk_check_sync_task'}, function(response) {

                if(response.sync_posts_finished && response.sync_posts_total) {

                    if(response.sync_posts_finished > response.sync_posts_total ) {
                        response.sync_posts_total = response.sync_posts_finished;
                    }

                    var procent = (response.sync_posts_finished / response.sync_posts_total) * 100;

                    $(".progress-ready").css("width", procent+"%");
                }

                if(response.status) {
                    $("#status").text(response.status);
                }
                if(response.sync_posts_finished) {
                    $("#posts_sync").text(response.sync_posts_finished);
                }
                if(response.sync_posts_total) {
                    $("#posts_total").text(response.sync_posts_total);
                }
                if(response.sync_posts_failed) {
                    $("#posts_failed").text(response.sync_posts_failed);
                }
                if(response.start_time) {
                    $("#start_time").text(TimeToString(response.start_time));
                }
                if(response.end_time) {
                    $("#end_time").text(TimeToString(response.end_time));
                } else {
                    $("#end_time").text("");

                }

                if(response.log)
                {
                    $("#last_log").html(response.log);
                }

                if(response.last_time) {
                    $("#last_time").text(TimeToString(response.last_time));
                } else {
                    $("#last_time").text("");

                }

                $("div.wk-loading").remove();
                $("table#wk-content").css("opacity", 1);

                if(response.sync_posts_finished && response.last_time && response.start_time) {
                    var speed = response.sync_posts_finished / (response.last_time - response.start_time);
                    $("#sync_speed").text(roundPlus(speed, 2)+" posts per second");
                }

            })

        }, 2000);
    }

    /**
     * Converts timestamp to formatted date string
     * @param {number} timestamp
     * @returns {string}
     */
    function TimeToString(timestamp) {
        var date = new Date(timestamp*1000);
        return date.toLocaleString();
    }

    /**
     * Rounds number
     * @param {number} x - value
     * @param {number} n - number of digits after point
     *
     * @example
     * // returns 2.6
     * roundPlus(2.6783, 2);
     *
     * @returns {number}
     */
    function roundPlus(x, n) {
        if(isNaN(x) || isNaN(n)) return false;
        var m = Math.pow(10,n);
        return Math.round(x*m)/m;
    }

    $(document).ready(function($) {

        let codeMirrorPaywallStyles = $("#wk_paywall_styles, #wk_my_account_styles");
        if(codeMirrorPaywallStyles.length > 0)
        {
            codeMirrorPaywallStyles.each((i, textarea) => {
                wp.codeEditor.initialize(textarea, window.codemirror_paywall_styles);
            });
        }

        let codeMirrorAdditionalOptions = $("#wk_additional_options");
        if(codeMirrorAdditionalOptions.length > 0)
        {
            wp.codeEditor.initialize(codeMirrorAdditionalOptions, window.codemirror_additional_options);
        }

        let codeMirrorAdditionalScript = $("#wk_additional_script");
        if(codeMirrorAdditionalScript.length > 0)
        {
            wp.codeEditor.initialize(codeMirrorAdditionalScript, window.codemirror_additional_options);
        }
    })

})( jQuery );