"use strict"; 

(function(window) {

    var ts4 = (new Date).getTime() + Math.floor(Math.random() * 14872417);
    var http = 'http' + (window.location.protocol.charAt(4) == 's' ? 's://' : '://');

    function jsmultiloader(url, callback) {
        var script_list = [];
        var total = 0;
        var _d = window.document;
        var l;
    
        if (typeof (url) === 'string') {
            script_list.push(url);
            total++;
        }
        else if (typeof (url) === 'object') {
            l = url.length;
            for (var i = 0; i < l; i++) {
                script_list.push(url[i]);
                total++;
            }
        }
    
        l = script_list.length;
        var script;
        for (var i = 0; i < l; i++) {
            script = '';
            if (script_list[i].split('.').pop() === "css") {
                script = window.document.createElement("link");
                script.type = "text/css";
                script.rel = "stylesheet";
                script.href = script_list[i];
                total--;
            }
            else {
                script = _d.createElement("script");
                script.type = "text/javascript";
                script.src = script_list[i];
                if (script.readyState) { // IE
                    script.onreadystatechange = function() {
                        if (script.readyState == "loaded" || script.readyState == "complete") {
                            total--;
                            if (typeof (callback) === "function" && total <= 0) {
                                script.onreadystatechange = null;
                                callback();
                            }
                        }
                    };
                }
                else { // other browsers
                    script.onload = function() {
                        total--;
                        if (typeof (callback) === "function" && total <= 0) {
                            callback();
                        }
                    };
                }
            }
    
            (_d.getElementsByTagName('head')[0] || _d.getElementsByTagName('body')[0])
            .appendChild(script);
        }
    }

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

            load(url + query + (callbackName||config['callbackName']||'callback') + '=' + jsonp);
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

    function getMetaContentByProperty(property,content){
        try {
            var content = (content==null)?'content':content;
            return document.querySelector("meta[property='" + property + "']").getAttribute(content);
        } catch(e) {
            return "";
        }
    }

    var jsArr = [];
    if (typeof(Fingerprint) === "undefined") {
        jsArr.push(http + "cdnjs.cloudflare.com/ajax/libs/fingerprintjs2/1.5.0/fingerprint2.min.js?a=" + ts4);
    }

    jsmultiloader(
        jsArr,
        function() {
            new Fingerprint2().get(function(fingerprint, components){
            var json = {
                "fp": fingerprint,
                "title": document.title,
                "desc": getMetaContentByProperty("og:description"),
                "url": document.URL,
                "ts": Math.round(new Date().getTime() / 1000),
                "ua": navigator.userAgent
            };

            if (typeof(pbfp) === 'object' && pbfp.hasOwnProperty("sid") && pbfp.hasOwnProperty("sn")) {
                json.sid = pbfp.sid;
                json.sn= pbfp.sn;
            }

            JSONP.get('/footprintjsonp', {"json":JSON.stringify(json)}, function(data) {
                     console.log(data);  
                 });
            });
        }
    );

})(window);
