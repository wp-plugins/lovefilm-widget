LFWidget$(document).ready(function($){
    var reviews = $('#lf-widget #featured-title .featured-title-review');
    
    reviews.find('.wrap').each(function(){
        var self = $(this);
        
        self
            .mouseover(function(){
                self.find('.rental').show();
            })
            .mouseout(function(){
                self.find('.rental').hide();
            });
    });
    
    reviews.find('.featured-title-description').each(function(){
        var debuglf = false;
        var self = $(this);
        
        if(debuglf)
        alert("description");
        
        // IE6 cannot read the original css value for some strange reason
        // var css_height = parseInt(self.css('height'), 10) + parseInt(self.css('padding-top'), 10) + parseInt(self.css('padding-bottom'), 10);
        var css_height = 145;
        
        var p = self.find('p');
        
        self.css('height', 'auto');
        var actual_height = self.outerHeight();
        
        if(debuglf)
        alert(actual_height + " > " + css_height);
        
        if(actual_height > css_height) {
            var diff = actual_height - css_height;
            var pheight = p.outerHeight();
            var lheight = parseInt(p.css('line-height'), 10);
            
            // If we can simply reduce the height of the paragraph...
            if(pheight >= diff) {
                var max_pheight = css_height - (actual_height - pheight);
                var chars_per_line = p.text().length / Math.floor(pheight / lheight);
                var lines_allowed = Math.floor(max_pheight / lheight);
                var new_text = p.text().replace(/^\s\s*/, '').replace(/\s\s*$/, '').substr(0, (((lines_allowed - 2) * chars_per_line)));
                
                if(debuglf)
                alert(lines_allowed + " lines");
                
                // Only reduce to nearest word if there are multiple words (will only be untrue when length = 0 or long word)
                if(lines_allowed == 0 || new_text.indexOf(' ') == -1) {
                    p.hide();
                    self.find('.ellipsis').hide();
                    
                } else {
                    new_text = new_text.match(/^(.*)\s+(\S+)?$/)[1];
                    
                    // Hide the text if it does not turn out to be a reasonable length
                    if(new_text.length < 15) {
                        p.hide();
                        self.find('.ellipsis').hide();
                        
                    } else {
                        p.html(new_text);
                    }
                }
                
            } else {
                p.hide();
                self.find('.ellipsis').hide();
                self.find('dl').hide();
            }
        }
    });
});