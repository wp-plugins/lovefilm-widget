(function(){
    tinymce.create('tinymce.plugins.lf_button',{
        init:function(ed,url){
            this.editor = ed;
     
            ed.addButton('lf_button',{
                title:'LOVEFiLM: Please select the text and click this button to a) add a product to the LOVEFiLM widget and b) insert a link between that product and the selected text. For wordpress 2.9 users only use the button after publishing your post.',
                image:url+'/lovefilm.jpg',
                onclick:function(){
                    var search_text = "";
                    jQuery(".lf-contextual-lightbox").trigger('click');
                    jQuery('#lf-contextual-results-popup').html("");
                    jQuery('#lf-contextual-SearchMode-popup').val('film');
                    jQuery('#lf-contextual-selection .lf-contextual-menu-top').html("");
                    currentSelection = ed.selection.getSel();
                    
                    if(!(ed.selection.isCollapsed()))
                         {
                             if(currentSelection.getRangeAt)
                             {
                                 search_text = jQuery.trim(currentSelection.getRangeAt(0).toString());
                             }
                            else
                            {
                                 search_text =(jQuery.trim((ed.selection.getContent({ format : 'text'}))));
                            }
                         }
                         
                         /* This the chrome fix to avoid the special characters form the search text */
                         var rep = new RegExp(String.fromCharCode(65279), 'g');
                         search_text = search_text.replace(rep, '');
                         
                         
                    /** If the selection is not empty then allow it for the IE fix.
                     */     
                    var IEcheck = 0;
                    if (ed.selection.getContent() != "") {
                        /** This is the fix for IE where it loose the focus while switching betweent the editor and the pop up window. */
     
                        IEcheck = 1;
                        var bm = ed.selection.getBookmark();   
                    }
                   
                   
                    
                    /**
                     *  Search box to search the given title by the default mode as filims.
                     */                   
                    jQuery('#lf-contextual-SearchText-popup').val(search_text);
                    jQuery('#lf-contextual-loading_popup').css('display','inline');
                    var data={
                        action:'search_action',
                        searchmode:"film",
                        searchtext:search_text,
                        lf_post_id:(jQuery('#lf_post_id').val())
                    };
                    /**
                     *  Return the result of the given response.
                     */
                   jQuery.post(ajaxurl,data,function(response){
                        var response_result=unescape(response);
                        jQuery('#lf-contextual-results-popup').html(response_result);
                        jQuery('#lf-contextual-loading_popup').css('display','none');
                        
                    });
                    //close the pop up when clicked on pop up close button
                    jQuery('.lf-contextual-close').click(function(){
                        close_box()
                    });
                    //close the pop up when clicked on pop up gray area
                    jQuery('.lf-contextual-backdrop').click(function(){
                        close_box()
                    });
                     
                   //popup_closeme is used to add the title and close the popup by adding the selected title to the featured title.
                        
                    jQuery('.lf-contextual-popup_closeme').live('click', function() {
                       
                       /** if IE is used the do this */
                        
                       if(IEcheck == 1)
                           {
                               ed.selection.moveToBookmark(bm);
                           }
                           
                      
                        if(ed.selection.getContent()){
                            var title =jQuery('#lf-contextual-selection .lf-contextual-menu-top h4').html();
                            var link=jQuery('#lf-contextual-selection .lf-contextual-menu-top').attr('href');
                            
                            if(!link)
                            {
                                link = jQuery('.lf-contextual-film-selected .lf-contextual-film-title').attr('href');                                
                            }
                           
                            if(link){
                                if(!(ed.selection.isCollapsed()))
                                { 
                                    
                                    
                                    if(currentSelection.getRangeAt)
                                    {          
                                        ed.selection.setContent('<a href="'+link+'" target="_blank" class="featured-article-title-inpost" >'+ (currentSelection.getRangeAt(0).toString()) +'</a>')
                                    }
                                    else
                                    {
                                       
                                        ed.selection.setContent('<a href="'+link+'" target="_blank" class="featured-article-title-inpost" >'+ (ed.selection.getContent({ format : 'text'})) +'</a>')
                                    }
                                }
                            }
                        }
                        
                        jQuery('.lf-contextual-backdrop, .lf-contextual-box').fadeOut();
                    });
                    /**
                     * Closes the pop up box.
                     */
                    function close_box()
                    {
                        jQuery('.lf-contextual-backdrop, .lf-contextual-box').fadeOut();
                    }
                  
                }//End of onclick function
            })
        },
        createControl:function(n,cm){
            return null
        },
        getInfo:function(){
            return{
                longname:"Lovefilm",
                author:'Stickeyes',
                authorurl:'http://stickeyeys.com/',
                infourl:'http://lovefilm.com/'
            }
        }
    });
    tinymce.PluginManager.add('lf_button',tinymce.plugins.lf_button);
})();