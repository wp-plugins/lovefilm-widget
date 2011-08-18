// LOVEFiLM Config Options
LFWidget$('#lf-widget').ready(function() {
    LFWidget.title_length = 34;

    LFWidget.page_width = parseInt(LFWidget$('#lf-widget #lf-wrapped').width(), 10) - 2;
    LFWidget.page_height = 177;
    
    LFWidget.film_width = 75;
    LFWidget.film_height = 140;
    LFWidget.film_count = 6;
    
    LFWidget.image_height = 85;
    LFWidget.button_width = 17;
    LFWidget.button_margin = 4;
    
    if(LFWidget.page_width >= 270) {
        LFWidget.position_increment = 2;
        LFWidget.target_position = (LFWidget.page_width / 2) - (LFWidget.film_width * 2);
    } else {
        LFWidget.position_increment = 2;
        LFWidget.target_position = (LFWidget.page_width / 2) - (LFWidget.film_width * 2);
    }
    
    LFWidget.button_count = Math.ceil(LFWidget.film_count / LFWidget.position_increment);
    
    LFWidget$('#lf-widget #favourites .movie .text').each(function() {
        var txt = LFWidget$(this).text().match(/^\s*(.*)\s*$/)[1];
        if(txt.length > LFWidget.title_length) {
            txt = txt.substring(0, LFWidget.title_length);
            
            var lastSpace = txt.lastIndexOf(' ');
            if(lastSpace == -1) lastSpace = txt.length;
            LFWidget$(this).text(txt.substring(0, lastSpace) + '...');
        }
    });
    
    for(var i = 0; i < LFWidget.film_count; i++) {
        LFWidget$('#lf-widget #favourites #movie-' + i).css('left', (LFWidget.target_position + (i * LFWidget.film_width)) + 'px');
    }
    
    LFWidget$('#lf-widget #favourites #scroller-indent').append('<span class="nav-scroller scroll-left"></span>');
    LFWidget$('#lf-widget #favourites #scroller-indent .scroll-left').click(function() {
        LFWidget.scroller(LFWidget$('#lf-widget .nav-selected').prev(), LFWidget.movie_selected - LFWidget.position_increment);
    });

    for(var i = 0; i < LFWidget.button_count; i++) {
        LFWidget$('#lf-widget #favourites #scroller-indent').append('<span id="scroll-icon-'+i+'" class="nav-icon'+((i == 0)?' nav-selected':'')+'"></span>');
        LFWidget$('#lf-widget #favourites #scroller-indent #scroll-icon-'+i).click(function() {
            LFWidget.scroller(LFWidget$(this).get(0), (parseInt(LFWidget$(this).attr('id').match(/^scroll-icon-(\d+)$/)[1], 10) * LFWidget.position_increment) + 1);
        });
    }
    
    LFWidget$('#lf-widget #favourites #scroller-indent').append('<span class="nav-scroller scroll-right"></span>');
    LFWidget$('#lf-widget #favourites #scroller-indent .scroll-right').click(function() {
        LFWidget.scroller(LFWidget$('#lf-widget .nav-selected').next(), LFWidget.movie_selected + LFWidget.position_increment);
    });
    
    LFWidget$('#lf-widget #favourites #scroller').width(LFWidget.page_width);
    LFWidget$('#lf-widget #favourites #scroller-indent').css('left', ((LFWidget.page_width - ((LFWidget.button_width + LFWidget.button_margin) * (LFWidget.button_count + 2)))/2)+'px');
    LFWidget$('#lf-widget #favourites #scroller-indent span.nav-icon').width(LFWidget.button_width);
    LFWidget$('#lf-widget #favourites #scroller-indent span.nav-scroller').width(LFWidget.button_width);
    LFWidget$(document).ready(LFWidget.document_loaded);
    
	LFWidget$('#lf-widget iframe#most-popular').error(function(){this.src = this.src}).
    attr('src', LFWidget.remote_addr+'frame-provider/most-popular?context='+LFWidget.context+'&lfid='+LFWidget.lfid+'&domain='+encodeURIComponent(LFWidget.domain)+'&promo='+encodeURIComponent(LFWidget.promoCode)+'&affid='+encodeURIComponent(LFWidget.affid)+'&class='+LFWidget.theme+'&width='+Math.ceil(LFWidget$('#lf-widget .frame iframe').width()));
	LFWidget$('#lf-widget iframe#latest-releases').error(function(){this.src = this.src}).
    attr('src', LFWidget.remote_addr+'frame-provider/latest-releases?context='+LFWidget.context+'&lfid='+LFWidget.lfid+'&domain='+encodeURIComponent(LFWidget.domain)+'&promo='+encodeURIComponent(LFWidget.promoCode)+'&affid='+encodeURIComponent(LFWidget.affid)+'&class='+LFWidget.theme+'&width='+Math.ceil(LFWidget$('#lf-widget .frame iframe').width()));
	/*LFWidget$('.lf-widget .accordion div.heading').click(lf_toggleDD);
	LFWidget$('.lf-widget .accordion div.heading span').not('.open').parent().each(function(index) {
		LFWidget$(this).next().hide();
	});*/
	LFWidget$('#lf-widget .accordion').accordion({active:0}).bind('accordionchangestart', function(event,ui) {
		ui.oldHeader.find('.arrow').toggleClass('open');
		ui.newHeader.find('.arrow').toggleClass('open');
        
        if(ui.oldContent.attr('id') == 'favourites' && LFWidget.is_scrolling) {
            LFWidget.force_scroll();
        }
	});
    
    if(typeof DD_belatedPNG != 'undefined') {
        DD_belatedPNG.fix("span.nav-icon");
        DD_belatedPNG.fix("span.nav-scroller");
        DD_belatedPNG.fix(".number");
        DD_belatedPNG.fix("li.movie a");
        DD_belatedPNG.fix("li.movie");
        DD_belatedPNG.fix("li.movie a .wrap");
        DD_belatedPNG.fix("li.movie a:hover .wrap");
    }
});

