<?php
/**
 * Autoload namespaces, class names and class file names must contain uppercase first letter.
 */

spl_autoload_register(
/**
 * @param $class
 * @return bool
 */
    function( $class )
    {
        $path = APPLICATION_PATH . \app\core\Library::path( $class . '.php', '\\' );
        if( is_file( $path ) ):
            require_once( $path );
            return( true );
        endif;
        return( false );
    },
    false
);