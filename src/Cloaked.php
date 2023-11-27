<?php

namespace deemru;

class Cloaked
{
    private $cloaked;
    private $variants;

    private $cloakBytes;
    private $cloakMap;

    /**
     * Creates Cloaked instance
     */
    public function __construct()
    {
    }

    private function used( &$data )
    {
        static $sodiumZero;
        if( !isset( $sodiumZero ) )
            $sodiumZero = function_exists( 'sodium_memzero' );
        if( $sodiumZero )
            sodium_memzero( $data );
        else
        {
            $n = strlen( $data );
            for( $i = 0; $i < $n; ++$i )
                $data[$i] = chr( 0 );
            unset( $data );
        }
    }

    /**
     * Cloaks sensitive data
     *
     * @param  string $data Sensitive data
     * @param  string|null $vairant Variant of sensitive data (default: null)
     */
    public function cloak( &$data, $variant = null )
    {
        $this->cloakInit();

        $cloaked = [];
        $n = strlen( $data );
        for( $i = 0; $i < $n; ++$i )
        {
            $pointers = $this->cloakMap[ord( $data[$i] )];
            $cloaked[] = $pointers[array_rand( $pointers )];
        }

        $this->used( $data );
        if( isset( $variant ) )
            $this->variants[$variant] = $cloaked;
        else
            $this->cloaked = $cloaked;
    }

    /**
     * Uncloaks sensitive data and calls user function
     *
     * @param callable $function Function to be used with sensitive data
     * @param string|null $variant Variant of sensitive data (default: null)
     */
    public function uncloak( $function, $variant = null )
    {
        $cloaked = isset( $variant ) ? $this->variants[$variant] : $this->cloaked;
        $n = count( $cloaked );
        $data = str_pad( '', $n, ' ' );
        for( $i = 0; $i < $n; ++$i )
            $data[$i] = $this->cloakBytes[$cloaked[$i]];
        $function( $data );
        $this->used( $data );
    }

    private function cloakInit()
    {
        if( !isset( $this->cloakMap ) )
        {
            for( ;; )
            {
                $n = 131072;
                static $randomBytes;
                if( !isset( $randomBytes ) )
                    $randomBytes = function_exists( 'random_bytes' );
                if( $randomBytes )
                    $bytes = random_bytes( $n );
                else
                    for( $i = 0; $i < $n; ++$i )
                        $bytes[$i] = chr( mt_rand() );
                $map = [];
                for( $i = 0; $i < $n; ++$i )
                    $map[ord($bytes[$i])][] = $i;
                if( count( $map ) === 256 )
                    break;
            }

            $this->cloakBytes = $bytes;
            $this->cloakMap = $map;
        }
    }
}