LFWidget$(document).ready(function(){

	// CSS HOOKS
	LFWidget$('#lf-widget li:first-child').addClass('first');
	LFWidget$('#lf-widget li:nth-child(even)').addClass('alt');
	LFWidget$('#lf-widget li:last-child').addClass('last');
	
	// SUCKERFISH
	if( LFWidget$('#lf-widget #nav > li').length ){
		LFWidget$('#lf-widget #nav > li ul').parent().addClass('parent');	
		LFWidget$('#lf-widget #nav > li').hover(
			function(){
				LFWidget$(this).addClass('sfhover')
			},
			function(){ 
				LFWidget$(this).removeClass('sfhover')
			}
		);
	}

});

var LFWidget = {
    movie_selected : 1,
    is_scrolling : 0,

    toggleDD : function(e) {
        e=e||window.event;
        var target=(typeof(e.target)=='undefined')?e.srcElement:e.target; 
        
        if(target != LFWidget$('#lf-widget .accordion div.heading span.open')[0]) {
            LFWidget$('#lf-widget .accordion div.heading span.open').parent().next().slideToggle();
            LFWidget$('#lf-widget .accordion div.heading span.open').toggleClass('open');
            LFWidget$(target).parent().next().slideToggle();
            LFWidget$(target).toggleClass('open');
        }
    },
    
    highlight_button : function(btn) {
        LFWidget$('#lf-widget span.nav-selected').removeClass('nav-selected');
        LFWidget$(btn).addClass('nav-selected');
    },

    pop_to_left : function() {
        LFWidget$('#lf-widget li.movie').each(function(i, me) {
            var current_left = LFWidget$(me).position().left;
            if(current_left >= LFWidget.page_width) {
                LFWidget$(me).css('left', (current_left - (LFWidget.film_width * LFWidget.film_count))+"px");
            }
        });
    },

    pop_to_right : function() {
        LFWidget$('#lf-widget li.movie').each(function(i, me) {
            var current_left=LFWidget$(me).position().left;
            if(current_left < -LFWidget.film_width) {
                LFWidget$(me).css('left', (current_left + (LFWidget.film_width * LFWidget.film_count))+"px");
            }
        });
    },

    scroller_go : function() {
        var direction = arguments[0],
            positional_difference = arguments[1],
            distance = arguments[2],
            distance_travelled = arguments[3],
            positions_travelled = arguments[4],
            acceleration = arguments[5],
            velocity = arguments[6],
            cutoff = arguments[7];
            
        // if we're less than 1/3 of the way, speed up
        if(distance_travelled < Math.floor((distance - (distance % 3)) / 3)) {
            velocity += acceleration;
            cutoff += velocity;
        } else {
            // if we're more than 2/3 of the way, slow down
            if((distance_travelled - velocity) > (distance - cutoff)) {
                if((distance_travelled + velocity) > distance) {
                    velocity = distance - distance_travelled;
                    
                } else {
                    velocity -= acceleration;
                }
            }
        }
        
        distance_travelled += velocity;
        
        if(positions_travelled == 0 || distance_travelled >= ((positions_travelled) * LFWidget.film_width)) {
            positions_travelled++;
            if(direction < 0) {
                LFWidget.pop_to_right();
            } else {
                LFWidget.pop_to_left();
            }
        }
        
        for(var i = 0;i < LFWidget.film_count;i++) {
            LFWidget$('#lf-widget li#movie-'+i).css('left', (LFWidget$('#lf-widget li#movie-'+i).position().left+(direction * velocity))+"px");
        }
        
        if(distance_travelled < distance) {
            LFWidget.is_scrolling = 1;
            LFWidget.scroller_timer = setTimeout("LFWidget.scroller_go.apply(null, "+(LFWidget.JSON.stringify([
                direction, positional_difference, distance, distance_travelled,
                positions_travelled, acceleration, velocity, cutoff
            ]))+")", 40);
        } else {
            LFWidget.is_scrolling = 0;
        }
        
    },
    
    force_scroll : function() {
        for(var i = 0; i < LFWidget.film_count; i++) {
            LFWidget$('#lf-widget #favourites #movie-'+i).css('left', (LFWidget.target_position + ((i - LFWidget.movie_selected + 1) * LFWidget.film_width)) + "px");
        }
        
        LFWidget.pop_to_left();
        LFWidget.pop_to_right();
        
        clearTimeout(LFWidget.scroller_timer);
        LFWidget.is_scrolling = 0;
    },

    scroll_to : function(index) {
        if(LFWidget.movie_selected==index) return;
        if(index < 1) {
            index = 1;
        }
        
        if(LFWidget.is_scrolling) {
            LFWidget.force_scroll();
        }

        var direction = -1;
        var positional_difference = index - LFWidget.movie_selected;
        if(positional_difference < 0) {
            direction = 1;
            positional_difference = -positional_difference;
        }
        
        var distance = positional_difference * LFWidget.film_width;
        LFWidget.movie_selected = index;
        LFWidget.scroller_go.apply(null, [direction, positional_difference, distance, 0, 0, 3, 0, 0]);
    },

    move_image : function(image, distance) {  
        LFWidget$('#lf-widget #favourites #'+image).find('.wrap').css('top', (2-distance)+"px");
    },

    document_loaded : function() {
        LFWidget.pop_to_left();
        LFWidget$('#lf-widget #favourites a').mousedown(function() {
        	return LFWidget.switch_link(this);
        });
        
        LFWidget$('#lf-widget #favourites a img').click(function() {
            LFWidget$(this).parent('a').
            mousedown();
            LFWidget$(this).parent('a').
            click();
            return true;
        });

        LFWidget$('#lf-widget li.movie').mouseenter(function() {
            var myid = LFWidget$(this).attr('id');
            setTimeout((function() {LFWidget.move_image(myid, 1)}), 50);
            setTimeout((function() {LFWidget.move_image(myid, 2)}), 100);
        });
        
        LFWidget$('#lf-widget li.movie').mouseleave(function() {
            var myid = LFWidget$(this).attr('id');
            setTimeout((function() {LFWidget.move_image(myid, 1)}), 50);
            setTimeout((function() {LFWidget.move_image(myid, 0)}), 100);
        });
        
        // Marketing Message
        LFWidget$('#lf-widget .header a').mousedown(function() {
        	return LFWidget.switch_link(this);
        });
        
        // Featured Title
        LFWidget$('#lf-widget #featured-title a').mousedown(function() {
        	return LFWidget.switch_link(this);
        });
        // Featured Article Title
        LFWidget$('#featured-article-title a').mousedown(function() {
        	return LFWidget.switch_link(this);
        });
    },

    scroller : function(target, positions) {
        if(positions < 1) {
            target = LFWidget$('#lf-widget .nav-icon').last();
            positions = (LFWidget.film_count - LFWidget.position_increment + 1);
            
        } else if(positions > (LFWidget.film_count - LFWidget.position_increment + 1)) {
            target = LFWidget$('#lf-widget .nav-icon').first();
            positions = 1;
        }
        
        LFWidget.highlight_button(target);
        LFWidget.scroll_to(positions);
    },
    
    switch_link : function(obj) {
        if(!obj.href.match(/^http:\/\/www\.awin1\.com/)) {
            if(!!LFWidget.affid) {
                q = '?';
                if (LFWidget.promoCode != null)
                    q += 'promotion_code='+LFWidget.promoCode+'&';
                q += 'cid=lfaffwidget-'+LFWidget.domain;
                obj.href.replace('?cid=LFAPI', '');
                obj.href = obj.href+q;
                obj.href = 'http://www.awin1.com/cread.php?awinmid=2605&awinaffid='+LFWidget.affid+'&clickref=&p='+encodeURIComponent(obj.href);
            } else {
                var q = [];
                var url = obj.href;
            
                if(obj.href.indexOf('?') != -1) {
                    var current_q = url.substring(url.indexOf('?') + 1).split('&');
                    url = url.substring(0, url.indexOf('?'));
                    
                    for(var fragment in current_q) {
                        var pair = current_q[fragment].split('=');
                        q[pair[0]] = (pair.length > 1) ? pair[1] : '';
                    }
                    
                }
                
                if (LFWidget.promoCode != null)
                    q['promotion_code'] = LFWidget.promoCode;
                    
                q['cid'] = 'lfaffwidget-' + LFWidget.domain;
                
                qstring = [];
                
                for(var name in q) {
                    qstring.push(name+'='+q[name]);
                }
                
                obj.href = url + '?' + qstring.join('&');
            } 	
        }
    }

};

