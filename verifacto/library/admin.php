<?php

// Allow queries of non-published content
// add_action('json_api_query_args', 'my_query_args');

// function my_query_args($args) {
//   $args['post_status'] = array('draft', 'future', 'publish');
// }

/**
 * Plugin Name: JSON API - Custom fields support for the create_post method
 * Version:     0.0.1
 * Author:      Birgir Erlendsson (birgire)
 * Author URI:  https://github.com/birgire
 * Plugin URI:  https://gist.github.com/birgire/02dfadbcb0ddc3fdb064
 * Description: Extension to the JSON API plugin, to enable custom fields to be added with the "create_post" method
 * Licence:     GPLv2+
 */

/*  Copyright 2014 Birgir Erlendsson (birgire)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * By JSON API plugin, we mean:
 *     http://wordpress.org/plugins/json-api/
 *
 * The problem this plugin is trying to solve:
 *     How to add custom post meta data (custom fields) when you create a post with the "create_post" method?
 *
 * Solution:
 *     Use the filters:
 *        - json_api-posts-create_post
 *        - wp_insert_post
 *        - json_api_encode
 *     to adjust the custom_fields GET parameter so it understands JSON strings. 
 *     We sanitize and then update the custom fields with the update_post_meta() function.  
 * 
 * Usage Example:
 *
 *     http://example.com/api/create_post/
 *     ?title=Foo
 *     &content=Bar
 *     &nonce=1234567890
 *     &status=draft
 *     &custom_fields={"foo1":"bar1","foo2":{"a":"1","b":"2"}} 
 *
 *   where the "custom_fields" query variable is a JSON string, to allow custom fields with arrays.  
 */
   
/**
 * Init:
 */

add_action( 'wp', 'customized_json_api' );
  
function customized_json_api()
{
    if( class_exists( 'JSON_API_CreatePostWithCustomFields' ) )
    {
        $obj = new JSON_API_CreatePostWithCustomFields;
        $obj->init();
    }
}    
 

if( ! class_exists( 'JSON_API_CreatePostWithCustomFields' ) )
{
    
    /**
     * Class JSON_API_CreatePostWithCustomFields
     */
 
    class JSON_API_CreatePostWithCustomFields
    {
        private $updated_fields = array();
      
        public function init()
        {
            add_action( 'json_api-posts-create_post', array( $this, 'create_post_action' ) );
        }
      
        public function create_post_action()
        {
            add_action( 'wp_insert_post', array( $this, 'wp_insert_post' ) );
        }
      
        private function get_custom_fields()
        {
            foreach( array( INPUT_GET, INPUT_POST ) as $method )
                if( $custom_fields = filter_input( $method, 'custom_fields' ) )
                    return json_decode( $custom_fields, true );
              
            return null;
        }
          
        public function wp_insert_post( $post_ID )
        {
            if( $post_ID > 0 )
            {
                if( $fields = $this->get_custom_fields() )
                {
                    // Update the custom fields:
                    $this->updated_fields = array(); 
                    foreach( $fields as $key => $value )
                    {
                        $key   = sanitize_key( $key );
                        $value = sanitize_meta( $key, $value, 'post' );
                  
                        if( update_post_meta( $post_ID, $key, $value, is_array( $value ) ) )
                            $this->updated_fields[$key] = $value;
                    }
                
                    // Modify the JSON_API response:
                    add_filter( 'json_api_encode', array( $this, 'json_api_encode' ) );
                }
            }
        }
          
        public function json_api_encode( $data )
        {
            // Add our updated custom fields to the JSON response:
            if( isset( $data['post'] ) )
                $data['post']->custom_fields = $this->updated_fields;
        
            return $data; 
        }
        
    } // end class
}// end if class exists