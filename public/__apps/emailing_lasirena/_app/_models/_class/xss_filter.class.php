<?php

/**
 * Class for filtering the output text to prevent XSS attacks
 */
/** requires */
require_once 'log_events.class.php';

/**
 * XSS Filter Evasion Cheat Sheet
 * @filename: filter_xss.class.php
 * Location: _app/_models/_class
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 * 	20181212 RBM Created
 */
class AntiXSS
{

    /**
     * XSS filter text
     * @param string $text
     * @param string $encoding
     * @param string $whitelist_type
     * @param string $allowable_tags
     * @return string
     */
    public static function xssFilter( $text, $encoding = 'UTF-8', $whitelist_type = false, $allowable_tags = null )
    {
        if ( $text === '' )
        {
            return false;
        }

        if ( $whitelist_type !== false )
        {
            $text = self::whitelistFilter( $text, $whitelist_type );
        }

        $text = self::setEncoding( $text, $encoding );
        $text = rawurldecode( $text );
        $text = trim( $text );

        if ( $allowable_tags == null )
        {
            $text = strip_tags( $text );
            $text = htmlspecialchars( $text, ENT_QUOTES, $encoding );
        }
        else
        {
            $text = strip_tags( $text, $allowable_tags );
        }

        return $text;
    }

    /**
     * XSS filter text and echo text
     * @param string $text
     * @param string $encoding
     * @param string $whitelist_type
     * @param string $allowable_tags
     */
    public static function show( $text, $encoding = 'UTF-8', $whitelist_type = false, $allowable_tags = null )
    {
        echo self::xssFilter( $text, $encoding, $whitelist_type, $allowable_tags );
    }

    /**
     * XSS filter html
     * @param string $html
     * @param string $whitelist_tags
     * @return string
     */
    public static function htmlWhitelist( $html, $whitelist_tags = false )
    {
        if ( $whitelist_tags !== false )
        {
            $html = strip_tags( $html, $whitelist_tags );
        }
        else
        {
            $html = strip_tags( $html );
        }

        return $html;
    }

    /**
     * Setting a particular encoding to text
     * @param string $text
     * @param string $encoding
     * @return string
     */
    private static function setEncoding( $text, $encoding = 'UTF-8' )
    {
        $encoding_list = mb_list_encodings();
        $current_encoding = mb_detect_encoding( $text, $encoding_list );
        return mb_convert_encoding( $text, $encoding, $current_encoding );
    }

    /**
     * Whitelist filter (optional)
     * @param string $text
     * @param string $whitelist_type
     * @return string
     */
    private static function whitelistFilter( $text, $whitelist_type )
    {
        switch ( $whitelist_type )
        {
            case "string":
                $regex = "([-A-z áéíóúñüàèìòù]+)";
                break;
            case "number":
                $regex = "([0-9]+)";
                break;
            case "alphanumeric":
                $regex = "([-0-9A-z áéíóúñüàèìòù]+)";
                break;
			case "alphabetical_lowercase":
                $regex = "([-a-z]+)";
                break;				
            case "everything":
                $regex = "(.*)";
                break;
            case "url":
                $regex = "^https?:\/\/(.+\.)+.{2,4}(\/.*)?$";
                break;				
            default:
                $regex = "([-0-9A-z áéíóúñüàèìòù]+)";
                break;
        }

        if ( preg_match( "/^" . $regex . "$/i", $text, $matches ) > 0 )
        {
            return $text;
        }
        else
        {
            $regex = "([^" . substr( $regex, 2 );/** Everything that comes out of the regular expression is removed */
            $filtered_text = preg_replace( "/" . $regex . "/i", "", $text );
            $filtered_text = stripslashes( $filtered_text );

            //Logs
            $obj_log_event = new LogEvent();
            $obj_log_event->saveLogEvent( LogEvent::LOG_EVENT_SECURITY, 'Filtrado ataque XSS: ' . $text . ' -> ' . $filtered_text, 0 );

            return $filtered_text;
        }
    }

}