if (!LFWidget.JSON) {
    LFWidget.JSON = {};
}

(function () {
    JSON = LFWidget.JSON;

    function f(n) {
        // Format integers to have at least two digits.
        return n < 10 ? '0' + n : n;
    }

    if (typeof Date.prototype.toJSON !== 'function') {

        Date.prototype.toJSON = function (key) {

            return isFinite(this.valueOf()) ?
                   this.getUTCFullYear()   + '-' +
                 f(this.getUTCMonth() + 1) + '-' +
                 f(this.getUTCDate())      + 'T' +
                 f(this.getUTCHours())     + ':' +
                 f(this.getUTCMinutes())   + ':' +
                 f(this.getUTCSeconds())   + 'Z' : null;
        };

        String.prototype.toJSON =
        Number.prototype.toJSON =
        Boolean.prototype.toJSON = function (key) {
            return this.valueOf();
        };
    }

    var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
        escapable = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
        gap,
        indent,
        meta = {    // table of character substitutions
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"' : '\\"',
            '\\': '\\\\'
        },
        rep;


    function quote(string) {

// If the string contains no control characters, no quote characters, and no
// backslash characters, then we can safely slap some quotes around it.
// Otherwise we must also replace the offending characters with safe escape
// sequences.

        escapable.lastIndex = 0;
        return escapable.test(string) ?
            '"' + string.replace(escapable, function (a) {
                var c = meta[a];
                return typeof c === 'string' ? c :
                    '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
            }) + '"' :
            '"' + string + '"';
    }


    function str(key, holder) {

// Produce a string from holder[key].

        var i,          // The loop counter.
            k,          // The member key.
            v,          // The member value.
            length,
            mind = gap,
            partial,
            value = holder[key];

// If the value has a toJSON method, call it to obtain a replacement value.

        if (value && typeof value === 'object' &&
                typeof value.toJSON === 'function') {
            value = value.toJSON(key);
        }

// If we were called with a replacer function, then call the replacer to
// obtain a replacement value.

        if (typeof rep === 'function') {
            value = rep.call(holder, key, value);
        }

// What happens next depends on the value's type.

        switch (typeof value) {
        case 'string':
            return quote(value);

        case 'number':

// JSON numbers must be finite. Encode non-finite numbers as null.

            return isFinite(value) ? String(value) : 'null';

        case 'boolean':
        case 'null':

// If the value is a boolean or null, convert it to a string. Note:
// typeof null does not produce 'null'. The case is included here in
// the remote chance that this gets fixed someday.

            return String(value);

// If the type is 'object', we might be dealing with an object or an array or
// null.

        case 'object':

// Due to a specification blunder in ECMAScript, typeof null is 'object',
// so watch out for that case.

            if (!value) {
                return 'null';
            }

// Make an array to hold the partial results of stringifying this object value.

            gap += indent;
            partial = [];

// Is the value an array?

            if (Object.prototype.toString.apply(value) === '[object Array]') {

// The value is an array. Stringify every element. Use null as a placeholder
// for non-JSON values.

                length = value.length;
                for (i = 0; i < length; i += 1) {
                    partial[i] = str(i, value) || 'null';
                }

// Join all of the elements together, separated with commas, and wrap them in
// brackets.

                v = partial.length === 0 ? '[]' :
                    gap ? '[\n' + gap +
                            partial.join(',\n' + gap) + '\n' +
                                mind + ']' :
                          '[' + partial.join(',') + ']';
                gap = mind;
                return v;
            }

// If the replacer is an array, use it to select the members to be stringified.

            if (rep && typeof rep === 'object') {
                length = rep.length;
                for (i = 0; i < length; i += 1) {
                    k = rep[i];
                    if (typeof k === 'string') {
                        v = str(k, value);
                        if (v) {
                            partial.push(quote(k) + (gap ? ': ' : ':') + v);
                        }
                    }
                }
            } else {

// Otherwise, iterate through all of the keys in the object.

                for (k in value) {
                    if (Object.hasOwnProperty.call(value, k)) {
                        v = str(k, value);
                        if (v) {
                            partial.push(quote(k) + (gap ? ': ' : ':') + v);
                        }
                    }
                }
            }

// Join all of the member texts together, separated with commas,
// and wrap them in braces.

            v = partial.length === 0 ? '{}' :
                gap ? '{\n' + gap + partial.join(',\n' + gap) + '\n' +
                        mind + '}' : '{' + partial.join(',') + '}';
            gap = mind;
            return v;
        }
    }

// If the JSON object does not yet have a stringify method, give it one.

    if (typeof JSON.stringify !== 'function') {
        JSON.stringify = function (value, replacer, space) {

// The stringify method takes a value and an optional replacer, and an optional
// space parameter, and returns a JSON text. The replacer can be a function
// that can replace values, or an array of strings that will select the keys.
// A default replacer method can be provided. Use of the space parameter can
// produce text that is more easily readable.

            var i;
            gap = '';
            indent = '';

// If the space parameter is a number, make an indent string containing that
// many spaces.

            if (typeof space === 'number') {
                for (i = 0; i < space; i += 1) {
                    indent += ' ';
                }

// If the space parameter is a string, it will be used as the indent string.

            } else if (typeof space === 'string') {
                indent = space;
            }

// If there is a replacer, it must be a function or an array.
// Otherwise, throw an error.

            rep = replacer;
            if (replacer && typeof replacer !== 'function' &&
                    (typeof replacer !== 'object' ||
                     typeof replacer.length !== 'number')) {
                throw new Error('JSON.stringify');
            }

// Make a fake root object containing our value under the key of ''.
// Return the result of stringifying the value.

            return str('', {'': value});
        };
    }


// If the JSON object does not yet have a parse method, give it one.

    if (typeof JSON.parse !== 'function') {
        JSON.parse = function (text, reviver) {

// The parse method takes a text and an optional reviver function, and returns
// a JavaScript value if the text is a valid JSON text.

            var j;

            function walk(holder, key) {

// The walk method is used to recursively walk the resulting structure so
// that modifications can be made.

                var k, v, value = holder[key];
                if (value && typeof value === 'object') {
                    for (k in value) {
                        if (Object.hasOwnProperty.call(value, k)) {
                            v = walk(value, k);
                            if (v !== undefined) {
                                value[k] = v;
                            } else {
                                delete value[k];
                            }
                        }
                    }
                }
                return reviver.call(holder, key, value);
            }


// Parsing happens in four stages. In the first stage, we replace certain
// Unicode characters with escape sequences. JavaScript handles many characters
// incorrectly, either silently deleting them, or treating them as line endings.

            text = String(text);
            cx.lastIndex = 0;
            if (cx.test(text)) {
                text = text.replace(cx, function (a) {
                    return '\\u' +
                        ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
                });
            }

// In the second stage, we run the text against regular expressions that look
// for non-JSON patterns. We are especially concerned with '()' and 'new'
// because they can cause invocation, and '=' because it can cause mutation.
// But just to be safe, we want to reject all unexpected forms.

// We split the second stage into 4 regexp operations in order to work around
// crippling inefficiencies in IE's and Safari's regexp engines. First we
// replace the JSON backslash pairs with '@' (a non-JSON character). Second, we
// replace all simple value tokens with ']' characters. Third, we delete all
// open brackets that follow a colon or comma or that begin the text. Finally,
// we look to see that the remaining characters are only whitespace or ']' or
// ',' or ':' or '{' or '}'. If that is so, then the text is safe for eval.

            if (/^[\],:{}\s]*$/
.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@')
.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']')
.replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {

// In the third stage we use the eval function to compile the text into a
// JavaScript structure. The '{' operator is subject to a syntactic ambiguity
// in JavaScript: it can begin a block or an object literal. We wrap the text
// in parens to eliminate the ambiguity.

                j = eval('(' + text + ')');

// In the optional fourth stage, we recursively walk the new structure, passing
// each name/value pair to a reviver function for possible transformation.

                return typeof reviver === 'function' ?
                    walk({'': j}, '') : j;
            }

// If the text is not JSON parseable, then a SyntaxError is thrown.

            throw new SyntaxError('JSON.parse');
        };
    }
}());
