/* jshint loopfunc:true */

(function(window) {
    "use strict"; 

    var http = 'http' + (window.location.protocol.charAt(4) == 's' ? 's://' : '://');

    /*
    * Lightweight JSONP fetcher
    * Copyright 2010-2012 Erik Karlsson. All rights reserved.
    * BSD licensed
    */
    
    
    /*
    * Usage:
    * 
    * JSONP.get( 'someUrl.php', {param1:'123', param2:'456'}, function(data){
    *   //do something with data, which is the JSON object you should retrieve from someUrl.php
    * });
    */
    var JSONP = (function(window){
        var counter = 0, head, query, key, config = {};
        function load(url) {
            var script = document.createElement('script'),
                done = false;
            script.src = url;
            script.async = true;
     
            script.onload = script.onreadystatechange = function() {
                if ( !done && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete") ) {
                    done = true;
                    script.onload = script.onreadystatechange = null;
                    if ( script && script.parentNode ) {
                        script.parentNode.removeChild( script );
                    }
                }
            };
            if ( !head ) {
                head = document.getElementsByTagName('head')[0];
            }
            head.appendChild( script );
        }
        function encode(str) {
            return encodeURIComponent(str);
        }
        function jsonp(url, params, callback, callbackName) {
            query = (url||'').indexOf('?') === -1 ? '?' : '&';
            params = params || {};
            for ( key in params ) {
                if ( params.hasOwnProperty(key) ) {
                    query += encode(key) + "=" + encode(params[key]) + "&";
                }
            }
            
            var jsonp = "json" + (++counter);
            window[ jsonp ] = function(data){
                callback(data);
                try {
                    delete window[ jsonp ];
                } catch (e) {}
                window[ jsonp ] = null;
            };

            load(url + query + (callbackName||config.callbackName||'callback') + '=' + jsonp);
            return jsonp;
        }
        function setDefaults(obj){
            config = obj;
        }
        return {
            get:jsonp,
            init:setDefaults
        };
    }(window));

    function getMetaContentByProperty(property, content){
        try {
            var ct = (content === null) ? 'content' : content;
            return document.querySelector("meta[property='" + property + "']").getAttribute(ct);
        } catch(e) {
            return "";
        }
    }

    window.pbfp = {};

    window.pbfp.profile = function (profile) {
        if (typeof txnId !== "undefined" && typeof myfp !== "undefined") {
        	var json = {
        	    "fp": myfp,
                "txn_id": txnId,
        	    "action": "profile",
        	};

			Object.keys(profile).map(function(key, index) {
			   	json[key] = profile[key]; 
			});                                    

	        JSONP.get(http + 'your_host/footprintjsonp', {"json":JSON.stringify(json)}, function(data) {
	               if (typeof console !== "undefined" && typeof console.log !== "undefined") {
	                   console.log(data);
	               }
	           });
        } 
    };

	var txnId;
	var myfp;
	var loadTs;

    new Fingerprint2().get(function(fingerprint, components){
	    myfp = fingerprint;	
	    loadTs = Math.round(new Date().getTime() / 1000);
        var json = {
            "fp": myfp,
            "title": document.title,
            "url": document.URL,
            "ua": navigator.userAgent,
            "action": "load",
        };

	    if (typeof myfp === 'string' && myfp.length !== 0) {
	    	txnId = myfp + loadTs;
	    	json.txn_id = txnId;
	    }

        JSONP.get(http + 'your_host/footprintjsonp', {"json":JSON.stringify(json)}, function(data) {
               if (typeof console !== "undefined" && typeof console.log !== "undefined") {
                   console.log(data);
               }
           });
    });

	if (typeof window.onbeforeunload !== "undefined") {
		window.onbeforeunload = function() {
			if (typeof txnId !== "undefined" && typeof myfp !== "undefined" && typeof loadTs !== "undefined") {
	        	var json = {
	        	    "fp": myfp,
	        	    "url": document.URL,
					"txn_id": txnId,
                    "action": "unload",
	        	};
	    	
				var unloadTs = Math.round(new Date().getTime() / 1000);
				json.stay = unloadTs - loadTs;
				
		        JSONP.get(http + 'your_host/footprintjsonp', {"json":JSON.stringify(json)}, function(data) {
		               if (typeof console !== "undefined" && typeof console.log !== "undefined") {
		                   console.log(data);
		               }
		         });
		     }
	
        };
	}

})(window);
