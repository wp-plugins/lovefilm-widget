<?php

class Lovefilm_Image_Stub
{

    /**
     * A basic constructor for Lovefilm_Image_Stub
     * copies supplied href into var
     * @param void
     * @return Lovefilm_Image_Stub constructed object
     */
    public function Lovefilm_Image_Stub($href)
    {
        $this->_href = $href;
    }
    
    /**
     * Getter for Href
     * @param void
     * @return string film image href
     */
    public function GetHref()
    {
        return $this->_href;
    }
    
    protected $_href;
    
};

class Lovefilm_Item
{

    /**
     * A basic constructor for Lovefilm_Item
     * currently doesn't really do any constructing
     * @param void
     * @return Lovefilm_Item constructed object
     */
    public function Lovefilm_Item()
    {
        
    }
    
    /**
     * Getter for Images
     * @param void
     * @return array all artworks for the film
     */
    public function GetImages()
    {
        return $this->_images;
    }
    
    /**
     * Getter for URL
     * @param void
     * @return string film url/href
     */
    public function GetUrl()
    {
        return $this->_url;
    }
    
    /**
     * Getter for Title
     * @return array title
     */
    public function GetTitle()
    {
        return $this->_title;
    }
    
    /**
     * Getter for ReleaseDate
     * @return string release year
     */
    public function GetReleaseDate()
    {
        return $this->_release_date;
    }
   
   /**
    * A stub function to generate a false Lovefilm_Item
    * @param void
    * @return Lovefilm_Item a stub item
    */
   public function Stub()
   {
        $this->_images      = array('small' => new Lovefilm_Image_Stub('/wordpress/wp-content/plugins/lovefilm/img/jsb.jpg'));
        $this->_url         = 'http://www.google.com';
        $this->_title       = 'Jay &amp; Silent Bob Strike Back';
        $this->_release_date = '2010';
   }
   
   protected $_images;
   protected $_url;
   protected $_title;
   protected $_release_date;
   
};

class Lovefilm_Catalogue
{

    /** 
     * Downloads the "our favourites" data and returns it for displaying on the widget
     * CURRENTLY A STUB
     * @param string unique identifier for the page on this blog
     * @return array an array of Lovefilm_Item objects
     */
    public static function Favourites($uniqid)
    {
        $stub = new Lovefilm_Item();
        $stub->Stub();
        return array_fill(0, 12, $stub);
    }

};

?>