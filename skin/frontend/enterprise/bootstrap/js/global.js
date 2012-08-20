// Sandbox for all jQuery that is global for the site
// - wrapped in self-executing anonymous function for no conflict use of $ alias
// - use appropriate namespacing, don't pollute global namespace (not implemented, maybe a phase 2 cleanup)
(function($) {

    // SillyExample namespace
    if (typeof(SillyExample) === "undefined") {
        var SillyExample = {
            init : function() {
                // define events handlers and such
                $('#logo').on('click', SillyExample.clickHandler);
            },
            
            // results / actions
            clickHandler : function() {
                alert('OOPs there goes the namespace');
            }
        };
    }
    
    // MyNameSpace
    if (typeof(MyNameSpace) === 'undefined') {
        var MyNameSpace = {
            init: function() {
                console.log('Running MyNameSpace.init');
            }
        };
    }
    

    // DOM is ready, now do stuf
    $(function() {
        
        //SillyExample.init();
        //MyNameSpace.init();
 
        /**
         * Following are one-offs… should still be namespaced
         */
                      
        /*
        * Prevent default if menu links are "#"
        */
        $('nav a').each( function() {
        	var nav = $(this); 
        	if( nav.length > 0 ) {
        		if( nav.attr('href') == '#' ) {
        			//console.log(nav);
        			$(this).click(
        				function(e) {
        					e.preventDefault();
        				}
        			);
        		}
        	}
        }); 

        /*
         * Back to Top
         */
        $(window).scroll(function () {
            if ($(this).scrollTop() != 0) {
                $('#toTop').fadeIn();
            } else {
                $('#toTop').fadeOut();
            }
        });
        $('#toTop a').click(function (e) {
            e.preventDefault();
            $('body,html').animate({scrollTop: 0},800);
        });
        
        /*
         * Monetate placeholder div
         * - if banner is display:block, add 75px to margin-top of #mainContent.content_push
         * - requires Monetate to set to display:block in their script
         * - https://totsy1.jira.com/browse/MGN-919
         */
        //$('#monetateBanner').show(); /* test */
        if ( $('#monetateBanner').is(':visible') ) {
            $('#mainContent').css('margin-top', '+=75');
        }
        
        /*
         * Monetate JS
         * - commenting out temporarily
         * - remove from here if including in header.phtml or admin include instead
         */
/*
        var monetateT = new Date().getTime(); 
        (function() {
            var p = document.location.protocol; 
            if (p == "http:" || p == "https:") { 
                var m = document.createElement('script'); m.type ='text/javascript'; 
                m.async = true; 
                m.src = (p == "https:" ? "https://s" : "http://") + "b.monetate.net/js/1/a-6ac93b84/p/totsy.com/" + Math.floor((monetateT + 2372868) / 3600000) + "/g";
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(m, s);
            }
        })();        
*/
         
    
    });
    
})(jQuery);